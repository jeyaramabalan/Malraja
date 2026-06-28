<?php

namespace App\Http\Controllers;

use App\Models\CategoryModel;
use App\Models\CustomerModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    public function index()
    {
        $category = CategoryModel::select('id', 'name', 'status')->get();
        $this->datas['categories'] = $category;
        $this->datas['category'] = 'category';
        $this->datas['route'] = route('category.create');
        return view('category.category')->with($this->datas);
    }

    public function getCategorylist(Request $request)
    {
        $dataQry = CategoryModel::select('id', 'name')->where('status', 1);
        $limit = $request->input('length');
        $offset = $request->input('start');

        $datas = $dataQry->get();
        $datalist = [];
        $i = $offset;
        foreach($datas as $list)
        {
            $list->sno          = ++$i .'';
            $list->action       = '<td align="center">
                                        <a href="'.route('category.edit',[$list->id]).'"><i style="color: green;" class="fa fa-edit"></i></a>
                                        <a class="pl-2" href="'.route('category-delete', [$list->id]).'"><i style="color: red;" class="fa fa-trash"></i></a>
                                   </td>';
            $datalist[]         = $list;
        }

        $json_data = ['data'=>$datalist];
        echo json_encode($json_data);
    }

    public function delete($id)
    {
        CategoryModel::where('id', $id)->update(["status" => 0]);
        $output = array('success' => 0, 'msg' => "Category Deleted Successfully");
        return redirect()->route('category.index')->with('status', $output);
    }

    public function edit($id)
    {
        $category = CategoryModel::find($id);
        $this->datas['route'] = route('category.update', $id);
        $this->datas['category'] = $category;
        return view('category.categoryaddedit')->with($this->datas);
    }

    public function create()
    {
        $this->datas['category'] = array();
        $this->datas['route'] = route('category.store');
        return view('category.categoryaddedit')->with($this->datas);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'unique:category,name'
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        CategoryModel::create($data);
        $output = array('success' => 1, 'msg' => 'Category Created Successfully');
        return redirect()->route('category.index')->with('status', $output);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required'
        ]);

        if ($validator->fails())
        {
            $output = array('success' => 0, 'msg' => $validator->errors()->first());
            return redirect()->back()->withErrors($validator)->withInput()->with('status', $output);
        }

        $data = [];
        $data['name'] = $request->name;
        
        CategoryModel::where('id', $request->id)->update($data);
        $output = array('success' => 1, 'msg' => 'Category Updated Successfully');
        return redirect()->route('category.index')->with('status', $output);
    }
}
