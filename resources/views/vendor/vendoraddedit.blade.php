@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('vendor.vendor') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('vendor.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('vendor.vendor') }}</li>
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
                            <?php if(isset($vendor->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('vendor.name') }}</label>
                                        <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($vendor->name)){echo $vendor->name;} ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('vendor.mobile') }}</label>
                                        <input class="form-control" type="number" name="mobile" id="mobile" value="<?php if(isset($vendor->mobile)){echo $vendor->mobile;} ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('vendor.email') }}</label>
                                        <input class="form-control" type="email" name="email" id="email" value="<?php if(isset($vendor->email)){echo $vendor->email;} ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('vendor.gst') }}</label>
                                        <input class="form-control" type="text" name="gst" id="gst"  value="<?php if(isset($vendor->gst)){echo $vendor->gst;} ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('vendor.address') }}</label>
                                        <textarea class="form-control" type="text" name="address" id="address"><?php if(isset($vendor->address)){echo $vendor->address;} ?></textarea>
                                    </div>                                  
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($vendor->id)){echo $vendor->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($vendor->id)){echo __('vendor.update_vendor');}else{echo __('vendor.add_vendor');}?>" onclick="validate()">
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
        $("form#vendor_form").validate({
            rules: {
                name: { required: true},
                address: { required: true},
                mobile: { required: true, minlength:10},
            },
            messages: {
                name: { required: "{{ __('vendor.validation_enter_name') }}"},
                mobile: { required: "{{ __('vendor.validation_enter_mobile') }}"},
                address: { required: "{{ __('vendor.validation_enter_address') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });           
</script>
@endsection