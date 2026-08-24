<?php

use App\Models\Teste;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Valida o componente Inertia sem exigir a página buildada em disco.
    config()->set('inertia.testing.ensure_pages_exist', false);
    $this->withoutVite();
});

test('guests are redirected from the teste index to login', function () {
    $this->get(route('teste.get.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user sees the teste index with the status options', function () {
    actingAs(User::factory()->create())
        ->get(route('teste.get.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('teste/index')
            ->where('statusOptions', [
                ['label' => 'Ativo', 'value' => '1'],
                ['label' => 'Inativo', 'value' => '0'],
            ]));
});

test('getDataTable paginates, filters by nome and honours the sort whitelist', function () {
    Teste::factory()->create(['nome' => 'Alfa']);
    Teste::factory()->create(['nome' => 'Beta']);
    Teste::factory()->inativo()->create(['nome' => 'Gama']);

    $user = User::factory()->create();

    actingAs($user)
        ->getJson(route('teste.get.datatable'))
        ->assertOk()
        ->assertJsonPath('total', 3);

    actingAs($user)
        ->getJson(route('teste.get.datatable', ['nome' => 'Al']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.nome', 'Alfa');

    actingAs($user)
        ->getJson(route('teste.get.datatable', ['status' => '0']))
        ->assertOk()
        ->assertJsonPath('total', 1)
        ->assertJsonPath('data.0.nome', 'Gama');

    actingAs($user)
        ->getJson(route('teste.get.datatable', ['sortField' => 'nome', 'sortOrder' => 'desc']))
        ->assertOk()
        ->assertJsonPath('data.0.nome', 'Gama');
});

test('getDataTable ignores a sort field outside the whitelist', function () {
    Teste::factory()->create(['nome' => 'Alfa']);

    actingAs(User::factory()->create())
        ->getJson(route('teste.get.datatable', ['sortField' => 'descricao', 'sortOrder' => 'desc']))
        ->assertOk()
        ->assertJsonPath('total', 1);
});

test('getModelForm returns the create defaults when no id is given', function () {
    actingAs(User::factory()->create())
        ->getJson(route('teste.get.modelform'))
        ->assertOk()
        ->assertJson([
            'registro' => ['id' => null, 'nome' => '', 'descricao' => null, 'status' => true],
        ]);
});

test('getModelForm returns the record when an id is given', function () {
    $teste = Teste::factory()->create(['nome' => 'Alfa', 'descricao' => 'Uma descrição']);

    actingAs(User::factory()->create())
        ->getJson(route('teste.get.modelform', ['id' => $teste->id]))
        ->assertOk()
        ->assertJson([
            'registro' => [
                'id' => $teste->id,
                'nome' => 'Alfa',
                'descricao' => 'Uma descrição',
                'status' => true,
            ],
        ]);
});

test('getForm renders the page form for create and for edit', function () {
    $teste = Teste::factory()->create(['nome' => 'Alfa']);
    $user = User::factory()->create();

    actingAs($user)
        ->get(route('teste.get.form'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('teste/form')
            ->where('registro.id', null)
            ->has('statusOptions'));

    actingAs($user)
        ->get(route('teste.get.form', ['id' => $teste->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('teste/form')
            ->where('registro.id', $teste->id)
            ->where('registro.nome', 'Alfa'));
});

test('getView renders the record', function () {
    $teste = Teste::factory()->create(['nome' => 'Alfa']);

    actingAs(User::factory()->create())
        ->get(route('teste.get.view', ['id' => $teste->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('teste/view')
            ->where('registro.nome', 'Alfa'));
});

test('postForm creates a new registro', function () {
    actingAs(User::factory()->create())
        ->post(route('teste.post.form'), [
            'nome' => 'Novo registro',
            'descricao' => 'Descrição',
            'status' => true,
        ])
        ->assertRedirect(route('teste.get.index'));

    $this->assertDatabaseHas('testes', ['nome' => 'Novo registro', 'status' => true]);
});

test('postForm updates an existing registro', function () {
    $teste = Teste::factory()->create(['nome' => 'Antigo', 'status' => true]);

    actingAs(User::factory()->create())
        ->post(route('teste.post.form'), [
            'id' => $teste->id,
            'nome' => 'Atualizado',
            'status' => false,
        ])
        ->assertRedirect(route('teste.get.index'));

    $this->assertDatabaseHas('testes', ['id' => $teste->id, 'nome' => 'Atualizado', 'status' => false]);
    $this->assertDatabaseCount('testes', 1);
});

test('postForm rejects a duplicated nome but allows keeping its own on edit', function () {
    Teste::factory()->create(['nome' => 'Existente']);
    $teste = Teste::factory()->create(['nome' => 'Proprio']);
    $user = User::factory()->create();

    actingAs($user)
        ->post(route('teste.post.form'), ['nome' => 'Existente', 'status' => true])
        ->assertSessionHasErrors('nome');

    actingAs($user)
        ->post(route('teste.post.form'), ['id' => $teste->id, 'nome' => 'Proprio', 'status' => true])
        ->assertSessionHasNoErrors();
});

test('postDelete removes an inactive registro', function () {
    $teste = Teste::factory()->inativo()->create();

    actingAs(User::factory()->create())
        ->post(route('teste.post.delete'), ['id' => $teste->id])
        ->assertRedirect(route('teste.get.index'));

    $this->assertDatabaseMissing('testes', ['id' => $teste->id]);
});

test('postDelete refuses to remove an active registro', function () {
    $teste = Teste::factory()->create(['status' => true]);

    actingAs(User::factory()->create())
        ->post(route('teste.post.delete'), ['id' => $teste->id])
        ->assertRedirect(route('teste.get.index'));

    $this->assertDatabaseHas('testes', ['id' => $teste->id]);
});
