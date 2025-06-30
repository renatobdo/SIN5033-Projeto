<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class QuizController extends Controller
{
    public function show() {
        {
            $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?tipo ?titulo ?tema WHERE {
  ?recurso a :RecursoEducacional ;
           :temTipo ?tipo ;
           :temTitulo ?titulo ;
           :temTema ?tema .
}
SPARQL;
            
            $response = Http::withHeaders([
                'Accept' => 'application/sparql-results+json',
                'Content-Type' => 'application/x-www-form-urlencoded',
                ])->asForm()->post(env('FUSEKI_URL') . '/query', [
                    'query' => $query
                ]);
                
                $data = $response->json();
                
                $recursos = [];
                foreach ($data['results']['bindings'] as $item) {
                    $tipo = $item['tipo']['value'];
                    $recursos[$tipo][] = [
                        'titulo' => $item['titulo']['value'],
                        'tema' => $item['tema']['value']
                    ];
                }
                
                return view('quiz', compact('recursos'));
            }
        }
            public function submit(Request $request) {
                $preferencias = $request->input('preferencias', []);
                $usuario = Auth::user();
                $nomeId = strtolower(str_replace(' ', '', $usuario->name));
                
                $triples = "";
                foreach ($preferencias as $pref) {
                    $triples .= ":usuario$nomeId :temPreferencia :$pref .\n";
                }
                
                $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
INSERT DATA {
    $triples
}
SPARQL;
                
                Http::asForm()->post(env('FUSEKI_URL') . '/update', [
                    'update' => $query
                ]);
                
                return redirect('/dashboard')->with('status', 'Preferências salvas e recomendações disponíveis!');
            }
        }
