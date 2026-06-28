<nav class="main-header navbar navbar-expand navbar-white navbar-light">

  <ul class="navbar-nav">
    <li class="nav-item">
      <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('order.create')}}" class="nav-link {{ Route::is('order.create') ? 'active' : '' }}">{{ __('sidebar.new_order') }}</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('new-pos-order')}}" class="nav-link {{ Route::is('new-pos-order') ? 'active' : '' }}">{{ __('sidebar.new_pos') }}</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('order.index')}}" class="nav-link {{ Route::is('order.index') ? 'active' : '' }}">{{ __('sidebar.all_orders') }}</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('retail.create')}}" class="nav-link {{ Route::is('retail.create') ? 'active' : '' }}">{{ __('sidebar.add_retail') }}</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('retail.index')}}" class="nav-link {{ Route::is('retail.index') ? 'active' : '' }}">{{ __('sidebar.all_retail') }}</a>
    </li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="{{route('customer.create')}}" class="nav-link {{ Route::is('customer.create') ? 'active' : '' }}">{{ __('sidebar.add_customer') }}</a>
    </li>
  </ul>
    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLanguage" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <i class="fas fa-globe"></i> {{ __('messages.language') }}
          </a>
          <div class="dropdown-menu" aria-labelledby="navbarDropdownLanguage">
            <a class="dropdown-item" href="{{ route('language.switch', 'en') }}">{{ __('messages.english') }}</a>
            <a class="dropdown-item" href="{{ route('language.switch', 'ta') }}">{{ __('messages.tamil') }}</a>
          </div>
        </li>

      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <form id="logoutForm" method="POST" action="{{ route('admin.logout') }}">
          @csrf
          <a class="nav-link" onclick="submitForm()" href="#" role="button">
            <i class="fas fa-sign-out-alt"></i>
          </a>
        </form>
      </li>
    </ul>
  </nav>
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-custom elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
      <span style="color: white;" class="brand-text font-weight-bold">Malraja Traders</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-1">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
        <li class="nav-item">
          <a href="{{route('dashboard')}}" class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
            <i class="nav-icon fas fa-tachometer-alt"></i>
            <p>{{ __('sidebar.dashboard') }}</p>
          </a>
        </li>
        @if(Auth::user()->role == 1 || Auth::user()->role == 2)
          <li class="nav-item">
            <a href="{{route('customer.index')}}" class="nav-link {{ Route::is('customer.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-users"></i>
              <p>{{ __('sidebar.customers') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('products.index')}}" class="nav-link {{ Route::is('products.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-cubes"></i>
              <p>{{ __('sidebar.products') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="{{route('users')}}" class="nav-link {{ Route::is('users') ? 'active' : '' }}">
              <i class="nav-icon fas fa-user-secret"></i>
              <p>{{ __('sidebar.users') }}</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                {{ __('sidebar.masters') }}
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview pl-2" style="display: none;">
              <li class="nav-item">
                <a href="{{route('category.index')}}" class="nav-link {{ Route::is('category.index') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-tags"></i>
                  <p>{{ __('sidebar.category') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('unit.index')}}" class="nav-link {{ Route::is('unit.index') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-weight-hanging"></i>
                  <p>{{ __('sidebar.unit') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('hsn.index')}}" class="nav-link {{ Route::is('hsn.index') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-barcode"></i>
                  <p>{{ __('sidebar.hsn') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('expense.index')}}" class="nav-link {{ Route::is('expense.index') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-wallet"></i>
                  <p>{{ __('sidebar.expense_type') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('route.index')}}" class="nav-link {{ Route::is('route') ? 'active' : '' }}">
                  <i class="nav-icon fas fa-road"></i>
                  <p>{{ __('sidebar.route') }}</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="{{route('vendor.index')}}" class="nav-link {{ Route::is('vendor.index') ? 'active' : '' }}">
              <i class="nav-icon fas fa-server"></i>
              <p>{{ __('sidebar.vendor') }}</p>
            </a>
          </li>
        @endif
        
        
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-store"></i>
            <p>
              {{ __('sidebar.retail') }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview pl-2" style="display: none;">
            @if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3)
            <li class="nav-item">
              <a href="{{route('retail.create')}}" class="nav-link {{ Route::is('retail.create') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.create_retail') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('retail.index')}}" class="nav-link {{ Route::is('retail.index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.all_retail_orders') }}</p>
              </a>
            </li>
            @endif
            <li class="nav-item">
              <a href="{{route('retailstock')}}" class="nav-link {{ Route::is('retailstock') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.retail_stock') }}</p>
              </a>
            </li>
          </ul>
        </li>
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-shopping-cart"></i>
            <p>
              {{ __('sidebar.orders') }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview pl-2" style="display: none;">
            @if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3)
            <li class="nav-item">
              <a href="{{route('new-pos-order')}}" class="nav-link {{ Route::is('new-pos-order') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.create_pos') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('order.create')}}" class="nav-link {{ Route::is('order.create') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.create_delivery_order') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('order.index')}}" class="nav-link {{ Route::is('order.index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.all_order') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('get-order-confirmed')}}" class="nav-link {{ Route::is('get-order-confirmed') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.confirmed_order') }}</p>
              </a>
            </li>
            @endif
            @if(Auth::user()->role != 5 || Auth::user()->role == 6)
              <li class="nav-item">
                <a href="{{route('get-order-delivery')}}" class="nav-link {{ Route::is('get-order-delivery') ? 'active' : '' }}">
                  <i class="nav-icon far fa-circle"></i>
                  <p>{{ __('sidebar.pending_delivery') }}</p>
                </a>
              </li>
              @endif
            @if(Auth::user()->role != 4 || Auth::user()->role == 6)
              <li class="nav-item">
                <a href="{{route('get-order-payment')}}" class="nav-link {{ Route::is('get-order-payment') ? 'active' : '' }}">
                  <i class="nav-icon far fa-circle"></i>
                  <p>{{ __('sidebar.pending_payment') }}</p>
                </a>
              </li>
            @endif
            @if(Auth::user()->role == 1 || Auth::user()->role == 2 || Auth::user()->role == 3)
              <li class="nav-item">
                <a href="{{route('get-order-completed')}}" class="nav-link {{ Route::is('get-order-completed') ? 'active' : '' }}">
                  <i class="nav-icon far fa-circle"></i>
                  <p>{{ __('sidebar.completed_orders') }}</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="{{route('return.index')}}" class="nav-link {{ Route::is('return.index') ? 'active' : '' }}">
                  <i class="nav-icon far fa-circle"></i>
                  <p>{{ __('sidebar.return_orders') }}</p>
                </a>
              </li>
            @endif
          </ul>
        </li>
        <li class="nav-item">
          <a href="{{route('purchase.index')}}" class="nav-link {{ Route::is('purchase.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-cart-plus"></i>
            <p>{{ __('sidebar.purchase_order') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{route('stock')}}" class="nav-link {{ Route::is('stock') ? 'active' : '' }}">
            <i class="nav-icon fas fa-truck"></i>
            <p>{{ __('sidebar.stock') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{route('dailyexpense.index')}}" class="nav-link {{ Route::is('dailyexpense.index') ? 'active' : '' }}">
            <i class="nav-icon fa fa-tags"></i>
            <p>{{ __('sidebar.daily_expense') }}</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="{{route('collection.index')}}" class="nav-link {{ Route::is('collection.index') ? 'active' : '' }}">
            <i class="nav-icon fas fa-rupee-sign"></i>
            <p>{{ __('sidebar.collection') }}</p>
          </a>
        </li>
        @if(Auth::user()->role == 1 || Auth::user()->role == 2)
        <li class="nav-item">
          <a href="#" class="nav-link">
            <i class="nav-icon fas fa-file-alt"></i>
            <p>
              {{ __('sidebar.reports') }}
              <i class="right fas fa-angle-left"></i>
            </p>
          </a>
          <ul class="nav nav-treeview pl-2" style="display: none;">
            <li class="nav-item">
              <a href="{{route('daily-profit-index')}}" class="nav-link {{ Route::is('daily-profit-index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.daily_profit_report') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('stock-report')}}" class="nav-link {{ Route::is('stock-report') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.stock_report') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('daily-product-index')}}" class="nav-link {{ Route::is('daily-product-index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.product_daily_sale_report') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('customer-report-index')}}" class="nav-link {{ Route::is('customer-report-index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.customer_report') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('get-pending-payment-index')}}" class="nav-link {{ Route::is('get-pending-payment-index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.payment_pending_report') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('gst.r1.index')}}" class="nav-link {{ Route::is('gst.r1.index') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.gst_reports') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('eway.index')}}" class="nav-link {{ Route::is('eway.*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.eway_bill') }}</p>
              </a>
            </li>
            <li class="nav-item">
              <a href="{{route('einvoice.index')}}" class="nav-link {{ Route::is('einvoice.*') ? 'active' : '' }}">
                <i class="nav-icon far fa-circle"></i>
                <p>{{ __('sidebar.e_invoice') }}</p>
              </a>
            </li>
          </ul>
        </li>
        @endif
      </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <script>
    function submitForm() {
      $("form#logoutForm").submit();
    }
  </script>