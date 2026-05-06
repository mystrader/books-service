<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Autor;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AutorController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Autor::query()->orderBy('nome')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $autor = Autor::query()->create(['nome' => $request->string('nome')->toString()]);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao criar autor.'], 500);
        }

        return response()->json($autor, 201);
    }

    public function show(int $codAu): JsonResponse
    {
        $autor = Autor::query()->where('cod_au', $codAu)->first();
        if (! $autor) {
            return response()->json(['message' => 'Autor nao encontrado'], 404);
        }

        return response()->json($autor);
    }

    public function update(Request $request, int $codAu): JsonResponse
    {
        $autor = Autor::query()->where('cod_au', $codAu)->first();
        if (! $autor) {
            return response()->json(['message' => 'Autor nao encontrado'], 404);
        }

        $v = Validator::make($request->all(), [
            'nome' => 'required|string|max:100',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $autor->update(['nome' => $request->string('nome')->toString()]);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao atualizar autor.'], 500);
        }

        return response()->json($autor);
    }

    public function destroy(int $codAu): JsonResponse|Response
    {
        $autor = Autor::query()->where('cod_au', $codAu)->first();
        if (! $autor) {
            return response()->json(['message' => 'Autor nao encontrado'], 404);
        }

        try {
            $autor->delete();
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Autor vinculado a livros. Remova vinculos antes.',
                ], 409);
            }

            return response()->json(['message' => 'Erro ao remover autor.'], 500);
        }

        return response()->noContent();
    }
}
