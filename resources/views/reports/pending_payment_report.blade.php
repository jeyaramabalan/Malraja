@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('reports.pending_payment_report') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('reports.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('reports.pending_payment_report') }}</li>
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
                        <form method="POST" action={{route("post-pending-payment-report")}}>
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-6">
                                    <label>{{ __('reports.date') }}</label>
                                    <input class="form-control" type="text" id="datetimepicker" name="date" />
                                </div>
                                <div class="col-2">
                                    <label>{{ __('reports.click_to_generate') }}</label>
                                    <input type="button" onclick="getData()" class="form-control btn btn-success" value="{{ __('reports.view') }}"/>
                                    <input type="hidden" id="completeData" name="completeData" value=""/>
                                </div>
                            </div>
                            
                            <div id="master"></div>
                            <p></p>
                            <input id="generate" style="display: none" type="submit" class="col-6 form-control btn btn-primary" value="{{ __('reports.print') }}" />
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
        url: '{{route("get-pending-payment-report")}}',
        data:{_token: '{{csrf_token()}}', date:date},
        success:function(res) {
            if(res) {
                var output = $.parseJSON(res);
                $('#master').empty();
                $('#generate').show();
                $('#master').html(output);
                $('#completeData').val(output);
            }
        }
    });
}
</script>

@endsection