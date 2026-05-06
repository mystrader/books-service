<?php

namespace Tests\Feature;

use App\Models\Assunto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssuntoTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_retorna_lista_de_assuntos(): void
    {
        Assunto::create(['descricao' => 'Tecnologia']);
        Assunto::create(['descricao' => 'Arte']);

        $this->getJson('/api/assuntos')
            ->assertOk()
            ->assertJsonCount(2)
            ->assertJsonPath('0.descricao', 'Arte'); // ordenado por descricao
    }

    public function test_store_cria_assunto(): void
    {
        $this->postJson('/api/assuntos', ['descricao' => 'Filosofia'])
            ->assertCreated()
            ->assertJsonPath('descricao', 'Filosofia');

        $this->assertDatabaseHas('assuntos', ['descricao' => 'Filosofia']);
    }

    public function test_store_valida_descricao_obrigatoria(): void
    {
        $this->postJson('/api/assuntos', [])
            ->assertUnprocessable()
            ->assertJsonStructure(['errors' => ['descricao']]);
    }

    public function test_store_valida_descricao_max_100(): void
    {
        $this->postJson('/api/assuntos', ['descricao' => str_repeat('x', 101)])
            ->assertUnprocessable();
    }

    public function test_show_retorna_assunto(): void
    {
        $assunto = Assunto::create(['descricao' => 'História']);

        $this->getJson("/api/assuntos/{$assunto->cod_as}")
            ->assertOk()
            ->assertJsonPath('descricao', 'História');
    }

    public function test_show_retorna_404_para_inexistente(): void
    {
        $this->getJson('/api/assuntos/9999')
            ->assertNotFound();
    }

    public function test_update_altera_assunto(): void
    {
        $assunto = Assunto::create(['descricao' => 'Descrição Antiga']);

        $this->putJson("/api/assuntos/{$assunto->cod_as}", ['descricao' => 'Descrição Nova'])
            ->assertOk()
            ->assertJsonPath('descricao', 'Descrição Nova');

        $this->assertDatabaseHas('assuntos', ['descricao' => 'Descrição Nova']);
    }

    public function test_update_retorna_404_para_inexistente(): void
    {
        $this->putJson('/api/assuntos/9999', ['descricao' => 'X'])
            ->assertNotFound();
    }

    public function test_destroy_remove_assunto(): void
    {
        $assunto = Assunto::create(['descricao' => 'Para Remover']);

        $this->deleteJson("/api/assuntos/{$assunto->cod_as}")
            ->assertNoContent();

        $this->assertDatabaseMissing('assuntos', ['cod_as' => $assunto->cod_as]);
    }

    public function test_destroy_retorna_404_para_inexistente(): void
    {
        $this->deleteJson('/api/assuntos/9999')
            ->assertNotFound();
    }
}
