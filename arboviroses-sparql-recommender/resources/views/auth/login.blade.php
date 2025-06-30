<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
        }
        .login-container {
            max-width: 800px;
            margin: auto;
            margin-top: 5%;
            padding: 80px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center justify-content-center">

    <div class="login-container">
        <h2 class="mb-4 text-center">Login</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="/login" method="POST">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" class="form-control form-control-lg" name="email" required autofocus>

            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" class="form-control form-control-lg" name="password" required>

            </div>
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Entrar</button>
                <a href="/register" class="btn btn-link text-center">Criar conta</a>
            </div>
        </form>
    </div>

</body>
</html>
