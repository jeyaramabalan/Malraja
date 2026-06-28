@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('daily_expense.expense') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('daily_expense.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('daily_expense.expense') }}</li>
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
                            <?php if(isset($daily_expense->id)){?>@method('PATCH')<?php }else {?>@method('POST')<?php }?>
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.expense_type') }}</label>
                                        <select class="form-control" name="expense_id" style="width: 100%;" required>
                                        <option selected="selected">{{ __('daily_expense.select_expense_type') }}</option>
                                        <?php echo $expense_type; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.expense_by') }}</label>
                                        <select class="form-control" name="user_id" style="width: 100%;" required>
                                        <option selected="selected">{{ __('daily_expense.select_user') }}</option>
                                        <?php echo $admin_option; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.date') }}</label>
                                        <input class="form-control" type="date" name="date" id="date">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.amount') }}</label>
                                        <input class="form-control" type="number" name="amount" id="amount" required>
                                    </div>
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.bill') }}</label>
                                        <input class="form-control" type="file" name="bill" id="bill" >
                                    </div>   
                                    <div class="form-group">
                                        <label>{{ __('daily_expense.description') }}</label>
                                        <textarea class="form-control" name="desc" id="desc"></textarea>
                                    </div>                                  
                                    <div class="col-12">
                                        <input type="hidden" name="id" value="<?php if(isset($daily_expense->id)){echo $daily_expense->id;} ?>">
                                        <input class="btn btn-success float-right" type="submit" value="<?php if(isset($daily_expense->id)){echo __('daily_expense.update_expense');}else{echo __('daily_expense.add_expense');}?>" onclick="validate()">
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
