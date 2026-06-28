@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('category.category') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('category.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('category.category') }}</li>
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
                            <?php if(isset($category->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('category.name_label') }}</label>
                                        <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($category->name)){echo $category->name;} ?>" required>
                                    </div>
                                                            
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($category->id)){echo $category->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($category->id)){echo __('category.update_category');}else{echo __('category.add_category');}?>" onclick="validate()">
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
                name: { required: true},
            },
            messages: {
                name: { required: "{{ __('category.validation_enter_name') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });           
</script>
@endsection
