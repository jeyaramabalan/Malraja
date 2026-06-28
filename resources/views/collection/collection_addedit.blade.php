@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('collection.collection') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('collection.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('collection.collection') }}</li>
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
                        <form id="product_form" method="POST" action="{{$route}}">
                            <?php if(isset($collection->id)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('collection.order') }}</label>
                                        <select class="form-control select2" name="order_id"  id="order_id" style="width: 100%;" required>
                                          <option value="0" selected="selected">{{ __('collection.select_order') }}</option>
                                          <?php echo $order_option; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('collection.collected_by') }}</label>
                                        <select class="form-control" name="collected_by" style="width: 100%;" required>
                                        <option selected="selected">{{ __('collection.select_user') }}</option>
                                        <?php echo $admin_option; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('collection.amount') }}
                                            <span id="show_details">
                                                &nbsp; ({{ __('collection.paid') }} : <span style="color: green" id="paid"></span>, {{ __('collection.pending') }} : <span style="color: orange" id="pending"></span>)
                                            </span>
                                        </label>
                                        <input onchange="getCashByAmount(this.value)" class="form-control" type="number" name="amount" id="amount" required>
                                    </div>     
                                    <div class="form-group">
                                        <label>{{ __('collection.payment_method') }}</label>
                                        <select class="form-control select2" onchange="showDetails(this.value)" name="payment_method_id" style="width: 100%;" required>
                                            <option selected="selected" value="Cash">{{ __('collection.cash') }}</option>
                                            <option value="UPI">{{ __('collection.upi') }}</option>
                                            <option value="Mixed">{{ __('collection.mixed') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">                               
                                    <div class="form-group">
                                        <label>{{ __('collection.date') }}</label>
                                        <input class="form-control" type="date" value="@php echo date("Y-m-d"); @endphp" name="date" id="date">
                                    </div> 
                                    <div class="form-group">
                                        <label>{{ __('collection.description') }}</label>
                                        <textarea class="form-control" name="desc" id="desc"></textarea>
                                    </div>                                  
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($collection->id)){echo $collection->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($collection->id)){echo __('collection.update_collection');}else{echo __('collection.add_collection');}?>">
                                    </div>
                                </div>
                            </div>
                            <div id="cash_upi" style="display: none" class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('collection.upi') }}</b></label>
                                        <input onchange="getCashAmount(this.value)" type="number" class="form-control"  name="upi" id="upi" value="0">
                                      </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class=""><b>{{ __('collection.cash') }}</b></label>
                                        <input readonly type="number" class="form-control" min="1" id="cash" name="cash" value="0">
                                        <input type="hidden" id="pendingAmount" value="0">
                                      </div>
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
    $('#order_id').on('change', function() {
        if(this.value != 0) {
            var jsonData = JSON.parse(this.value);
            $('#paid').text(jsonData.paid);
            $('#pending').text(jsonData.total - jsonData.paid);
            $('#pendingAmount').val(jsonData.total - jsonData.paid);
            $('#show_details').show();
        } else {
            $('#show_details').hide();
        }
    });

    $("#amount").change(function() {
        if(parseInt($('#pendingAmount').val()) < parseInt(this.value)) {
            var alertText = "{{ __('collection.amount_validation', ['amount' => ':amount']) }}";
            alert(alertText.replace(':amount', $('#pendingAmount').val()));
            this.value = 0;
        }
    });

    $(document).ready(function () {
        $('#show_details').hide();
    });

    function showDetails(selectedPayment) {
        if(selectedPayment == "Mixed") {
            $('#cash_upi').show();
        } else {
            $('#cash_upi').hide();
        }
    }
    
    function getCashAmount(upi) {
        var total_amount = $('#amount').val();
        $('#cash').val(total_amount - upi);
    }
           
    function getCashByAmount(amount) {
        var upi = $('#upi').val();
        $('#cash').val(amount - upi);
    }
</script>
@endsection
