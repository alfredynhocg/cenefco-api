<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_articulo', function (Blueprint $table) {
            $table->string('slug', 300)->nullable()->unique()->after('titulo');
            $table->string('entradilla', 500)->nullable()->after('slug');
            $table->string('imagen_principal_url', 255)->nullable()->after('contenido');
            $table->string('imagen_alt', 255)->nullable()->after('imagen_principal_url');
            $table->boolean('destacada')->default(false)->after('imagen_alt');
            $table->timestampTz('fecha_publicacion')->nullable()->after('destacada');
            $table->integer('vistas')->default(0)->after('fecha_publicacion');
            $table->string('meta_titulo', 300)->nullable()->after('vistas');
            $table->string('meta_descripcion', 500)->nullable()->after('meta_titulo');
            $table->string('estado_web', 50)->default('borrador')->after('meta_descripcion');
            // borrador | publicado | archivado
            $table->timestampTz('updated_at')->nullable()->after('fecha_reg');
            $table->timestampTz('deleted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('t_articulo', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'entradilla',
                'imagen_principal_url',
                'imagen_alt',
                'destacada',
                'fecha_publicacion',
                'vistas',
                'meta_titulo',
                'meta_descripcion',
                'estado_web',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};
