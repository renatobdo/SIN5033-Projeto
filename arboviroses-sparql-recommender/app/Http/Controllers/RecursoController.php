<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class RecursoController extends Controller
{
    public function index()
    {
        $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?recurso ?titulo ?tipo ?url WHERE {
  ?recurso a :RecursoEducacional ;
           :temTitulo ?titulo ;
           :temTipo ?tipo .
  OPTIONAL { ?recurso :temURL ?url }
}
ORDER BY ?titulo
SPARQL;
        
        
        $response = Http::asForm()
        ->accept('application/sparql-results+json')
        ->post(env('FUSEKI_URL') . '/query', ['query' => $sparql]);
        
        $dados = $response->json();
        $recursos = [];
        
        foreach ($dados['results']['bindings'] as $linha) {
            $recursos[] = [
                'uri' => $linha['recurso']['value'], // ✅ Corrigido
                'titulo' => $linha['titulo']['value'],
                'tipo' => $linha['tipo']['value'],
                'url' => $linha['url']['value'] ?? null, // Adicione isso se estiver usando a URL
            ];
        }
        
        
        return view('recursos.index', compact('recursos'));
    }
    
    public function acessar(Request $request)
    {
        $user = Auth::user();
        $recursoUri = $request->input('recurso_uri');
        
        // Armazena no Postgre
        DB::table('acessos')->insert([
            'user_id'     => $user->id,
            'recurso_uri' => $recursoUri,
            'created_at'  => now(),
        ]);
        
        // Envia ao Fuseki
        $nomeId = strtolower(preg_replace('/\s+/', '', $user->name));
        $recursoId = str_replace('http://www.exemplo.org/arboviroses#', '', $recursoUri);
        
        $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
INSERT DATA {
  :$nomeId :acessouRecurso :$recursoId .
}
SPARQL;
        
        Http::asForm()->post(env('FUSEKI_URL') . '/update', [
            'update' => $sparql
        ]);
        
        return redirect()->route('recursos.index')->with('success', 'Acesso registrado!');
    }
}
