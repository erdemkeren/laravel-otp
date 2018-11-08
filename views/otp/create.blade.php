@extends('layouts.app')

@section('content')
    <div class="container be-detail-container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">

                    <div class="card-header">
                        OTP Required
                    </div><!-- Card Header-->

                    <div class="card-body">
                        <form method="POST" id="otp-verification-form" action="{{ route('otp.store') }}">
                        @csrf

                        <!-- Input -->
                            <div class="form-group row">
                                <label for="email" class="col-sm-4 col-form-label text-md-right">One Time Password:</label>

                                <!-- Error handling -->
                                <div class="col-md-6">
                                    <input
                                            id="password"
                                            type="password"
                                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}"
                                            name="password"
                                            required autofocus
                                    >

                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="form-group row mb-0">
                                <div class="col-md-8 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Verify
                                    </button>
                                    <a class="btn btn-link" href="">
                                        Need a new password?
                                    </a>
                                </div>
                            </div>

                        </form>
                    </div><!-- Card Body -->

                </div><!-- Card -->
            </div><!-- Column -->
        </div><!-- Row -->
    </div><!-- Container -->
@endsection
