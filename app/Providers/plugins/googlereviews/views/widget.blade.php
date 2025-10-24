<!-- Arquivo: views/widget.blade.php -->
<div class="google-review-widget">
    <div class="google-review-header">
        <div class="google-logo">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 18 18">
                <path fill="#4285F4" d="M17.64 9.2c0-.637-.057-1.251-.164-1.84H9v3.481h4.844c-.209 1.125-.843 2.078-1.796 2.717v2.258h2.908c1.702-1.567 2.684-3.874 2.684-6.615z"/>
                <path fill="#34A853" d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z"/>
                <path fill="#FBBC05" d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z"/>
                <path fill="#EA4335" d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z"/>
            </svg>
            <span>Google Avaliação</span>
        </div>
        <div class="rating-value">{{ number_format($placeDetails['rating'] ?? 0, 1) }}</div>
    </div>
    <div class="stars">
        @php
            $rating = $placeDetails['rating'] ?? 0;
        @endphp
        
        @for($i = 1; $i <= 5; $i++)
            @if($i <= $rating)
                <i class="fas fa-star"></i>
            @elseif($i - 0.5 <= $rating)
                <i class="fas fa-star-half-alt"></i>
            @else
                <i class="far fa-star"></i>
            @endif
        @endfor
    </div>
    <div class="reviews-count">
        <a href="#" target="_blank">Leia nossas {{ $placeDetails['user_ratings_total'] ?? 0 }} avaliações</a>
    </div>
</div>

<style>
.google-review-widget {
    width: 180px;
    padding: 15px;
    border-radius: 8px;
    background-color: white;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    font-family: Arial, sans-serif;
    text-align: center;
}

.google-review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.google-logo {
    display: flex;
    align-items: center;
    font-size: 14px;
    font-weight: 500;
}

.google-logo svg {
    margin-right: 5px;
}

.rating-value {
    font-size: 18px;
    font-weight: bold;
}

.stars {
    color: #FBBC05;
    font-size: 16px;
    margin-bottom: 10px;
}

.reviews-count {
    font-size: 12px;
}

.reviews-count a {
    color: #4285F4;
    text-decoration: none;
}

.reviews-count a:hover {
    text-decoration: underline;
}
</style>
