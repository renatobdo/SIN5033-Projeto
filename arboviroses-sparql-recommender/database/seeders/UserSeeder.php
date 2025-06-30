<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            ['name' => 'Ana', 'email' => 'ana@example.com', 'dtnascimento' => '1995-03-15', 'preferencia' => ['video', 'infografico']],
            ['name' => 'Bruno', 'email' => 'bruno@example.com', 'dtnascimento' => '1993-07-22', 'preferencia' => ['jogo','quiz']],
            ['name' => 'Clara', 'email' => 'clara@example.com', 'dtnascimento' => '2001-11-10', 'preferencia' => ['video', 'podcast']],
            ['name' => 'Daniel', 'email' => 'daniel@example.com', 'dtnascimento' => '1983-01-05', 'preferencia' => ['infografico', 'cartilha']],
            ['name' => 'Eduarda', 'email' => 'eduarda@example.com', 'dtnascimento' => '1999-09-18', 'preferencia' => 'jogo'],
            ['name' => 'Felipe', 'email' => 'felipe@example.com', 'dtnascimento' => '1987-04-03', 'preferencia' => ['video', 'caça-palavras']],
            ['name' => 'Gabriel', 'email' => 'gabriel@example.com', 'dtnascimento' => '1991-06-21', 'preferencia' => ['infografico', 'quiz']],
            ['name' => 'Helena', 'email' => 'helena@example.com', 'dtnascimento' => '2002-02-12', 'preferencia' => ['video', 'jogo']],
            ['name' => 'Igor', 'email' => 'igor@example.com', 'dtnascimento' => '2012-12-07', 'preferencia' => ['jogo', 'video']],
            ['name' => 'Juliana', 'email' => 'juliana@example.com', 'dtnascimento' => '1996-08-30', 'preferencia' => ['infografico', 'cartilha']],
            ['name' => 'Renato', 'email' => 'renatobdo@gmail.com', 'dtnascimento' => '1981-09-05', 'preferencia' => ['video', 'podcast']],
        ];

        foreach ($usuarios as $u) {
            $user = User::create([
                'name' => $u['name'],
                'email' => $u['email'],
                'password' => Hash::make('senha123'),
                'dtnascimento' => $u['dtnascimento'],
            ]);

            $nomeId = strtolower(preg_replace('/\s+/', '', $u['name']));
            $dataNascimento = date('Y-m-d', strtotime($u['dtnascimento']));

            $preferencias = is_array($u['preferencia']) ? $u['preferencia'] : [$u['preferencia']];

            $preferenciasTriples = '';
            foreach ($preferencias as $p) {
                $preferenciasTriples .= "    :temPreferenciaTipo \"$p\" ;\n";
            }

            $preferenciasTriples = preg_replace('/;\s*$/', '.', $preferenciasTriples);

            $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
INSERT DATA {
  :usuario$nomeId a :Usuario ;
    :temNome "{$u['name']}" ;
    :temEmail "{$u['email']}" ;
    :temDataNascimento "$dataNascimento"^^xsd:date ;
$preferenciasTriples
}
SPARQL;

            Http::asForm()->post(env('FUSEKI_URL') . '/update', [
                'update' => $sparql
            ]);
        }
    }
}
