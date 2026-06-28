@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Customers</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="   ">Home</a></li>
                    <li class="breadcrumb-item active">Customers</li>
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
                        {{-- <form method="POST" id="filterForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                    <label>Users</label>
                                    <select id='user-select' class="form-control select2" style="width: 100%;">
                                        <option value="0" selected="selected">All</option>
                                        <?php echo $users;?>
                                    </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                <div class="form-group">
                                    <label>Date:</label>
                                        <div class="input-group date" id="datetimepicker" data-target-input="nearest">
                                            <input type="text" value="<?php if(isset($_GET['date'])){echo $_GET['date'];}?>" name="datetimepicker" class="form-control datetimepicker-input" data-target="#datetimepicker"/>
                                            <div class="input-group-append " data-target="#datetimepicker" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form> --}}
                        <div class="tbale-responsive">
                            <table style="width: 100%;" id="example1" class="table table-bordered table-striped export-table">
                                <thead>
                                    <tr>
                                        <th>SNO</th>
                                        <th>Name</th>
                                        <th>Mobile</th>
                                        <th>Email</th>
                                        <th>Address</th>
                                        <th>Aadhar</th>
                                        <th>GST</th>
                                        <th>Created By</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>                                
                                </tbody>
                            </table>
                        </div>                        
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
<script type="text/javascript">
$(document).ready(function() {
    fetchTable('', '');
});
$(document).on("change",'#user-select',function() {
    var temp = $('#user-select').val();
    var url = "customer?user="+temp;
    if(temp == 0) {
        url = "customer";
    }
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
            url: '{{route("get-customer-list")}}',
            type: 'POST',
            dataType:'json',
            data:{_token: '{{csrf_token()}}', userId:userId, sDate:sDate, eDate:eDate},
        },
        aoColumnDefs:[{bSortable:false,aTargets:[0,6]}],
        columns: [
            {data:'sno'},
            {data:'name'},
            {data:'mobile'},
            {data:'email'},
            {data:'address'},
            {data:'aadhar_number'},
            {data:'gst'},
            {data:'user_name'},
            {data:'status'},
            {data:'action'}
        ]
    });
}



</script>
@endsection