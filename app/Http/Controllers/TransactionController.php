<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{
    private $title = '';
    private $breadcrumb = '';
    private $active = 'transaction';
    private $page_title = 'Transaction';
    private $user = '';
    
    function setup(){
        $this->title = 'Transaction';
        $this->breadcrumb = 'Transaction';
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
        return view('page/transaction', $this->dataset());
    }

    //
    function store(Request $request){
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'quantity' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }

       DB::beginTransaction();
       try {
            $product_id = $request->product_id;
            $quantity = $request->quantity;
            $product = Product::where('id', $request->product_id)->first();
            if($product->stock < $quantity){
                throw new \Exception('the number of stock of this product is less than the quantity');
            }

            $payment_amount = $product->price * $quantity;
            $update_stock = $product->stock - $quantity;

            $get_reference = Http::withHeaders([
                'X-API-KEY' => 'DATAUTAMA',
            ])->post('https://sandbox.saebo.id/api/v1/transactions', [
                'quantity' => $quantity,
                'price' => $product->price,
                'payment_amount' => $payment_amount,
            ]);
            
            $reference_body = $get_reference->json();
            if($reference_body['code'] != 20000){
                throw new \Exception($reference_body['message']);
            }
            $reference_no = $reference_body['data']['reference_no'];

            // add transaction
            $transaction = new Transaction();
            $transaction->reference_no = $reference_no;
            $transaction->price = $product->price;
            $transaction->quantity = $quantity;
            $transaction->payment_amount = $payment_amount;
            $transaction->product_id = $product_id;
            $transaction->save();

            // update stock
            $product->stock = $update_stock;
            $product->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Successfully added transaction data'
            ], 200);

       } catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 422);
       }
    }

    function search(Request $request){
        $draw = $request->draw;
        $start = ($request->start != null) ? $request->start : 0;
        $rowperpage = ($request->length != null) ? $request->length : 10;
        $order = ($request->order != null) ? $request->order : false;
        $search = ($request->search != null && $request->search['value'] != null) ? $request->search : false;

        $model = Transaction::leftJoin('products', function($q){
            $q->on('products.id', '=', 'transactions.product_id');
        });
        $totalRows = $model->count();
        $filteredRows = $model->count();

        if ($search) {
            $search = $request->search['value'];
            $model = $model->where('reference_no', 'LIKE', '%'.$search.'%')
            ->orWhere('transactions.price', 'LIKE', '%'.$search.'%')
            ->orWhere('quantity', 'LIKE', '%'.$search.'%')
            ->orWhere('payment_amount', 'LIKE', '%'.$search.'%')
            ->orWhere('products.name', 'LIKE', '%'.$search.'%');
            $filteredRows = $model->count();
        }

        $model = $model->skip((int) $start);
        $model = $model->take((int) $rowperpage);

        if($order){
            foreach(request()->input('columns') as $key => $column){
                $direction = ($order[0]['dir'] == 'asc') ? 'ASC' : 'DESC';
                if($key == $order[0]['column']){
                    $model = $model->orderBy($column['name'], $direction);
                }
            }
        }
        
        $resuls = $model->select('transactions.*', 'products.name as product')->get();

        $data_arr = [];

        foreach($resuls as $result){
            $data_arr[] = [
                'reference_no' => $result->reference_no,
                'price' => $result->price,
                'quantity' => $result->quantity,
                'payment_amount' => $result->payment_amount,
                'product' => $result->product,
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
