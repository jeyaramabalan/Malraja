@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h2 class="my-3">{{ __('einvoice.title') }}</h2>
    <p class="text-muted">{{ __('einvoice.subtitle') }}</p>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>{{ __('einvoice.scope') }}</label>
                    <select id="scope" class="form-control">
                        <option value="bill">{{ __('einvoice.scope_bill') }}</option>
                        <option value="day" selected>{{ __('einvoice.scope_day') }}</option>
                        <option value="week">{{ __('einvoice.scope_week') }}</option>
                        <option value="month">{{ __('einvoice.scope_month') }}</option>
                    </select>
                </div>
                <div class="col-md-3 form-group scope-day-week">
                    <label>{{ __('einvoice.date') }}</label>
                    <input type="date" id="filter_date" class="form-control" value="{{ date('Y-m-d') }}">
                </div>
                <div class="col-md-3 form-group scope-month" style="display:none;">
                    <label>{{ __('einvoice.month') }}</label>
                    <input type="month" id="filter_month" class="form-control" value="{{ date('Y-m') }}">
                </div>
                <div class="col-md-6 form-group scope-bill" style="display:none;">
                    <label>{{ __('einvoice.bill_doc_type') }}</label>
                    <select id="bill_doc_type" class="form-control">
                        <option value="delivery">{{ __('einvoice.type_delivery') }}</option>
                        <option value="retail">{{ __('einvoice.type_retail') }}</option>
                    </select>
                </div>
                <div class="col-md-6 form-group scope-bill" style="display:none;">
                    <label>{{ __('einvoice.bill_no') }}</label>
                    <input type="text" id="bill_no" class="form-control" placeholder="Bill ID">
                </div>
            </div>
            <div class="row scope-not-bill">
                <div class="col-md-12">
                    <label>{{ __('einvoice.doc_types') }}</label><br>
                    <label class="mr-3"><input type="checkbox" class="doc-type" value="delivery" checked> {{ __('einvoice.type_delivery') }}</label>
                    <label class="mr-3"><input type="checkbox" class="doc-type" value="retail" checked> {{ __('einvoice.type_retail') }}</label>
                </div>
            </div>
            <button type="button" id="btn_preview" class="btn btn-primary mt-2">{{ __('einvoice.load_preview') }}</button>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Preview</span>
            <div>
                <button type="button" id="btn_select_eligible" class="btn btn-sm btn-outline-secondary">{{ __('einvoice.select_all_eligible') }}</button>
                <button type="button" id="btn_zip" class="btn btn-sm btn-success">{{ __('einvoice.download_zip') }}</button>
            </div>
        </div>
        <div class="card-body table-responsive">
            <div id="preview_error" class="alert alert-danger d-none"></div>
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th width="30"><input type="checkbox" id="check_all"></th>
                        <th>{{ __('einvoice.col_bill') }}</th>
                        <th>{{ __('einvoice.col_date') }}</th>
                        <th>Type</th>
                        <th>{{ __('einvoice.col_party') }}</th>
                        <th>{{ __('einvoice.col_gstin') }}</th>
                        <th class="text-right">{{ __('einvoice.col_total') }}</th>
                        <th>{{ __('einvoice.col_status') }}</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="preview_body">
                    <tr><td colspan="9" class="text-muted">{{ __('einvoice.no_items') }}</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const previewUrl = @json(route('einvoice.preview'));
    const jsonUrl = @json(route('einvoice.json'));
    const pdfUrl = @json(route('einvoice.pdf'));
    const zipUrl = @json(route('einvoice.zip'));
    const skipLabels = {
        no_gstin: @json(__('einvoice.skip_no_gstin')),
        returned: @json(__('einvoice.skip_returned')),
    };

    function typesParam() {
        return $('.doc-type:checked').map(function () { return this.value; }).get().join(',');
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
            $body.append('<tr><td colspan="9" class="text-muted">{{ __('einvoice.no_items') }}</td></tr>');
            return;
        }
        items.forEach(function (item) {
            const key = item.doc_type + ':' + item.id;
            let status = item.eligible
                ? '<span class="badge badge-success">{{ __('einvoice.eligible') }}</span>'
                : '<span class="badge badge-secondary">{{ __('einvoice.skipped') }}</span>';
            if (!item.eligible && item.skip_reasons && item.skip_reasons.length) {
                status += ' <small>' + item.skip_reasons.map(function (c) { return skipLabels[c] || c; }).join(', ') + '</small>';
            }
            const q = 'docType=' + encodeURIComponent(item.doc_type) + '&id=' + item.id;
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
                '<td>' +
                '<a class="btn btn-xs btn-outline-info btn-sm mr-1" href="' + jsonUrl + '?' + q + '" target="_blank">{{ __('einvoice.json') }}</a>' +
                '<a class="btn btn-xs btn-outline-primary btn-sm" href="' + pdfUrl + '?' + q + '" target="_blank">{{ __('einvoice.pdf') }}</a>' +
                '</td></tr>'
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
        let q = previewQuery();
        if (selected) q += '&selected=' + encodeURIComponent(selected);
        window.location = zipUrl + '?' + q;
    });
});
</script>
@endpush
