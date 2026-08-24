<?php

use App\Http\Controllers\TesteController;
use Illuminate\Support\Facades\Route;

/**
 * Rotas do painel. Cada módulo entra como uma linha no mapa abaixo e o `AdvancedRoute` deriva
 * as rotas a partir dos nomes dos métodos do controller (`getIndex`, `postForm`, ...).
 * Ver `App\Http\Controllers\TesteController` para a tabela completa da convenção.
 */
Route::group(['middleware' => ['auth', 'verified'], 'prefix' => 'painel'], function () {
    AdvancedRoute::controllers([
        'teste' => TesteController::class,
        // Outros módulos...
    ]);
});
