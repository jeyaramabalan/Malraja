@extends('layouts.app')

@section('content')
<div class="container">
    <h2 class="my-4">{{ __('gstreport.gstr1_b2b_title') }}</h2>

    @if (session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header">
            {{ __('gstreport.select_date_range') }}
        </div>
        <div class="card-body">
            <div class="row">
                {{-- NEW: Report Type Dropdown --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="report_type">{{ __('gstreport.report_type') }}</label>
                        <select id="report_type" name="report_type" class="form-control">
                            <option value="sales" selected>{{ __('gstreport.sales') }}</option>
                            <option value="purchase">{{ __('gstreport.purchase') }}</option>
                        </select>
                    </div>
                </div>

                {{-- Existing Date Range Picker --}}
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="date_range">{{ __('gstreport.date_range_label') }}</label>
                        <input type="text" id="date_range" name="date_range" class="form-control" placeholder="{{ __('gstreport.date_range_placeholder') }}">
                    </div>
                </div>
            </div>
            
            <div class="my-3">
                <a href="#" id="exportExcelLink" class="btn btn-success mr-2 disabled" target="_blank">
                    <i class="fas fa-file-excel"></i>
                    {{ __('gstreport.export_excel') }}
                </a>
                <a href="#" id="exportJsonLink" class="btn btn-info disabled" target="_blank">
                    <i class="fas fa-file-code"></i>
                    {{ __('gstreport.export_json') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function() {
        const baseUrlExcel = '{{ route('gst.r1.export.excel') }}';
        const baseUrlJson = '{{ route('gst.r1.export.json') }}';
        const excelLink = $('#exportExcelLink');
        const jsonLink = $('#exportJsonLink');
        const reportTypeDropdown = $('#report_type');
        const dateRangeInput = $('#date_range');

        function updateExportLinks() {
            const dateRangePicker = dateRangeInput.data('daterangepicker');

            if (dateRangeInput.val() === '') {
                excelLink.addClass('disabled').attr('href', '#');
                jsonLink.addClass('disabled').attr('href', '#');
                return;
            }

            const startDate = dateRangePicker.startDate.format('YYYY-MM-DD');
            const endDate = dateRangePicker.endDate.format('YYYY-MM-DD');
            const reportType = reportTypeDropdown.val();

            const excelUrl = `${baseUrlExcel}?start_date=${startDate}&end_date=${endDate}&type=${reportType}`;
            const jsonUrl = `${baseUrlJson}?start_date=${startDate}&end_date=${endDate}&type=${reportType}`;

            excelLink.attr('href', excelUrl);
            jsonLink.attr('href', jsonUrl);

            excelLink.removeClass('disabled');
            jsonLink.removeClass('disabled');
        }

        dateRangeInput.daterangepicker({
            opens: 'left',
            locale: { format: 'DD-MM-YYYY' },
            autoUpdateInput: false,
        });

        dateRangeInput.on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
            updateExportLinks();
        });

        dateRangeInput.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            updateExportLinks();
        });

        reportTypeDropdown.on('change', function() {
            updateExportLinks();
        });
    });
</script>
@endpush