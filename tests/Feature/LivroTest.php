<?php

namespace Tests\Feature;

use App\Models\Assunto;
use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LivroTest extends TestCase
{
    use RefreshDatabase;

    private function criarLivro(array $attrs = []): Livro
    {
        return Livro::create(array_merge([
            'titulo' => 'Dom Casmurro',
            'editora' => 'Ática',
            'edicao' => 1,
            'ano_publicacao' => '1899',
            'valor' => 49.90,
        ], $attrs));
    }

    public function test_index_retorna_pagina_de_livros(): void
    {
        $this->criarLivro(['titulo' => 'Livro A']);
        $this->criarLivro(['titulo' => 'Livro B']);

        $resp = $this->getJson('/api/livros')->assertOk();

        $resp->assertJsonStructure(['data', 'meta' => ['current_page', 'last_page', 'per_page', 'total']]);
        $this->assertCount(2, $resp->json('data'));
    }

    public function test_index_retorna_todos_com_all_true(): void
    {
        $this->criarLivro(['titulo' => 'L1']);
        $this->criarLivro(['titulo' => 'L2']);

        $resp = $this->getJson('/api/livros?all=true')->assertOk();

        $this->assertCount(2, $resp->json());
    }

    public function test_index_pagina_com_per_page(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->criarLivro(['titulo' => "Livro $i"]);
        }

        $resp = $this->getJson('/api/livros?per_page=5')->assertOk();

        $this->assertCount(5, $resp->json('data'));
        $this->assertEquals(5, $resp->json('meta.per_page'));
        $this->assertEquals(10, $resp->json('meta.total'));
    }

    public function test_store_cria_livro_simples(): void
    {
        $this->postJson('/api/livros', [
            'titulo' => 'Iracema',
            'editora' => 'José de Alencar',
            'edicao' => 1,
            'ano_publicacao' => '1865',
            'valor' => 35.00,
        ])
            ->assertCreated()
            ->assertJsonPath('titulo', 'Iracema');

        $this->assertDatabaseHas('livros', ['titulo' => 'Iracema']);
    }

    public function test_store_cria_livro_com_autores_e_assuntos(): void
    {
        $autor = Autor::create(['nome' => 'José de Alencar']);
        $assunto = Assunto::create(['descricao' => 'Romance']);

        $resp = $this->postJson('/api/livros', [
            'titulo' => 'Iracema',
            'valor' => 35.00,
            'autor_ids' => [$autor->cod_au],
            'assunto_ids' => [$assunto->cod_as],
        ])->assertCreated();

        $this->assertDatabaseHas('livro_autor', [
            'livro_codl' => $resp->json('codl'),
            'autor_cod_au' => $autor->cod_au,
        ]);
        $this->assertDatabaseHas('livro_assunto', [
            'livro_codl' => $resp->json('codl'),
            'assunto_cod_as' => $assunto->cod_as,
        ]);
    }

    public function test_store_valida_titulo_obrigatorio(): void
    {
        $this->postJson('/api/livros', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['titulo']]);
    }

    public function test_store_valida_ano_publicacao_formato(): void
    {
        $this->postJson('/api/livros', ['titulo' => 'T', 'ano_publicacao' => 'invalido'])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['ano_publicacao']]);
    }

    public function test_store_valida_autor_ids_existentes(): void
    {
        $this->postJson('/api/livros', ['titulo' => 'T', 'autor_ids' => [9999]])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['autor_ids.0']]);
    }

    public function test_show_retorna_livro_com_relacionamentos(): void
    {
        $livro = $this->criarLivro();
        $autor = Autor::create(['nome' => 'Machado de Assis']);
        $livro->autores()->attach($autor->cod_au);

        $resp = $this->getJson("/api/livros/{$livro->codl}")->assertOk();

        $resp->assertJsonPath('titulo', $livro->titulo);
        $resp->assertJsonStructure(['autores', 'assuntos']);
    }

    public function test_show_retorna_404_para_inexistente(): void
    {
        $this->getJson('/api/livros/9999')->assertNotFound();
    }

    public function test_update_altera_livro(): void
    {
        $livro = $this->criarLivro(['titulo' => 'Título Antigo']);

        $this->putJson("/api/livros/{$livro->codl}", ['titulo' => 'Título Novo'])
            ->assertOk()
            ->assertJsonPath('titulo', 'Título Novo');

        $this->assertDatabaseHas('livros', ['titulo' => 'Título Novo']);
    }

    public function test_update_sincroniza_autores(): void
    {
        $livro = $this->criarLivro();
        $a1 = Autor::create(['nome' => 'Autor 1']);
        $a2 = Autor::create(['nome' => 'Autor 2']);
        $livro->autores()->attach($a1->cod_au);

        $this->putJson("/api/livros/{$livro->codl}", ['autor_ids' => [$a2->cod_au]])->assertOk();

        $this->assertDatabaseMissing('livro_autor', ['livro_codl' => $livro->codl, 'autor_cod_au' => $a1->cod_au]);
        $this->assertDatabaseHas('livro_autor', ['livro_codl' => $livro->codl, 'autor_cod_au' => $a2->cod_au]);
    }

    public function test_update_retorna_404_para_inexistente(): void
    {
        $this->putJson('/api/livros/9999', ['titulo' => 'X'])->assertNotFound();
    }

    public function test_destroy_remove_livro(): void
    {
        $livro = $this->criarLivro();

        $this->deleteJson("/api/livros/{$livro->codl}")->assertNoContent();

        $this->assertDatabaseMissing('livros', ['codl' => $livro->codl]);
    }

    public function test_destroy_retorna_404_para_inexistente(): void
    {
        $this->deleteJson('/api/livros/9999')->assertNotFound();
    }

    public function test_destroy_remove_pivot_em_cascata(): void
    {
        $livro = $this->criarLivro();
        $autor = Autor::create(['nome' => 'Autor']);
        $livro->autores()->attach($autor->cod_au);

        $this->deleteJson("/api/livros/{$livro->codl}")->assertNoContent();

        $this->assertDatabaseMissing('livro_autor', ['livro_codl' => $livro->codl]);
    }
}
