@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('expense.expense') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('expense.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('expense.expense') }}</li>
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
                        <a href="{{$route}}" class="btn btn-primary float-right">{{ __('expense.add') }}</a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                        <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                            <thead>
                                <tr>
                                    <th>{{ __('expense.s_no') }}</th>
                                    <th>{{ __('expense.expense') }}</th>
                                    <th>{{ __('expense.action') }}</th>
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
            buttons: [
                // 'excelHtml5',
                // 'pdfHtml5',
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-expense-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}'},
            },
            // aoColumnDefs:[{bSortable:false,aTargets:[0,3]}],
            columns: [
                {data:'sno'},
                {data:'name'},
                {data:'action'}
            ]
        });
    }
    
    
    
    </script>

@endsection
