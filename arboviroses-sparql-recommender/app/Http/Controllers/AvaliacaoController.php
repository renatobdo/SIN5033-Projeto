<?php
namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use App\Models\RecursoEducacional;
use App\Models\Nota;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;


class AvaliacaoController extends Controller
{
    public function index()
    {
        $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
        
SELECT ?recurso ?titulo ?tipo ?mediaNota WHERE {
  ?recurso a :RecursoEducacional ;
           :temTitulo ?titulo ;
           :temTipo ?tipo .
  OPTIONAL { ?recurso :temMediaNota ?mediaNota }
}

SPARQL;
        
        $client = new Client();
        $response = $client->post('http://localhost:3030/arboviroses/query', [
            'form_params' => ['query' => $sparql],
            'headers' => ['Accept' => 'application/sparql-results+json']
        ]);
        
        $dados = json_decode($response->getBody(), true);
        $recursos = [];
        
        foreach ($dados['results']['bindings'] as $linha) {
            $recursos[] = [
                'uri' => $linha['recurso']['value'],
                'titulo' => $linha['titulo']['value'],
                'tipo' => $linha['tipo']['value'],
                'mediaNota' => isset($linha['mediaNota']) ? round(floatval($linha['mediaNota']['value']), 2) : null
            ];
        }
        $notasUsuario = Nota::where('user_id', Auth::id())
        ->pluck('nota', 'recurso_uri')
        ->toArray();
        
        
        return view('avaliacao.index', compact('recursos','notasUsuario'));
    }
    
    public function store(Request $request)
    {
        
        //dd(array_keys($request->notas));
        
        //dd($request->notas);
        foreach ($request->notas as $uri => $nota) {
            //    dd($uri); // <-- adicione isso
            if (empty($uri)) {
                // ignora entradas sem URI
                continue;
            }
            $uri = (string) $uri;
            Nota::updateOrCreate(
                ['user_id' => Auth::id(), 'recurso_uri' => $uri],
                ['nota' => $nota]
            );
        }
        // ======= CÁLCULO E SINCRONIZAÇÃO COM FUSEKI =======
        $medias = DB::table('notas')
        ->select('recurso_uri', DB::raw('AVG(nota) as media'))
        ->groupBy('recurso_uri')
        ->get();
        
        foreach ($medias as $item) {
            $uriCompleta = $item->recurso_uri;
            $media = number_format($item->media, 2, '.', ''); // Ex: 3.50
            $recursoId = str_replace('http://www.exemplo.org/arboviroses#', '', $uriCompleta);
            
            $sparql = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
DELETE {
  :$recursoId :temMediaNota ?old .
}
INSERT {
  :$recursoId :temMediaNota "$media"^^xsd:float .
}
WHERE {
  OPTIONAL { :$recursoId :temMediaNota ?old }
}
SPARQL;
            
            
            $response = \Http::asForm()->post(env('FUSEKI_URL') . '/update', [
                'update' => $sparql
            ]);
            if (!$response->successful()) {
                \Log::error('Erro no SPARQL UPDATE: ' . $response->body());
            }
        }
        return redirect()->route('dashboard')->with('success', 'Avaliações salvas com sucesso!');
        
    }
    
}
