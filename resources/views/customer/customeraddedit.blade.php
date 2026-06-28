@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('customer.customer') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('customer.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('customer.customer') }}</li>
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
                        <form id="customer_form" method="POST" action="{{$route}}">
                            <?php if(isset($customer->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('customer.name') }}</label>
                                        <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($customer->name)){echo $customer->name;} ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('customer.mobile') }}</label>
                                        <input class="form-control" type="number" name="mobile" id="mobile" value="<?php if(isset($customer->mobile)){echo $customer->mobile;} ?>" >
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('customer.email') }}</label>
                                        <input class="form-control" type="email" name="email" id="email" value="<?php if(isset($customer->email)){echo $customer->email;} ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('customer.address') }}</label>
                                        <textarea class="form-control" type="text" name="address" id="address"><?php if(isset($customer->address)){echo $customer->address;} ?></textarea>
                                    </div>       
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('customer.route') }}</label>
                                        <select class="form-control" name="route" style="width: 100%;">
                                          <option value="0" selected="selected">{{ __('customer.select_route') }}</option>
                                          <?php echo $route_option; ?>
                                        </select>
                                      </div>
                                    <div class="form-group">
                                        <label>{{ __('customer.aadhar') }}</label>
                                        <input class="form-control" type="number" minlength="12" maxlength="12" name="aadhar" id="aadhar" value="<?php if(isset($customer->aadhar_number)){echo $customer->aadhar_number;} ?>">
                                    </div>   
                                    <div class="form-group">
                                        <label>{{ __('customer.gst') }}</label>
                                        <input class="form-control" type="text" name="gst" id="gst" value="<?php if(isset($customer->gst)){echo $customer->gst;} ?>">
                                    </div>                           
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($customer->id)){echo $customer->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($customer->id)){echo __('customer.update_customer');}else{echo __('customer.add_customer');}?>" onclick="validate()">
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
    $(document).ready(function () {
        $("form#customer_form").validate({
            rules: {
                name: { required: true, minlength:4},
            },
            messages: {
                name: { required: "{{ __('customer.validation_enter_name') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });           
</script>
@endsection
