<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

trait DataTableQuery
{
    /**
     * Ordena e pagina a query para o feed JSON consumido pelo composable `useDataTable`.
     *
     * @param  array<int, string>  $allowedFields  colunas que podem ser ordenadas via query string;
     *                                             qualquer outro sortField é ignorado
     */
    protected function dataTable(Request $request, Builder $query, array $allowedFields, int $perPage = 10): JsonResponse
    {
        $sortField = $request->query('sortField');
        $sortOrder = $request->query('sortOrder');

        if ($sortField && in_array($sortField, $allowedFields, true)) {
            $direction = $sortOrder === 'desc' ? 'desc' : 'asc';
            $query->orderBy($sortField, $direction);
        }

        return response()->json($query->paginate($perPage));
    }
}
