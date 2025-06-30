<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class SituacaoController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bairro = $user->bairro;
        
        $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>
SELECT ?inc WHERE {
  ?s :temDA "$bairro" ;
     :temINC ?inc .
}
SPARQL;
        
        $response = Http::withHeaders([
            'Accept' => 'application/sparql-results+json',
            'Content-Type' => 'application/x-www-form-urlencoded',
            ])->asForm()->post(env('FUSEKI_URL') . '/query', [
                'query' => $query
            ]);
            
            $data = $response->json();
            
            $inc = isset($data['results']['bindings'][0]['inc']['value'])
            ? floatval($data['results']['bindings'][0]['inc']['value'])
            : null;
            
            if (is_null($inc)) {
                $nivel = '❓ Dados não encontrados';
                $recomendacao = 'Não foi possível obter a situação do bairro informado.';
            } elseif ($inc > 300) {
                $nivel = '🚨 Epidemia';
                $recomendacao = 'Evite áreas com aglomeração, use repelente e procure atendimento ao apresentar sintomas.';
            } elseif ($inc >= 150) {
                $nivel = '⚠️ Alerta';
                $recomendacao = 'Atenção redobrada com criadouros de mosquito. Reforce medidas preventivas.';
            } else {
                $nivel = '✅ Situação normal';
                $recomendacao = 'Continue seguindo as recomendações básicas de prevenção.';
            }
            
            return redirect()->route('recomendacoes', [
                'bairro' => $bairro,
                'inc' => $inc,
                'nivel' => $nivel,
                'recomendacao_bairro' => $recomendacao
            ]);
            
        }
        
        private function removerAcentos($string)
        {
            return iconv('UTF-8', 'ASCII//TRANSLIT', $string);
        }
    }
