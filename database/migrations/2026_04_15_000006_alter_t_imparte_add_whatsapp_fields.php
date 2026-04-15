<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_imparte', function (Blueprint $table) {
            $table->string('whatsapp_grupo_url', 500)->nullable()->after('inscripcion_auto');
            $table->string('whatsapp_grupo_nombre', 200)->nullable()->after('whatsapp_grupo_url');
            $table->boolean('whatsapp_grupo_activo')->default(false)->after('whatsapp_grupo_nombre');
            $table->timestampTz('whatsapp_grupo_expira')->nullable()->after('whatsapp_grupo_activo');
        });
    }

    public function down(): void
    {
        Schema::table('t_imparte', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_grupo_url',
                'whatsapp_grupo_nombre',
                'whatsapp_grupo_activo',
                'whatsapp_grupo_expira',
            ]);
        });
    }
};
