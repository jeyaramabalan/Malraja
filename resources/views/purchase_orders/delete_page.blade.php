@extends('layouts.app')
@section('content')

<section class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Delete Purchase Order</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    <li class="breadcrumb-item active">Delete Purchase Order</li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    </div>
                @endif

                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Select Purchase Order to Delete</h3>
                    </div>
                    <div class="card-body">
                        <form id="delete_po_form" method="POST" action="{{ route('purchase.delete.destroy') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Purchase Order</label>
                                        <select class="form-control select2" name="purchase_order_id" style="width: 100%;" required>
                                          <option value="">Select a Purchase Order Bill No...</option>
                                          @foreach($purchaseOrders as $order)
                                            {{-- START OF THE FIX --}}
                                            <option value="{{ $order->id }}">
                                                Bill No: {{ $order->bill_no }} | Vendor: {{ $order->vendor_name }} (Date: {{ \Carbon\Carbon::parse($order->date)->format('d-m-Y') }})
                                            </option>
                                            {{-- END OF THE FIX --}}
                                          @endforeach
                                        </select>
                                      </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <button type="submit" class="btn btn-danger form-control">
                                            <i class="fas fa-trash"></i>
                                            Delete & Generate Backups
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>    
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('delete_po_form');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            const confirmation = confirm('WARNING: This will permanently delete the purchase order and may also delete/modify related customer sales records. This action cannot be undone. Are you absolutely sure?');
            
            if (confirmation) {
                const submitButton = this.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Deleting...';

                fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: { 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(errorData => {
                            let errorMessage = errorData.message || 'An unknown error occurred.';
                            if (errorData.errors) {
                                errorMessage = Object.values(errorData.errors).flat().join('\n');
                            }
                            throw new Error(errorMessage);
                        });
                    }
                    return response.json();
                })
                .then(successData => {
                    alert(successData.message);
                    const triggerDownload = (url) => {
                        const a = document.createElement('a');
                        a.href = url;
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    };

                    if (successData.excel_url) {
                        triggerDownload(successData.excel_url);
                    }
                    if (successData.sql_url) {
                        setTimeout(() => {
                           triggerDownload(successData.sql_url);
                        }, 1000);
                    }
                    
                    setTimeout(() => { window.location.reload(); }, 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Operation Failed: ' + error.message);
                    submitButton.disabled = false;
                    submitButton.innerHTML = '<i class="fas fa-trash"></i> Delete & Generate Backups';
                });
            }
        });
    }
});
</script>
@endpush
@endsection