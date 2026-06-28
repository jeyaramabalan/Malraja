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
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form id="vendor_form" method="POST" action="{{$route}}">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.vendor') }}</label>
                                        <select class="form-control select2" name="cust_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('purchase.select_vendor') }}</option>
                                          <?php echo $vendor; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.date') }}</label>
                                        <input type="date" name="date" class="form-control" id="date">
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.discount') }}</label>
                                        <input type="number" class="form-control" name="discount" id="discount" value="0">
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.bill_no') }}</label>
                                        <input type="text" class="form-control" name="billno" id="billno" value="" required>
                                      </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.category') }}</label>
                                        <select class="form-control select2" onchange="getProduct(this)" name="cat_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('purchase.select_category') }}</option>
                                          <?php echo $category; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('purchase.products') }}</label>
                                        <select class="form-control select2" onchange="getSelectedProduct(this)" id="pro_id" name="pro_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('purchase.select_product') }}</option>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('purchase.quantity') }}</b><span id="ava_qt"></span></label>
                                        <input type="number" class="form-control" min="1" id="pro_qty" value="1">
                                        <input type="hidden" class="form-control" id="sel_pro_obj">
                                      </div>
                                </div>
                                <div class=" form-group col-md-3" style="padding-top: 30px;">
                                    <button type="button" onclick="addToCart()" id="add_list" class="form-control btn btn-outline-primary">{{ __('purchase.add_to_list') }}</button>
                                </div>
                            </div>
                        <div class="table-responsive" id="order_table">
                            <table class="table table-striped table-bordered show-cart">
                                {{-- Dynamically loaded table --}}
                            </table>
                            <div class="col-md-12 text-right text-black">
                                <h4>{{ __('purchase.total') }} : Rs <span class="total-cart"></span> </h4>
                            </div>
                             <div style="text-align:center;">
                                <input class="btn btn-outline-warning " id="check1" type="submit" value="{{ __('purchase.place_order') }}" />
                          </div>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script>
    $(document).ready(function () {
        document.getElementById("date").valueAsDate = new Date();
        $("form#vendor_form").validate({
            rules: {
                name: { required: true},
                address: { required: true},
                mobile: { required: true, minlength:10},
                billno: { required: true},
            },
            messages: {
                name: { required: "{{ __('orders.validation_enter_name') }}"},
                mobile: { required: "{{ __('orders.validation_enter_mobile') }}"},
                address: { required: "{{ __('orders.validation_enter_address') }}"},
                billno: { required: "{{ __('orders.validation_enter_bill_no') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });

    function getProduct(sel) {
        $.ajax({
            type:"post",
            url: '{{route("get-category-product")}}',
            data:{_token: '{{csrf_token()}}', id:sel.value},
            success:function(res) {
                if(res) {
                    var res = $.parseJSON(res);
                    $('#pro_id').empty().append('<option value="0">{{ __("purchase.select_product") }}</option>');
                    $.each(res,function(key, value) {
                        $('#pro_id').append($("<option/>", { value: value.id, text: value.name }));
                    });
                }
            }        
        });
    }
    
    function getSelectedProduct(sel) {
        $.ajax({
           type:"post",
           url: '{{route("get-product")}}',
           data:{_token: '{{csrf_token()}}', id:sel.value},
           success:function(res) {
                if(res) {
                    $("#sel_pro_obj").val(res);
                    var res = $.parseJSON(res);
                    var availableText = "{{ __('orders.available_stock', ['stock' => ':stock']) }}";
                    $("#ava_qt").text(availableText.replace(':stock', res.stock));
                }
           }
        });
    }
    
    function addToCart() {
        var proId = $("#pro_id").val();
        var proQty = $("#pro_qty").val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart-purchase")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    $('.show-cart').on("change", ".item-rate", function(event) {
        var rate = $(this).val();
        var proId = $(this).data('id');
        var proQty = $("#number"+proId).val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart-purchase")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty, rate:rate},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
                }
           }
        });
    });

    function removeToCart(id) {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart-purchase")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:0},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function plusCartQty(id, proQty) {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart-purchase")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function minusCartQty(id, proQty) {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart-purchase")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty - 1},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }
</script>
@endsection