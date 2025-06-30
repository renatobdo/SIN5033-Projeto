<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Arboviroses - Recomendador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="/dashboard">Arboviroses</a>
        <div class="d-flex">
            <form action="{{ url('/logout') }}" method="POST">
             @csrf
                <button type="submit" class="btn btn-light">Sair</button>
            </form>

        </div>
    </div>
</nav>

<main class="container">
    @yield('content')
</main>
<!-- ✅ Inclusão do Bootstrap JS (obrigatório para o modal funcionar) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- ✅ Inclusão dos scripts específicos da view -->
@yield('scripts')
</body>
</html>
