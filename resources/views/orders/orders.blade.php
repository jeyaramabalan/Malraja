@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('orders.orders') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('orders.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('orders.orders') }}</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('orders.add') }}</a>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{route('daily-profit')}}">
                            @csrf
                            <div class="row">
                                <div class="col-3">
                                    <label>{{ __('orders.status') }}</label>
                                    <select class="form-control" onchange="fetchTable()" id="status" style="width: 100%;">
                                        <option selected="selected" value="">{{ __('orders.select_status') }}</option>
                                        <option value="1">{{ __('orders.status_pending') }}</option>
                                        <option value="2">{{ __('orders.status_confirmed') }}</option>
                                        <option value="3">{{ __('orders.status_payment_pending') }}</option>
                                        <option value="4">{{ __('orders.status_completed') }}</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label>{{ __('orders.order_type') }}</label>
                                    <select class="form-control" onchange="fetchTable()" id="type" style="width: 100%;">
                                        <option selected="selected" value="">{{ __('orders.select_order_type') }}</option>
                                        <option value="1">{{ __('orders.delivery') }}</option>
                                        <option value="2">{{ __('orders.pos') }}</option>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label>{{ __('orders.date') }}</label>
                                    <input class="form-control" type="text" id="datetimepicker" name="date" />
                                </div>
                                <div class="col-3">
                                    <label>&nbsp;</label> {{-- Using non-breaking space for layout --}}
                                    <input type="button" onclick="fetchTableWithdate()" class="form-control btn btn-success" value="{{ __('orders.view_button') }}"/>
                                </div>
                            </div>
                        </form>
                        <div class="table-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('orders.s_no') }}</th>
                                        <th>{{ __('orders.bill_no') }}</th>
                                        <th>{{ __('orders.customer') }}</th>
                                        <th>{{ __('orders.order_type') }}</th>
                                        <th>{{ __('orders.date') }}</th>
                                        <th>{{ __('orders.payment_method') }}</th>
                                        <th>{{ __('orders.status') }}</th>
                                        <th>{{ __('orders.total') }}</th>
                                        <th>{{ __('orders.pending') }}</th>
                                        <th>{{ __('orders.created_by') }}</th>
                                        <th>{{ __('orders.action') }}</th>
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
        
    function fetchTable() 
    {
        var status = $('#status').val();
        var types = $('#type').val();
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
            buttons: [
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-order-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}', status:status, types:types},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'bill_id'},
                {data:'customerName'},
                {data:'type'},
                {data:'date'},
                {data:'payment_method'},
                {data:'orderStatus'},
                {data:'total'},
                {data:'paymentPending'},
                {data:'userName'},
                {data:'action'},
            ]
        });
    }   

    function fetchTableWithdate() 
    {
        var date = $("#datetimepicker").val();
        var status = $('#status').val();
        var types = $('#type').val();
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
            buttons: [
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-order-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}', status:status, types:types, date:date},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'bill_id'},
                {data:'customerName'},
                {data:'type'},
                {data:'date'},
                {data:'payment_method'},
                {data:'orderStatus'},
                {data:'total'},
                {data:'paymentPending'},
                {data:'userName'},
                {data:'action'},
            ]
        });
    }   

    function updateStatus(orderId, statusId) 
    {
        $('#update-btn'+orderId).hide();
        $('#spinner'+orderId).show();
        $.ajax({
           type:"post",
           url: '{{route("status-update")}}',
           data:{_token: '{{csrf_token()}}', id:orderId, status:statusId},
           success:function(res){
            location.reload();
           }
    
        });
    }
</script>
@endsection