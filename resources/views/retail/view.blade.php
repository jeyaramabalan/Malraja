@extends('layouts.app')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('purchase.orders_view') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">{{ __('purchase.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('purchase.order_view') }}</li>
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
                        <div class="card-body">
                            <table class="table">
                                <tbody class="row-hover">
                                    <tr class="row-1 odd">
                                        <td class="column-1">{{ __('purchase.order_id') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3">{{ $order->bill_id }}</td>
                                        <td class="column-1">{{ __('purchase.bill_date') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3">{{ $order->date }}</td>
                                    </tr>
                                    <tr class="row-4 even" style="border-bottom: 1px solid rgba(26,54,126,0.125);">
                                        <td class="column-1">{{ __('purchase.vendor_name') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3"> {{ $order->customerName }} </td>
                                        <td class="column-1">{{ __('purchase.employee_name') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3"> {{ $order->userName }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="card-body p-0">
                                <div>
                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                            <tr>
                                                <th rowspan="2" class="text-center" width="400">{{ __('orders.category') }}</th>
                                                <th rowspan="2" class="text-center" width="400">{{ __('orders.prod_name') }}</th>
                                                <th rowspan="2" class="text-center" width="400">{{ __('orders.quantity') }}</th>
                                                <th rowspan="2" class="text-center" width="100">{{ __('orders.free_item') }}</th>
                                                <th rowspan="2" class="text-center" width="300">{{ __('orders.rate') }}<br><small>({{ __('orders.rs_symbol') }})</small></th>
                                                <th rowspan="2" class="text-center" width="300">{{ __('orders.gross_rate') }}<br><small>({{ __('orders.rs_symbol') }})</small></th>
                                                <th colspan="2" class="text-center">{{ __('orders.discount') }}</th>
                                                <th colspan="2" class="text-center" width="100">{{ __('orders.gst') }}</th>
                                                <th rowspan="2" width="100">{{ __('orders.total') }}<br><small>({{ __('orders.rs_symbol') }})</small></th>
                                            </tr>
                                            <tr>
                                                <th width="100">{{ __('orders.percent') }}</th>
                                                <th width="300">{{ __('orders.amount') }}</th>
                                                <th width="100">{{ __('orders.percent') }}</th>
                                                <th width="300">{{ __('orders.amount') }}</th>
                                            </tr>
                                            @foreach ($order_items as $item)
                                            <tr>
                                                <td>{{$item->category_name}}</td>
                                                <td>{{$item->product_name}}</td>
                                                <td>{{$item->quantity}}</td>
                                                <td>0</td>
                                                <td>{{$item->quantity_price}}</td>
                                                <td>{{$item->quantity_price * $item->quantity}}</td>
                                                <td>0</td>
                                                <td>0</td>
                                                <td>{{$item->gst}}</td>
                                                <td>{{($item->quantity_price * $item->gst) / 100}}</td>
                                                <td><div class="align-right">{{$item->amount}}</div></td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="8"></td>
                                                <td>{{ __('orders.total') }}</td>
                                                <td><div class="text-right">Rs.{{$order->total}}</div></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection