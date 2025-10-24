<!-- /home/ubuntu/plugins/UserDashboardImage/resources/views/upload_form.blade.php -->
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger">
        {{ session('error') }}
    </div>
@endif

<form action="{{ route('user.dashboard.image.upload') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="form-group">
        <label for="dashboard_image">Atualizar Imagem do Dashboard:</label>
        <input type="file" class="form-control-file" id="dashboard_image" name="dashboard_image" required>
        @error('dashboard_image')
            <div class="text-danger">{{ $message }}</div>
        @enderror
    </div>
    <button type="submit" class="btn btn-primary mt-2">Upload</button>
</form>

