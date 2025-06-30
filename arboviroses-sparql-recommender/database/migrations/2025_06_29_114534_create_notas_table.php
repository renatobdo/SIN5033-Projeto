<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('recurso_uri'); // URI RDF como string
            $table->tinyInteger('nota'); // 1 a 5
            $table->timestamps();

            $table->unique(['user_id', 'recurso_uri']); // evita duplicatas
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notas');
    }
};
