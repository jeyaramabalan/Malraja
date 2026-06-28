<?php

namespace App\Http\Controllers;
use App\Models\ProductsModel;
use App\Models\RouteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RouteController extends Controller
{
    public function index()
    {
        $this->datas['route'] = route("route.create");
        return view('route.route')->with($this->datas);
    }

    public function getRoutelist(Request $request)
    {
        $dataQry = RouteModel::where('status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');

        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('route.edit', [$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('route-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function create()
    {
        $this->datas['route'] = array();
        $this->datas['route'] = route("route.store");
        return view('route.routeaddedit')->with($this->datas);
    }

    public function delete($id)
    {
        RouteModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 1, 'msg' => "Route Deleted Successfully");
        return redirect()->route('route.index')->with('status', $output);
    }

    public function edit($id)
    {
        $route = RouteModel::find($id);
        $this->datas['route'] = route("route.update", $id);
        $this->datas['routes'] = $route;
        return view('route.routeaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route' => 'unique:route,name',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
        $data = [];
        $data['name'] = $request->name;
        $count = RouteModel::where("name", $request->name)->count();
        $output = array('success' => 1, 'msg' => 'Route Create Successfully');
        if($count == 0) {
            RouteModel::create($data);
            $output = array('success' => 1, 'msg' => 'Route Create Successfully');
        } else {
            $output = array('failed' => 0, 'msg' => 'Route Name Already Exists');
            return redirect()->back()->with('status', $output);
        }
        return redirect()->route('route.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'route' => 'unique:route,name',
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }
           
        $data = [];
        $data['name'] = $request->name;
        RouteModel::where("id", $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Route Updated Successfully');
        return redirect()->route('route.index')->with('status', $output);
    }

}
