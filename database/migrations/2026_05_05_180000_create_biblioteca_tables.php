<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('livros', function (Blueprint $table) {
            $table->unsignedInteger('codl')->autoIncrement();
            $table->string('titulo', 255);
            $table->string('editora', 100)->nullable();
            $table->unsignedInteger('edicao')->nullable();
            $table->string('ano_publicacao', 4)->nullable();
            $table->decimal('valor', 12, 2)->default(0);
            $table->string('thumbnail', 500)->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });

        Schema::create('autores', function (Blueprint $table) {
            $table->unsignedInteger('cod_au')->autoIncrement();
            $table->string('nome', 100);
            $table->timestamps();
        });

        Schema::create('assuntos', function (Blueprint $table) {
            $table->unsignedInteger('cod_as')->autoIncrement();
            $table->string('descricao', 100);
            $table->timestamps();
        });

        Schema::create('livro_autor', function (Blueprint $table) {
            $table->unsignedInteger('livro_codl');
            $table->unsignedInteger('autor_cod_au');
            $table->primary(['livro_codl', 'autor_cod_au']);
            $table->foreign('livro_codl')->references('codl')->on('livros')->cascadeOnDelete();
            $table->foreign('autor_cod_au')->references('cod_au')->on('autores')->cascadeOnDelete();
        });

        Schema::create('livro_assunto', function (Blueprint $table) {
            $table->unsignedInteger('livro_codl');
            $table->unsignedInteger('assunto_cod_as');
            $table->primary(['livro_codl', 'assunto_cod_as']);
            $table->foreign('livro_codl')->references('codl')->on('livros')->cascadeOnDelete();
            $table->foreign('assunto_cod_as')->references('cod_as')->on('assuntos')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('livro_assunto');
        Schema::dropIfExists('livro_autor');
        Schema::dropIfExists('assuntos');
        Schema::dropIfExists('autores');
        Schema::dropIfExists('livros');
    }
};
