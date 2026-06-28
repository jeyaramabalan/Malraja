@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('orders.order_edit') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('orders.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('orders.order_edit') }}</li>
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
                    <div class="card-body">
                        <form id="vendor_form" method="POST" action="{{$route}}">
                            @method('PATCH')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.customer') }}</label>
                                        <select class="form-control select2" name="cust_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('orders.select_customer') }}</option>
                                          <?php echo $vendor; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.date') }}</label>
                                        <input type="date" name="date" class="form-control" id="date">
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.payment_method') }}</label>
                                        <select class="form-control select2" onchange="showDetails(this.value)" name="payment_method" id="payment_method" style="width: 100%;" required>
                                          <option <?php if($payment_method == "Cash") {echo "selected='selected'";} ?> value="Cash">{{ __('orders.cash') }}</option>
                                          <option <?php if($payment_method == "UPI") {echo "selected='selected'";} ?> value="UPI">{{ __('orders.upi') }}</option>
                                          <option <?php if($payment_method == "Mixed") {echo "selected='selected'";} ?> value="Mixed">{{ __('orders.mixed') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.bill_no') }}</label>
                                        <input type="hidden" name="order_id" id="order_id" value="{{$order_id}}">
                                        <input type="text" readonly class="form-control" value="{{$bill_no}}" name="billno" id="billno" required>
                                      </div>
                                </div>
                            </div>
                            <div id="cash_upi" style="display: none" class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('orders.upi') }}</b></label>
                                        <input onchange="getCashAmount(this.value)" type="number" class="form-control" min="1" name="upi" id="upi" value="{{$upi}}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('orders.cash') }}</b></label>
                                        <input readonly type="number" class="form-control" min="1" id="cash" name="cash" value="0">
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" id="order_table">
                                <table class="table table-striped table-bordered show-cart">
                                    {{-- Table headers are the same as add.blade.php, they are loaded dynamically --}}
                                </table>
                                <div class="col-md-12 text-right text-black">
                                    <h4>{{ __('orders.total') }} : Rs <span class="total-cart"></span></h4>
                                </div>
                                <div style="text-align:center;">
                                    <input class="btn btn-outline-warning " id="check1" type="submit" value="{{ __('orders.edit_order') }}" />
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
    // Keep your JS as is, it dynamically loads the table which doesn't need static text translation here.
    // The validation messages need translation
    $(document).ready(function () {
        showDetails($("#payment_method").val());
        getCart();
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
                    $('#total_amount').val(output.total);
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
                    $('#total_amount').val(output.total);
                    $('#cash').val(output.total - $('#upi').val());
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
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1},
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
                    $('#total_amount').val(output.total);
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
                    $('#total_amount').val(output.total);
                    $('#cash').val(output.total - upi);
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