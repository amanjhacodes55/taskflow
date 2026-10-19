@extends('layouts.app')
@section('title', 'Sign up')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-3">Create your account</h1>
                <form method="POST" action="{{ route('register') }}" novalidate>
                    @csrf
                    @foreach (['name' => 'Name', 'email' => 'Email'] as $field => $label)
                        <div class="mb-3">
                            <label for="{{ $field }}" class="form-label">{{ $label }}</label>
                            <input id="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}"
                                   value="{{ old($field) }}" required class="form-control @error($field) is-invalid @enderror">
                            @error($field) <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    @endforeach
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" name="password" required
                               class="form-control @error('password') is-invalid @enderror">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required class="form-control">
                    </div>
                    <button class="btn btn-primary w-100">Sign up</button>
                </form>
                <p class="text-center small text-muted mt-3 mb-0">
                    Already have an account? <a href="{{ route('login') }}">Log in</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
