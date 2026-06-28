@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('route.routes') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('route.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('route.routes') }}</li>
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
                    <!-- /.card-header -->
                    <div class="card-body">
                        <form id="product_form" method="POST" action="{{$route}}">
                            <?php if(isset($routes->name)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="form-group col-12">
                                    <label>{{ __('route.route') }}</label>
                                    <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($routes->name)){echo $routes->name;} ?>" required>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="id" value="<?php if(isset($routes->id)){echo $routes->id;} ?>">
                                    <input class="btn btn-success float-right" type="submit" value="<?php if(isset($routes->id)){echo __('route.update_route');}else{echo __('route.add_route');}?>" onclick="validate()">
                                </div>
                                <!-- /.col -->
                            </div>
                        </form>    
                    </div>
                    <!-- /.card-body -->
                </div>
                <!-- /.card -->
            </div>
            <!-- /.col -->
        </div>
        <!-- /.row -->
    </div>
</section>
<script>
    // No JS validation was present in the original file
</script>
@endsection