@extends('layouts.app')

@section('content')
<pre>
    
</pre>

<h2 class="mb-4">Recomendações Personalizadas</h2>
@if (!empty($preferencias))
<p class="mb-3">
    <strong>Suas preferências de formato:</strong>
    {{ implode(', ', $preferencias) }}
</p>
@endif
@if (!empty($nivel) && !empty($recomendacao_bairro))
<div
class="alert alert-{{ str_contains($nivel, 'Epidemia') ? 'danger' : (str_contains($nivel, 'Alerta') ? 'warning' : 'success') }}">
<strong>Situação no seu bairro ({{ $bairro ?? 'não informado' }}):</strong><br>
Nível: {{ $nivel }}<br>
Incidência: {{ $inc ?? '-' }}<br>
{{ $recomendacao_bairro }}
</div>
@endif



{{-- ALERTAS DE ERRO (já existentes) --}}
@if (session('erro'))
<div class="alert alert-warning">
    {{ session('erro') }}
</div>
@endif

{{-- =========== NOVO: mensagem sobre vacinação =========== --}}
@isset($mensagem_vacina)
<div class="alert alert-info">
    {!! $mensagem_vacina !!}
</div>
@endisset

{{-- =========== NOVO: vídeos da vacina (se houver) =========== --}}
@if (($elegivel_vacina ?? false) && !empty($videos_vacina))
<h4>Vídeos recomendados sobre a vacina da dengue</h4>
<ul class="list-group mb-4">
    @foreach ($videos_vacina as $v)
    <li class="list-group-item">
        <a href="{{ $v['url'] }}" target="_blank">{{ $v['titulo'] }}</a>
    </li>
    @endforeach
</ul>
@endif

{{-- =========== NOVO: situação epidemiológica =========== --}}
@isset($semana)
<div class="card mb-4">
    <div class="card-header">
        Situação epidemiológica – Semana {{ $semana['semana'] }} - São Paulo
    </div>
    <div class="card-body">
        <p><strong>Casos de dengue:</strong> {{ $semana['dengue'] }}</p>
        <p><strong>Dengue com sinais de alarme:</strong> {{ $semana['alarme'] }}</p>
        <p><strong>Dengue grave:</strong> {{ $semana['grave'] }}</p>
    </div>
</div>
@endisset

{{-- =========== RECOMENDAÇÃO POR CONTEÚDO (original) =========== --}}
<h4>Baseado nas suas preferências</h4>
@if (count($conteudo) > 0)
<div class="row">
    @foreach ($conteudo as $rec)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Recurso Educacional</h5>
                <p class="card-text">
                    <strong>Tipo:</strong> {{ $rec['tipo'] ?? '-' }}<br>
                    <strong>Nota média:</strong> {{ isset($rec['nota']) ? number_format($rec['nota'], 2, ',', '.') : '-' }}<br>
                    

                    <strong>Recurso:</strong><br>
                    <code>{{ $rec['recurso'] ?? '-' }}</code>
                </p>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<p class="text-muted">Nenhuma recomendação por conteúdo.</p>
@endif

<hr class="my-4">

{{-- =========== RECOMENDAÇÃO COLABORATIVA (original) =========== --}}
<h4>Outros usuários com preferências similares também acessaram</h4>
<!--<pre>Colab brutos: {{ json_encode($colaborativa, JSON_PRETTY_PRINT) }}</pre> -->

@if (count($colaborativa) > 0)
<div class="row">
    @foreach ($colaborativa as $rec)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Recurso Educacional</h5>
                <p class="card-text">
                    <strong>Tipo:</strong> {{ $rec['tipo'] ?? '-' }}<br>
                    <strong>Nota média:</strong> {{ $rec['nota'] ?? '-' }}<br>
                    <strong>Acessos totais:</strong> {{ $rec['qtd_acessos'] ?? 0 }}<br>
                    <strong>Recurso:</strong><br>
                    <code>{{ $rec['recurso'] ?? '-' }}</code><br>
                    
                </p>
                
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<p class="text-muted">Nenhuma recomendação colaborativa.</p>
@endif
@endsection
