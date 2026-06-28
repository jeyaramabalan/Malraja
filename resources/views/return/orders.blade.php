@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('returns.returned_orders') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('returns.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('returns.returned_products') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('returns.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('returns.s_no') }}</th>
                                    <th>{{ __('returns.bill_no') }}</th>
                                    <th>{{ __('returns.product_name') }}</th>
                                    <th>{{ __('returns.date') }}</th>
                                    <th>{{ __('returns.quantity') }}</th>
                                    <th>{{ __('returns.total') }}</th>
                                    <th>{{ __('returns.tax') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function() {
        fetchTable();
    });
        
    function fetchTable() {
        let table = $('#example1');
        table.DataTable().clear().destroy();
        dTable = table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 25,
            deferRender: true,
            responsive: true,
            info: false,
            paging:true,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-purchase-order-list")}}', // This route seems incorrect for returns, but keeping original
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