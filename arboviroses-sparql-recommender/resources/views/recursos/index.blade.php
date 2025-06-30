@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Recursos Educacionais Disponíveis</h3>
    
    @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    
    <div class="row">
        @foreach ($recursos as $rec)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">{{ $rec['titulo'] }}</h5>
                    <p class="card-text">
                        <strong>Tipo:</strong> {{ $rec['tipo'] }}
                    </p>
                    @if (!empty($rec['url']))
                    <button type="button"
                    class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#modalRecurso"
                    data-url="{{ $rec['url'] }}"
                    data-uri="{{ $rec['uri'] }}"
                    data-tipo="{{ $rec['tipo'] }}">
                    👁️ Acessar
                </button>
                @else
                <span class="text-muted">🔒 Recurso sem URL</span>
                @endif
            </div>
        </div>
    </div>
    @endforeach
</div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalRecurso" tabindex="-1" aria-labelledby="modalRecursoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Visualização do Recurso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>
            <div class="modal-body">
                <!-- Conteúdo será carregado dinamicamente -->
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('modalRecurso');
        const modalBody = modal.querySelector('.modal-body');
        
        modal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const url = button.getAttribute('data-url');
            const uri = button.getAttribute('data-uri');
            const tipo = (button.getAttribute('data-tipo') || '').toLowerCase();
            
            let html = '';
            
            if (!url) {
                html = `<p class="text-muted">Este recurso não possui uma URL disponível.</p>`;
                if (tipo === 'video') {
                    if (url.includes('youtube.com/watch')) {
                        const videoId = url.split('v=')[1];
                        html = `<iframe width="100%" height="450" src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe>`;
                    } else {
                        html = `
            <div class="alert alert-warning">
                Este vídeo não pode ser exibido diretamente aqui.<br>
                <a href="${url}" target="_blank" class="btn btn-primary mt-2">Clique para assistir</a>
            </div>`;
                    }
                }
                
            } else if (tipo === 'cartilha' || url.endsWith('.pdf')) {
                html = `<embed src="${url}" width="100%" height="500px" type="application/pdf">`;
                } else if (tipo === 'infografico' || url.match(/\.(jpg|jpeg|png|gif)$/i)) {
                    html = `<img src="${url}" class="img-fluid" alt="Infográfico">`;
                } else if (tipo === 'podcast' || tipo === 'audio') {
                    if (url.endsWith('.mp3') || url.endsWith('.ogg')) {
                        html = `
            <audio controls style="width: 100%;">
                <source src="${url}" type="audio/mpeg">
                Seu navegador não suporta áudio.
            </audio>`;
                    } else {
                        html = `
            <div class="alert alert-warning">
                Este conteúdo de áudio não pôde ser carregado.<br>
                <a href="${url}" target="_blank" class="btn btn-primary mt-2">Clique para ouvir</a>
            </div>`;
                    }
                    
                } else {
                    html = `<a href="${url}" target="_blank" class="btn btn-outline-primary">Abrir recurso em nova aba</a>`;
                }
                
                modalBody.innerHTML = html;
                
                // Registra acesso
                fetch("{{ route('recursos.acessar') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify({ recurso_uri: uri })
                });
            });
            
            modal.addEventListener('hidden.bs.modal', function () {
                modalBody.innerHTML = '';
            });
        });
    </script>
    @endsection
    
