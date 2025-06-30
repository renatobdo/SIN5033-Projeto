@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Avaliação de Recursos Educacionais</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Legenda de notas --}}
    <div class="mb-3">
        <p><strong>Legenda:</strong></p>
        <ul class="list-inline">
            <li class="list-inline-item"><strong>1</strong> = Muito ruim</li>
            <li class="list-inline-item"><strong>2</strong> = Ruim</li>
            <li class="list-inline-item"><strong>3</strong> = Regular</li>
            <li class="list-inline-item"><strong>4</strong> = Bom</li>
            <li class="list-inline-item"><strong>5</strong> = Excelente</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('avaliacao.store') }}">
        @csrf
        @foreach ($recursos as $recurso)
            @if (!empty($recurso['uri']))
                @php
                    $notaAtual = $notasUsuario[$recurso['uri']] ?? null;
                @endphp
                <div class="card mb-3">
                    <div class="card-header">{{ $recurso['titulo'] }}</div>
                    <div class="card-body">
                        <p><strong>Tipo:</strong> {{ $recurso['tipo'] }}</p>
                        <div class="mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="form-check form-check-inline">
                                    <input class="form-check-input"
                                           type="radio"
                                           name="notas[{{ $recurso['uri'] }}]"
                                           value="{{ $i }}"
                                           {{ $notaAtual == $i ? 'checked' : '' }}>
                                    {{ $i }}
                                </label>
                            @endfor
                        </div>

                        @if ($notaAtual)
                            <p class="text-muted">Você avaliou com nota {{ $notaAtual }}.</p>
                        @endif
                    </div>
                </div>
            @else
                <div class="alert alert-danger">
                    ⚠️ Recurso sem URI, não será avaliado.
                </div>
            @endif
        @endforeach

        <button type="submit" class="btn btn-primary">Salvar avaliações</button>
    </form>
</div>
@endsection
