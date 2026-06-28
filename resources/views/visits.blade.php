@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('visits.visits') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('visits.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('visits.visits') }}</li>
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
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('visits.users') }}</label>
                                    <select id='user-select' class="form-control select2" style="width: 100%;">
                                        <option value="0" selected="selected">{{ __('visits.all') }}</option>
                                        <?php echo $users; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('visits.date') }}</label>
                                    <div class="input-group date" id="datetimepicker" data-target-input="nearest">
                                        <input type="text" value="<?php if (isset($_GET['date'])) { echo $_GET['date']; } ?>" name="datetimepicker" class="form-control datetimepicker-input" data-target="#datetimepicker" />
                                        <div class="input-group-append " data-target="#datetimepicker" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('visits.purpose_of_visits') }}</label>
                                    <select id='purpose-select' class="form-control select2" style="width: 100%;">
                                        <option value="0" selected="selected">{{ __('visits.all') }}</option>
                                        <?php echo $purpose_options; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('visits.follow_up_needed') }}</label>
                                    <select id='follow-select' class="form-control select2" style="width: 100%;">
                                        <option value="" selected="selected">{{ __('visits.all') }}</option>
                                        <?php echo $follow_up_option; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <table id="example1" class="table table-bordered table-striped export-table">
                        <thead>
                            <tr>
                                <th>{{ __('visits.s_no') }}</th>
                                <th>{{ __('visits.purpose_of_visits') }}</th>
                                <th>{{ __('visits.customer') }}</th>
                                <th>{{ __('visits.products') }}</th>
                                <th>{{ __('visits.quantity') }}</th>
                                <th>{{ __('visits.remarks') }}</th>
                                <th>{{ __('visits.campaign_name') }}</th>
                                <th>{{ __('visits.follow_up_needed') }}</th>
                                <th>{{ __('visits.date') }}</th>
                                <th>{{ __('visits.created_by') }}</th>
                                <th>{{ __('visits.accompanied_by') }}</th>
                                <th>{{ __('visits.location') }}</th>
                                <th>{{ __('visits.action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->
    </div>
</section>
<script>
$(document).ready(function() {
    fetchTable('', '');
});
    $(document).on("change", '#user-select', function() {
        fetchTable('', '');
    });

    $(document).on("change", '#purpose-select', function() {
        fetchTable('', '');
    });

    $(document).on("change", '#follow-select', function() {
        fetchTable('', '');
    });

    $('#datetimepicker').on('apply.daterangepicker', function(ev, picker) {
        var sDate = picker.startDate.format('YYYY-MM-DD');
        var eDate = picker.endDate.format('YYYY-MM-DD');
        fetchTable(sDate, eDate);
    });

    function fetchTable(sDate, eDate) 
    {
        let table = $('#example1');
        table.DataTable().clear().destroy();
        var userId = $('#user-select').val();
        var followUp = $('#follow-select').val();
        var purposeId = $('#purpose-select').val();
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
            buttons: [
                'excelHtml5',
                'pdfHtml5',
            ],
            stateSave: true,
            ajax: {
                url: '{{route("get-visit-list")}}',
                type: 'POST',
                dataType:'json',
                data:{_token: '{{csrf_token()}}', user:userId, purpose:purposeId, follow:followUp, sDate:sDate, eDate:eDate},
            },
            aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
            columns: [
                {data:'sno'},
                {data:'purpose_of_visit_name'},
                {data:'customer_name'},
                {data:'product_list_id'},
                {data:'quantity'},
                {data:'remarks'},
                {data:'campaign_name'},
                {data:'follow_up_needed'},
                {data:'date'},
                {data:'user_name'},
                {data:'accompany_list_id'},
                {data:'map'},
                {data:'action'}
            ]
        });
    }
</script>
@endsection