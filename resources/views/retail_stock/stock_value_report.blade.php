@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('stock.stock') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('stock.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('stock.stocks') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('stock.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('stock.s_no') }}</th>
                                        <th>{{ __('stock.product_name') }}</th>
                                        <th>{{ __('stock.sale') }}</th>
                                        <th>{{ __('stock.purchase') }}</th>
                                        <th>{{ __('stock.stock') }}</th>
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
    fetchTable('', '');
});
$('#datetimepicker').on('apply.daterangepicker', function(ev, picker) {
    var sDate = picker.startDate.format('YYYY-MM-DD');
    var eDate = picker.endDate.format('YYYY-MM-DD');
    fetchTable(sDate, eDate);
});

function fetchTable(sDate, eDate) {
    let table = $('#example1');
    table.DataTable().clear().destroy();
    var userId = $('#user-select').val();
    dTable = table.DataTable({
        processing: true,
        serverSide: true,
        pageLength: 1, // This is unusual, you may want to increase it
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
            url: '{{route("get-stock-value-list")}}',
            type: 'POST',
            dataType:'json',
            data:{_token: '{{csrf_token()}}', userId:userId, sDate:sDate, eDate:eDate},
        },
        aoColumnDefs:[{bSortable:false,aTargets:[0]}], // Corrected aTargets from [0,6] to [0]
        columns: [
            {data:'sno'},
            {data:'product_name'},
            {data:'sale'},
            {data:'purchase'},
            {data:'stock'},
        ]
    });
}
</script>
@endsection