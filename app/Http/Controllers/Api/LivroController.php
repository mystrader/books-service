<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Livro;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class LivroController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Livro::query()
            ->with(['autores:cod_au,nome', 'assuntos:cod_as,descricao'])
            ->orderBy('titulo');

        if ($request->boolean('all')) {
            return response()->json($q->get());
        }

        $perPage = min(max((int) $request->query('per_page', 18), 5), 100);
        $paginator = $q->paginate($perPage);

        return response()->json([
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->merge([
            'ano_publicacao' => self::normalizeAno($request->input('ano_publicacao')),
            'edicao' => self::normalizeEdicao($request->input('edicao')),
        ]);

        $v = Validator::make($request->all(), [
            'titulo' => 'required|string|max:255',
            'editora' => 'nullable|string|max:100',
            'edicao' => 'nullable|integer|min:1',
            'ano_publicacao' => 'nullable|regex:/^\d{4}$/',
            'valor' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|string|max:500',
            'observacoes' => 'nullable|string',
            'autor_ids' => 'array',
            'autor_ids.*' => 'integer|exists:autores,cod_au',
            'assunto_ids' => 'array',
            'assunto_ids.*' => 'integer|exists:assuntos,cod_as',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $livro = Livro::query()->create([
                'titulo' => $request->string('titulo')->toString(),
                'editora' => $request->input('editora'),
                'edicao' => $request->input('edicao'),
                'ano_publicacao' => $request->input('ano_publicacao'),
                'valor' => $request->input('valor', 0),
                'thumbnail' => $request->input('thumbnail'),
                'observacoes' => $request->input('observacoes'),
            ]);
            $livro->autores()->sync($request->input('autor_ids', []));
            $livro->assuntos()->sync($request->input('assunto_ids', []));

            return response()->json($livro->load(['autores', 'assuntos']), 201);
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erro ao gravar livro no banco.',
                'code' => $e->getCode(),
            ], 500);
        }
    }

    public function show(int $codl): JsonResponse
    {
        $livro = Livro::query()
            ->with(['autores', 'assuntos'])
            ->where('codl', $codl)
            ->first();

        if (! $livro) {
            return response()->json(['message' => 'Livro nao encontrado'], 404);
        }

        return response()->json($livro);
    }

    public function update(Request $request, int $codl): JsonResponse
    {
        $livro = Livro::query()->where('codl', $codl)->first();
        if (! $livro) {
            return response()->json(['message' => 'Livro nao encontrado'], 404);
        }

        if ($request->has('ano_publicacao')) {
            $request->merge(['ano_publicacao' => self::normalizeAno($request->input('ano_publicacao'))]);
        }
        if ($request->has('edicao')) {
            $request->merge(['edicao' => self::normalizeEdicao($request->input('edicao'))]);
        }

        $v = Validator::make($request->all(), [
            'titulo' => 'sometimes|required|string|max:255',
            'editora' => 'nullable|string|max:100',
            'edicao' => 'nullable|integer|min:1',
            'ano_publicacao' => 'nullable|regex:/^\d{4}$/',
            'valor' => 'nullable|numeric|min:0',
            'thumbnail' => 'nullable|string|max:500',
            'observacoes' => 'nullable|string',
            'autor_ids' => 'array',
            'autor_ids.*' => 'integer|exists:autores,cod_au',
            'assunto_ids' => 'array',
            'assunto_ids.*' => 'integer|exists:assuntos,cod_as',
        ]);

        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $livro->fill($request->only([
                'titulo', 'editora', 'edicao', 'ano_publicacao', 'valor', 'thumbnail', 'observacoes',
            ]));
            $livro->save();

            if ($request->has('autor_ids')) {
                $livro->autores()->sync($request->input('autor_ids', []));
            }
            if ($request->has('assunto_ids')) {
                $livro->assuntos()->sync($request->input('assunto_ids', []));
            }

            return response()->json($livro->load(['autores', 'assuntos']));
        } catch (QueryException $e) {
            return response()->json([
                'message' => 'Erro ao atualizar livro.',
                'code' => $e->getCode(),
            ], 500);
        }
    }

    public function destroy(int $codl): JsonResponse|Response
    {
        $livro = Livro::query()->where('codl', $codl)->first();
        if (! $livro) {
            return response()->json(['message' => 'Livro nao encontrado'], 404);
        }

        try {
            $livro->delete();
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao remover livro.'], 500);
        }

        return response()->noContent();
    }

    private static function normalizeAno(mixed $v): ?string
    {
        if ($v === null || $v === '') {
            return null;
        }

        return (string) $v;
    }

    private static function normalizeEdicao(mixed $v): ?int
    {
        if ($v === null || $v === '') {
            return null;
        }

        return is_numeric($v) ? (int) $v : null;
    }
}
