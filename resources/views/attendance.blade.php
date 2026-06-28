@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('attendance.attendance') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="">{{ __('attendance.home') }}</a></li>
                    <li class="breadcrumb-item active">{{ __('attendance.attendance') }}</li>
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
                        <form method="POST" id="filterForm">
                            <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>{{ __('attendance.date') }}</label>
                                        <div class="input-group date" id="datetimepicker" data-target-input="nearest">
                                            <input type="text" value="<?php if(isset($_GET['date'])){echo $_GET['date'];}?>" name="datetimepicker" class="form-control datetimepicker-input" data-target="#datetimepicker"/>
                                            <div class="input-group-append " data-target="#datetimepicker" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                        <table id="example1" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>{{ __('attendance.s_no') }}</th>
                                    <th>{{ __('attendance.user_name') }}</th>
                                    <th>{{ __('attendance.has_checked_in') }}</th>
                                    <th>{{ __('attendance.check_in_time') }}</th>
                                    <th>{{ __('attendance.check_out_time') }}</th>
                                    <th>{{ __('attendance.check_in_location') }}</th>
                                    <th>{{ __('attendance.check_out_location') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach ($attendance_data as $attendance)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$attendance->user_name}}</td>
                                        <td>{{$attendance->check_in_status == 1 ? __('attendance.yes') : __('attendance.no')}}</td>
                                        <td>{{$attendance->check_in_time}}</td>
                                        <td>{{$attendance->check_out_time}}</td>
                                        <td align="center"><a href="https://maps.google.com/maps?q={{$attendance->check_in_latitude}},{{$attendance->check_in_longitude}}" target="_blank"><i style="color: green;" class="fa fa-map"></i></a></td>
                                        <?php 
                                            if(empty($attendance->check_out_latitude)) {
                                                echo '<td></td>';
                                            } else {
                                                echo '<td align="center"><a href="https://maps.google.com/maps?q='.$attendance->check_out_latitude.','.$attendance->check_out_longitude.'" target="_blank"><i style="color: red;" class="fa fa-map"></i></a></td>';
                                            }
                                        ?>
                                    </tr>
                                    @endforeach
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

$(document).ready(function () {
    $('#datetimepicker').on('apply.daterangepicker', function(ev, picker) {
    var sDate = picker.startDate.format('YYYY-MM-DD');
    var eDate = picker.endDate.format('YYYY-MM-DD');
    var url = "attendance?date="+sDate+','+eDate;
    window.location.href = url;
});
});

</script>
@endsection