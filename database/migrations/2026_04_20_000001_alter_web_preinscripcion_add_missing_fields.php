<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('web_preinscripcion', function (Blueprint $table) {
            $table->date('fecha_nacimiento')->nullable()->after('email');
            $table->string('archivo_cv', 255)->nullable()->after('archivo_titulo');
            $table->string('archivo_foto_3x3', 255)->nullable()->after('archivo_cv');
        });
    }

    public function down(): void
    {
        Schema::table('web_preinscripcion', function (Blueprint $table) {
            $table->dropColumn(['fecha_nacimiento', 'archivo_cv', 'archivo_foto_3x3']);
        });
    }
};
