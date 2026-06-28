@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('customer.customers') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('customer.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('customer.customers') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('customer.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('customer.s_no') }}</th>
                                        <th>{{ __('customer.name') }}</th>
                                        <th>{{ __('customer.mobile') }}</th>
                                        <th>{{ __('customer.address') }}</th>
                                        <th>{{ __('customer.gst') }}</th>
                                        <th>{{ __('customer.created_by') }}</th>
                                        <th>{{ __('customer.action') }}</th>
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
</section>

<script type="text/javascript">
$(document).ready(function() {
    fetchTable('', '');
});
$(document).on("change",'#user-select',function() {
    var temp = $('#user-select').val();
    var url = "customer?user="+temp;
    if(temp == 0) {
        url = "customer";
    }
    fetchTable('', '');
});

$('#datetimepicker').on('apply.daterangepicker', function(ev, picker) {
    var sDate = picker.startDate.format('YYYY-MM-DD');
    var eDate = picker.endDate.format('YYYY-MM-DD');
    fetchTable(sDate, eDate);
});

function fetchTable(sDate, eDate) 
{
    let table = $('#example1');
    
    table.DataTable().clear().destroy();
    var userId = $('#user-select').val();
    dTable = table.DataTable({
        processing: true,
        serverSide: true,
        pageLength: 50,
        deferRender: true,
        responsive: true,
        info: false,
        paging:false,
        autoWidth: false,
        searching: true,
        sScrollX: '100%',
        dom: 'Bfrtip',
        buttons: [
            // 'excelHtml5',
            // 'pdfHtml5',
        ],
        stateSave: true,
        ajax: {
            url: '{{route("get-customer-list")}}',
            type: 'POST',
            dataType:'json',
            data:{_token: '{{csrf_token()}}', userId:userId, sDate:sDate, eDate:eDate},
        },
        aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
        columns: [
            {data:'sno'},
            {data:'name'},
            {data:'mobile'},
            // {data:'email'},
            {data:'address'},
            // {data:'aadhar_number'},
            {data:'gst'},
            {data:'user_name'},
            {data:'action'}
        ]
    });
}
</script>
@endsection
