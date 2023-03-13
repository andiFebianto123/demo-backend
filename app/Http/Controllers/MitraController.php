<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Mitra;
use App\Models\Order;
use App\Models\Customer;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use App\Models\MessageService;
use Illuminate\Validation\Rule;
use App\Models\OrderDetailService;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;


class MitraController extends Controller
{
    function listOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'mitra_id' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }

       $mitra_id = $request->mitra_id;

       $orders = Order::whereHas('order_details', function(Builder $query) use($mitra_id){
            $query->where('mitra_id', $mitra_id);
       })->orderBy('id', 'DESC')->get();

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
            'mitra_id' => 'required|integer',
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

        $order_detail = OrderDetail::leftJoin('mitras', 'mitras.id', '=', 'order_details.mitra_id')
        ->where('order_id', $order_id)
        ->where('mitra_id', $mitra_id)->select(
            "order_details.mitra_id as mitra_id",
            "order_details.status as status",
            "mitras.name as name",
            "mitras.address as address",
            "mitras.latitude",
            "mitras.longitude",
        )->first();


        $customer = Customer::where('id', $order->customer_id)->first();

        $data = [
            'order_id' => $order->id,
            'date_order' => $order->date_order,
            'status' => $order->status,
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'address' => $customer->address,
            ],
            'detail' => [
                'mitra_id' => $order_detail->mitra_id,
                'name' => $order_detail->name,
                'address' => $order_detail->address,
                'latitude' => $order_detail->latitude,
                'longitude' => $order_detail->longitude,
                'status' => $order_detail->status,
            ]
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

    function allService(){
        $all_service = MessageService::select('id', 'name', 'description')->get();
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => $all_service->toArray(),
        ], 200);
    }

    function requestOrder(Request $request){
        $validator = Validator::make($request->all(), [
            'order_id' => 'required|integer',
            'mitra_id' => 'required|integer',
            'status' => [
                'required', 
                Rule::in([Order::APPROVED, Order::REJECTED])
            ],
            'message_service_id' => 'array|nullable',
            'message_service_id.*' => 'integer|distinct',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Request is invalid',
                'errors' => $validator->errors(),
            ], 422);
       }

       $order_id = $request->order_id;
       $mitra_id = $request->mitra_id;
       $status = $request->status;
       $message_service_id = ($request->message_service_id != null) ? $request->message_service_id : null;
       
       DB::beginTransaction();

       try{
            $order = Order::where('id', $order_id)->first();

            $order_detail = OrderDetail::where('order_id', $order_id)
            ->where('mitra_id', $mitra_id)->first();

            if( ($order_detail->status == Order::REJECTED) || ($order_detail->status == Order::APPROVED)){
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => "Order is your " . $order_detail->status,
                    'data' => []
                ], 200);
            }

            if($status == Order::APPROVED){
                // jika mitra setuju
                if($message_service_id != null){
                    foreach($message_service_id as $message){
                        $order_service = new OrderDetailService;
                        $order_service->order_detail_id = $order_detail->id;
                        $order_service->mitra_id = $order_detail->mitra_id;
                        $order_service->message_service_id = $message;
                        $order_service->save();
                    }
                }else{
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => "massage services must be filled",
                        'data' => []
                    ], 200);
                }
                
                $order_detail->status = Order::APPROVED;
                $order_detail->save();
                $order->status = Order::APPROVED;
                $order->save();
            }else{

                $latitude = $order->latitude;
                $longitude = $order->longitude;
                $customer_id = $order->customer_id;

                $iqnore_mitras = OrderDetail::select('mitra_id')->where('order_id', $order->id)->get()->map(function($item, $key){
                    return $item->mitra_id;
                })->all();

                $check_mitra = $this->checkMitra($customer_id, $latitude, $longitude, $iqnore_mitras);

                if($check_mitra != null){
                    $order_detail_new = new OrderDetail;
                    $order_detail_new->order_id = $order_id;
                    $order_detail_new->mitra_id = $check_mitra->id;
                    $order_detail_new->status = Order::PENDING;
                    $order_detail_new->save();
                    $order_detail->status = Order::REJECTED;
                    $order->status = Order::PENDING;
                }else{
                    $order_detail->status = Order::REJECTED;
                    $order->status = Order::REJECTED;
                }
                $order_detail->save();
                $order->save();
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Success '. $status .' data',
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

    private function checkMitra($customer_id, $latitude, $longitude, $iqnore){
        $latitude = $latitude;
        $longitude = $longitude;
        $data_iqnore = $iqnore;
        $customer_id = $customer_id;

        $mitra = Mitra::select(DB::raw("
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
            ) AS distance"))
            ->whereNotIn('id', $data_iqnore)
            ->orderBy('distance', 'ASC')->first();

        if($mitra != null){
            $mitra_id = $mitra->id;
            $check_mitra = Order::where('customer_id', $customer_id)
            ->whereHas('order_details', function(Builder $query) use($mitra_id){
                $query->where('mitra_id', $mitra_id)
                ->where(function($query){
                    $query->where('status', Order::PENDING);
                });
            })->first();
            
            if($check_mitra != null){
                $data_iqnore[] = $mitra_id;
                return $this->checkMitra($customer_id, $latitude, $longitude, $data_iqnore);
            }
            return $mitra;
        }
        return null;
    }


}
