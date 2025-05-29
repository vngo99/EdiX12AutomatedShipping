<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use App\Models\OrderItem;
use App\Models\ShipTo;
use App\Services\MyEdi\MyEdi;
use DataTables;
use Illuminate\Support\Facades\DB;

class PoInboundController extends Controller
{
    public function index()
    {
        return view('poinbound');
    }

    public function getPoInbound(Request $request)
    {
       
        if ($request->ajax()) {
            $po = app(PurchaseOrder::class);
            $params = array(
                'start' => $request->get('start'),
                'length' => $request->get('length'),
                'search' => $request->get('search'),
                'status_filter' => $request->get('status_filter')
            );

            $data_result = $po->orders($params);
            $data1 = array_slice($data_result, $params['start'], $params['length']);

            $data1 = $data_result;
            if($data_result){
               
                return Datatables::of($data1)
                ->addIndexColumn()
                ->addColumn('items', function($row){
                    $items =  OrderItem::where('po_id', $row->id)
                    ->get();
                    $item_array = null;
                    foreach ($items as $i) {
                        $item_array[] = $i->toArray();
                    }
                    return $item_array;
                })
                ->rawColumns(['items'])
                ->addColumn('action', function($row){
                    return '';
                })
                ->rawColumns(['action'])
                ->setTotalRecords(count($data_result))
                ->make(true);

            }else{
                return Datatables::of($data1)
               
                ->addIndexColumn()
                ->addColumn('items', function($row){
                    $items =  OrderItem::where('po_id', $row->id)
                    ->get();
                    $item_array = null;

                    foreach ($items as $i) {
                        $item_array[] = $i->toArray();
                    }
                    return $item_array;
                })
                ->rawColumns(['items'])
                ->addColumn('action', function($row){
                    return '';
                })
                ->rawColumns(['action'])
                ->setTotalRecords(count($data_result))
                ->make(true);
            }
        }
    }
    public function services(Request $request){
        $input = $request->all();
        $success = 0;
        $message = '';

        switch($input['action']){
            case 'item_edit':
                $data = $input['data'];
                $a = OrderItem::where('id', $data['id'])->firstorFail();
              
                $field = $data['field'];
                $val = $data['value'];
                if($a){
                    $a->$field = $val;
                    $a->save();
                    $success =1 ;
                    $message = "$field with $val saved..";

                    if($field =='actual_quantity' && $val ==0){
                        $a->quantity = $val;
                        $a->save();

                    }
                }else{
                    $message = "$field with $val failed to saved..";
                }
               
                break;

            case 'edit_address':
                $data = $input['data'];
                $a = ShipTo::where('po_id', $data['id'])->firstorFail();
              
                $field = $data['field'];
                $val = $data['value'];
                if($a){
                    $a->$field = $val;
                    $a->save();
                    $success =1 ;
                    $message = "$field with $val saved..";
                }else{
                    $message = "$field with $val failed to saved..";
                }
               
                break;
            case 'bulkinvoice':
              
            
                $o = new PurchaseOrder();
                $orders = $o->get_orders_bulk_invoice($input['data']);

            
                if(count($orders)){
                    $edi = app(MyEdi::class);
                    \Log::channel('invoice_notice')->info("bulk invoice:");
                    foreach($orders as $order){
            
                        $rs = $edi->send_invoice($order, $type='DI', $debug = 0);

                        \Log::channel('invoice_notice')->info($rs);
                        if($rs['success']){
                            \Log::channel('invoice_notice')->info("$order->order_id invoice notice sent.");
                            $message .= "$order->order_id invoice notice sent.";
                        }else{
                            \Log::channel('invoice_notice')->info("invoice notice fail to send");
                            $message .= "$order->order_id invoice notice fail to sent.";
                        } 
                    }

                    $success =1;

                
                }else{
                    \Log::channel('invoice_notice')->info("No Shipped Status..no orders found..");
                    $message = "No PO invoice data provided";
                }
                
                break;
            default:
                $message = "unknow action..";
            break;

        }

        return array('success'=>$success, 'response'=>$message);
    }
}
