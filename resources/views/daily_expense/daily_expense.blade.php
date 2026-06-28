@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('daily_expense.daily_expense') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('daily_expense.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('daily_expense.daily_expense') }}</li>
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
                    <div class="card-header">
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('daily_expense.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('daily_expense.s_no') }}</th>
                                    <th>{{ __('daily_expense.expense_by') }}</th>
                                    <th>{{ __('daily_expense.expense_type') }}</th>
                                    <th>{{ __('daily_expense.expense_amount') }}</th>
                                    <th>{{ __('daily_expense.date') }}</th>
                                    <th>{{ __('daily_expense.action') }}</th>
                                </tr>
                            </thead>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<script type="text/javascript">
    // ... existing JS ...
</script>
@endsection
