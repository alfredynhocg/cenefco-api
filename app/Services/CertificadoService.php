<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class CertificadoService
{
    private const FONT_PATH = '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf';

    private ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    public function generarLote(int $imparteId, int $plantillaId): array
    {
        $plantilla = DB::table('t_cert_plantilla')->find($plantillaId);
        if (! $plantilla) {
            throw new \RuntimeException('Plantilla no encontrada.');
        }

        $campos = DB::table('t_cert_plantilla_campo')
            ->where('plantilla_id', $plantillaId)
            ->where('activo', true)
            ->orderBy('orden')
            ->get();

        $imparte = DB::table('t_imparte as i')
            ->leftJoin('t_materia as m', 'i.id_mat', '=', 'm.id_mat')
            ->select('i.*', 'm.nombre as nombre_materia')
            ->where('i.id_imp', $imparteId)
            ->where('i.id_us_reg', 0)
            ->first();

        if (! $imparte) {
            throw new \RuntimeException('Apertura de curso no encontrada (id_imp='.$imparteId.').');
        }

        // Incluye registros "pendiente" O registros "generado"/"aprobado" sin certificado emitido
        $aprobados = DB::table('t_lista_aprobados as la')
            ->join('t_usuario as u', function ($join) {
                $join->on('la.usuario_id', '=', 'u.id_us')
                    ->where('u.id_us_reg', 0);
            })
            ->select('la.*', 'u.nombre', 'u.appaterno', 'u.apmaterno', 'u.email', 'u.ci')
            ->where('la.imparte_id', $imparteId)
            ->where('la.condicion', 'aprobado')
            ->whereNotIn('la.id', DB::table('t_certificado')->pluck('lista_aprobado_id'))
            ->get();

        $generados = [];
        $errores = [];

        foreach ($aprobados as $aprobado) {
            try {
                $cert = DB::transaction(fn () => $this->generarCertificado($aprobado, $plantilla, $campos, $imparte));
                $generados[] = $cert;
            } catch (\Throwable $e) {
                $errores[] = [
                    'usuario_id' => $aprobado->usuario_id,
                    'nombre'     => trim(($aprobado->appaterno ?? '').' '.($aprobado->nombre ?? '')),
                    'error'      => $e->getMessage(),
                ];
            }
        }

        return [
            'generados' => count($generados),
            'total_aprobados' => $aprobados->count(),
            'errores' => $errores,
            'certificados' => $generados,
        ];
    }

    private function generarCertificado(
        object $aprobado,
        object $plantilla,
        $campos,
        object $imparte
    ): object {
        $codigo = $this->generarCodigo();

        $nombreCompleto = trim(implode(' ', array_filter([
            $aprobado->appaterno,
            $aprobado->apmaterno,
            $aprobado->nombre,
        ])));

        $programaNombre = $imparte->titulo_personalizado ?: ($imparte->nombre_materia ?: '');
        $verifyUrl = rtrim(config('app.url'), '/').'/verificar/'.$codigo;

        $bgPath = $this->resolveStoragePath($plantilla->imagen_url);
        if (! file_exists($bgPath)) {
            throw new \RuntimeException("Imagen de plantilla no encontrada: {$plantilla->imagen_url}");
        }

        $image = $this->manager->read($bgPath);
        $ancho = (int) $plantilla->ancho_px;
        $alto = (int) $plantilla->alto_px;

        $valores = [
            'nombre_completo' => mb_strtoupper($nombreCompleto),
            'nombre' => $aprobado->nombre ?? '',
            'apellidos' => trim(($aprobado->appaterno ?? '').' '.($aprobado->apmaterno ?? '')),
            'programa' => mb_strtoupper($programaNombre),
            'condicion' => $aprobado->condicion ?? 'APROBADO',
            'nota' => $aprobado->nota_final !== null ? number_format((float) $aprobado->nota_final, 2) : '',
            'fecha_inicio' => $imparte->imparte_fecha_inicio ?? '',
            'fecha_fin' => $imparte->imparte_fecha_fin ?? '',
            'codigo' => $codigo,
            'ci' => $aprobado->ci ?? '',
        ];

        $dir = 'certificados/'.$imparte->id_imp;
        Storage::disk('public')->makeDirectory($dir);

        $qrFilename = $codigo.'_qr.png';
        $qrSavePath = Storage::disk('public')->path($dir.'/'.$qrFilename);
        $qr = QrCode::create($verifyUrl)->setSize(300)->setMargin(4);
        $qrPng = (new PngWriter)->write($qr)->getString();
        file_put_contents($qrSavePath, $qrPng);
        $qrUrl = '/storage/'.$dir.'/'.$qrFilename;

        foreach ($campos as $campo) {
            $x = (int) round((float) $campo->pos_x_pct / 100 * $ancho);
            $y = (int) round((float) $campo->pos_y_pct / 100 * $alto);

            if ($campo->tipo === 'imagen' || $campo->clave === 'qr') {
                $qrSize = $campo->ancho_pct
                    ? (int) round((float) $campo->ancho_pct / 100 * $ancho)
                    : 300;

                $tempQr = tempnam(sys_get_temp_dir(), 'qr_').'.png';
                $qrT = QrCode::create($verifyUrl)->setSize($qrSize)->setMargin(4);
                $qrPngT = (new PngWriter)->write($qrT)->getString();
                file_put_contents($tempQr, $qrPngT);
                $image->place($tempQr, 'top-left', $x, $y);
                @unlink($tempQr);
            } else {
                $texto = $campo->valor_fijo ?: ($valores[$campo->clave] ?? '');
                if ((string) $texto === '') {
                    continue;
                }

                $texto = match ($campo->mayusculas) {
                    'upper' => mb_strtoupper((string) $texto),
                    'lower' => mb_strtolower((string) $texto),
                    default => (string) $texto,
                };

                $fontSize = (int) $campo->tamano_pt;
                $color = $campo->color ?: ($plantilla->color_default ?? '#000000');
                $align = $campo->alineacion ?: 'center';
                $fontFile = file_exists((string) ($campo->fuente ?? ''))
                    ? $campo->fuente
                    : self::FONT_PATH;

                $image->text($texto, $x, $y, function ($font) use ($fontFile, $fontSize, $color, $align) {
                    $font->filename($fontFile);
                    $font->size($fontSize);
                    $font->color($color);
                    $font->align($align);
                    $font->valign('middle');
                });
            }
        }

        $jpgData = $image->toJpeg(quality: (int) ($plantilla->calidad_jpg ?? 85));
        $b64Image = base64_encode((string) $jpgData);

        $widthPt = round($ancho * 0.75);
        $heightPt = round($alto * 0.75);

        $html = <<<HTML
            <!DOCTYPE html>
            <html>
            <head>
            <style>
            * { margin:0; padding:0; }
            body { width:{$widthPt}pt; height:{$heightPt}pt; overflow:hidden; }
            img  { width:100%; height:100%; display:block; }
            </style>
            </head>
            <body>
            <img src="data:image/jpeg;base64,{$b64Image}" />
            </body>
            </html>
            HTML;

        $pdf = Pdf::loadHTML($html)
            ->setPaper([0, 0, $widthPt, $heightPt])
            ->setOptions(['dpi' => 96, 'isRemoteEnabled' => false, 'isHtml5ParserEnabled' => true]);

        $pdfFilename = $codigo.'.pdf';
        $pdfSavePath = Storage::disk('public')->path($dir.'/'.$pdfFilename);
        file_put_contents($pdfSavePath, $pdf->output());
        $archivoUrl = '/storage/'.$dir.'/'.$pdfFilename;

        $previewPath = Storage::disk('public')->path($dir.'/'.$codigo.'_preview.jpg');
        $image->scale(width: 800)->save($previewPath, quality: 70);
        $previewUrl = '/storage/'.$dir.'/'.$codigo.'_preview.jpg';

        $certId = DB::table('t_certificado')->insertGetId([
            'lista_aprobado_id' => $aprobado->id,
            'plantilla_id' => $plantilla->id,
            'usuario_id' => $aprobado->usuario_id,
            'imparte_id' => $aprobado->imparte_id,
            'nombre_en_certificado' => $nombreCompleto,
            'programa_en_certificado' => $programaNombre,
            'condicion' => $aprobado->condicion ?? 'aprobado',
            'nota_final' => $aprobado->nota_final,
            'fecha_inicio_curso' => $imparte->imparte_fecha_inicio ?? null,
            'fecha_fin_curso' => $imparte->imparte_fecha_fin ?? null,
            'codigo_verificacion' => $codigo,
            'qr_url' => $qrUrl,
            'archivo_url' => $archivoUrl,
            'estado' => 'generado',
            'id_us_reg' => 0,
            'created_at' => now(),
        ]);

        DB::table('t_lista_aprobados')
            ->where('id', $aprobado->id)
            ->update(['estado_certificado' => 'generado', 'updated_at' => now()]);

        return DB::table('t_certificado')->find($certId);
    }

    private function generarCodigo(): string
    {
        $year = now()->year;
        do {
            $random = strtoupper(Str::random(6));
            $codigo = "cenefco-{$year}-{$random}";
        } while (DB::table('t_certificado')->where('codigo_verificacion', $codigo)->exists());

        return $codigo;
    }

    private function resolveStoragePath(string $url): string
    {
        $relative = ltrim(str_replace('/storage/', '', $url), '/');

        return Storage::disk('public')->path($relative);
    }
}
