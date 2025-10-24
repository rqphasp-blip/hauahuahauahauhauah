<!-- /home/ubuntu/plugins/UserDashboardImage/resources/views/display_image.blade.php -->
@php
    // Attempt to get the authenticated user. 
    // This might need adjustment based on how user data is typically accessed in your main dashboard view.
    $currentUser = Auth::user();
    $imagePath = null;

    if ($currentUser && !empty($currentUser->dashboard_image_path)) {
        // Ensure the path is a valid public path if stored with Storage::url()
        // If you stored just the relative path from the 'public' disk, Storage::url() is correct.
        if (Storage::disk('public')->exists($currentUser->dashboard_image_path)) {
             $imagePath = Storage::url($currentUser->dashboard_image_path);
        }
    }
@endphp

@if ($imagePath)
    <div class="user-dashboard-image-container" style="margin-bottom: 15px;">
        <img src="{{ $imagePath }}" alt="User Dashboard Image" style="max-width: 100%; height: auto; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
    </div>
@else
    <div class="user-dashboard-image-placeholder" style="margin-bottom: 15px; padding: 20px; background-color: #f8f9fa; border: 1px dashed #dee2e6; border-radius: 8px; text-align: center;">
        <p style="margin: 0; color: #6c757d;">Nenhuma imagem do dashboard definida.</p>
    </div>
@endif

