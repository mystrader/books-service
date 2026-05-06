<?php

namespace Tests\Feature;

use App\Models\Assunto;
use App\Models\Autor;
use App\Models\Livro;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatorioTest extends TestCase
{
    use RefreshDatabase;

    public function test_livros_por_autor_retorna_estrutura_correta(): void
    {
        $this->getJson('/api/relatorios/livros-por-autor')
            ->assertOk()
            ->assertJsonStructure(['fonte', 'grupos', 'total_linhas']);
    }

    public function test_livros_por_autor_vazio_quando_sem_dados(): void
    {
        $resp = $this->getJson('/api/relatorios/livros-por-autor')->assertOk();

        $this->assertCount(0, $resp->json('grupos'));
        $this->assertEquals(0, $resp->json('total_linhas'));
    }

    public function test_livros_por_autor_agrupa_corretamente(): void
    {
        $autor = Autor::create(['nome' => 'Carlos Drummond']);
        $assunto = Assunto::create(['descricao' => 'Poesia']);

        $livro1 = Livro::create(['titulo' => 'A Rosa do Povo', 'valor' => 0]);
        $livro2 = Livro::create(['titulo' => 'Brejo das Almas', 'valor' => 0]);

        $livro1->autores()->attach($autor->cod_au);
        $livro2->autores()->attach($autor->cod_au);
        $livro1->assuntos()->attach($assunto->cod_as);

        $resp = $this->getJson('/api/relatorios/livros-por-autor')->assertOk();

        $this->assertCount(1, $resp->json('grupos'));
        $this->assertEquals(2, $resp->json('total_linhas'));

        $grupo = $resp->json('grupos.0');
        $this->assertEquals($autor->cod_au, $grupo['autor_id']);
        $this->assertEquals('Carlos Drummond', $grupo['autor_nome']);
        $this->assertCount(2, $grupo['livros']);
    }

    public function test_livros_por_autor_ignora_livros_sem_autor(): void
    {
        Livro::create(['titulo' => 'Livro Sem Autor', 'valor' => 0]);

        $resp = $this->getJson('/api/relatorios/livros-por-autor')->assertOk();

        $this->assertEquals(0, $resp->json('total_linhas'));
    }

    public function test_livros_por_autor_multiplos_autores(): void
    {
        $a1 = Autor::create(['nome' => 'Autor A']);
        $a2 = Autor::create(['nome' => 'Autor B']);
        $livro = Livro::create(['titulo' => 'Livro Coautoria', 'valor' => 0]);
        $livro->autores()->attach([$a1->cod_au, $a2->cod_au]);

        $resp = $this->getJson('/api/relatorios/livros-por-autor')->assertOk();

        $this->assertCount(2, $resp->json('grupos'));
        $this->assertEquals(2, $resp->json('total_linhas'));
    }
}
