@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('reports.daily_product_sale_count') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('reports.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('reports.daily_product_sale_count') }}</li>
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
                        <form method="POST" action="{{route('daily-product-generate')}}">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <label>{{ __('reports.date') }}</label>
                                    <input class="form-control" type="text" id="datetimepicker" name="date" />
                                </div>
                                <div class="col-3" id="data_button">
                                    <label>{{ __('reports.click_to_view') }}</label>
                                    <input type="button" onclick="getData()" class="form-control btn btn-success" value="{{ __('reports.view') }}"/>
                                </div>
                                <div style="display: none;" id="spinner" class="spinner-border m-5" role="status">
                                    <span class="sr-only">{{ __('reports.loading') }}</span>
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
    $("#data_button").hide();
    $("#spinner").show();
    var date = $("#datetimepicker").val();
    $.ajax({
        type:"post",
        url: '{{route("daily-product-get")}}',
        data:{_token: '{{csrf_token()}}', date:date},
        success:function(res) {
            if(res) {
                var output = $.parseJSON(res);
                $("#data_button").show();
                $("#spinner").hide();
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
        url: '{{route("daily-product-generate")}}',
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