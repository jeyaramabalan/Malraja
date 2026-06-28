@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('unit.units') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('unit.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('unit.units') }}</li>
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
                            <?php if(isset($unit->unit)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="form-group col-12">
                                    <label>{{ __('unit.unit') }}</label>
                                    <input class="form-control" type="text" name="unit" id="unit" value="<?php if(isset($unit->unit)){echo $unit->unit;} ?>" required>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="id" value="<?php if(isset($unit->id)){echo $unit->id;} ?>">
                                    <input class="btn btn-success float-right" type="submit" value="<?php if(isset($unit->id)){echo __('unit.update_unit');}else{echo __('unit.add_unit');}?>" onclick="validate()">
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
    // No JS validation was present in original file
</script>
@endsection