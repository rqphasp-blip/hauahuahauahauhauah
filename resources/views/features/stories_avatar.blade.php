{{-- Feature: Stories Avatar - Exibe indicador de stories no avatar do usuário --}}
@php
    // Verifica se o usuário tem stories ativos
    $hasActiveStories = false;
    $userId = ($userinfo->id ?? null);
    $debugInfo = [];
    
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
                
                $debugInfo[] = "Story ID: " . $story->id . ", Criado há: " . $diff->h . " horas e " . $diff->i . " minutos";
                
                // Story ativo se foi criado há menos de 24 horas
                if ($diff->days == 0 && $diff->h < 24) {
                    $hasActiveStories = true;
                    $debugInfo[] = "Story ativo encontrado!";
                    break;
                }
            }
        }
    }
    
    // Adiciona informação final
    $debugInfo[] = "hasActiveStories: " . ($hasActiveStories ? "true" : "false");
@endphp

<!-- Comentário de debug (visível apenas no código-fonte) -->
<!-- Debug Stories: {{ implode(' | ', $debugInfo) }} -->

@if($hasActiveStories)
<style>
    /* Estilo para o anel de stories ao redor do avatar */
    .avatar-stories-ring {
        position: relative;
        display: inline-block;
    }
    
    .avatar-stories-ring::before {
        content: '';
        position: absolute;
        top: -3px;
        left: -3px;
        right: -3px;
        bottom: -3px;
        border-radius: 50%;
        background: linear-gradient(45deg, #f09433, #e6683c, #dc2743, #cc2366, #bc1888);
        z-index: 0;
        animation: rotate 1.5s linear infinite;
    }
    
    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }
        100% {
            transform: rotate(360deg);
        }
    }
    
    /* Cursor de mão ao passar sobre o avatar com stories */
    .has-stories {
        cursor: pointer;
        position: relative;
        z-index: 1;
    }
</style>

<script>
    // Script para abrir os stories ao clicar no avatar
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM carregado, procurando avatar...');
        const avatarElement = document.querySelector('.avatar-image');
        console.log('Avatar encontrado:', avatarElement);
		document.write "AVATAR";
        
        if (avatarElement) {
            avatarElement.classList.add('has-stories');
            
            // Adiciona o anel de stories
            const parent = avatarElement.parentElement;
            if (parent && !parent.classList.contains('avatar-stories-ring')) {
                console.log('Adicionando anel de stories');
				document.write "ANEL";
                const wrapper = document.createElement('div');
                wrapper.className = 'avatar-stories-ring';
                parent.insertBefore(wrapper, avatarElement);
                wrapper.appendChild(avatarElement);
            }
            
            // Adiciona evento de clique para abrir os stories
            avatarElement.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Avatar clicado, redirecionando para stories');
                window.location.href = '/user/{{ $userinfo->name }}/stories';
            });
        }
    });
</script>
@else
<!-- Sem stories ativos -->
@endif
