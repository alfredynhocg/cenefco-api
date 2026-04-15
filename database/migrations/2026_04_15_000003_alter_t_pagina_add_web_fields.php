<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_pagina', function (Blueprint $table) {
            $table->string('slug', 300)->nullable()->unique()->after('titulo_pagina');
            $table->longText('contenido_html')->nullable()->after('slug');
            $table->string('imagen_og_url', 255)->nullable()->after('contenido_html');
            $table->string('template', 100)->nullable()->after('imagen_og_url');
            $table->string('meta_titulo', 300)->nullable()->after('template');
            $table->string('meta_descripcion', 500)->nullable()->after('meta_titulo');
            $table->boolean('indexar')->default(true)->after('meta_descripcion');
            $table->integer('orden')->default(0)->after('indexar');
            $table->timestampTz('updated_at')->nullable()->after('fecha_reg');
            $table->timestampTz('deleted_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('t_pagina', function (Blueprint $table) {
            $table->dropColumn([
                'slug',
                'contenido_html',
                'imagen_og_url',
                'template',
                'meta_titulo',
                'meta_descripcion',
                'indexar',
                'orden',
                'updated_at',
                'deleted_at',
            ]);
        });
    }
};
