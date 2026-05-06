<?php

use App\Models\Livro;
use App\Services\OpenLibraryCatalogSync;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('biblioteca:sync-acervo {--target='.OpenLibraryCatalogSync::DEFAULT_CATALOG_TARGET.' : Quantidade alvo de livros no acervo}', function () {
    $target = max(1, (int) $this->option('target'));
    $before = Livro::query()->count();
    $this->info("Importando do Open Library até ~{$target} títulos (só entra com capa: cover_i ou ISBN com imagem OK)…");
    app(OpenLibraryCatalogSync::class)->importUpTo($target);
    $n = Livro::query()->count();
    $added = $n - $before;
    $this->info("Concluído. Total: {$n} (+{$added} nesta execução).");
})->purpose('Preenche o acervo via Open Library (sem chave de API)');

Artisan::command('biblioteca:sync-google {--target='.OpenLibraryCatalogSync::DEFAULT_CATALOG_TARGET.' : Redireciona para sync Open Library}', function () {
    $this->warn('biblioteca:sync-google foi substituído; executando biblioteca:sync-acervo.');
    $this->call('biblioteca:sync-acervo', ['--target' => $this->option('target')]);
})->purpose('Alias legado → biblioteca:sync-acervo');
