@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('users.users') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('users.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('users.users') }}</li>
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
                    <div class="card-header">
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('users.add') }}</a>
                    </div>
                    <div class="card-body">
                        <table id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>    
                                    <th>{{ __('users.s_no') }}</th>
                                    <th>{{ __('users.name') }}</th>
                                    <th>{{ __('users.email') }}</th>
                                    <th>{{ __('users.mobile') }}</th>
                                    <th>{{ __('users.dob') }}</th>
                                    <th>{{ __('users.aadhar') }}</th>
                                    <th>{{ __('users.action') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    $(document).ready(function() {
        fetchTable();
    });
    function fetchTable() {
        let table = $('#example1');
        table.DataTable().clear().destroy();
        dTable = table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            deferRender: true,
            responsive: true,
            info: false,
            paging:false,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-user-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            columns: [
                {data:'sno'},
                {data:'name'},
                {data:'email'},
                {data:'mobile'},
                {data:'dob'},
                {data:'aadhar_number'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection