@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('purchase.purchase_orders') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('purchase.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('purchase.purchase_orders') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('purchase.add') }}</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('purchase.s_no') }}</th>
                                    <th>{{ __('purchase.bill_no') }}</th>
                                    <th>{{ __('purchase.vendor') }}</th>
                                    <th>{{ __('purchase.date') }}</th>
                                    <th>{{ __('purchase.total') }}</th>
                                    <th>{{ __('purchase.created_by') }}</th>
                                    <th>{{ __('purchase.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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
    $(document).ready(function() {
        fetchTable();
    });
        
    function fetchTable() 
    {
        let table = $('#example1');
        table.DataTable().clear().destroy();
        dTable = table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            deferRender: true,
            responsive: true,
            info: true,
            paging:true,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-purchase-order-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'bill_id'},
                {data:'customerName'},
                {data:'date'},
                {data:'total'},
                {data:'userName'},
                {data:'action'},
            ]
        });
    }
</script>
@endsection