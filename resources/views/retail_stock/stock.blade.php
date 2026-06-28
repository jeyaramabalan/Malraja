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
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>{{ __('stock.category') }}</label>
                                    <select class="form-control" name="cat_id" id="cat_id" style="width: 100%;" required>
                                      <option value="0" selected="selected">{{ __('stock.select_category') }}</option>
                                      <?php echo $category; ?>
                                    </select>
                                  </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                  <div class="form-group">
                                    <label>{{ __('stock.product_name') }}</label>
                                    <input class="form-control" type="text" name="name" id="name" required>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>{{ __('stock.s_no') }}</th>
                                        <th>{{ __('stock.product_name') }}</th>
                                        <th>{{ __('stock.purchase') }}</th>
                                        <th>{{ __('stock.sale') }}</th>
                                        <th>{{ __('stock.stock') }}</th>
                                        <th>{{ __('stock.loss') }}</th>
                                        <th>{{ __('stock.free_item_on_sale') }}</th>
                                        <th>{{ __('stock.free_item_on_purchase') }}</th>
                                        <th>{{ __('stock.sale_damage') }}</th>
                                        <th>{{ __('stock.purchase_damage') }}</th>
                                        <th>{{ __('stock.sale_damage_return') }}</th>
                                        <th>{{ __('stock.purchase_damage_return') }}</th>
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
    fetchTable('', '', '', '');
});
$(document).on("change",'#user-select',function() {
    fetchTable('', '', '', '');
});
$('#cat_id').on('change', function() {
    fetchTable('', '', this.value, "");
});
$('#name').keyup(function() {
    fetchTable('', '', "0", this.value);
});
$('#datetimepicker').on('apply.daterangepicker', function(ev, picker) {
    var sDate = picker.startDate.format('YYYY-MM-DD');
    var eDate = picker.endDate.format('YYYY-MM-DD');
    fetchTable(sDate, eDate, '', '');
});

function fetchTable(sDate, eDate, catId, searchName) {
    let table = $('#example1');
    table.DataTable().clear().destroy();
    var userId = $('#user-select').val();
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
            url: '{{route("get-retail_stock-list")}}',
            type: 'POST',
            dataType:'json',
            data:{_token: '{{csrf_token()}}', userId:userId, sDate:sDate, eDate:eDate, catId:catId, searchName:searchName},
        },
        aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
        columns: [
            {data:'sno'},
            {data:'product_name'},
            {data:'purchase'},
            {data:'sale'},
            {data:'stock'},
            {data:'loss'},
            {data:'sale_free'},
            {data:'purchase_free'},
            {data:'sale_damage'},
            {data:'purchase_damage'},
            {data:'sale_damage_return'},
            {data:'purchase_damage_return'}
        ]
    });
}
</script>
@endsection