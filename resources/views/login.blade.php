@include('layouts.header')
<body class="login-page" style="min-height: 466px;">

    <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-success">
        <div class="card-header text-center">
        <p class="h1"><b>Malraja</b>Traders</a>
        </div>
        <div class="card-body">
        <form action="{{ route('admin.login.submit') }}" method="post">
        @csrf
            <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="{{ __('login.email_placeholder') }}">
            <div class="input-group-append">
                <div class="input-group-text">
                <span class="fas fa-envelope"></span>
                </div>
            </div>
            </div>
            <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="{{ __('login.password_placeholder') }}">
            <div class="input-group-append">
                <div class="input-group-text">
                <span class="fas fa-lock"></span>
                </div>
            </div>
            </div>
            <!-- /.col -->
            <div class="center col-12">
                <button type="submit" class="btn btn-success btn-block">{{ __('login.sign_in') }}</button>
            </div>
        </form>
        <!-- /.card-body -->
    </div>
    <!-- /.card -->
    </div>
    {{-- Success Alert --}}
                    @if(session('status'))
                    <div class="alert alert-success alert-dismissible show" role="alert">
                        {{session('status.msg')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif

                    {{-- Error Alert --}}
                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible show" role="alert">
                        {{session('error.msg')}}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    @endif
</div>