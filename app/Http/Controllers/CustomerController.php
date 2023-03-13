<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Mitra;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\MessageService;
use App\Models\OrderDetailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    //
    function searchMitra(Request $request){
        $validator = Validator::make($request->all(), [
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }
       
       $latitude = $request->latitude;
       $longitude = $request->longitude;

       DB::beginTransaction();
       try{
            $mitras = Mitra::select(DB::raw("
            id, 
            name,
            address,
            (
              6371 * acos (
                cos ( radians($latitude) )
                * cos( radians( latitude ) )
                * cos( radians( longitude ) - radians($longitude) )
                + sin ( radians($latitude) )
                * sin( radians( latitude ) )
              )
            ) AS distance"))->orderBy('distance', 'ASC')->get();

            $results = [];
            foreach($mitras as $mitra){
                $results[] = [
                    'id' => $mitra->id,
                    'name' => $mitra->name,
                    'address' => $mitra->address,
                ];
            }
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Show data mitra',
                'data' => $results
            ], 200);         
       }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 422);
        }
    }

    function sendOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'mitra_id' => 'required|integer',
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }
       
       $customer_id = $request->customer_id;
       $mitra_id = $request->mitra_id;
       $latitude = $request->latitude;
       $longitude = $request->longitude;

       DB::beginTransaction();
       try{

            $check_mitra = Order::where('customer_id', $customer_id)
            ->whereHas('order_details', function(Builder $query) use($mitra_id){
                $query->where('mitra_id', $mitra_id)
                ->where('status', Order::PENDING);
            })->first();

            if($check_mitra != null){
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => "Your partner's order is still waiting",
                    'data' => []
                ], 200);
            }

            $order = new Order;
            $order->customer_id = $customer_id;
            $order->date_order = Carbon::now();
            $order->latitude = $latitude;
            $order->longitude = $longitude;
            $order->status = Order::PENDING;
            $order->save();

            $order_detail = new OrderDetail;
            $order_detail->order_id = $order->id;
            $order_detail->mitra_id = $mitra_id;
            $order_detail->status = Order::PENDING;
            $order_detail->save();

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Success order data',
                'data' => []
            ], 200);      
       }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 422);
        }
    }

    function listOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }

       $orders = Order::where('customer_id', $request->customer_id)->orderBy('id', 'DESC')->get();

       $results = [];
       foreach($orders as $order){
         $results[] = [
            'order_id' => $order->id,
            'date' => $order->date_order,
            'status' => $order->status,
         ];
       }

       return response()->json([
        'status' => true,
        'message' => '',
        'data' => $results
       ], 200);

    }

    function detailOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|integer',
            'order_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }
       DB::beginTransaction();
       try{
        $mitra_id = $request->mitra_id;
        $order_id = $request->order_id;

        $order = Order::where('id', $order_id)->first();

        $order_details = OrderDetail::leftJoin('mitras', 'mitras.id', '=', 'order_details.mitra_id')
        ->where('order_id', $order_id)->select(
            "order_Details.id as id",
            "order_details.mitra_id as mitra_id",
            "order_details.status as status",
            "mitras.name as name",
            "mitras.address as address",
            "mitras.latitude",
            "mitras.longitude",
        )->orderBy('order_details.id', 'DESC')
        ->get();

        $customer = Customer::where('id', $order->customer_id)->first();

        $details = [];

        foreach($order_details as $detail){
            $data_service = [];
            if($detail->status == Order::APPROVED){
                $services = OrderDetailService::leftJoin('message_services', 
                    'message_services.id', '=', 'order_detail_services.message_service_id')
                ->where('order_detail_id', $detail->id)
                ->where('mitra_id', $detail->mitra_id)->get();

                foreach($services as $service){
                    $data_service[] = $service->name;
                }
            }
            $details[] = [
                'mitra_id' => $detail->mitra_id,
                'name' => $detail->name,
                'address' => $detail->address,
                'latitude' => $detail->latitude,
                'longitude' => $detail->longitude,
                'status' => $detail->status,
                'service' => $data_service,
            ];
        }

        $data = [
            'order_id' => $order->id,
            'date_order' => $order->date_order,
            'status' => $order->status,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'address' => $customer->address,
            ],
            'detail' => $details,
        ];

        DB::commit();
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => $data,
        ]);


       }catch (\Exception $e){
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'errors' => [],
            ], 422);
        }
    }

}
