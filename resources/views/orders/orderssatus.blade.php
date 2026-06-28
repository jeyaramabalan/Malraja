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
                        <input type="hidden" value="{{$order_status}}" id="status" />
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{route('daily-profit')}}">
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <label>{{ __('orders.date') }}</label>
                                    <input class="form-control" type="text" id="datetimepicker" name="date" />
                                </div>
                                <div class="col-3">
                                    <label>&nbsp;</label>
                                    <input type="button" onclick="fetchTableWithdate()" class="form-control btn btn-success" value="{{ __('orders.view_button') }}"/>
                                </div>
                                @if($order_status == 1 || $order_status == 2)
                                    <div class="col-3">
                                        <label>{{ __('orders.change_all_status') }}</label>
                                        <input type="button" data-toggle="modal" data-target="#exampleModal" class="form-control btn btn-warning" value="<?php if($order_status == 1) {echo __('orders.move_all_to_delivery');} else if($order_status == 2) {echo __('orders.move_all_to_payment');}?>"/>
                                    </div>
                                @endif  
                            </div>
                            <input id="generate" style="display: none" type="submit" class="form-control btn btn-primary" value="{{ __('orders.generate') }}" />
                        </form>
                        <div class="table-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('orders.s_no') }}</th>
                                        <th>{{ __('orders.bill_no') }}</th>
                                        <th>{{ __('orders.customer') }}</th>
                                        <th>{{ __('orders.date') }}</th>
                                        <th>{{ __('orders.payment_method') }}</th>
                                        <th>{{ __('orders.status') }}</th>
                                        <th>{{ __('orders.total') }}</th>
                                        <th>{{ __('orders.pending') }}</th>
                                        <th>{{ __('orders.created_by') }}</th>
                                        <th>{{ __('orders.action') }}</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Status Update Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">{{ __('orders.modal_order_status_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">{{ __('orders.modal_confirm_change_all') }}</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('orders.modal_no') }}</button>
                    <button type="button" onclick="updateAllStatus()" class="btn btn-danger">{{ __('orders.modal_yes') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Complete Order Status Modal -->
    <div class="modal fade" id="orderCompleteModal" tabindex="-1" role="dialog" aria-labelledby="orderCompleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="orderCompleteModalLabel">{{ __('orders.modal_complete_order_title') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <p>{{ __('orders.modal_payment_method') }} : <span class="font-weight-bold text-success" id="method"></span></p>
                    <p>{{ __('orders.modal_pending_amount') }} : <span class="font-weight-bold text-success" id="amount"></span></p>
                    <p>{{ __('orders.modal_confirm_complete_order') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('orders.modal_no') }}</button>
                    <button type="button" onclick="completeOrder()" class="btn btn-danger">{{ __('orders.modal_yes') }}</button>
                    <input type="hidden" id="orderId" value="" />
                    <input type="hidden" id="orderStatus" value="" />
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
            searching: true,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-order-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}', status:status},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'bill_id'},
                {data:'customerName'},
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
                data:{_token: '{{csrf_token()}}', status:status, date:date},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'bill_id'},
                {data:'customerName'},
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

    function completeOrder() 
    {
        var orderId = $('#orderId').val();
        var statusId = $('#orderStatus').val();
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

    function showModal(id, status, paymentMethod, pending) {
        $('#orderId').val(id);
        $('#orderStatus').val(status);
        $('#method').text(paymentMethod);
        $('#amount').text(pending);
        $('#orderCompleteModal').modal('show');
    }

    function updateAllStatus() 
    {
        var newStatus = 1;
        var status = $('#status').val();
        if(status == 1) {
            newStatus = 2;
        }
        else if(status == 2) {
            newStatus = 3;
        }
        else if(status == 3) {
            newStatus = 4;
        }

        $.ajax({
           type:"post",
           url: '{{route("status-update-all")}}',
           data:{_token: '{{csrf_token()}}', status: status, newStatus: newStatus},
           success:function(res) {
            location.reload();
           }
    
        });
    }
</script>
@endsection