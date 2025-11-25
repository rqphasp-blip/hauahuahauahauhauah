@extends('layouts.sidebar')

@section('content')
<div class="conatiner-fluid content-inner mt-n5 py-0">
  <div class="row">
    <div class="col-lg-12">
      <div class="card rounded">
        <div class="card-body">
          <h2 class="mb-4 card-header"><i class="bi bi-patch-check-fill"></i> {{ __('messages.Verification badges') }}</h2>

          @if(session('success'))
            <div class="alert alert-success mt-3">{{ session('success') }}</div>
          @endif
          @if($errors->any())
            <div class="alert alert-danger mt-3">
              <ul class="mb-0">
                @foreach($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            </div>
          @endif

          <div class="row">
            <div class="col-lg-6">
              <form action="{{ route('verification-badges.store') }}" method="post" enctype="multipart/form-data" class="mt-3">
                @csrf
                <div class="mb-3">
                  <label class="form-label">{{ __('messages.Badge name') }}</label>
                  <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('messages.Badge description') }}</label>
                  <input type="text" name="alt_text" value="{{ old('alt_text') }}" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">{{ __('messages.Badge icon') }}</label>
                  <input type="file" name="icon" class="form-control" accept="image/*,.svg" required>
                  <small class="text-muted">{{ __('messages.Verification badge help') }}</small>
                </div>
                <button type="submit" class="btn btn-primary">{{ __('messages.Save badge') }}</button>
              </form>
            </div>

            <div class="col-lg-6 mt-4 mt-lg-0">
              <h5 class="mb-3">{{ __('messages.Available badges') }}</h5>
              @if($badges->isEmpty())
                <p class="text-muted">{{ __('messages.No verification badges yet') }}</p>
              @else
                <div class="table-responsive">
                  <table class="table align-middle">
                    <thead>
                      <tr>
                        <th>{{ __('messages.Badge icon') }}</th>
                        <th>{{ __('messages.Badge name') }}</th>
                        <th>{{ __('messages.Badge description') }}</th>
                        <th class="text-end">{{ __('messages.Actions') }}</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($badges as $badge)
                        <tr>
                          <td style="width:70px;">
                            <img src="{{ asset($badge->icon_path) }}" alt="{{ $badge->alt_text }}" style="max-width:48px;max-height:48px;object-fit:contain;">
                          </td>
                          <td>{{ $badge->name }}</td>
                          <td>{{ $badge->alt_text }}</td>
                          <td class="text-end">
                            <form action="{{ route('verification-badges.destroy', $badge) }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                              @csrf
                              @method('DELETE')
                              <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('messages.Delete') }}</button>
                            </form>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              @endif
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
@endsection