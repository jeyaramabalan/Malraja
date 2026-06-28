@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('users.user') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('users.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('users.user') }}</li>
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
                        <form id="customer_form" method="POST" action="{{$route}}">
                            <?php if(isset($user->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('users.name') }}</label>
                                        <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($user->name)){echo $user->name;} ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('users.mobile') }}</label>
                                        <input class="form-control" type="number" name="mobile" id="mobile" value="<?php if(isset($user->mobile)){echo $user->mobile;} ?>" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('users.email') }}</label>
                                        <input class="form-control" type="email" name="email" id="email" value="<?php if(isset($user->email)){echo $user->email;} ?>">
                                    </div>       
                                </div>
                                <div class="col-md-6">
                                    <?php if(!isset($user->password)){?>
                                    <div class="form-group">
                                        <label>{{ __('users.password') }}</label>
                                        <input class="form-control" type="text" name="password" id="password">
                                    </div>
                                    <?php } ?>
                                    <div class="form-group">
                                        <label>{{ __('users.dob') }}</label>
                                        <input class="form-control" type="date" name="dob" id="dob" value="<?php if(isset($user->dob)){echo $user->dob;} ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('users.aadhar') }}</label>
                                        <input class="form-control" type="number" minlength="12" maxlength="12" name="aadhar" id="aadhar" value="<?php if(isset($user->aadhar_number)){echo $user->aadhar_number;} ?>">
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('users.role') }}</label>
                                        <select class="form-control" name="role" id="role" style="width: 100%;" required>
                                          <option>{{ __('users.select_role') }}</option>
                                          <?php echo $roles; ?>
                                        </select>
                                    </div>                           
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($user->id)){echo $user->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($user->id)){echo __('users.update_user');}else{echo __('users.add_user');}?>" onclick="validate()">
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
                mobile: { required: true, minlength:10},
            },
            messages: {
                name: { required: "{{ __('users.validation_enter_name') }}"},
                address: { required: "{{ __('users.validation_enter_address') }}"},
            },
            focusInvalid: true,
            invalidHandler: function () {
                $(this).find(":input.error:first").focus();
            }
        });
    });           
</script>
@endsection