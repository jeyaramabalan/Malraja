@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('expense.expense') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('expense.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('expense.expense') }}</li>
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
                            <?php if(isset($expense->id)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="form-group col-12">
                                    <label>{{ __('expense.expense_label') }}</label>
                                    <input class="form-control" type="text" name="name" id="name" value="<?php if(isset($expense->name)){echo $expense->name;} ?>" required>
                                </div>
                                <div class="col-12">
                                    <input type="hidden" name="id" value="<?php if(isset($expense->id)){echo $expense->id;} ?>">
                                    <input class="btn btn-success float-right" type="submit" value="<?php if(isset($expense->id)){echo __('expense.update_expense');}else{echo __('expense.add_expense');}?>" onclick="validate()">
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
        
    });
    
           
    </script>
@endsection
