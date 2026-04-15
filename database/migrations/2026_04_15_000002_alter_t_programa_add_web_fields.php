<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_programa', function (Blueprint $table) {
            $table->string('slug', 300)->nullable()->unique()->after('nombre_programa');
            $table->string('imagen_banner_url', 255)->nullable()->after('foto');
            $table->string('imagen_alt', 255)->nullable()->after('imagen_banner_url');
            $table->boolean('destacado')->default(false)->after('imagen_alt');
            $table->integer('vistas')->default(0)->after('destacado');
            $table->integer('orden')->default(0)->after('vistas');
            $table->unsignedBigInteger('categoria_web_id')->nullable()->after('orden');
            $table->string('meta_titulo', 300)->nullable()->after('categoria_web_id');
            $table->string('meta_descripcion', 500)->nullable()->after('meta_titulo');
            $table->string('estado_web', 50)->default('borrador')->after('meta_descripcion');
            $table->timestampTz('fecha_publicacion')->nullable()->after('estado_web');
            $table->timestampTz('updated_at')->nullable()->after('fecha_reg');
            $table->timestampTz('deleted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('t_programa', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'imagen_banner_url',
                'imagen_alt',
                'destacado',
                'vistas',
                'orden',
                'categoria_web_id',
                'meta_titulo',
                'meta_descripcion',
                'estado_web',
                'fecha_publicacion',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};
