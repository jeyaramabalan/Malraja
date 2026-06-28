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
                    <div class="card-body">
                        <form id="product_form" method="POST" action="{{$route}}">
                            <?php if(isset($product->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                  <div class="form-group">
                                    <label>{{ __('products.category') }}</label>
                                    <select class="form-control" name="cat_id" style="width: 100%;" required>
                                      <option value="" selected="selected">{{ __('products.select_category') }}</option>
                                      <?php echo $category; ?>
                                    </select>
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.product_name') }}</label>
                                      <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($product->name)){echo $product->name;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.tamil_name') }}</label>
                                      <input class="form-control" type="text" name="tname" id="tname" value="<?php if(isset($product->tamil_name)){echo $product->tamil_name;} ?>">
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.product_code') }}</label>
                                      <input class="form-control" type="text" name="code" id="code" value="<?php if(isset($product->code)){echo $product->code;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.product_unit') }}</label>
                                      <select class="form-control" name="unit" id="unit" style="width: 100%;" required>
                                        <option value="" selected="selected">{{ __('products.select_unit') }}</option>
                                        <?php echo $units; ?>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.product_hsn') }}</label>
                                      <select class="form-control" name="hsn" id="hsn" style="width: 100%;" required>
                                        <option selected="selected">{{ __('products.select_hsn') }}</option>
                                        <?php echo $hsn; ?>
                                      </select>
                                  </div>
                                  <div class="form-group">
                                    <label>{{ __('products.purchase_rate') }}</label>
                                    <input class="form-control" type="text" name="prate" id="prate" value="<?php if(isset($product->purchase_rate)){echo $product->purchase_rate;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                      <label>{{ __('products.wholesale_rate') }}</label>
                                      <input class="form-control" type="text" name="mrp" id="mrp" value="<?php if(isset($product->mrp)){echo $product->mrp;} ?>" required>
                                  </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('products.customer_rate') }}</label>
                                        <input class="form-control" type="text" name="crate" id="crate" value="<?php if(isset($product->customer_rate)){echo $product->customer_rate;} ?>" required>
                                    </div>
                                  <div class="form-group">
                                      <label>{{ __('orders.gst') }}</label>
                                      <input class="form-control" type="text" name="gst" id="gst" value="<?php if(isset($product->gst)){echo $product->gst;}else{echo 0;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                    <label>{{ __('products.sgst') }}</label>
                                    <input readonly="true" class="form-control" type="text" name="sgt" id="sgt" value="<?php if(isset($product->sgt)){echo $product->sgt;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                    <label>{{ __('products.cgst') }}</label>
                                    <input readonly="true" class="form-control" type="text" name="cgst" id="cgst" value="<?php if(isset($product->cgst)){echo $product->cgst;} ?>" required>
                                  </div>
                                  <div class="form-group">
                                    <label>{{ __('products.additional_tax') }}</label>
                                    <input class="form-control" type="text" name="atax" id="atax" value="<?php if(isset($product->additional_tax)){echo $product->additional_tax;}else{echo 0;} ?>">
                                  </div>
                                  <div class="form-group">
                                    <label>{{ __('products.final_price') }}</label>
                                    <input class="form-control" type="text" name="fprice" id="fprice" value="<?php if(isset($product->final_price)){echo $product->final_price ;} ?>" required>
                                  </div>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="id" value="<?php if(isset($product->id)){echo $product->id;} ?>">
                                  <input class="btn btn-success float-right" type="submit" value="<?php if(isset($product->id)){echo __('products.update_product');}else{echo __('products.add_product');}?>" onclick="validate()">
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
        $("form#product_form").validate({
            rules: {
                name: { required: true},
                cat_id: { required: true},
                code: { required: true },
                unit: { required: true},
                prate: { required: true},
            },
            messages: {
                name: { required: "{{ __('products.validation_enter_name') }}"},
                cat_id: { required: "{{ __('products.validation_select_category') }}"},
                code: { required: "{{ __('products.validation_enter_code') }}"},
                prate: { required: "{{ __('products.validation_enter_purchase_rate') }}"},
                unit: { required: "{{ __('products.validation_select_unit') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });
    
    $(document).ready(function() {
        $('#gst, #atax, #crate').keyup(function(ev) {
            var total = $('#crate').val() * 1;
            var gstvalue = $('#gst').val() * 1;
            var addlvalue = $('#atax').val() * 1;

            var tot_price =  (total - (total * (100/(100+gstvalue))));
            var tot_price1 =  (total - (total * (100/(100+addlvalue))));

            var tot_netprice = (total - (tot_price+tot_price1)).toFixed(2);
            $('#fprice').val(tot_netprice);
            
            var sum_gst  = gstvalue / 2;
            $('#cgst').val(sum_gst);
            $('#sgt').val(sum_gst);
        });
    });
</script>
@endsection