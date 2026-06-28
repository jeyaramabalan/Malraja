@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('reports.customer_report') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('reports.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('reports.customer_report') }}</li>
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
                            <div class="row">
                                <div class="col-3">
                                    <label>{{ __('reports.customer') }}</label>
                                    <select class="form-control select2" id="cat_id" style="width: 100%;" >
                                        <option value="" selected="selected">{{ __('reports.select_customer') }}</option>
                                        <?php echo $customers; ?>
                                    </select>
                                </div>
                                <div class="col-2" id="data_button">
                                    <label>&nbsp;</label>
                                    <input type="button" onclick="getData()" class="form-control btn btn-success" value="{{ __('reports.view') }}"/>
                                </div>
                                <div style="display: none;" id="spinner" class="spinner-border m-5" role="status">
                                    <span class="sr-only">{{ __('reports.loading') }}</span>
                                </div>
                            </div>
                            
                            <div id="master"></div>
                            <input id="generate" style="display: none" type="submit" class="form-control btn btn-primary" value="{{ __('reports.generate') }}" />
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
    var customerId = $("#cat_id").val();
    $.ajax({
        type:"post",
        url: '{{route("customer-report-get")}}',
        data:{_token: '{{csrf_token()}}', customerId:customerId},
        success:function(res)
        {
            $("#spinner").hide();
            $("#data_button").show();
            $('#master').html(res);
        }
    });
}
</script>

@endsection