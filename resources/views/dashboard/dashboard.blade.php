@extends('layouts.app')
@section('content')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>{{ __('dashboard.dashboard') }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="#">{{ __('dashboard.home') }}</a></li>
                </ol>
            </div>
        </div>
    </div>
</section>
<section class="content">
    <div class="container-fluid">
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$completed}}</h3>
              <p>{{ __('dashboard.completed_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{$total}}</h3>
              <p>{{ __('dashboard.overall_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{$pending}}</h3>
              <p>{{ __('dashboard.pending_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-hand-paper"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3>{{$todayCompletedOrders}}</h3>
              <p>{{ __('dashboard.today_completed_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3>{{$todayTotalOrders}}</h3>
              <p>{{ __('dashboard.today_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3>{{$todayPendingOrders}}</h3>
              <p>{{ __('dashboard.today_pending_orders') }}</p>
            </div>
            <div class="icon"><i class="fas fa-shopping-bag"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      <div class="row">
        @foreach($paymentMethodWise as $payment)
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$payment->total}}</h3>
                <p>{{ __('dashboard.payment_method_wise', ['method' => $payment->payment_method]) }}</p>
              </div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
              <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        @endforeach
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><sup style="font-size: 20px">?</sup>{{$todayExpense}}</h3>
              <p>{{ __('dashboard.today_expense') }}</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      <div class="row">
        @foreach($collectionpaymentMethodWise as $payment)
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$payment->total}}</h3>
                <p>{{ __('dashboard.today_collection_method_wise', ['method' => $payment->payment_method]) }}</p>
              </div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
              <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        @endforeach
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><sup style="font-size: 20px">?</sup>{{$todayCollectionTotal}}</h3>
              <p>{{ __('dashboard.today_collection') }}</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
      <div class="row">
        @foreach($retailpaymentMethodWise as $payment)
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3>{{$payment->total}}</h3>
                <p>{{ __('dashboard.today_retail_method_wise', ['method' => $payment->payment_method]) }}</p>
              </div>
              <div class="icon"><i class="fas fa-check-circle"></i></div>
              <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
            </div>
          </div>
        @endforeach
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><sup style="font-size: 20px">?</sup>{{$todayExpense}}</h3>
              <p>{{ __('dashboard.today_retail_amount') }}</p>
            </div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="#" class="small-box-footer">{{ __('dashboard.more_info') }} <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>
    </div>
  </section>
@endsection
