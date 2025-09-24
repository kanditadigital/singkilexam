@extends('site.main')

@section('content')
    <div class="home-header d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8"></div>
                <div class="col-md-4">
                    <div class="card card-login shadow">
                        <div class="card-body">
                            <div class="my-4 text-center">
                                <h5>Member Area</h5>
                            </div>
                            <form action="{{ route('login.sch') }}" method="POST">
                                @csrf
                                <div class="input-group flex-nowrap mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control" placeholder="Email" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-4">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-key"></i></span>
                                    <input type="password" name="password" class="form-control" placeholder="Password" autocomplete="off" required>
                                </div>
                                <div class="input-group mb-3">
                                    <div class="d-flex align-items-center">
                                        <img id="captcha-img" src="{{ captcha_src('flat') }}" alt="captcha" style="width: 100%; height:auto;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm ms-2" id="refresh-captcha">
                                            <i class="fa-solid fa-sync"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1"><i class="fa-solid fa-barcode"></i></span>
                                    <input type="text" class="form-control" name="captcha" placeholder="Kode Keamanan" required>
                                </div>
                                <div class="mb-3">
                                    <button type="submit" class="btn btn-cat w-100">Login</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="home-about p-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 text-center">
                    <h4>Apa Itu Assesmen Nasional?</h4>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('refresh-captcha').addEventListener('click', function () {
            fetch('/refresh-captcha')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('captcha-img').src = data.captcha;
                });
        });
    });
    </script>
    
@endpush
