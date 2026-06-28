@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                {{-- CORRECTED to use 'vendors' (plural) --}}
                <h1>{{ __('vendor.vendors') }}</h1> 
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    {{-- CORRECTED to use vendor.home --}}
                    <li class="breadcrumb-item"><a href="#">{{ __('vendor.home') }}</a></li> 
                    <li class="breadcrumb-item active">{{ __('vendor.vendors') }}</li>
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
                    <div class="card-header">
                        {{-- CORRECTED to use vendor.add --}}
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('vendor.add') }}</a> 
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('vendor.s_no') }}</th>
                                    <th>{{ __('vendor.name') }}</th>
                                    <th>{{ __('vendor.mobile') }}</th>
                                    <th>{{ __('vendor.email') }}</th>
                                    <th>{{ __('vendor.address') }}</th>
                                    <th>{{ __('vendor.gst') }}</th>
                                    <th>{{ __('vendor.status') }}</th>
                                    <th>{{ __('vendor.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    // Full JavaScript is preserved. No changes needed here as the headers are now in Blade.
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
            pageLength: 25,
            deferRender: true,
            responsive: true,
            info: false,
            paging:true,
            autoWidth: false,
            searching: false,
            sScrollX: '100%',
            dom: 'Bfrtip',
            buttons: [],
            stateSave: true,
            ajax: {
                url: '{{route("get-vendor-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'name'},
                {data:'mobile'},
                {data:'email'},
                {data:'address'},
                {data:'gst'},
                {data:'status'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection