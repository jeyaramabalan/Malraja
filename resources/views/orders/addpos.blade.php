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
                    <div class="card-body">
                        <form id="vendor_form" method="POST" action="{{$route}}">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.date') }}</label>
                                        <input type="date" name="date" class="form-control" id="date">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.payment_method') }}</label>
                                        <select onchange="showDetails(this.value)" class="form-control select2" name="payment_method_id" style="width: 100%;" required>
                                          <option selected="selected" value="Cash">{{ __('orders.cash') }}</option>
                                          <option value="UPI">{{ __('orders.upi') }}</option>
                                          <option value="Mixed">{{ __('orders.mixed') }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div id="cash_upi" style="display: none" class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('orders.upi') }}</b></label>
                                        <input onchange="getCashAmount(this.value)" type="number" class="form-control" min="1" name="upi" id="upi" value="0">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('orders.cash') }}</b></label>
                                        <input readonly type="number" class="form-control" min="1" id="cash" name="cash" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.category') }}</label>
                                        <select class="form-control select2" onchange="getProduct(this)" name="cat_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('orders.select_category') }}</option>
                                          <?php echo $category; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.products') }}</label>
                                        <select class="form-control select2" onchange="getSelectedProduct(this)" id="pro_id" name="pro_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('orders.select_product') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('orders.quantity') }}</b><span id="ava_qt"></span></label>
                                        <input type="number" class="form-control" min="1" id="pro_qty" value="1">
                                        <input type="hidden" class="form-control" id="sel_pro_obj">
                                    </div>
                                </div>
                                <div class=" form-group col-md-3" style="padding-top: 30px;">
                                    <button type="button" onclick="addToCart()" id="add_list" class="form-control btn btn-outline-primary">{{ __('orders.add_to_list') }}</button>
                                </div>
                            </div>
                            <div class="row ml-3">
                                <div class="col-md-3 pl-2">
                                    <div class="form-group">
                                        <input class="form-check-input" type="checkbox" name="iswhole" value="1" id="iswhole">
                                        <label class="form-check-label" for="iswhole">{{ __('orders.is_wholesale') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3 pl-2">
                                    <div class="form-group">
                                        <input class="form-check-input" type="checkbox" name="isPrint" value="1" checked id="isPrint">
                                        <label class="form-check-label" for="isPrint">{{ __('orders.save_and_print') }}</label>
                                    </div>
                                </div>
                                <div class="col-md-3 pl-2">
                                    <div class="form-group">
                                        <input class="form-check-input" type="checkbox" name="isGstPrint" value="1" checked id="isGstPrint">
                                        <label class="form-check-label" for="isGstPrint">{{ __('orders.is_gst_print') }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" id="order_table">
                                <table class="table table-striped table-bordered show-cart">
                                    {{-- Table headers are loaded dynamically from JS --}}
                                </table>
                                <div class="col-md-12 text-right text-black">
                                    <h4>{{ __('orders.total') }} : Rs <span class="total-cart"></span></h4>
                                </div>
                                <div style="text-align:center;">
                                    <input class="btn btn-outline-warning " id="check1" type="submit" value="{{ __('orders.place_order') }}" />
                                    <input id="total_amount" name="total_amount" type="hidden" />
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
        $("vendor_form").submit(function() {
            $(this).submit(function() {
                return false;
            });
            return true;
        }); 

        document.getElementById("date").valueAsDate = new Date();
        $(document).on('click', '#vendor_form', function (e) {
            $("form#vendor_form").validate({
                rules: {
                    name: { required: true},
                    address: { required: true},
                    mobile: { required: true, minlength:10},
                },
                messages: {
                    name: { required: "Please enter name"},
                    mobile: { required: "Please enter mobile"},
                    address: { required: "Please enter address"},
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

    function addToCart()
    {
        var iswhole = 0;
        if($("#iswhole").is(':checked')) {
            iswhole = 1;
        } else {
            iswhole = 0;
        }
        var res = $.parseJSON($("#sel_pro_obj").val());
        if(res.stock < $("#pro_qty").val()) {
            alert("{{ __('orders.stock_not_available') }}");
            return;
        }
        var proId = $("#pro_id").val();
        var proQty = $("#pro_qty").val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty, iswhole:iswhole},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
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
                    $('#total_amount').val(output.total);
                }
           }
        });
    }

    function plusCartQty(id, proQty)
    {
        var iswhole = 0;
        if($("#iswhole").is(':checked')) {
            iswhole = 1;
        } else {
            iswhole = 0;
        }
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1, iswhole:iswhole},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    if(output.error == 1) {
                        alert("Stock not available");
                    } else {
                        $('.show-cart').html(output.cart);
                        $('.total-cart').text(output.total);
                        $('#total_amount').val(output.total);
                    }
                }
           }
        });
    }

    function minusCartQty(id, proQty)
    {
        var iswhole = 0;
        if($("#iswhole").is(':checked')) {
            iswhole = 1;
        } else {
            iswhole = 0;
        }
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty - 1, iswhole:iswhole},
           success:function(res)
           {
                if(res)
                {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
                }
           }
        });
    }

    function getCustomerName(value)
    {
        if(value.indexOf("-") > 0) {
            var temp = value.split("-");
            $('#name').val(temp[1]);
            $('#cust_id').val(temp[0]);
        } else {
            $('#cust_id').val("");
            $('#name').val("");
        }
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
                    $('#total_amount').val(output.total);
                }
           }
        });
    });

    function showDetails(selectedPayment) {
        if(selectedPayment == "Mixed") {
            $('#cash_upi').show();
        } else {
            $('#cash_upi').hide();
        }
    }
    
    
    function getCashAmount(upi) {
        var total_amount = $('#total_amount').val();
        $('#cash').val(total_amount - upi);
    }
    
</script>
@endsection