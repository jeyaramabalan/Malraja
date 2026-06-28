@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('retail.retail_order') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('retail.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('retail.retail_order') }}</li>
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
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('retail.customer') }}</label>
                                        <select class="form-control select2" name="cust_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('retail.select_customer') }}</option>
                                          <?php echo $customers; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('retail.date') }}</label>
                                        <input type="date" name="date" class="form-control" id="date">
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('retail.payment_method') }}</label>
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
                                        <label>{{ __('retail.category') }}</label>
                                        <select class="form-control select2" onchange="getProduct(this)" name="cat_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('retail.select_category') }}</option>
                                          <?php echo $category; ?>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('retail.products') }}</label>
                                        <select class="form-control select2" onchange="getSelectedProduct(this)" id="pro_id" name="pro_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('retail.select_product') }}</option>
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('retail.quantity') }}</b><span id="ava_qt"></span></label>
                                        <input type="number" class="form-control" min="1" id="pro_qty" value="1">
                                        <input type="hidden" class="form-control" id="sel_pro_obj">
                                      </div>
                                </div>
                                <div class=" form-group col-md-3" style="padding-top: 30px;">
                                    <button type="button" onclick="addToCart()" id="add_list" class="form-control btn btn-outline-primary">{{ __('retail.add_to_list') }}</button>
                                </div> 
                            </div>
                            <div class="table-responsive" id="order_table">
                                <table class="table table-striped table-bordered show-cart">
                                    {{-- Dynamically loaded table --}}
                                </table>
                                <div class="col-md-12 text-right text-black">
                                    <h4>{{ __('retail.total') }} : Rs <span class="total-cart"></span></h4>
                                </div>
                                <div style="text-align:center;">
                                    <input class="btn btn-outline-warning " id="check1" type="submit" value="{{ __('retail.place_order') }}" />
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

    function getProduct(sel) {
        $.ajax({
            type:"post",
            url: '{{route("get-category-product")}}',
            data:{_token: '{{csrf_token()}}', id:sel.value},
            success:function(res) {
                if(res) {
                    var res = $.parseJSON(res);
                    $('#pro_id').empty().append('<option value="0">{{ __("retail.select_product") }}</option>');
                    $.each(res,function(key, value) {
                        $('#pro_id').append($("<option/>", { value: value.id, text: value.name }));
                    });
                }
            }        
        });
    }

    function showDetails(selectedPayment) {
        if(selectedPayment == "Mixed") {
            $('#cash_upi').show();
        } else {
            $('#cash_upi').hide();
        }
    }
    
    function getSelectedProduct(sel) {
        $.ajax({
           type:"post",
           url: '{{route("get-retail-product")}}',
           data:{_token: '{{csrf_token()}}', id:sel.value},
           success:function(res) {
                if(res) {
                    $("#sel_pro_obj").val(res);
                }
           }
        });
    }
    
    function addToCart() {
        var proId = $("#pro_id").val();
        var proQty = $("#pro_qty").val();
        $.ajax({
           type:"post",
           url: '{{route("retail-add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:proId, proQty:proQty},
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

    function getCart() {
        $.ajax({
           type:"post",
           url: '{{route("get-retail-cart")}}',
           data:{_token: '{{csrf_token()}}'},
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
           url: '{{route("retail-add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:0},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('#total_amount').val(output.total);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function plusCartQty(id, proQty) {
        $.ajax({
           type:"post",
           url: '{{route("retail-add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty + 1},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('#total_amount').val(output.total);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function minusCartQty(id, proQty) {
        $.ajax({
           type:"post",
           url: '{{route("retail-add-to-cart")}}',
           data:{_token: '{{csrf_token()}}', proId:id, proQty:proQty - 1},
           success:function(res) {
                if(res) {
                    var output = $.parseJSON(res);
                    $('.show-cart').html(output.cart);
                    $('#total_amount').val(output.total);
                    $('.total-cart').text(output.total);
                }
           }
        });
    }

    function getCashAmount(upi) {
        var total_amount = $('#total_amount').val();
        $('#cash').val(total_amount - upi);
    }
</script>
@endsection