<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('web_preinscripcion', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('programa_id')->nullable();
            $table->unsignedInteger('imparte_id')->nullable();
            $table->string('nombre', 200);
            $table->string('email', 100);
            $table->string('telefono', 20)->nullable();
            $table->string('ciudad', 120)->nullable();
            $table->string('profesion', 200)->nullable();
            $table->text('mensaje')->nullable();
            $table->boolean('notificado')->default(false);
            $table->timestampTz('fecha_notificacion')->nullable();
            $table->string('estado', 50)->default('pendiente');
            $table->string('origen', 100)->nullable();
            $table->string('utm_source', 100)->nullable();
            $table->string('utm_medium', 100)->nullable();
            $table->string('utm_campaign', 100)->nullable();
            $table->string('ip_origen', 45)->nullable();
            $table->timestampTz('created_at')->nullable()->useCurrent();
            $table->timestampTz('updated_at')->nullable();

            $table->index('email');
            $table->index('programa_id');
            $table->index('estado');
            $table->index('notificado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('web_preinscripcion');
    }
};
