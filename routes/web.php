<?php

use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\VisitsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\AdminLoginController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ClaimedDetailsController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\DailyExpenseController;
use App\Http\Controllers\DailyExports;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\HsnController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PurchaseOrdersController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UsersController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RetailController;
use App\Http\Controllers\RetailStockController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\GSTR1Controller;
use App\Http\Controllers\EwayBillController;
use App\Http\Controllers\EInvoiceController;
use App\Models\CollectionModel;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/logout', function () {
//     return view('login');
// });

// Route::get('customer', [CustomerController::class,'index'])->name('customer');
// Route::get('visits', [VisitsController::class,'index'])->name('visits');
// Route::get('attendance', [AttendanceController::class,'index'])->name('attendance');
Route::get('login', [AdminLoginController::class,'index'])->name('login');
Route::get('/', [AdminLoginController::class,'index'])->name('home');
Route::post('logout', [AdminLoginController::class,'postLogout'])->name('admin.logout');
Route::post('/login/post', [AdminLoginController::class,'postLogin'])->name('admin.login.submit');
Route::middleware('auth:web')->group(function () {    
    // Resources
    Route::resource('customer', CustomerController::class);
    Route::resource('category', CategoryController::class);
    Route::resource('products', ProductController::class);
    Route::resource('unit', UnitController::class);
    Route::resource('hsn', HsnController::class);
    Route::resource('vendor', VendorController::class);
    Route::resource('order', OrdersController::class);
    Route::resource('purchase', PurchaseOrdersController::class);
    Route::resource('user', UsersController::class);
    Route::resource('expense', ExpenseController::class);
    Route::resource('dailyexpense', DailyExpenseController::class);
    Route::resource('claimeddetails', ClaimedDetailsController::class);
    Route::resource('collection', CollectionController::class);
    Route::resource('route', RouteController::class);
    Route::resource('return', ReturnController::class);
    Route::resource('retail', RetailController::class);

    // Reports
    Route::get('reports/dailyprofit', [ReportsController::class,'dailyProfitIndex'])->name('daily-profit-index');
    Route::get('reports/dailyproduct', [ReportsController::class,'dailyProductIndex'])->name('daily-product-index');
    Route::post('reports/dailyproduct/get', [ReportsController::class,'dailyProductGet'])->name('daily-product-get');
    Route::post('reports/dailyprofit/get', [ReportsController::class,'dailyProfitGet'])->name('daily-profit-get');
    Route::get('reports/dailyproduct/generate', [ReportsController::class,'dailyProduct'])->name('daily-product-generate');
    Route::post('reports/dailyprofit/generate', [ReportsController::class,'dailyProfit'])->name('daily-profit');
    Route::get('reports/customer', [ReportsController::class,'customerReport'])->name('customer-report-index');
    Route::post('reports/customer/get', [ReportsController::class,'customerReportGet'])->name('customer-report-get');
    Route::get('reports/pending-payment/get', [ReportsController::class,'paymentPendingIndex'])->name('get-pending-payment-index');
    Route::post('reports/pending-payment/getdata', [ReportsController::class,'paymentPendingGet'])->name('get-pending-payment-report');
    Route::post('reports/pending-payment/print', [ReportsController::class,'paymentPendingPrint'])->name('post-pending-payment-report');

    Route::get('reports/stockreport', [ReportsController::class,'stockReport'])->name('stock-report');
    Route::post('reports/stockreport/get', [ReportsController::class,'getStockReport'])->name('get-stock-report');
    Route::post('reports/stockreport/print', [ReportsController::class,'getStockReportPrint'])->name('get-stock-report-print');

    // Get Call
    Route::get('dashboard', [UsersController::class,'dashboard'])->name('dashboard');
    Route::get('users', [UsersController::class,'index'])->name('users');
    Route::get('stock', [StockController::class,'getStock'])->name('stock');
    Route::get('retailstock', [RetailStockController::class,'getStock'])->name('retailstock');
    Route::get('reports/stockvalue', [StockController::class,'getStockValueReport'])->name('stock-value-report');
    Route::get('attendance', [AttendanceController::class,'index'])->name('attendance');
    Route::get('settings', [AttendanceController::class,'index'])->name('settings');

    
    //  Delete
    Route::get('customer/delete/{id}', [CustomerController::class,'delete'])->name('customer-delete');
    Route::get('category/delete/{id}', [CategoryController::class,'delete'])->name('category-delete');
    Route::get('products/delete/{id}', [ProductController::class,'delete'])->name('products-delete');
    Route::get('unit/delete/{id}', [CustomerController::class,'delete'])->name('unit-delete');
    Route::get('hsn/delete/{id}', [CustomerController::class,'delete'])->name('hsn-delete');
    Route::get('vendor/delete/{id}', [VendorController::class,'delete'])->name('vendor-delete');
    Route::get('order/delete/{id}', [CustomerController::class,'delete'])->name('order-delete');
    Route::get('user/delete/{id}', [UsersController::class,'delete'])->name('user-delete');
    Route::get('expense/delete/{id}', [ExpenseController::class,'delete'])->name('expense-delete');
    Route::get('dailyexpense/delete/{id}', [DailyExpenseController::class,'delete'])->name('dailyexpense-delete');
    Route::get('claimeddetails/delete/{id}', [ClaimedDetailsController::class,'delete'])->name('claimed-details-delete');
    Route::get('collection/delete/{id}', [CollectionController::class,'delete'])->name('collection-delete');
    Route::get('route-delete/delete/{id}', [RouteController::class,'delete'])->name('route-delete');

    // Bill
    Route::get('order/bill/{id}',[OrdersController::class,'generateBill'])->name('order-bill');
    Route::get('order/bill_gst/{id}',[OrdersController::class,'generateBillgst'])->name('order-bill-gst');
    Route::get('retail/bill/{id}',[RetailController::class,'generateBill'])->name('retail-bill');
    Route::get('retail/bill_gst/{id}',[RetailController::class,'generateBillgst'])->name('retail-bill-gst');
    Route::post('order/pos/store',[OrdersController::class,'posStore'])->name('order-pos-store');

    //  Show
    Route::get('order/{id}', [OrdersController::class,'show'])->name('order-show');

    // Ajax call
    Route::post('get-category-list',[CategoryController::class,'getCategorylist'])->name('get-category-list');
    Route::post('get-user-list',[UsersController::class,'getUserlist'])->name('get-user-list');
    Route::post('get-vendor-list',[VendorController::class,'getvendorlist'])->name('get-vendor-list');
    Route::post('get-unit-list',[UnitController::class,'getUnitlist'])->name('get-unit-list');
    Route::post('get-route-list',[RouteController::class,'getRoutelist'])->name('get-route-list');
    Route::post('get-hsn-list',[HsnController::class,'gethsnlist'])->name('get-hsn-list');
    Route::post('get-customer-list',[CustomerController::class,'getcustomerlist'])->name('get-customer-list');
    Route::post('get-visit-list',[VisitsController::class,'getVisitList'])->name('get-visit-list');
    Route::post('get-category-product',[ProductController::class,'getCategoryProduct'])->name('get-category-product');
    Route::post('get-order-list',[OrdersController::class,'getOrderList'])->name('get-order-list');
    Route::post('get-purchase-order-list',[PurchaseOrdersController::class,'getOrderList'])->name('get-purchase-order-list');
    Route::post('get-return-list',[ReturnController::class,'getReturnList'])->name('get-return-list');
    Route::post('get-product',[ProductController::class,'getProduct'])->name('get-product');
    Route::post('get-retail-product',[ProductController::class,'getRetailProduct'])->name('get-retail-product');
    Route::post('get-product-list',[ProductController::class,'getProductList'])->name('get-product-list');
    Route::post('get-stock-list',[StockController::class,'getStockList'])->name('get-stock-list');
    Route::post('get-retail_stock-list',[RetailStockController::class,'getStockList'])->name('get-retail_stock-list');
    Route::post('get-stock-value-list',[StockController::class,'getStockValueList'])->name('get-stock-value-list');
    Route::post('get-expense-list',[ExpenseController::class,'getExpenseList'])->name('get-expense-list');
    Route::post('get-daily-expense-list',[DailyExpenseController::class,'getDailyExpenseList'])->name('get-daily-expense-list');
    Route::post('get-claimed-detail-list',[ClaimedDetailsController::class,'getClaimedlist'])->name('get-claimed-detail-list');
    Route::post('get-collection-list',[CollectionController::class,'getCollectionlist'])->name('get-collection-list');
    Route::post('add-to-cart',[ProductController::class,'addToCart'])->name('add-to-cart');
    Route::post('add-to-cart-purchase',[ProductController::class,'addToCartPurchase'])->name('add-to-cart-purchase');
    Route::post('status-update',[OrdersController::class,'statusUpdate'])->name('status-update');
    Route::post('approve-expense',[DailyExpenseController::class,'approveExpense'])->name('approve-expense');
    Route::post('get-customer-pending',[OrdersController::class,'getCustomerPendingPayment'])->name('get-customer-pending');
    Route::get('order/status/payment',[OrdersController::class,'paymentPending'])->name('get-order-payment');
    Route::get('order/status/delivery',[OrdersController::class,'deliveryPending'])->name('get-order-delivery');
    Route::get('order/status/completed',[OrdersController::class,'completedOrder'])->name('get-order-completed');
    Route::get('order/status/confirmed',[OrdersController::class,'confirmedOrder'])->name('get-order-confirmed');
    
    Route::get('retail/status/payment',[OrdersController::class,'paymentPending'])->name('get-retail-payment');
    Route::get('retail/status/delivery',[OrdersController::class,'deliveryPending'])->name('get-retail-delivery');
    Route::get('retail/status/completed',[OrdersController::class,'completedOrder'])->name('get-retail-completed');
    Route::get('retail/status/confirmed',[OrdersController::class,'confirmedOrder'])->name('get-retail-confirmed');

    Route::get('order/pos/new',[OrdersController::class,'newPos'])->name('new-pos-order');
    Route::post('purchase/editdetails', [PurchaseOrdersController::class,'getEditPurchase'])->name('get-purchase-cart');
    Route::post('order/cart', [OrdersController::class,'getOrderCart'])->name('get-order-cart');
    Route::post('order/status/all', [OrdersController::class,'statusUpdateAll'])->name('status-update-all');
    Route::post('retail-add-to-cart',[RetailController::class,'addToCart'])->name('retail-add-to-cart');
    Route::post('retail/cart', [RetailController::class,'getOrderCart'])->name('get-retail-cart');
    Route::post('get-retail-order-list', [RetailController::class,'getRetailOrderList'])->name('get-retail-order-list');

    // Routes for Deleting Purchase Orders
    Route::get('/purchase-orders/delete-page', [\App\Http\Controllers\PurchaseDeletionController::class, 'index'])->name('purchase.delete.index');
    Route::post('/purchase-orders/delete', [\App\Http\Controllers\PurchaseDeletionController::class, 'destroy'])->name('purchase.delete.destroy');
    Route::get('/purchase-orders/download-file', [\App\Http\Controllers\PurchaseDeletionController::class, 'downloadFile'])->name('purchase.download.file');
    Route::get('/purchase-orders/download-sql', [\App\Http\Controllers\PurchaseDeletionController::class, 'downloadSql'])->name('purchase.download.sql');

    //GST Report routes
    Route::get('/gst-r1-report', [GSTR1Controller::class, 'index'])->name('gst.r1.index');
    Route::get('/gst-r1-report/export/excel', [GSTR1Controller::class, 'exportExcel'])->name('gst.r1.export.excel');
    Route::get('/gst-r1-report/export/json', [GSTR1Controller::class, 'exportJson'])->name('gst.r1.export.json');

    Route::get('/eway-bill', [EwayBillController::class, 'index'])->name('eway.index');
    Route::get('/eway-bill/preview', [EwayBillController::class, 'preview'])->name('eway.preview');
    Route::get('/eway-bill/pdf', [EwayBillController::class, 'pdf'])->name('eway.pdf');
    Route::get('/eway-bill/zip', [EwayBillController::class, 'zip'])->name('eway.zip');

    Route::get('/e-invoice', [EInvoiceController::class, 'index'])->name('einvoice.index');
    Route::get('/e-invoice/preview', [EInvoiceController::class, 'preview'])->name('einvoice.preview');
    Route::get('/e-invoice/json', [EInvoiceController::class, 'json'])->name('einvoice.json');
    Route::get('/e-invoice/pdf', [EInvoiceController::class, 'pdf'])->name('einvoice.pdf');
    Route::get('/e-invoice/zip', [EInvoiceController::class, 'zip'])->name('einvoice.zip');
    
    Route::get('language/{locale}', [\App\Http\Controllers\LocalizationController::class, 'switch'])->name('language.switch');
});