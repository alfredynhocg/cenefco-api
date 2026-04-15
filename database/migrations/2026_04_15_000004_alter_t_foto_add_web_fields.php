<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('t_foto', function (Blueprint $table) {
            $table->string('alt', 255)->nullable()->after('foto');
            $table->integer('orden')->default(0)->after('alt');
            $table->boolean('destacada')->default(false)->after('orden');
            $table->unsignedBigInteger('galeria_categoria_id')->nullable()->after('destacada');
        });
    }

    public function down(): void
    {
        Schema::table('t_foto', function (Blueprint $table) {
            $table->dropColumn([
                'alt',
                'orden',
                'destacada',
                'galeria_categoria_id',
            ]);
        });
    }
};
