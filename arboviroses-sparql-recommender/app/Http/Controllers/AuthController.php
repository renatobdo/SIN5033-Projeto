<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            return redirect('/dashboard');
        }

        return back()->withErrors(['email' => 'Login inválido']);
    }

    public function showRegister()
    {
        $bairros = [
        "BOM RETIRO", "CONSOLAÇÃO", "SANTA CECÍLIA", "BELA VISTA", "CAMBUCI",
        "LIBERDADE", "REPÚBLICA", "SÉ", "CIDADE TIRADENTES", "ERMELINO",
        "PONTE RASA", "GUAIANASES", "LAJEADO", "ITAIM PAULISTA", "VILA CURUÇA",
        "CIDADE LÍDER", "ITAQUERA", "JOSÉ BONIFÁCIO", "PARQUE DO CARMO", "SÃO MATEUS",
        "SÃO RAFAEL", "JARDIM HELENA", "SÃO MIGUEL", "VILA JACUÍ", "CACHOEIRINHA",
        "CASA VERDE", "LIMÃO", "BRASILÂNDIA", "FREGUESIA DO Ó", "JAÇANÃ",
        "TREMEMBÉ", "ANHANGUERA", "PERUS", "JARAGUÁ", "PIRITUBA", "SÃO DOMINGOS",
        "SANTANA", "MANDAQUI", "TUCURUVI", "VILA GUILHERME", "VILA MARIA",
        "VILA MEDEIROS", "IGUATEMI"
    ];
        return view('auth.register', compact('bairros'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'dtnascimento' => 'required|date',
            'bairro'       => 'required|string',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'dtnascimento' => $request->dtnascimento,
                'bairro'       => $request->bairro,
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors(['email' => 'E-mail já cadastrado']);
        }

        // ✅ Inserir no Fuseki
        $nome = $user->name;
        $email = $user->email;
        $dtnascimento = date('Y-m-d', strtotime($user->dtnascimento));
        $nomeId = strtolower(preg_replace('/\s+/', '', $nome)); // remove espaços
        $bairro = $user->bairro;


        $query = <<<SPARQL
PREFIX : <http://www.exemplo.org/arboviroses#>  
PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>
INSERT DATA {
  :usuario$nomeId a :Usuario ;
    :temNome "$nome" ;
    :temEmail "$email" ;
    :temDataNascimento "$dtnascimento"^^xsd:date ;
    :temDA "$bairro" .
}
SPARQL;
       

        \Illuminate\Support\Facades\Http::asForm()->post(env('FUSEKI_URL') . '/update', [
            'update' => $query
        ]);

        Auth::login($user);

        return redirect('/quiz');
    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login');
    }
}
