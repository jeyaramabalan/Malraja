@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('unit.units') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('unit.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('unit.units') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('unit.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('unit.s_no') }}</th>
                                    <th>{{ __('unit.unit') }}</th>
                                    <th>{{ __('unit.action') }}</th>
                                </tr>
                            </thead>
                        </table>
                        </div>
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
                url: '{{route("get-unit-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            columns: [
                {data:'sno'},
                {data:'unit'},
                {data:'action'}
            ]
        });
    }
</script>

@endsection