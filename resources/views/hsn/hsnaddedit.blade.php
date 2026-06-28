@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('hsn.hsn') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('hsn.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('hsn.hsn') }}</li>
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
                            <?php if(isset($hsn->hsn)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="form-group col-12">
                                    <label>{{ __('hsn.hsn_label') }}</label>
                                    <input class="form-control" type="text" name="hsn" id="hsn" value="<?php if(isset($hsn->hsn)){echo $hsn->hsn;} ?>" required>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="id" value="<?php if(isset($hsn->id)){echo $hsn->id;} ?>">
                                    <input class="btn btn-success float-right" type="submit" value="<?php if(isset($hsn->id)){echo __('hsn.update_hsn');}else{echo __('hsn.add_hsn');}?>" onclick="validate()">
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
        // $(document).on('click', '#product_form', function (e) {
        //     $("form#product_form").validate({
        //         rules: {
        //             name: { required: true},
        //             code: { required: true},
        //             category:{required:true},
        //             unit: { required: true},
        //         },
        //         messages: {
        //             name: { required: "Please enter name"},
        //             code: { required: "Please enter code"},
        //             category: { required: "Please select category"},
        //             unit: { required: "Please enter unit"},
        //         },
        //         focusInvalid: true,
        //         invalidHandler: function () {
        //             $(this).find(":input.error:first").focus();
        //         }
        //     });
        // });
    });
    
           
    </script>
@endsection
