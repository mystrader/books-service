<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    public function livrosPorAutor(): JsonResponse
    {
        $linhas = DB::table('vw_relatorio_livros_por_autor')
            ->orderBy('autor_nome')
            ->orderBy('livro_titulo')
            ->get();

        $agrupado = $linhas->groupBy('autor_id')->map(function ($itens) {
            $primeiro = $itens->first();

            return [
                'autor_id' => $primeiro->autor_id,
                'autor_nome' => $primeiro->autor_nome,
                'livros' => $itens->map(fn ($r) => [
                    'livro_id' => $r->livro_id,
                    'titulo' => $r->livro_titulo,
                    'editora' => $r->livro_editora,
                    'edicao' => $r->livro_edicao,
                    'ano_publicacao' => $r->livro_ano_publicacao,
                    'valor' => $r->livro_valor,
                ])->values(),
            ];
        })->values();

        return response()->json([
            'fonte' => 'view: vw_relatorio_livros_por_autor',
            'grupos' => $agrupado,
            'total_linhas' => $linhas->count(),
        ]);
    }
}
