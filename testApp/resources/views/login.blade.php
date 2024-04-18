@extends('layouts.body')

@section('title', 'Login Page')

@section('main')

    <div class="container d-flex flex-column flex-grow-1">
        <div class="row justify-content-center flex-grow-1">
            <div class="col-md-6 mt-5">
                <div class="card" data-bs-theme="dark">
                    <div class="card-header">Login</div>
                    <div class="card-body">
                        <form action="{{ route('login') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Remember me</label>
                            </div>
                            @if ($errors->any())
                                <div class="alert alert-danger">
                                    <ul>
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <button type="submit" class="btn btn-primary">Login</button>
                            {{-- <a href="{{ route('register') }}" class="btn btn-secondary">Register</a> --}}
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
