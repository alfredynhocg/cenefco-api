<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_boletin', function (Blueprint $table) {
            $table->string('slug', 300)->nullable()->unique()->after('titulo_boletin');
            $table->string('imagen_url', 255)->nullable()->after('descripcion_boletin');
            $table->timestampTz('fecha_publicacion')->nullable()->after('imagen_url');
            $table->string('meta_titulo', 300)->nullable()->after('fecha_publicacion');
            $table->string('meta_descripcion', 500)->nullable()->after('meta_titulo');
            $table->integer('vistas')->default(0)->after('meta_descripcion');
            $table->timestampTz('updated_at')->nullable()->after('fecha_reg');
            $table->timestampTz('deleted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('t_boletin', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'imagen_url',
                'fecha_publicacion',
                'meta_titulo',
                'meta_descripcion',
                'vistas',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};
