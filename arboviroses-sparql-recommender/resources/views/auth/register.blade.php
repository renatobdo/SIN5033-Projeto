<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Registrar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container mt-5" style="max-width: 500px;">
        <h2 class="mb-4">Cadastro de Usuário</h2>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $erro)
                        <li>{{ $erro }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="name" class="form-label">Nome completo</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}"
                    required autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}"
                    required>
            </div>

            <div class="mb-3">
                <label for="dtnascimento" class="form-label">Data de Nascimento</label>
                <input type="date" class="form-control" name="dtnascimento" required>
            </div>

            <div class="mb-3">
                <label for="bairro">Bairro</label>
                <select name="bairro" id="bairro" class="form-control" required>
                    @foreach ($bairros as $bairro)
                        <option value="{{ $bairro }}">{{ $bairro }}</option>
                    @endforeach
                </select>
            </div>


            <div class="mb-3">
                <label for="password" class="form-label">Senha</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label">Confirme a senha</label>
                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control"
                    required>
            </div>

            <button type="submit" class="btn btn-success w-100">Registrar</button>

            <div class="mt-3 text-center">
                <a href="{{ route('login') }}" class="btn btn-link">Já tem uma conta? Faça login</a>
            </div>
        </form>
    </div>

</body>

</html>
