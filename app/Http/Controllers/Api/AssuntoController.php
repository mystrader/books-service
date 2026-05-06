<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assunto;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class AssuntoController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(
            Assunto::query()->orderBy('descricao')->get()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $v = Validator::make($request->all(), [
            'descricao' => 'required|string|max:100',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $assunto = Assunto::query()->create([
                'descricao' => $request->string('descricao')->toString(),
            ]);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao criar assunto.'], 500);
        }

        return response()->json($assunto, 201);
    }

    public function show(int $codAs): JsonResponse
    {
        $assunto = Assunto::query()->where('cod_as', $codAs)->first();
        if (! $assunto) {
            return response()->json(['message' => 'Assunto nao encontrado'], 404);
        }

        return response()->json($assunto);
    }

    public function update(Request $request, int $codAs): JsonResponse
    {
        $assunto = Assunto::query()->where('cod_as', $codAs)->first();
        if (! $assunto) {
            return response()->json(['message' => 'Assunto nao encontrado'], 404);
        }

        $v = Validator::make($request->all(), [
            'descricao' => 'required|string|max:100',
        ]);
        if ($v->fails()) {
            return response()->json(['errors' => $v->errors()], 422);
        }

        try {
            $assunto->update(['descricao' => $request->string('descricao')->toString()]);
        } catch (QueryException $e) {
            return response()->json(['message' => 'Erro ao atualizar assunto.'], 500);
        }

        return response()->json($assunto);
    }

    public function destroy(int $codAs): JsonResponse|Response
    {
        $assunto = Assunto::query()->where('cod_as', $codAs)->first();
        if (! $assunto) {
            return response()->json(['message' => 'Assunto nao encontrado'], 404);
        }

        try {
            $assunto->delete();
        } catch (QueryException $e) {
            if ((string) $e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Assunto vinculado a livros.',
                ], 409);
            }

            return response()->json(['message' => 'Erro ao remover assunto.'], 500);
        }

        return response()->noContent();
    }
}
