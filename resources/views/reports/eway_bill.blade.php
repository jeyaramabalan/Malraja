@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="my-3">{{ __('eway.title') }}</h2>
    <p class="text-muted">{{ __('eway.subtitle') }}</p>
    <p class="small text-muted">{{ __('eway.min_value_note', ['min' => number_format(config('gst.eway_min_value'))]) }}</p>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>{{ __('eway.scope') }}</label>
                    <select id="scope" class="form-control">
                        <option value="bill">{{ __('eway.scope_bill') }}</option>
                        <option value="day" selected>{{ __('eway.scope_day') }}</option>
                        <option value="week">{{ __('eway.scope_week') }}</option>
                        <option value="month">{{ __('eway.scope_month') }}</option>
                    </select>
                </div>
                <div class="col-md-3 form-group scope-day-week">
                    <label>{{ __('eway.date') }}</label>
                    <input type="date" id="filter_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 form-group scope-month" style="display:none;">
                    <label>{{ __('eway.month') }}</label>
                    <input type="month" id="filter_month" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div class="col-md-6 form-group scope-bill" style="display:none;">
                    <label>{{ __('eway.bill_doc_type') }}</label>
                    <select id="bill_doc_type" class="form-control">
                        <option value="delivery">{{ __('eway.type_delivery') }}</option>
                        <option value="retail">{{ __('eway.type_retail') }}</option>
                        <option value="purchase">{{ __('eway.type_purchase') }}</option>
                    </select>
                </div>
                <div class="col-md-6 form-group scope-bill" style="display:none;">
                    <label>{{ __('eway.bill_no') }}</label>
                    <input type="text" id="bill_no" class="form-control" placeholder="Bill ID">
                </div>
            </div>
            <div class="row scope-not-bill">
                <div class="col-md-12">
                    <label>{{ __('eway.doc_types') }}</label><br>
                    <label class="mr-3"><input type="checkbox" class="doc-type" value="delivery" checked> {{ __('eway.type_delivery') }}</label>
                    <label class="mr-3"><input type="checkbox" class="doc-type" value="retail" checked> {{ __('eway.type_retail') }}</label>
                    <label class="mr-3"><input type="checkbox" class="doc-type" value="purchase" checked> {{ __('eway.type_purchase') }}</label>
                </div>
            </div>
            <button type="button" id="btn_preview" class="btn btn-primary mt-2">{{ __('eway.load_preview') }}</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">{{ __('eway.transport') }}</div>
        <div class="card-body row">
            <div class="col-md-3 form-group">
                <label>{{ __('eway.transporter_name') }}</label>
                <input type="text" id="transporter_name" class="form-control">
            </div>
            <div class="col-md-3 form-group">
                <label>{{ __('eway.transporter_gstin') }}</label>
                <input type="text" id="transporter_gstin" class="form-control">
            </div>
            <div class="col-md-2 form-group">
                <label>{{ __('eway.vehicle_no') }}</label>
                <input type="text" id="vehicle_no" class="form-control">
            </div>
            <div class="col-md-2 form-group">
                <label>{{ __('eway.transport_mode') }}</label>
                <select id="transport_mode" class="form-control">
                    <option value="road">Road</option>
                    <option value="rail">Rail</option>
                    <option value="air">Air</option>
                    <option value="ship">Ship</option>
                </select>
            </div>
            <div class="col-md-2 form-group">
                <label>{{ __('eway.distance_km') }}</label>
                <input type="text" id="distance_km" class="form-control">
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Preview</span>
            <div>
                <button type="button" id="btn_select_eligible" class="btn btn-sm btn-outline-secondary">{{ __('eway.select_all_eligible') }}</button>
                <button type="button" id="btn_zip" class="btn btn-sm btn-success">{{ __('eway.download_zip') }}</button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <div id="preview_error" class="alert alert-danger d-none"></div>
            <table class="table table-sm table-bordered" id="preview_table">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="check_all"></th>
                        <th>{{ __('eway.col_bill') }}</th>
                        <th>{{ __('eway.col_date') }}</th>
                        <th>Type</th>
                        <th>{{ __('eway.col_party') }}</th>
                        <th>{{ __('eway.col_gstin') }}</th>
                        <th class="text-right">{{ __('eway.col_total') }}</th>
                        <th>{{ __('eway.col_status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="preview_body">
                    <tr><td colspan="9" class="text-muted">{{ __('eway.no_items') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const previewUrl = @json(route('eway.preview'));
    const pdfUrl = @json(route('eway.pdf'));
    const zipUrl = @json(route('eway.zip'));
    const skipLabels = {
        no_gstin: @json(__('eway.skip_no_gstin')),
        returned: @json(__('eway.skip_returned')),
        under_threshold: @json(__('eway.skip_under_threshold')),
    };

    function typesParam() {
        return $('.doc-type:checked').map(function () { return this.value; }).get().join(',');
    }

    function transportQuery() {
        const q = new URLSearchParams();
        const fields = ['transporter_name', 'transporter_gstin', 'vehicle_no', 'transport_mode', 'distance_km'];
        fields.forEach(function (id) {
            const v = $('#' + id).val();
            if (v) q.set(id, v);
        });
        return q.toString();
    }

    function previewQuery() {
        const scope = $('#scope').val();
        const q = new URLSearchParams({ scope: scope, types: typesParam() || 'all' });
        if (scope === 'bill') {
            q.set('docType', $('#bill_doc_type').val());
            const bn = $('#bill_no').val().trim();
            if (bn) q.set('billNo', bn);
        } else if (scope === 'month') {
            q.set('month', $('#filter_month').val());
            q.set('date', $('#filter_date').val());
        } else {
            q.set('date', $('#filter_date').val());
        }
        return q.toString();
    }

    function toggleScope() {
        const s = $('#scope').val();
        $('.scope-bill').toggle(s === 'bill');
        $('.scope-not-bill').toggle(s !== 'bill');
        $('.scope-month').toggle(s === 'month');
        $('.scope-day-week').toggle(s === 'day' || s === 'week');
    }

    $('#scope').on('change', toggleScope);
    toggleScope();

    function fmtMoney(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function renderPreview(data) {
        const items = data.items || [];
        const $body = $('#preview_body').empty();
        if (!items.length) {
            $body.append('<tr><td colspan="9" class="text-muted">{{ __('eway.no_items') }}</td></tr>');
            return;
        }
        items.forEach(function (item) {
            const key = item.doc_type + ':' + item.id;
            let status = item.eligible
                ? '<span class="badge badge-success">{{ __('eway.eligible') }}</span>'
                : '<span class="badge badge-secondary">{{ __('eway.skipped') }}</span>';
            if (!item.eligible && item.skip_reasons && item.skip_reasons.length) {
                status += ' <small>' + item.skip_reasons.map(function (c) { return skipLabels[c] || c; }).join(', ') + '</small>';
            }
            const pdfLink = pdfUrl + '?docType=' + encodeURIComponent(item.doc_type) + '&id=' + item.id + '&' + transportQuery();
            $body.append(
                '<tr>' +
                '<td><input type="checkbox" class="row-check" data-key="' + key + '" ' + (item.eligible ? '' : 'disabled') + '></td>' +
                '<td>' + item.bill_id + '</td>' +
                '<td>' + (item.bill_date || '') + '</td>' +
                '<td>' + item.doc_type + '</td>' +
                '<td>' + (item.party_name || '') + '</td>' +
                '<td>' + (item.party_gstin || '') + '</td>' +
                '<td class="text-right">' + fmtMoney(item.total) + '</td>' +
                '<td>' + status + '</td>' +
                '<td><a class="btn btn-xs btn-outline-primary btn-sm" href="' + pdfLink + '" target="_blank">{{ __('eway.pdf') }}</a></td>' +
                '</tr>'
            );
        });
    }

    $('#btn_preview').on('click', function () {
        $('#preview_error').addClass('d-none');
        $.getJSON(previewUrl + '?' + previewQuery())
            .done(renderPreview)
            .fail(function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Preview failed';
                $('#preview_error').removeClass('d-none').text(msg);
            });
    });

    $('#btn_select_eligible').on('click', function () {
        $('.row-check:not(:disabled)').prop('checked', true);
    });

    $('#check_all').on('change', function () {
        $('.row-check:not(:disabled)').prop('checked', this.checked);
    });

    $('#btn_zip').on('click', function () {
        const selected = $('.row-check:checked').map(function () { return this.dataset.key; }).get().join(',');
        let q = previewQuery() + '&' + transportQuery();
        if (selected) q += '&selected=' + encodeURIComponent(selected);
        window.location = zipUrl + '?' + q;
    });
});
</script>
@endpush
