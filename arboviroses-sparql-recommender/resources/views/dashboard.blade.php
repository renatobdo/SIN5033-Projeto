<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    
    <div class="container mt-5">
        <h2>Bem-vindo ao Sistema de Recomendação</h2>
        <p class="text-muted">Usuário logado: {{ Auth::user()->name }}</p>
        
        {{-- Mensagem de sucesso ao criar usuário --}}
        @if (session('status'))
        <div class="alert alert-success mt-3">
            {{ session('status') }}
        </div>
        @endif
        @if (session('success'))
        <div class="alert alert-success mt-3">
            {{ session('success') }}
        </div>
        @endif
        
        
        <div class="mt-4">
            <form action="{{ route('situacao') }}" method="GET" class="d-inline">
                <button type="submit" class="btn btn-primary">Ver recomendações</button>
            </form>
            
            <form action="{{ url('/avaliacao') }}" method="GET" class="d-inline">
                <button type="submit" class="btn btn-warning">Avaliar recursos</button>
            </form>
            
            <a href="{{ route('recursos.index') }}" class="btn btn-secondary d-inline">
                📚 Ver Recursos Disponíveis
            </a>
            
            <form action="{{ url('/logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-light">Sair</button>
            </form>
        </div>
        
        
    </div>
    
</body>

</html>
