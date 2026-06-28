@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('products.products') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('products.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('products.products') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('products.add') }}</a>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label>{{ __('products.category') }}</label>
                                    <select class="form-control" name="cat_id" id="cat_id" style="width: 100%;" required>
                                      <option value="0" selected="selected">{{ __('products.select_category') }}</option>
                                      <?php echo $category; ?>
                                    </select>
                                  </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                  <div class="form-group">
                                    <label>{{ __('products.product_name') }}</label>
                                    <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($product->name)){echo $product->name;} ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('products.s_no') }}</th>
                                    <th>{{ __('products.name') }}</th>
                                    <th>{{ __('products.tamil_name') }}</th>
                                    <th>{{ __('products.category_name') }}</th>
                                    <th>{{ __('products.code') }}</th>
                                    <th>{{ __('products.unit') }}</th>
                                    <th>{{ __('products.hsn') }}</th>
                                    <th>{{ __('products.purchase_rate') }}</th>
                                    <th>{{ __('products.wholesale_rate') }}</th>
                                    <th>{{ __('products.customer_rate') }}</th>
                                    <th>{{ __('products.action') }}</th>
                                </tr>
                            </thead>
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
<script type="text/javascript">
    $(document).ready(function() {
        fetchTable("0", "");
    });

    $('#cat_id').on('change', function()
    {
        fetchTable(this.value, "");
    });

    $('#name').keyup(function() {
        fetchTable("0", this.value);
    });

    function fetchTable(catId, searchName) 
    {
        let table = $('#example1');
        // var searchName = $('#name').val();
        table.DataTable().clear().destroy();
        dTable = table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            deferRender: true,
            responsive: true,
            info: true,
            paging:true,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [
                // 'excelHtml5',
                // 'pdfHtml5',
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-product-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}', catId:catId, searchName:searchName},
            },
            // aoColumnDefs:[{bSortable:false,aTargets:[0,3]}],
            columns: [
                {data:'sno'},
                {data:'name'},
                {data:'tamil_name'},
                {data:'category_name'},
                {data:'code'},
                {data:'unit'},
                {data:'hsn'},
                {data:'purchase_rate'},
                {data:'mrp'},
                {data:'customer_rate'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection