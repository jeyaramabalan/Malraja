<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\PlaceModel;
use App\Models\ProductsModel;
use App\Models\PurposeVisitModel;
use App\Models\RelativeTypesModel;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Schema;

class MasterController extends BaseController
{
    public function getPlaces(Request $request)
    {
        try {
            $places = PlaceModel::select('id', 'text')
                        ->where('status', 1)
                        ->get();

            return $this->sendResponse(["places" => $places], "Places retrived succesfully");
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getPurposeVisits(Request $request)
    {
        try {
            $pVisits = PurposeVisitModel::select('id', 'text')
                        ->where('status', 1)
                        ->get();

            return $this->sendResponse(["purpose_of_visits" => $pVisits], "Purpose of visits retrived succesfully");
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getRelativeTypes(Request $request)
    {
        try {
            $relativeTypes = RelativeTypesModel::select('id', 'text')
                        ->where('status', 1)
                        ->get();

            return $this->sendResponse(["relativeTypes" => $relativeTypes], "Relative types retrived succesfully");
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

    public function getProducts(Request $request)
    {
        try {
            // Support both legacy schemas (`text`) and current schemas (`name`).
            if (Schema::hasColumn('products', 'text')) {
                $products = ProductsModel::select('id', 'text')
                    ->where('status', 1)
                    ->get();
            } else {
                $products = ProductsModel::select('id', 'name as text')
                    ->where('status', 1)
                    ->get();
            }

            return $this->sendResponse(["products" => $products], "products retrived succesfully");
        } catch(Exception $e) {
            return $this->sendError($e->getMessage(), [], 200);
        }
    }

}
