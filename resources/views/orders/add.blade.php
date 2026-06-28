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
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('orders.customer') }}</label>
                                        <select class="form-control select2" onchange="getCustomerPendingPayment(this.value)" name="cust_id" style="width: 100%;" required>
                                          <option selected="selected" value="0">{{ __('orders.select_customer') }}</option>
                                          <?php echo $customers; ?>
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
                                        <select onchange="showDetails(this.value)" class="form-control select2" name="payment_method_id" style="width: 100%;" required>
                                          <option selected="selected" value="Cash">{{ __('orders.cash') }}</option>
                                          <option value="UPI">{{ __('orders.upi') }}</option>
                                          <option value="Mixed">{{ __('orders.mixed') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div style="padding-top: 36px;">  </div>
                                    <span style="font-size: 18px;color:chocolate;" id="pending"></span>
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
                                <!-- /.col -->
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
                                <!-- /.col -->
                            </div>
                            <div class="row ml-3">
                                <div class="col-md-3 pl-2">
                                    <div class="form-group">
                                        <input class="form-check-input" type="checkbox" name="ispaid" value="1" id="ispaid">
                                        <label class="form-check-label" for="ispaid">
                                          {{ __('orders.is_paid') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3 pl-2">
                                    <div class="form-group">
                                        <input class="form-check-input" type="checkbox" name="iswhole" value="1" id="iswhole">
                                        <label class="form-check-label" for="iswhole">
                                          {{ __('orders.is_wholesale') }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <div class="table-responsive" id="order_table">
                    
                            <table class="table table-striped table-bordered show-cart">
                                <thead class="text-center">
                                    <tr>
                                        <th rowspan="2">{{ __('orders.s_no') }}</th>
                                        <th rowspan="2" width="400">{{ __('orders.category') }}</th>
                                        <th rowspan="2" width="400">{{ __('orders.prod_name') }}</th>
                                        <th rowspan="2" width="400">{{ __('orders.quantity') }}</th>
                                        <th rowspan="2" width="100">{{ __('orders.unit') }}</th>
                                        <th rowspan="2" width="300">{{ __('orders.price') }}</th>
                                        <th rowspan="2" width="300">{{ __('orders.rate') }}</th>
                                        <th rowspan="2" width="300">{{ __('orders.g_rate') }}</th>
                                        <th rowspan="2" width="100">{{ __('orders.remove') }}</th>
                                        <th colspan="2" class="text-center">{{ __('orders.discount') }}</th>
                                        <th colspan="2" class="text-center" width="100">{{ __('orders.gst') }}</th>
                                        <th colspan="2" class="text-center" width="100">{{ __('orders.adt') }}</th>
                                        <th rowspan="2" width="100">{{ __('orders.grand_total') }}<br><small>{{ __('orders.rs_symbol') }}</small></th>
                                    </tr>
                                    <tr>
                                        <th width="100">{{ __('orders.percent') }}</th>
                                        <th width="300">{{ __('orders.amount') }}</th>
                                        <th width="100">{{ __('orders.percent') }}</th>
                                        <th width="300">{{ __('orders.amount') }}</th>
                                        <th width="100">{{ __('orders.percent') }}</th>
                                        <th width="300">{{ __('orders.amount') }}</th>
                                    </tr>
                                </thead>
                            </table>
                            <div class="col-md-12 text-right text-black">
                                <h4>{{ __('orders.total') }} : Rs <span class="total-cart"></span> </h4>
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
        document.getElementById("date").valueAsDate = new Date();
        
        // This validation message is now also translatable
        $("form#vendor_form").validate({
            rules: {
                cust_id: { required: true, notEqual: "0"},
            },
            messages: {
                cust_id: { required: "{{ __('orders.please_select_customer') }}"},
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
                    $('#pro_id').empty().append('<option value="0">{{ __("orders.select_product") }}</option>');
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
                    // Using translated string with placeholder
                    var availableText = "{{ __('orders.available_stock', ['stock' => ':stock']) }}";
                    $("#ava_qt").text(availableText.replace(':stock', res.stock));
                }
           }
        });
    }
    
    function addToCart() {
        var iswhole = $("#iswhole").is(':checked') ? 1 : 0;
        var res = $.parseJSON($("#sel_pro_obj").val());
        if(res.stock < $("#pro_qty").val()) {
            // Using translated alert message
            alert("{{ __('orders.stock_not_available') }}");
            return;
        }
        var proId = $("#pro_id").val();
        var proQty = $("#pro_qty").val();
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty, iswhole:iswhole},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
                }
           }
        });
    }

    function removeToCart(id) {
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:0},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
                }
           }
        });
    }

    function plusCartQty(id, proQty) {
        var iswhole = $("#iswhole").is(':checked') ? 1 : 0;
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1, iswhole:iswhole},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    if(output.error == 1) {
                        alert("{{ __('orders.stock_not_available') }}");
                    } else {
                        $('.show-cart').html(output.cart);
                        $('.total-cart').text(output.total);
                        $('#total_amount').val(output.total);
                    }
                }
           }
        });
    }

    function minusCartQty(id, proQty) {
        var iswhole = $("#iswhole").is(':checked') ? 1 : 0;
        $.ajax({
           type:"post",
           url: '{{route("add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty - 1, iswhole:iswhole},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('.total-cart').text(output.total);
                    $('#total_amount').val(output.total);
                }
           }
        });
    }

    function getCustomerPendingPayment(id) {
        $.ajax({
           type:"post",
           url: '{{route("get-customer-pending")}}',
           data:{_token: '{{csrf_token()}}', id:id},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    var pendingText = "{{ __('orders.customer_pending', ['amount' => ':amount']) }}";
                    $("#pending").text(pendingText.replace(':amount', output));
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
           url: '{{route("add-to-cart")}}',
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