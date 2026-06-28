@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('claimed_details.claimed_details') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('claimed_details.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('claimed_details.claimed_details') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('claimed_details.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('claimed_details.s_no') }}</th>
                                    <th>{{ __('claimed_details.vendor') }}</th>
                                    <th>{{ __('claimed_details.claimed_amount') }}</th>
                                    <th>{{ __('claimed_details.date') }}</th>
                                    <th>{{ __('claimed_details.claimed_by') }}</th>
                                    <th>{{ __('claimed_details.action') }}</th>
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
    $(document).ready(function() {
        fetchTable();
    });
    function fetchTable() 
    {
        let table = $('#example1');
        table.DataTable().clear().destroy();
        dTable = table.DataTable({
            processing: true,
            serverSide: true,
            pageLength: 50,
            deferRender: true,
            responsive: true,
            info: false,
            paging:false,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-claimed-detail-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            columns: [
                {data:'sno'},
                {data:'vendor_name'},
                {data:'amount'},
                {data:'date'},
                {data:'user_name'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection
