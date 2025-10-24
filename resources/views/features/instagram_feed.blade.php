{{-- Feature: Chamada de API Instagram (Feed) --}}
{{-- Controlado por: $userinfo->feature_instagram_feed_status --}}
{{-- Campos disponíveis: $userinfo->feature_instagram_username, $userinfo->feature_instagram_post_count --}}

@if(isset($userinfo) && $userinfo->feature_instagram_feed_status && !empty($userinfo->feature_instagram_username))
    <div class="feature-instagram-feed-container" style="margin-bottom: 20px;">
        {{-- A exibição de um feed do Instagram geralmente requer uma API (como a Instagram Basic Display API) --}}
        {{-- e autenticação OAuth. A API antiga (sem autenticação) foi descontinuada. --}}
        {{-- Este é um placeholder e NÃO FUNCIONARÁ sem uma implementação de API adequada. --}}
        <p>Funcionalidade de Feed do Instagram ativada para o usuário: {{ $userinfo->feature_instagram_username }}.</p>
        <p>Exibir até {{ $userinfo->feature_instagram_post_count ?? 6 }} posts.</p>
        <div id="instagram-feed-placeholder">
            <p><i>Conteúdo do feed do Instagram apareceria aqui após integração com a API.</i></p>
            {{-- Exemplo de como poderia ser (requer JS para buscar e renderizar): --}}
            {{-- <script>
                // async function fetchInstagramFeed() {
                //     // Lógica para chamar a API do Instagram (requer token de acesso)
                //     // const accessToken = "SEU_TOKEN_DE_ACESSO_AQUI"; 
                //     // const userId = "USER_ID_DO_INSTAGRAM_AQUI"; // Obtido via API
                //     // const fields = "id,caption,media_type,media_url,permalink,thumbnail_url,timestamp,username";
                //     // const limit = {{ $userinfo->feature_instagram_post_count ?? 6 }};
                //     // const url = `https://graph.instagram.com/v12.0/${userId}/media?fields=${fields}&access_token=${accessToken}&limit=${limit}`;
                //     try {
                //         const response = await fetch(url);
                //         const data = await response.json();
                //         const feedContainer = document.getElementById("instagram-feed-placeholder");
                //         feedContainer.innerHTML = ""; // Limpar placeholder
                //         if (data.data && data.data.length > 0) {
                //             data.data.forEach(post => {
                //                 const postElement = document.createElement("div");
                //                 let mediaElement = "";
                //                 if (post.media_type === "IMAGE" || post.media_type === "CAROUSEL_ALBUM") {
                //                     mediaElement = `<img src="${post.media_url}" alt="${post.caption ? post.caption.substring(0,50) : "Post do Instagram"}" style="max-width: 150px; margin: 5px;">`;
                //                 } else if (post.media_type === "VIDEO") {
                //                     mediaElement = `<video controls width="150" style="margin: 5px;"><source src="${post.media_url}" type="video/mp4">Seu navegador não suporta o vídeo.</video>`;
                //                 }
                //                 postElement.innerHTML = `<a href="${post.permalink}" target="_blank">${mediaElement}</a>`;
                //                 feedContainer.appendChild(postElement);
                //             });
                //         } else {
                //             feedContainer.innerHTML = "<p>Nenhum post encontrado ou erro ao buscar o feed.</p>";
                //         }
                //     } catch (error) {
                //         console.error("Erro ao buscar feed do Instagram:", error);
                //         document.getElementById("instagram-feed-placeholder").innerHTML = "<p>Erro ao carregar o feed do Instagram.</p>";
                //     }
                // }
                // fetchInstagramFeed();
            </script> --}}
        </div>
        <small>Nota: A integração real do feed do Instagram requer configuração da API Basic Display do Instagram e tratamento de tokens de acesso.</small>
    </div>
@endif

