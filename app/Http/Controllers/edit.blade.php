@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Order Edit</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="   ">Home</a></li>
                    <li class="breadcrumb-item active">Order Edit</li>
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
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form id="vendor_form" method="POST" action="{{$route}}">
                            @method('PATCH')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Customer</label>
                                        <select class="form-control select2" name="cust_id" style="width: 100%;" required>
                                          <option selected="selected">Select Customer</option>
                                          <?php echo $vendor; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date</label>
                                        <input type="date" name="date" class="form-control" id="date">
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Payment Method</label>
                                        <select class="form-control select2" name="payment_method" style="width: 100%;" required>
                                          <option value="Cash">Cash</option>
                                          <option value="UPI">UPI</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Bill No</label>
                                        <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                        <input type="text" readonly class="form-control" value="{{$bill_no}}" name="billno" id="billno" value="" required>
                                      </div>
                                </div>
                                <!-- /.col -->
                            </div>
                            <div class="row">
                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Category</label>
                                        <select class="form-control select2" onchange="getProduct(this)" name="cat_id" style="width: 100%;" required>
                                          <option selected="selected">Select Category</option>
                                          <?php echo $category; ?>
                                        </select>
                                      </div>
                                </div> --}}
                                <!-- /.col -->
                                {{-- <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Products</label>
                                        <select class="form-control select2" onchange="getSelectedProduct(this)" id="pro_id" name="pro_id" style="width: 100%;" required>
                                          <option selected="selected">Select Product</option>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>Quantity</b><span id="ava_qt"></span></label>
                                        <input type="number" class="form-control" min="1" id="pro_qty" value="1">
                                        <input type="hidden" class="form-control" id="sel_pro_obj">
                                      </div>
                                </div>
                                <div class=" form-group col-md-3" style="padding-top: 30px;">
                                    <button type="button" onclick="addToCart()" id="add_list" class="form-control btn btn-outline-primary">Add To List</button>
                                </div> --}}
                                <!-- /.col -->
                            </div>
                        <div class="table-responsive" id="order_table">
                    
                            <!-- My table 124 -->        
                            
                            <table class="table table-striped table-bordered show-cart"><thead class="text-center"><tr><th rowspan="2">S.No</th><th rowspan="2" width="400">Category</th><th rowspan="2" width="400">Prod_Name</th><th rowspan="2" width="400">Quantity</th>
                                <th rowspan="2" width="100">Unit</th><th rowspan="2" width="300">price</th><th rowspan="2" width="300">Rate</th><th rowspan="2" width="300">G Rate</th><th rowspan="2" width="100">Remove</th><th colspan="2" class="text-center">Discount</th><th colspan="2" class="text-center" width="100">GST</th><th colspan="2" class="text-center" width="100">ADT</th><th rowspan="2" width="100">Grand Total<br><small>(Rs)</small></th></tr><tr><th width="100">%</th><th width="300">Amt</th><th width="100">%</th><th width="300">Amt</th><th width="100">%</th><th width="300">Amt</th></tr></thead></table>
                            <div class="col-md-12 text-right text-black">
                            <h4>Total : Rs <span class="total-cart"></span> </h4></div>
                             <div style="text-align:center;">
                            <input class="btn btn-outline-warning " id="check1" type="submit" value="Edit Order" />
                          </div>
                            <!-- My table -->
                        
                        
                        
                        </div>
                    </form>
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
    $(document).ready(function () {
        getCart();
        document.getElementById("date").valueAsDate = new Date();
        $(document).on('click', '#vendor_form', function (e) {
            $("form#vendor_form").validate({
                rules: {
                    name: { required: true},
                    address: { required: true},
                    mobile: { required: true, minlength:10},
                    billno: { required: true},
                },
                messages: {
                    name: { required: "Please enter name"},
                    mobile: { required: "Please enter mobile"},
                    address: { required: "Please enter address"},
                    billno: { required: "Please enter bill no"},
                },
                focusInvalid: true,
                invalidHandler: function () {
                    $(this).find(":input.error:first").focus();
                }
            });
        });

    });

    function getProduct(sel)
    {
        $.ajax({
            type:"post",
            url: '{{route("get-category-product")}}',
            data:{_token: '{{csrf_token()}}', id:sel.value},
            success:function(res)
            {
                if(res)
                {
                    var res = $.parseJSON(res);
                    $('#pro_id')
                        .find('option')
                        .remove()
                        .end()
                        .append('<option value="0">Select Product</option>')
                        .val()
                    ;
                    $.each(res,function(key, value) {
                        $('#pro_id').append($("<option/>", {
                            value: value.id,
                            text: value.name
                        }));
                    });
                }
            }        
        });
    }
    
    function getSelectedProduct(sel)
    {
        $.ajax({
           type:"post",
           url: '{{route("get-product")}}',
           data:{_token: '{{csrf_token()}}', id:sel.value},
           success:function(res)
           {
                if(res)
                {
                    $("#sel_pro_obj").val(res);
                    var res = $.parseJSON(res);
                    $("#ava_qt").text(" (Available " + res.stock + ") ");
                }
           }
        });
    }
    
    function addToCart()
    {
        var proId = $("#pro_id").val();
        var proQty = $("#pro_qty").val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function getCart()
    {
        var id = $("#order_id").val();
        $.ajax({
           type:"post",
           url: '{{route("get-order-cart")}}',
           data:{_token: '{{csrf_token()}}', id:id},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function removeToCart(id)
    {
        var proId = id;
        var proQty = 0;
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function plusCartQty(id, proQty)
    {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function minusCartQty(id, proQty)
    {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty - 1},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    // Item Rate input -- Fixing values
    $('.show-cart').on("change", ".item-rate", function(event) {
        var rate = $(this).val();
        var proId = $(this).data('id');
        var proQty = $("#number"+proId).val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty, rate:rate},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    });

    
    
</script>
@endsection