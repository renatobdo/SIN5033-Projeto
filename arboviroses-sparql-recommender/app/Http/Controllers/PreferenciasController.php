<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreferenciasController extends Controller
{
    public function obterPreferencias($email)
{
    $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>

SELECT DISTINCT ?tipo WHERE {
  ?usuario :temEmail "$email" ;
           :temPreferenciaTipo ?tipo .
}
SPARQL;

    $response = Http::asForm()->post(env('FUSEKI_URL') . '/query', [
        'query' => $query
    ]);

    $json = $response->json();
    $tipos = [];

    foreach ($json['results']['bindings'] as $binding) {
        $tipos[] = $binding['tipo']['value'];
    }

    return response()->json(['preferencias' => $tipos]);
}

}
