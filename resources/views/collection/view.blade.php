@extends('layouts.app')
@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ __('collection.collection_view') }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">{{ __('collection.home') }}</a></li>
                        <li class="breadcrumb-item active">{{ __('collection.collection_view') }}</li>
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
                        <div class="card-body">
                            <table class="table">
                                <tbody class="row-hover">
                                    <tr class="row-2 odd">
                                        <td class="column-1">{{ __('collection.order_id') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3">{{ $orderId }}</td>
                                        <td class="column-1">{{ __('collection.total_order_amount') }}</td>
                                        <td class="column-2">:</td>
                                        <td class="column-3">{{ $orderTotal }}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="card-body p-0">
                                <div>
                                    <table class="table table-striped table-bordered">
                                        <tbody>
                                            <tr>
                                                <th class="text-center" width="400">{{ __('collection.collected_by') }}</th>
                                                <th class="text-center" width="400">{{ __('collection.date') }}</th>
                                                <th class="text-center" width="400">{{ __('collection.amount') }}</th>
                                            </tr>
                                            @foreach ($collections as $item)
                                            <tr>
                                                <td class="text-center">{{$item->name}}</td>
                                                <td class="text-center">{{$item->date}}</td>
                                                <td class="text-center">{{$item->amount}}</td>
                                            </tr>
                                            @endforeach
                                            <tr>
                                                <td class="text-right" colspan="2">{{ __('collection.total_paid_amount') }}</td>
                                                <td class="text-center">Rs.{{$total}}</td>
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
