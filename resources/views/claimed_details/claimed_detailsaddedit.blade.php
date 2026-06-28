@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('claimed_details.claimed_details') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('claimed_details.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('claimed_details.claimed_details') }}</li>
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
                            <?php if(isset($claimed_detail->id)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('claimed_details.vendor') }}</label>
                                        <select class="form-control select2" name="vendor_id" style="width: 100%;" required>
                                          <option selected="selected">{{ __('claimed_details.select_vendor') }}</option>
                                          <?php echo $vendors_option; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('claimed_details.claimed_by') }}</label>
                                        <select class="form-control" name="claimed_by" style="width: 100%;" required>
                                        <option selected="selected">{{ __('claimed_details.select_user') }}</option>
                                        <?php echo $admin_option; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('claimed_details.amount') }}</label>
                                        <input class="form-control" type="number" name="amount" id="amount" required>
                                    </div>     
                                </div>
                                <div class="col-md-6">                               
                                    <div class="form-group">
                                        <label>{{ __('claimed_details.date') }}</label>
                                        <input class="form-control" type="date" name="date" id="date">
                                    </div> 
                                    <div class="form-group">
                                        <label>{{ __('claimed_details.description') }}</label>
                                        <textarea class="form-control" name="desc" id="desc"></textarea>
                                    </div>                                  
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($claimed_detail->id)){echo $claimed_detail->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($claimed_detail->id)){echo __('claimed_details.update_claimed');}else{echo __('claimed_details.add_claimed');}?>" onclick="validate()">
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
</script>
@endsection
