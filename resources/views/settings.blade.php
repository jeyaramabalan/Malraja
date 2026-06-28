@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                {{-- This page seems to be a placeholder, using 'attendance' as a fallback --}}
                <h1>{{ __('attendance.attendance') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('attendance.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('attendance.attendance') }}</li>
                </ol>
            </div>
        </div>
    </div><!-- /.container-fluid -->
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form method="POST" id="filterForm">
                            <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('users.users') }}</label>
                                    <select id='user-select' class="form-control select2" style="width: 100%;">
                                        <option value="0" selected="selected">{{ __('users.select_user') }}</option>
                                        <?php echo $users; ?>
                                    </select>
                                </div>
                            </div>
                        </form>
                        <div>
                            
                        </div>
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
</section>
<script>
$(document).ready(function () {
    
});
</script>
@endsection