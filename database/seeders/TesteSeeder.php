<?php

namespace Database\Seeders;

use App\Models\Teste;
use Illuminate\Database\Seeder;

class TesteSeeder extends Seeder
{
    public function run(): void
    {
        Teste::factory()->count(18)->create();
        Teste::factory()->count(6)->inativo()->create();
    }
}
