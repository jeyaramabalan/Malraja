@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('reports.orders') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('reports.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('reports.daily_sales') }}</li>
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
                        <form method="POST" action="{{route('daily-sales')}}">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <label>{{ __('reports.date') }}</label>
                                    <input class="form-control" type="text" id="datetimepicker" name="date" />
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>{{ __('reports.payment_method') }}</label>
                                        <select class="form-control select2" name="payment_method_id" style="width: 100%;" required>
                                          <option selected="selected" value="">{{ __('reports.select_payment_method') }}</option>
                                          <option value="UPI">{{ __('reports.upi') }}</option>
                                          <option  value="cash">{{ __('reports.cash') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <label>{{ __('reports.click_to_view') }}</label>
                                    <input type="button" onclick="getData()" class="form-control btn btn-success" value="{{ __('reports.view') }}"/>
                                </div>
                            </div>
                            
                            <div id="master"></div>
                            <input id="generate" style="display: none" type="submit" class="form-control btn btn-primary" value="{{ __('reports.generate') }}" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function getData() {
    var date = $("#datetimepicker").val();
    $.ajax({
        type:"post",
        url: '{{route("daily-sales-get")}}',
        data:{_token: '{{csrf_token()}}', date:date},
        success:function(res) {
            if(res) {
                var output = $.parseJSON(res);
                $('#master').empty();
                $('#generate').show();
                $('#master').html(output);
            }
        }
    });
}

function generateData() {
    var date = $("#date").val();
    $.ajax({
        type:"post",
        url: '{{route("daily-sales")}}',
        data:{_token: '{{csrf_token()}}', date:date},
        success:function(res) {
            if(res) {
                var output = $.parseJSON(res);
                $('#master').empty();
                $('#master').html(output);
            }
        }
    });
}
</script>

@endsection