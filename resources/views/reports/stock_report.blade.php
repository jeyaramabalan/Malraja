@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('reports.stock_report') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('reports.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('reports.stock_report') }}</li>
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
                        <form method="POST" action="{{$route}}">
                            @method('POST')
                            @csrf
                            <div class="row">
                                <div class="col-3">
                                    <label>{{ __('reports.category') }}</label>
                                    <select class="form-control select2" name="cat_id" style="width: 100%;" >
                                        <option value="" selected="selected">{{ __('reports.select_category') }}</option>
                                        <?php echo $category; ?>
                                    </select>
                                </div>
                                <div class="col-3">
                                    <label>{{ __('reports.hsn') }}</label>
                                    <select class="form-control select2" name="hsn_id" style="width: 100%;" >
                                        <option value="" selected="selected">{{ __('reports.select_hsn') }}</option>
                                        <?php echo $hsn; ?>
                                    </select>
                                </div>
                                <div class="col-1">
                                    <p>&nbsp;</p>
                                    <label>{{ __('reports.print') }}&nbsp;</label>
                                    <input type="checkbox" class="form-check-input" name="isPrint" value="1" id="isPrint">
                                </div>
                                <div class="col-2">
                                    <label>{{ __('reports.click_to_generate') }}</label>
                                    <input type="submit" class="form-control btn btn-success" value="{{ __('reports.generate') }}"/>
                                </div>
                            </div>
                            <div id="master"></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function getData() {
    var cat_id = $("#cat_id").val();
    var hsn_id = $("#hsn_id").val();
    $.ajax({
        type:"post",
        url: '{{route("get-stock-report-print")}}',
        data:{_token: '{{csrf_token()}}', cat_id:cat_id, hsn_id:hsn_id},
        success:function(res) {
            if(res) {
            }
        }
    });
}
</script>

@endsection