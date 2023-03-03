<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Validator;


class ProductController extends Controller
{
    //
    private $title = '';
    private $breadcrumb = '';
    private $active = '';
    private $page_title = '';
    private $user = '';
    function setup(){
        $this->title = 'Products';
        $this->breadcrumb = 'Product';
        $this->active = 'product';
        $this->page_title = 'Product';
        $this->user = Auth::user()->name;
    }

    function dataset(){
        return [
            'title' => $this->title,
            'breadcrumb' => $this->breadcrumb,
            'active' => $this->active,
            'page_title' => $this->page_title,
            'user' => $this->user,
        ];
    }

    function index(){
        $this->setup();
        return view('page/product', $this->dataset());
    }

    function create(){
        $this->setup();
        $this->title = 'Create Product';
        return view('create/product', $this->dataset());
    }

    function store(Request $request){
        $name = $request->name;
        $price = $request->price;
        $stock = $request->stock;
        $description = $request->description;

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products,name',
            'price' => 'required',
            'stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                 ->withErrors($validator->errors())
                 ->withInput();
       }

       DB::beginTransaction();
       try{
            $product = new Product;
            $product->name = $name;
            $product->price = $price;
            $product->stock = $stock;
            $product->description = $description;
            $product->save();
            DB::commit();
            Alert::success('Product added successfully');
            return redirect('/product');
       }catch(\Exception $e){
            DB::rollBack();
            Alert::error($e->getMessage());
            return redirect()->back()->withInput();
       }
       
    }

    function edit($id){
        $this->setup();
        $this->title = 'Edit Product';
        $dataset = $this->dataset();
        $data = Product::where('id', $id)->first();

        $dataset['entry'] = $data;
        return view('update/product', $dataset);
    }

    function update(Request $request, $id){
        $name = $request->name;
        $price = $request->price;
        $stock = $request->stock;
        $description = $request->description;

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:products,name,'.$id,
            'price' => 'required',
            'stock' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                 ->withErrors($validator->errors())
                 ->withInput();
       }

       DB::beginTransaction();
       try{
            $product = Product::where('id', $id)->first();
            $product->name = $name;
            $product->price = $price;
            $product->stock = $stock;
            $product->description = $description;
            $product->save();
            DB::commit();
            Alert::success('Product changed successfully');
            return redirect('/product');
       }catch(\Exception $e){
            DB::rollBack();
            Alert::error($e->getMessage());
            return redirect()->back()->withInput();
       }
    }

    function delete($id){
        DB::beginTransaction();
        try{
            $delete = Product::where('id', $id)->delete();
            DB::commit();            
            return $delete;
        }catch(\Exception $e){
            DB::rollBack();
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }


    function search(Request $request){
        $draw = $request->draw;
        $start = ($request->start != null) ? $request->start : 0;
        $rowperpage = ($request->length != null) ? $request->length : 10;
        $order = ($request->order != null) ? $request->order : false;
        $search = ($request->search != null && $request->search['value'] != null) ? $request->search : false;

        $model = new Product;
        $totalRows = $model->count();
        $filteredRows = $model->count();

        if ($search) {
            $search = $request->search['value'];
            $model = $model->where('name', 'LIKE', '%'.$search.'%')
            ->orWhere('price', 'LIKE', '%'.$search.'%')
            ->orWhere('stock', 'LIKE', '%'.$search.'%')
            ->orWhere('description', 'LIKE', '%'.$search.'%');            
            $filteredRows = $model->count();
        }

        $model = $model->skip((int) $start);
        $model = $model->take((int) $rowperpage);

        if($order){
            foreach(request()->input('columns') as $key => $column){
                if($key == $order[0]['column']){
                    $direction = ($order[0]['dir'] == 'asc') ? 'ASC' : 'DESC';
                    $model = $model->orderBy($column['name'], $direction);
                }
            }
        }

        $resuls = $model->get();

        $data_arr = [];


        foreach($resuls as $result){
            $url_edit = url('product/'.$result->id.'/edit');
            $url_delete = url('product/'.$result->id);
            $btn_edit = '<a href="'.$url_edit.'">
                <button type="button" class="btn btn-warning btn-sm">
                    <i class="fa fa-edit"></i> 
                </button>
            </a>';
            $btn_delete = '
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteBtn(\''.$url_delete.'\')">
                    <i class="fa fa-trash"></i> 
                </button>';
            $data_arr[] = [
                'name' => $result->name,
                'price' => $result->price,
                'stock' => $result->stock,
                'description' => $result->description,
                'action' => $btn_edit. ''. $btn_delete,
            ];
        }

        return [
            'draw'            => $draw,
            'recordsTotal'    => $totalRows,
            'recordsFiltered' => $filteredRows,
            'data'            => $data_arr,
        ];

    }

}
