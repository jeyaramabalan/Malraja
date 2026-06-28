<?php

use App\Http\Controllers\API\AttendanceController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CustomerController;
use App\Http\Controllers\API\DesktopPosController;
use App\Http\Controllers\API\MasterController;
use App\Http\Controllers\API\VisitController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->group(function () {
    Route::post('add-customer', [CustomerController::class,'createCustomer']);
    Route::post('get-customers', [CustomerController::class,'getCustomers']);
    Route::post('update-customer', [CustomerController::class,'updateCustomer']);
    Route::post('update-family-member', [CustomerController::class,'updateFamilyMember']);
    Route::post('add-family-member', [CustomerController::class,'addFamilyMember']);
    Route::post('add-visit', [VisitController::class,'addVisit']);
    Route::post('get-visit', [VisitController::class,'getVisit']);
    Route::post('get-user', [AuthController::class,'getAllUser']);
    Route::post('add-attendance', [AttendanceController::class,'addAttendance']);

    // PDF report
    Route::post('get-customer-pdf', [CustomerController::class,'customerPdf']);
    Route::post('desktop/product-by-code', [DesktopPosController::class, 'getProductByCode']);
    Route::get('desktop/product-codes', [DesktopPosController::class, 'getProductCodes']);
    Route::post('desktop/customer-by-name', [DesktopPosController::class, 'getCustomerByName']);
    Route::get('desktop/customer-names', [DesktopPosController::class, 'getCustomerNames']);
    Route::post('desktop/stock-by-product-code', [DesktopPosController::class, 'getStockByProductCode']);
    Route::get('desktop/order/next-bill-no', [DesktopPosController::class, 'getNextBillNo']);
    Route::post('desktop/order/save-pos', [DesktopPosController::class, 'savePosOrder']);
    Route::get('desktop/report/daily-sales', [DesktopPosController::class, 'getDailySalesReport']);
    Route::post('desktop/report/datewise-product-sales', [DesktopPosController::class, 'getDatewiseProductSalesReport']);
    Route::post('desktop/report/tax', [DesktopPosController::class, 'getTaxReport']);
    Route::post('desktop/order/by-bill-no', [DesktopPosController::class, 'getOrderByBillNo']);
    Route::post('desktop/order/delete-by-bill-no', [DesktopPosController::class, 'deleteOrderByBillNo']);

});

// Login
Route::post('login', [AuthController::class,'login']);
//  Master data
Route::get('get-purpose-visit', [MasterController::class,'getPurposeVisits']);
Route::get('get-product', [MasterController::class,'getProducts']);
Route::get('get-places', [MasterController::class,'getPlaces']);
Route::get('get-relative-types', [MasterController::class,'getRelativeTypes']);