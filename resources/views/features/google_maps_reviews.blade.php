{{-- Feature: Chamada de API Google Maps Reviews --}}
{{-- Controlado por: $userinfo->feature_maps_reviews_status --}}
{{-- Campos disponíveis: $userinfo->feature_maps_place_id, $userinfo->feature_maps_reviews_min_rating, $userinfo->feature_maps_reviews_max_rows --}}

@if(isset($userinfo) && $userinfo->feature_maps_reviews_status && !empty($userinfo->feature_maps_place_id))
    <div class="feature-google-maps-reviews-container" style="margin-bottom: 20px;">
        {{-- A exibição de reviews do Google Maps geralmente requer uma chamada de API via JavaScript (Google Places API) --}}
        {{-- e, em seguida, a renderização dos resultados. Este é um placeholder. --}}
        <p>Funcionalidade de Google Maps Reviews ativada para o Place ID: {{ $userinfo->feature_maps_place_id }}.</p>
        <p>Exibir até {{ $userinfo->feature_maps_reviews_max_rows ?? 5 }} reviews com classificação mínima de {{ $userinfo->feature_maps_reviews_min_rating ?? 1 }}.</p>
        <div id="google-reviews-placeholder"></div>
        <script>
            // Exemplo de como você poderia buscar e exibir reviews (requer a API do Google Places carregada)
            // function initMapReviews() {
            //     if (typeof google !== 'undefined' && typeof google.maps.places !== 'undefined') {
            //         const service = new google.maps.places.PlacesService(document.createElement('div')); // Precisa de um mapa ou elemento DOM
            //         const request = {
            //             placeId: "{{ $userinfo->feature_maps_place_id }}",
            //             fields: ["name", "rating", "reviews", "user_ratings_total"]
            //         };
            //         service.getDetails(request, (place, status) => {
            //             if (status === google.maps.places.PlacesServiceStatus.OK && place && place.reviews) {
            //                 const reviewsContainer = document.getElementById("google-reviews-placeholder");
            //                 reviewsContainer.innerHTML = "<h3>Reviews para " + place.name + "</h3>";
            //                 place.reviews.filter(review => review.rating >= {{ $userinfo->feature_maps_reviews_min_rating ?? 1 }} )
            //                              .slice(0, {{ $userinfo->feature_maps_reviews_max_rows ?? 5 }})
            //                              .forEach(review => {
            //                                  const reviewElement = document.createElement("div");
            //                                  reviewElement.innerHTML = `<p><strong>${review.author_name} (${review.rating}/5):</strong> ${review.text}</p>`;
            //                                  reviewsContainer.appendChild(reviewElement);
            //                              });
            //             } else {
            //                 document.getElementById("google-reviews-placeholder").innerHTML = "<p>Não foi possível carregar as reviews.</p>";
            //             }
            //         });
            //     } else {
            //         console.error("Google Places API não carregada.");
            //         document.getElementById("google-reviews-placeholder").innerHTML = "<p>API do Google Places não disponível.</p>";
            //     }
            // }
            // Se você já carrega a API do Google Maps em outro lugar, pode chamar initMapReviews() após o carregamento.
            // Caso contrário, você precisaria adicionar o script da API do Google Maps aqui ou no cabeçalho.
            // Exemplo: <script async defer src="https://maps.googleapis.com/maps/api/js?key={{ env("GOOGLE_MAPS_API_KEY") }}&libraries=places&callback=initMapReviews"></script>
        </script>
        {{-- Nota: GOOGLE_MAPS_API_KEY deve estar configurada no seu arquivo .env e habilitada para a Places API --}}
    </div>
@endif

