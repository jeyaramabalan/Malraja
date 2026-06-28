@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('collection.collection') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('collection.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('collection.collection') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('collection.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('collection.s_no') }}</th>
                                    <th>{{ __('collection.collected_by') }}</th>
                                    <th>{{ __('collection.collection_amount') }}</th>
                                    <th>{{ __('collection.order_id') }}</th>
                                    <th>{{ __('collection.customer') }}</th>
                                    <th>{{ __('collection.date') }}</th>
                                    <th>{{ __('collection.collection_id') }}</th>
                                    <th>{{ __('collection.action') }}</th>
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
            info: true,
            paging:true,
            autoWidth: false,
            searching: true,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-collection-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            columns: [
                {data:'sno'},
                {data:'collected_name'},
                {data:'amount'},
                {data:'order_id'},
                {data:'customer_name'},
                {data:'date'},
                {data:'id'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection
