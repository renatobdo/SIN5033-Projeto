@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Quiz de Preferências</h2>

    <form method="POST" action="/salvar-preferencia">
    @csrf

    @foreach ($recursos as $tipo => $itens)
        <div class="form-check">
            <input class="form-check-input tipo-checkbox" type="checkbox" id="tipo-{{ $tipo }}" name="preferencias[]" value="{{ $tipo }}">
            <label class="form-check-label fw-bold" for="tipo-{{ $tipo }}">{{ ucfirst($tipo) }}</label>
        </div>

        <div class="ms-4">
            @foreach ($itens as $item)
                <div class="form-check">
                    <input class="form-check-input subtipo-checkbox" type="checkbox" name="subpreferencias[{{ $tipo }}][]" 
                           value="{{ $item['titulo'] }}" data-tipo="{{ $tipo }}" id="sub-{{ Str::slug($item['titulo']) }}">
                    <label class="form-check-label" for="sub-{{ Str::slug($item['titulo']) }}">{{ $item['titulo'] }} ({{ $item['tema'] }})</label>
                </div>
            @endforeach
        </div>
    @endforeach

    <button type="submit" class="btn btn-primary mt-3">Enviar</button>
</form>

</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Se marcar o tipo, marca todos os subtítulos
        document.querySelectorAll('.tipo-checkbox').forEach(tipo => {
            tipo.addEventListener('change', () => {
                const subtitulos = document.querySelectorAll(`.subtipo-checkbox[data-tipo="${tipo.value}"]`);
                subtitulos.forEach(st => st.checked = tipo.checked);
            });
        });

        // Se marcar um subtítulo, marca o tipo correspondente
        document.querySelectorAll('.subtipo-checkbox').forEach(sub => {
            sub.addEventListener('change', () => {
                const tipo = document.querySelector(`#tipo-${sub.dataset.tipo}`);
                if (sub.checked) {
                    tipo.checked = true;
                } else {
                    const outros = document.querySelectorAll(`.subtipo-checkbox[data-tipo="${sub.dataset.tipo}"]`);
                    const algumMarcado = Array.from(outros).some(el => el.checked);
                    tipo.checked = algumMarcado;
                }
            });
        });
    });
</script>

@endsection
