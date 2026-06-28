<?php
$settings = DB::table('settings')->first();
?>
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
</head>
<style>
label.error{ color:red !important; }
</style>
<body class="home-7">
    <!-- header start -->
    <header class="header-area">
        <div class="header-main-area">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="header-main">
                            <!-- logo start -->
                            <div class="header-element logo">
                                <a href="{{url('/')}}/home">
                                    <img src="{{url('/')}}/uploads/setting/<?php echo $settings->id; ?>/<?php echo $settings->company_logo; ?>" alt="logo-image" class="img-fluid">
                                </a>
                            </div>
                            <!-- logo end -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- minicart start -->
        <div class="mini-cart">
            <a href="javascript:void(0)" class="shopping-cart-close"><i class="fas fa-times"></i></a>
            <div class="cart-item-title">
                <p>
                    <span class="cart-count-desc">{{ __('frontend.cart_there_are') }}</span>
                    <span class="cart-count-item bigcounter">4</span>
                    <span class="cart-count-desc">{{ __('frontend.cart_products') }}</span>
                </p>
            </div>
            <ul class="cart-item-loop" id="cart_list"></ul>
            <ul class="subtotal-title-area">
                <li class="subtotal-info">
                    <div class="subtotal-titles">
                        <h6>{{ __('frontend.sub_total') }}</h6>
                        <span class="subtotal-price" id="sub_total">0 </span>
                    </div>
                </li>
                <li class="mini-cart-btns">
                    <div class="cart-btns">
                        <a href="{{route('cart')}}" class="btn btn-style1"><span>{{ __('frontend.view_cart') }}</span></a>
                        <a href="{{route('checkout')}}" class="btn btn-style1"><span>{{ __('frontend.checkout') }}</span></a>
                    </div>
                </li>
            </ul>
        </div>
        <!-- minicart end -->
        <!-- mobile menu start -->
        <div class="header-bottom-area">
            <div class="container">
                <div class="row">
                    <div class="col">
                        <div class="main-menu-area">
                            <div class="main-navigation navbar-expand-xl">
                                <div class="box-header menu-close"><button class="close-box" type="button"><i class="fas fa-times"></i></button></div>
                                <div class="navbar-collapse" id="navbarContent">
                                    <div class="megamenu-content">
                                        <div class="mainwrap" style="display:@if(Auth::guard('web')->check()) block @else none @endif;">
                                            <ul class="main-menu">
                                                <li class="link-title"><a href="{{route('product')}}" class="link-title"><span class="sp-link-title">{{ __('frontend.products') }}</span></a></li>
                                                <li class="link-title"><a href="{{route('orders')}}" class="link-title"><span class="sp-link-title">{{ __('frontend.orders') }}</span></a></li>
                                                <li class="link-title"><a href="{{route('delivery-note')}}" class="link-title"><span class="sp-link-title">{{ __('frontend.delivery_note') }}</span></a></li>   
                                                <li class="link-title"><a href="{{route('invoices')}}" class="link-title"><span class="sp-link-title">{{ __('frontend.invoices') }}</span></a></li> 
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- mobile menu end -->
    </header>
    <!--header end-->
    @if (session('status'))
    <input type="hidden" id="status_span" data-status="{{ session('status.success') }}" data-msg="{{ session('status.msg') }}">
    @endif
    
    @yield('content')
    @include('layouts.frontend-footer')

    {{-- Other popups and JS includes remain unchanged --}}

    @yield ('javascript')
    <script type="text/javascript">
        // Your existing JS code remains unchanged
    </script>
</body>
</html>