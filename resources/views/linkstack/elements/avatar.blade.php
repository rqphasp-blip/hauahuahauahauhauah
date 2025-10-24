{{-- Feature: Stories Avatar - Exibe indicador de stories no avatar do usuário com lightbox e autoplay --}}
@php
    // Verifica se o usuário tem stories ativos
    $hasActiveStories = false;
    $userId = ($userinfo->id ?? null);
    $debugInfo = [];
    $activeStories = [];
    
    if ($userId) {
        // Adiciona informação de debug
        $debugInfo[] = "ID do usuário: " . $userId;
        
        // Busca stories ativos (menos de 24 horas)
        $stories = DB::table('user_stories')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
            
        $debugInfo[] = "Total de stories encontrados: " . count($stories);
            
        if ($stories && count($stories) > 0) {
            foreach ($stories as $story) {
                $createdAt = new \DateTime($story->created_at);
                $now = new \DateTime();
                $diff = $now->diff($createdAt);
                
                // Story ativo se foi criado há menos de 24 horas
                if ($diff->days == 0 && $diff->h < 24) {
                    $hasActiveStories = true;
                    // Adiciona o story ativo à lista para o lightbox
                    $activeStories[] = $story;
                }
            }
        }
    }
@endphp

<!-- Comentário de debug (visível apenas no código-fonte) -->
<!-- Debug Stories: {{ implode(' | ', $debugInfo) }} -->

<style>
    /* Estilo para o anel de stories ao redor do avatar */
    .avatar-stories-ring {
        position: relative; /* IMPORTANTE: Para o posicionamento absoluto da pílula */
        border-radius: 50%;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        z-index: 0; /* Mantém o z-index base */
        width: 135px;
        height: 135px;
        margin: auto;
        padding: 2px;   
        box-sizing: border-box; 
        display: flex;          
        align-items: center;    
        justify-content: center; 
    }
    
    /* Estilo para a imagem do avatar dentro do anel */
    .avatar-stories-ring > img {
        display: block; 
        width: 100%;    
        height: 100%;   
        object-fit: cover; 
        border-radius: 50%; 
        border: 3px solid #000000; /* Cor da borda/espaçamento (ex: preto) */
        box-sizing: border-box; 
    }

    /* Cursor de mão ao passar sobre o avatar com stories */
    /* A classe .has-stories agora está no .avatar-stories-ring */
    .has-stories { /* Esta regra já deve existir ou ser adaptada */
        cursor: pointer;
        /* Se .has-stories já define position:relative, ótimo. Caso contrário, o do .avatar-stories-ring já serve. */
        /* z-index: 1; (Opcional, pode ser gerenciado pelo .avatar-stories-ring diretamente) */
    }
    
    /* --- NOVO CSS PARA A PÍLULA DE STATUS --- */
    .story-status-pill {
        position: absolute;
        top: 5px;         /* Posição a partir do topo do anel. Ajuste conforme necessário. */
        right: 5px;       /* Posição a partir da direita do anel. Ajuste conforme necessário. */
        background-color: #76c776; /* Cor verde similar ao exemplo (ou outra de sua preferência) */
        color: white;
        padding: 2px 7px;   /* Espaçamento interno (vertical, horizontal) */
        border-radius: 10px;/* Bordas arredondadas para forma de pílula */
        font-size: 9px;     /* Tamanho da fonte pequeno */
        font-weight: bold;
        line-height: 1.2;   /* Altura da linha para melhor legibilidade */
        z-index: 2;         /* Garante que a pílula fique sobre a imagem e o anel */
        text-align: center;
        box-shadow: 0 1px 2px rgba(0,0,0,0.3); /* Sombra sutil para dar profundidade */
        user-select: none;  /* Impede a seleção do texto da pílula */
    }
    /* --- FIM DO NOVO CSS PARA A PÍLULA DE STATUS --- */
    
    /* Estilos para o lightbox (permanecem inalterados) */
    .lightbox {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.9);
        align-items: center;
        justify-content: center;
    }
    
    .lightbox-content {
        display: block;
        max-width: 90%;
        max-height: 90%;
        margin: auto;
        border-radius: 8px;
    }
    
    .lightbox-close {
        position: absolute;
        top: 15px;
        right: 25px;
        color: #f1f1f1;
        font-size: 40px;
        font-weight: bold;
        transition: 0.3s;
        cursor: pointer;
    }
    
    .lightbox-close:hover,
    .lightbox-close:focus {
        color: #bbb;
        text-decoration: none;
    }
    
    .lightbox-navigation {
        position: absolute;
        width: 100%;
        display: flex;
        justify-content: space-between;
        top: 50%;
        transform: translateY(-50%);
        padding: 0 20px;
    }
    
    .lightbox-nav-btn {
        color: white;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
        background: rgba(0,0,0,0.5);
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        user-select: none;
    }
    
    .lightbox-nav-btn:hover {
        background: rgba(0,0,0,0.8);
    }
    
    .lightbox-counter {
        position: absolute;
        bottom: 20px;
        left: 0;
        right: 0;
        text-align: center;
        color: white;
        font-size: 14px;
    }
    
    /* Barra de progresso para o autoplay (permanece inalterada) */
    .progress-container {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background-color: rgba(255, 255, 255, 0.2);
        z-index: 1001; /* Garante que fique sobre a imagem no lightbox */
    }
    
    .progress-bar {
        height: 100%;
        width: 0;
        background-color: white;
        transition: width linear;
    }
</style>
<!-- Your Image Here -->

@if(file_exists(base_path(findAvatar($userinfo->id))))
    @if($hasActiveStories)
        {{-- Quando os stories estão ativos, o avatar é envolvido pelo anel e a pílula é adicionada --}}
        <div class="avatar-stories-ring has-stories"> {{-- Adicionada classe .has-stories aqui para ser o alvo do JS e CSS --}}
            <img alt="avatar" id="avatar" class="rounded-avatar fadein" 
                 src="{{ url(findAvatar($userinfo->id)) }}"> {{-- Largura/altura removidas, o CSS cuidará disso --}}
            <span class="story-status-pill">Novo Status</span> {{-- A NOVA PÍLULA --}}
        </div>
    @else
        {{-- Exibição padrão do avatar sem stories --}}
        <img alt="avatar" id="avatar" class="rounded-avatar fadein avatar-image" 
             src="{{ url(findAvatar($userinfo->id)) }}" 
             height="128px" width="128px" style="object-fit: cover;">
    @endif
@elseif(file_exists(base_path("assets/linkstack/images/").findFile('avatar')))
    {{-- Fallback para avatar padrão do LinkStack --}}
    <img alt="avatar" id="avatar" class="rounded-avatar fadein avatar-image" 
         src="{{ url("assets/linkstack/images/")."/".findFile('avatar') }}" 
         height="128px" width="128px" style="object-fit: cover;">
@else
    {{-- Fallback para logo SVG --}}
    <img alt="avatar" id="avatar" class="rounded-avatar fadein" 
         src="{{ asset('assets/linkstack/images/logo.svg') }}" 
         height="128px" style="width:auto;min-width:128px;object-fit: cover;">
@endif

<!-- Lightbox para exibir os stories -->
@if($hasActiveStories)
<div id="storiesLightbox" class="lightbox">
    <span class="lightbox-close">×</span>
    
    <!-- Barra de progresso para o autoplay -->
    <div class="progress-container">
        <div class="progress-bar" id="storyProgressBar"></div>
    </div>
    
    <img class="lightbox-content" id="lightboxImage">
    
    @if(count($activeStories) > 1)
    <div class="lightbox-navigation">
        <span class="lightbox-nav-btn" id="prevStory">‹</span>
        <span class="lightbox-nav-btn" id="nextStory">›</span>
    </div>
    <div class="lightbox-counter">
        <span id="currentStoryIndex">1</span> / <span id="totalStories">{{ count($activeStories) }}</span>
    </div>
    @endif
</div>

<script>
    // Array com os caminhos das imagens dos stories ativos
    const storyImages = [
        @foreach($activeStories as $story)
            "{{ asset($story->image_path) }}",
        @endforeach
    ];
    
    document.addEventListener('DOMContentLoaded', function() {
        const avatarElement = document.querySelector('.has-stories') || document.querySelector('#avatar');
        const lightbox = document.getElementById('storiesLightbox');
        const lightboxImg = document.getElementById('lightboxImage');
        const closeBtn = document.querySelector('.lightbox-close');
        const prevBtn = document.getElementById('prevStory');
        const nextBtn = document.getElementById('nextStory');
        const currentIndexEl = document.getElementById('currentStoryIndex');
        const progressBar = document.getElementById('storyProgressBar');
        
        let currentImageIndex = 0;
        let autoplayTimer = null;
        const autoplayDuration = 5000; // 5 segundos
        
        // Função para iniciar o autoplay e a barra de progresso
        function startAutoplay() {
            if (autoplayTimer) {
                clearTimeout(autoplayTimer);
            }
            
            // Reinicia a barra de progresso para uma nova animação
            progressBar.style.transition = 'none'; // Remove transição existente para resetar instantaneamente
            progressBar.style.width = '0%';
            
            // Força o navegador a aplicar o estilo 'width: 0%' antes de iniciar a nova transição
            // (Leitura de offsetWidth/offsetHeight é um truque comum para forçar reflow)
            void progressBar.offsetWidth; 
            
            // Configura a nova transição e inicia a animação
            progressBar.style.transition = `width ${autoplayDuration}ms linear`;
            progressBar.style.width = '100%';
            
            autoplayTimer = setTimeout(function() {
                if (currentImageIndex < storyImages.length - 1) {
                    nextImage(); 
                    // nextImage() já chama startAutoplay indiretamente via stopAutoplay -> startAutoplay
                } else {
                    closeLightbox(); // Fecha o lightbox se for o último story
                }
            }, autoplayDuration);
        }
        
        // Função para parar o autoplay e resetar a barra de progresso
        function stopAutoplay() {
            if (autoplayTimer) {
                clearTimeout(autoplayTimer);
                autoplayTimer = null;
            }
            // Para a animação da barra de progresso e a redefine para 0%
            // Se fosse uma pausa real, guardaríamos a largura atual e o tempo restante.
            // Para o comportamento de "reiniciar" a história ao pausar/retomar, resetar é o correto.
            progressBar.style.transition = 'none'; // Para a animação abruptamente
            progressBar.style.width = '0%';      // Reseta a barra
        }
        
        function openLightbox(index) {
            if (storyImages.length === 0) return;
            
            currentImageIndex = index;
            lightboxImg.src = storyImages[currentImageIndex];
            lightbox.style.display = 'flex';
            
            if (currentIndexEl) {
                currentIndexEl.textContent = currentImageIndex + 1;
            }
            
            document.body.style.overflow = 'hidden';
            startAutoplay(); // Inicia autoplay e barra de progresso
        }
        
        function closeLightbox() {
            stopAutoplay(); // Para autoplay e reseta barra
            lightbox.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        
        function nextImage() {
            stopAutoplay(); // Para o autoplay atual e reseta a barra
            
            if (currentImageIndex < storyImages.length - 1) {
                currentImageIndex++;
            } else {
                // Se desejar que volte ao início ao chegar no último e clicar em next
                // currentImageIndex = 0; 
                // Ou feche o lightbox (comportamento atual do autoplayTimeout)
                closeLightbox();
                return;
            }
            lightboxImg.src = storyImages[currentImageIndex];
            if (currentIndexEl) {
                currentIndexEl.textContent = currentImageIndex + 1;
            }
            startAutoplay(); // Reinicia o autoplay para o novo story
        }
        
        function prevImage() {
            stopAutoplay(); // Para o autoplay atual e reseta a barra
            
            if (currentImageIndex > 0) {
                currentImageIndex--;
            } else {
                // Se desejar que vá para o último ao chegar no primeiro e clicar em prev
                // currentImageIndex = storyImages.length - 1;
                // Ou mantenha no primeiro (comportamento atual)
                 // Não faz nada ou reinicia o autoplay para o primeiro
            }
            lightboxImg.src = storyImages[currentImageIndex];
            if (currentIndexEl) {
                currentIndexEl.textContent = currentImageIndex + 1;
            }
            startAutoplay(); // Reinicia o autoplay para o novo story (ou o mesmo se estava no início)
        }
        
        if (avatarElement && storyImages.length > 0) {
            avatarElement.addEventListener('click', function(e) {
                e.preventDefault();
                openLightbox(0);
            });
        }
        
        if (closeBtn) {
            closeBtn.addEventListener('click', closeLightbox);
        }
        
        if (prevBtn) {
            prevBtn.addEventListener('click', prevImage);
        }
        
        if (nextBtn) {
            nextBtn.addEventListener('click', nextImage);
        }
        
        lightbox.addEventListener('click', function(e) {
            if (e.target === lightbox) {
                closeLightbox();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (lightbox.style.display === 'flex') {
                if (e.key === 'Escape') {
                    closeLightbox();
                } else if (e.key === 'ArrowRight') {
                    nextImage();
                } else if (e.key === 'ArrowLeft') {
                    prevImage();
                }
            }
        });
        
        // Pausa o autoplay e a barra de progresso quando o mouse está sobre a imagem
        lightboxImg.addEventListener('mouseenter', function() {
            // Para pausar a animação da barra onde ela está, a lógica seria mais complexa:
            // 1. Obter a largura atual da barra (elapsed time).
            // 2. Calcular o tempo restante.
            // 3. Em mouseleave, reiniciar a animação da barra com o tempo restante.
            // O comportamento atual (reiniciar a barra e o timer de 5s) é mais simples:
            stopAutoplay();
        });
        
        // Reinicia o autoplay e a barra de progresso quando o mouse sai da imagem
        lightboxImg.addEventListener('mouseleave', function() {
            if (lightbox.style.display === 'flex') {
                startAutoplay();
            }
        });
    });
</script>
@endif