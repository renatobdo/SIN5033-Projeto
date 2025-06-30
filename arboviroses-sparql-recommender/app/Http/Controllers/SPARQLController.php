<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

class SPARQLController extends Controller
{
    public function dashboard()
    {
        return view('dashboard');
    }
    
    public function insertUser(Request $request)
    {
        $nome = $request->input('nome');
        $dataNascimento = $request->input('data_nascimento');
        $email = $request->input('email');
        $nomeId = strtolower(str_replace(' ', '', $nome));
        
        $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
INSERT DATA {
  :$nomeId a :Usuario ;
    :temNome "$nome" ;
    :temDataNascimento "$dataNascimento" ;
    :temEmail "$email" .
}
SPARQL;
        
        Http::asForm()->post(env('FUSEKI_URL') . '/update', [
            'update' => $query
        ]);
        
        return back()->with('status', 'Usuário inserido na ontologia!');
    }
    
    public function salvarPreferencia(Request $request)
    {
        $user = auth()->user();
        $nomeId = strtolower(str_replace(' ', '', $user->name));
        
        $preferencias = $request->input('preferencias', []);
        $subpreferencias = $request->input('subpreferencias', []); // lista de títulos
        
        $triples = "";
        
        // Preferência por tipo (ex: video, podcast)
        foreach ((array) $preferencias as $tipo) {
            $triples .= ":$nomeId :temPreferenciaTipo \"$tipo\" .\n";
        }
        $subpreferencias = collect($subpreferencias)->flatten()->toArray();
        
        
        // Subpreferências (por recurso e tema)
        foreach ((array) $subpreferencias as $tituloSelecionado) {
            $tituloLimpo = addslashes($tituloSelecionado);
            
            // Consulta a URI do recurso e seu tema
            $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?recurso ?tema WHERE {
  ?recurso a :RecursoEducacional ;
           :temTitulo ?titulo ;
           :temTema ?tema .
  FILTER (STR(?titulo) = "$tituloLimpo")
}
SPARQL;
            
            $res = Http::asForm()->post(env('FUSEKI_URL') . '/query', [
                'query' => $sparql,
                'Accept' => 'application/sparql-results+json'
                ])->json();
                
                $binding = $res['results']['bindings'][0] ?? null;
                
                if ($binding) {
                    $uriCompleta = $binding['recurso']['value'];
                    $tema = $binding['tema']['value'];
                    $uri = str_replace('http://www.exemplo.org/arboviroses#', '', $uriCompleta);
                    
                    $triples .= ":$nomeId :temPreferenciaRecurso :$uri .\n";
                    $triples .= ":$nomeId :temInteresseTema \"" . addslashes($tema) . "\" .\n";
                }
            }
            
            // Inserção final no Fuseki
            $queryInsert = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
INSERT DATA {
$triples
}
SPARQL;
            
            Http::asForm()->post(env('FUSEKI_URL') . '/update', [
                'update' => $queryInsert
            ]);
            
            return redirect('/recomendacoes')->with([
                'status' => 'Preferências salvas com sucesso!',
                'preferencias' => $preferencias
            ]);
            
        }
        
        
        
        
        
        
        public function recommend(Request $request)
        {
            $user   = auth()->user();
            $nomeId =  strtolower(preg_replace('/\s+/', '', $user->name));
            
            //  $nomeId = strtolower(preg_replace('/\s+/', '', $user->name));
            
            // Consulta Fuseki para obter preferências
            $queryPref = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?tipo WHERE {
  :$nomeId :temPreferenciaTipo ?tipo .
}
SPARQL;
            
            $prefResponse = Http::asForm()
            ->accept('application/sparql-results+json')
            ->post(env('FUSEKI_URL') . '/query', [
                'query' => $queryPref
            ]);
            
            $preferencias = [];
            if ($prefResponse->ok() && isset($prefResponse['results']['bindings'])) {
                $preferencias = array_map(fn($b) => $b['tipo']['value'], $prefResponse['results']['bindings']);
            }
            
            // Chamada à API de recomendação
            $response = Http::get(env('RECOMMENDER_API'), [
                'user'         => $nomeId,
                'dtnascimento' => $user->dtnascimento,
            ]);
            
            if ($response->failed() || !$response->json()) {
                return view('recomendacoes', [
                    'conteudo'         => [],
                    'colaborativa'     => [],
                    'mensagem_vacina'  => null,
                    'elegivel_vacina'  => false,
                    'videos_vacina'    => [],
                    'semana'           => null,
                    'preferencias'     => $preferencias,
                    ])->with('erro', 'Não foi possível obter recomendações.');
                }
                
                $json = $response->json();
                
                return view('recomendacoes', [
                    'conteudo'         => $json['conteudo']        ?? [],
                    'colaborativa'     => $json['colaborativa']    ?? [],
                    'mensagem_vacina'  => $json['mensagem_vacina'] ?? null,
                    'elegivel_vacina'  => $json['elegivel_vacina'] ?? false,
                    'videos_vacina'    => $json['videos_vacina']   ?? [],
                    'semana'           => $json['semana']          ?? null,
                    'preferencias'     => $preferencias,
                    'bairro' => $request->query('bairro') ?? null,
                    'inc' => $request->query('inc') ?? null,
                    'nivel' => $request->query('nivel') ?? null,
                    'recomendacao_bairro' => $request->query('recomendacao_bairro') ?? null,
                    
                    
                ]);
            }
            
            
        }
