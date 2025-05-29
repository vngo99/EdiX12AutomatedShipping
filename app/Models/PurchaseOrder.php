<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use App\Services\MyEdi\MyEdi;
use App\Models\X12Inbound;
use App\Services\Teapplix\TeapplixOrder;
use App\Models\ShipTo;
use App\Models\OrderItem;
use App\Services\Teapplix\AddressValidation;
use App\Services\Jobber\Jobber;
use App\Services\Teapplix\Tracking;
use App\Models\Vendor;

class PurchaseOrder extends Model
{
   /*
     *
    DROP TABLE IF EXISTS `purchase_orders`;
    CREATE TABLE `purchase_orders` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `inbound_id` int(11) NOT NULL,
        `po_number` varchar(80) NOT NULL,
        `po_type` varchar(50) NOT NULL,
        `shipping_via` varchar(40) default 'standard shipping',
        `shipping_date` datetime NOT NULL,
        `shipping_message` varchar(80) NOT NULL,
        `shipped_date` datetime default NULL,
        `tracking_number` varchar(80) default null,
        `carrier_name` varchar(80) default null,
        `date` datetime NOT NULL,
        `order_id` varchar(50) default null,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'id',
        'inbound_id',
        'po_number',
        'po_type',
        'shipping_via',
        'shipping_date',
        'shipping_message',
        'shipped_date',
        'tracking_number',
        'carrier_name',
        'date',
        'order_id'
     
    ];

    public function address(){
        return $this->hasOne(ShipTo::class,'po_id');
    }
    public function items(){
        return $this->hasMany(OrderItem::class,'po_id');
    }

    public function get_orders_to_validate_address($name ='turn5'){
        $orders = DB::table('x12_inbounds')
        ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
        ->select('x12_inbounds.status','purchase_orders.*')
        ->where('x12_inbounds.status','new')
        ->where('purchase_orders.po_type','DROP SHIP')
        ->where('x12_inbounds.vendor',$name)
        ->whereNull('purchase_orders.order_id')
        ->get();
        return $orders;
    }

    public function get_orders_to_check_shipping(){

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','PO Ack')
                    ->where('purchase_orders.po_type','DROP SHIP')
                    ->whereNotNull('purchase_orders.order_id')
                    ->get();
        return $orders;
    }

    public function autoany_get_orders_to_check_shipping(){

        $name = 'autoany';

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','Ordered')
                    ->where('x12_inbounds.vendor',$name)
                    ->whereNotNull('purchase_orders.order_id')
                    ->get();
        return $orders;
    }

    public function get_orders_for_shipment_notice($name = 'turn5'){

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','Shipped')
                    ->where('x12_inbounds.vendor',$name)
                    ->where('x12_inbounds.intransit',1)
                    ->whereNotNull('purchase_orders.tracking_number')
                    ->get();
        return $orders;
    }

    public function get_orders_for_invoice_notice($name = 'turn5'){

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','Shipment Notice')
                    ->where('x12_inbounds.vendor',$name)
                    ->whereNotNull('purchase_orders.order_id')
                    ->orderBy('shipping_date', 'asc')
                    ->get();
        return $orders;
    }

    public function get_orders_bulk_invoice($ids){

        $name = 'turn5';

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.vendor',$name)
                    ->whereIn('purchase_orders.inbound_id', $ids)
                    ->get();
        return $orders;
    }

    public function get_ordered($name = 'turn5'){

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','Ordered')
                    ->where('x12_inbounds.vendor',$name)
                    ->whereNotNull('purchase_orders.order_id')
                    ->get();
        return $orders;
    }

    public function validate_address($id, $provider ="TEAPPLIX"){

        $vaddr = new AddressValidation();
        $success = 0;
        $message = '';
        $rdata = null;
        $original_address = null;

        try{

            $address = PurchaseOrder::find($id)->address;
            $resp = $vaddr->check_address($address, $provider);
        
        }catch(\Exception $e){

            throw  new \Exception('check valid address failed');

        }
        if($resp['success']){
            $r = $resp['data'];
            if(isset($r['Message']) && $r['Message'] == 'Address not found'){

                $message =  $r['Message'];
                
            }else{
                if(isset($r['Status']) && $r['Status'] == 'Validated Not Changed'){
                    $message =  $r['Status'];
                   // $rdata = 
                    $success = 1;
                    $original_address = $r['Address'];


                }else if(isset($r['Status']) && $r['Status'] == 'Validated Changed'){
                    $message =  $r['Status'];
                    $rdata = $r['Address'];
                    $original_address = $address->toArray();

                }else if(isset($r['Status']) && $r['Status'] == 'House number is invalid'){
                    $message =  $r['Status'];
                    $rdata = $r['Address'];
                    $original_address = $address->toArray();

                }else{
                    $message = 'no result';
                    $rdata = $r['Address'];
                }
            }

        }else{
            $rdata = $resp;
        }
        return array('success'=>$success, 'response'=> $message, 'address'=>$original_address, 'change'=>$rdata);

       
    }

    public function update_order_id($inbound_id, $order_id){

        $r = PurchaseOrder::where('inbound_id', $inbound_id)->first();
        if($r){
            $r->order_id = $order_id;
            $r->save();
            return $r;

        }
        return false;

    }

    public function orders($params){

        if($params['search']['value']!=''){
            $search = $params['search']['value'];
            return DB::connection('mysql')->table('purchase_orders')
        
                    ->leftJoin('x12_inbounds','purchase_orders.inbound_id', '=','x12_inbounds.id')
                    ->leftJoin('ship_tos','purchase_orders.id', '=','ship_tos.po_id')
                    ->select('purchase_orders.id','x12_inbounds.vendor','x12_inbounds.edi_code','purchase_orders.po_type','purchase_orders.po_number','shipping_via',
                        DB::raw("DATE_FORMAT(x12_inbounds.date, '%m-%d-%Y') as date"),
                        'ship_tos.name','ship_tos.street','ship_tos.city','ship_tos.state','ship_tos.country','ship_tos.zip',
                        'x12_inbounds.status',DB::raw("DATE_FORMAT(purchase_orders.shipping_date, '%m-%d-%Y') as shipping_date"),
                        'purchase_orders.shipping_message','purchase_orders.inbound_id',
                        DB::raw("DATE_FORMAT(purchase_orders.shipped_date, '%m-%d-%Y') as shipped_date"),'purchase_orders.tracking_number',
                        'purchase_orders.order_id','ship_tos.street2','ship_tos.phone')
                    ->whereRaw("purchase_orders.po_number REGEXP '^$search'")
                    ->orWhere(function($query) use ($search){
                        $query->whereRaw("ship_tos.name REGEXP '^$search'");
                    })
                    ->orWhere(function($query) use ($search){
                        $query->whereRaw("purchase_orders.tracking_number REGEXP '^$search'");
                    })
                    ->orWhere(function($query) use ($search){
                        $query->whereRaw("purchase_orders.order_id REGEXP '^$search'");
                    })
                        
                    ->orderBy('purchase_orders.created_at','desc')
                    ->get()->toArray();
        }else{

            
            $status = $params['status_filter'];
            if($status =='all'){
                return DB::connection('mysql')->table('purchase_orders')
                ->leftJoin('x12_inbounds','purchase_orders.inbound_id', '=','x12_inbounds.id')
                ->leftJoin('ship_tos','purchase_orders.id', '=','ship_tos.po_id')
                ->select('purchase_orders.id','x12_inbounds.vendor','x12_inbounds.edi_code','purchase_orders.po_type','purchase_orders.po_number','shipping_via',
                    DB::raw("DATE_FORMAT(x12_inbounds.date, '%m-%d-%Y') as date"),
                    'ship_tos.name','ship_tos.street','ship_tos.city','ship_tos.state','ship_tos.country','ship_tos.zip',
                    'x12_inbounds.status',DB::raw("DATE_FORMAT(purchase_orders.shipping_date, '%m-%d-%Y') as shipping_date"),
                    'purchase_orders.shipping_message','purchase_orders.inbound_id',
                    DB::raw("DATE_FORMAT(purchase_orders.shipped_date, '%m-%d-%Y') as shipped_date"),'purchase_orders.tracking_number',
                    'purchase_orders.order_id','ship_tos.street2','ship_tos.phone')
                ->orderBy('purchase_orders.created_at','desc')
                ->where('x12_inbounds.status','!=','Invoice Notice DI')
                ->where('x12_inbounds.status','!=','PO Reject')
                ->get()->toArray();

            }else{
                return DB::connection('mysql')->table('purchase_orders')
                ->leftJoin('x12_inbounds','purchase_orders.inbound_id', '=','x12_inbounds.id')
                ->leftJoin('ship_tos','purchase_orders.id', '=','ship_tos.po_id')
                ->select('purchase_orders.id','x12_inbounds.vendor','x12_inbounds.edi_code','purchase_orders.po_type','purchase_orders.po_number','shipping_via',
                    DB::raw("DATE_FORMAT(x12_inbounds.date, '%m-%d-%Y') as date"),
                    'ship_tos.name','ship_tos.street','ship_tos.city','ship_tos.state','ship_tos.country','ship_tos.zip',
                    'x12_inbounds.status',DB::raw("DATE_FORMAT(purchase_orders.shipping_date, '%m-%d-%Y') as shipping_date"),
                    'purchase_orders.shipping_message','purchase_orders.inbound_id',
                    DB::raw("DATE_FORMAT(purchase_orders.shipped_date, '%m-%d-%Y') as shipped_date"),'purchase_orders.tracking_number',
                    'purchase_orders.order_id','ship_tos.street2','ship_tos.phone')
                ->orderBy('purchase_orders.created_at','desc')
                ->where('x12_inbounds.status',$status)
                ->get()->toArray();

            }
        }
    }

    public function discount_total($items){
        $total = 0;
        if($items){
            foreach($items as $item){
                $total += $item['discount_price'] * $item['quantity'];
            }

        }
        return $total;

    }

    public function discount_total_obj($items){
        $total = 0;
        if($items){

            foreach($items as $item){

                if($item->actual_quantity != 0){
                    $quantity = $item->actual_quantity;
    
                }else{
                    $quantity = $item->quantity;
                }

                $total += $item->discount_price * $quantity;
            }

        }

        return $total;

    }

    public function inbound_raw_to_order($inbound_id = 1){
        
       
        $edi = app(MyEdi::class);
        $order = $edi->x12_text_order($inbound_id);

        
        if($order['success']){
            $order_data = $order['data'];

            $tea = app(TeapplixOrder::class);

            $items = $tea->add_skus($order_data);

            
            $discount_total = $this->discount_total($items);
            if( $discount_total){

                $total =  $discount_total;
            }else{
                $total = $order_data['total'];

            }

            $ship_fee_enable = false;
            foreach($items as $e){
                if($e['shipping'] ==5){
                    $ship_fee_enable = true;
                }

            }

            $ship_fee = ($ship_fee_enable )?floatval(5.00):0;
            if($total < 40){
                $ship_fee = 0;

            }else{
                $ship_fee = 5.00;
            }
            $total_data = array(
    
                'shipping' => $ship_fee,
                'total' =>  $total+ $ship_fee
               
            );
        
            $order_totals = $tea->order_totals($total_data);
        
            $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        
        
            $date = $dt->format('Y-m-d');
            $detail_data = array(
        
                'Invoice' => '',
                'payment_date' =>  $date,
                'Memo' => $order_data['memo'],
                'PrivateMemo' => '',
                'WarehouseId' => '',
                'WarehouseName' => '',
                'QueueId' => '',
                'ShipClass' => '',
                'FirstName' => '',
                'LastName' => '',
                'Custom' => '',
                'Custom2' => ''
               
              
            );
            $order_details = $tea->order_details($detail_data);

            $vendor = Vendor::where('nick_name',$order_data['vendor'])->firstorFail();
            if($vendor){
                if($order_data['vendor'] =='turn5'){
                    $order_id = strval($vendor->order_id_gen+1);
                }else{
                    $order_id = 'aa'.strval($vendor->order_id_gen+1);
                }
               
                $vendor->order_id_gen +=1;
                $vendor->save();
                $vendor_name = $vendor->name;
              

            }else{
                $order_id = $order_data['vendor'].'-'.$order_data['po_number'];
                $vendor_name = $order_data['vendor'];
            }
            $tea_data = array(
                    'order_items' => $tea->order_items($items),
                    'to' => $tea->address($order_data['address'], $vendor_name),
                    'txnid' => strval($order_id),
                    'payment_status' => 'Completed',
                    'order_totals' => $order_totals,
                    'order_details' => $order_details
    
            );
        
            $order = $tea->format_order($tea_data, $order_data['vendor']);
            return $order;
        }
        return false;
    
    }

    public function address_to_teapplix($ship_to){

        if($ship_to){

            $data = $ship_to;
            $country_code = 'US';
            if($data->country_code){
                $country_code = $data->country_code;

            }else{
                if(strtolower($data->country) =='united states'){
                    $country_code = 'US';
                }
            }

            return array(
                'Name' =>$data->name,
                'Company' => $data->company,
                'Street' => $data->street,
                'Street2' => $data->street2,
                'State' => $data->state,
                'City' => $data->city,
                'ZipCode' => $data->zip,
                'Country' => $data->country,
                'CountryCode' => $country_code,
                'PhoneNumber' => $data->phone,
                'Email' => ''
            );

        }
        return false;
    }

    public function item_to_teapplix($order_item){

        if($order_item){

            $data = $order_item;
            if($data->actual_quantity !=0){
                $quantity = $data->actual_quantity;
            }else{
                $quantity = $data->quantity;
            }

            return array(
                'Name' => $data->sku,
                'ItemId' => $data->part_number,
                'ItemSKU' => $data->sku,
                'ItemLocation' => '',
                'Description' => $data->description,
                'Quantity' => $quantity,
                'Amount' => $data->discount_price,
                'Shipping' => 0.00, 
                'Tax' => 0.00,
                'Shipping Tax' => 0.00
            );
        }else{
            return false;
        }
    }

    public function items_to_teapplix($orders){
        if($orders){
            $items = null;
            foreach($orders as $o){
                $items[] = $this->item_to_teapplix($o);

            }
            return $items;
        }
        return false;
    }

    public function order_shipment($data = null){

        switch($data['method']){
            case 'STANDARD_OVERNIGHT':
                $s='FEDEX_STANDARD_OVERNIGHT';
                break;
            case 'FEDEX_2_DAY':
                $s='FEDEX_2_DAY';
                break;
            default:
                $s='FEDEX_GROUND';
        }
        return array(

            'Package' => array(
                'Method'=>$s,
            ),
        );

    }

    public function purchase_order_to_order_inbound($inbound_id, $debug = 0){


        $success = 0;
        $message = '';
        $rdata = null;
        $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();
        if($po){
            $tea = app(TeapplixOrder::class);
            $edi = app(MyEdi::class);
            $po_id = $po->id;
            $po_number = $po->po_number;
            $memo ='';
            $site ='';
            $company = '';
            $shipping_method = $po->shipping_via;

            try{

                $inbound = X12Inbound::where('id',$inbound_id)->firstorFail();
                $vendor_nickname = $inbound->vendor;

                $x12 = $edi->parse_raw($inbound->raw);
                $edi->process->set_x12($x12);
                $addr = $edi->process->po_n1_group();
                $alladdr = $edi->translate->po_to_address($addr);

                $site = '';
                if(isset($alladdr['store'])){
                    $site = $alladdr['store'];
                }


                $ship_to = Shipto::where('po_id', $po_id)->firstorFail();
                $items =  OrderItem::where('po_id', $po_id)->get();

                $discount_total = $this->discount_total_obj($items);
                $total = $discount_total;


                $vendor = Vendor::where('nick_name', $vendor_nickname)->firstorFail();


                $queue_id = 0;

                $on_site = NULL;

                if($vendor){
                
                    $dropship_fee = $vendor->dropship_fee;
                    if($vendor->teapplix_queue_id){

                        $queue_id = $vendor->teapplix_queue_id;

                    }

                    $on_site = $vendor->on_site;
            
                }else{
                  
                    $dropship_fee = 10.00;
                }

                if($debug){
                    $queue_id = 10;
                }
    
                $ship_fee = 0;

                if($total < 40){
                    $ship_fee = 0;
    
                }else{
                    $ship_fee = $dropship_fee;
                }
                $total_data = array(
        
                    'shipping' => $ship_fee,
                    'total' =>  $total+ $ship_fee
                   
                );
            
                $order_totals = $tea->order_totals($total_data);
            
                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Y-m-d');

                $memo .= "\r\nTransaction: Net30 \r\n";
               
                if($on_site){
                    $memo .= "Site:".$on_site." \r\n";
                }else{
                    $memo .= "Site:".$site." \r\n";
                }

                $memo .= "Purchase Order:".$po_number." \r\n";

                $memo .= "Shipping:".$shipping_method." \r\n";
                $detail_data = array(
            
                    'Invoice' => '',
                    'payment_date' =>  $date,
                    'Memo' => $memo,
                    'PrivateMemo' => '',
                    'WarehouseId' => '',
                    'WarehouseName' => '',
                    'QueueId' => $queue_id,
                    'ShipClass' => '',
                    'FirstName' => '',
                    'LastName' => '',
                    'Custom' => '',
                    'Custom2' => ''
                   
                );
                $order_details = $tea->order_details($detail_data);


                if($debug){

                    $order_id = 'test_1'.$vendor_nickname.'-'.$po_number;
                    $vendor_name = $vendor_nickname;

                }else{

                    $vendor = Vendor::where('nick_name',$vendor_nickname)->firstorFail();
                    if($vendor){
                        $company = $vendor->name;
                        $ship_to->company = $company;
                        if($vendor_nickname =='turn5'){
                            $order_id = strval($vendor->order_id_gen+1);
                        }else{
                            $order_id = 'aa'.strval($vendor->order_id_gen+1);
                        }
                        $vendor->order_id_gen +=1;
                        $vendor->save();
                        $vendor_name = $vendor->name;
                    
        
                    }else{
                        $order_id = $vendor_nickname.'-'.$po_number;
                        $vendor_name = $vendor_nickname;
                    }

                }


                if($vendor_nickname =='turn5'){
                    $tea_data = array(
                        'order_items' => $this->items_to_teapplix($items),
                        'to' => $this->address_to_teapplix($ship_to, $vendor_name),
                        'txnid' => strval($order_id),
                        'payment_status' => 'Completed',
                        'order_totals' => $order_totals,
                        'order_details' => $order_details,
                      
                        
                    );
                }else{
                    $tea_data = array(
                        'order_items' => $this->items_to_teapplix($items),
                        'to' => $this->address_to_teapplix($ship_to, $vendor_name),
                        'txnid' => strval($order_id),
                        'payment_status' => 'Completed',
                        'order_totals' => $order_totals,
                        'order_details' => $order_details,
                        'shipping_details' => $this->order_shipment(array('method'=>$shipping_method))
                        
                    );
                }
    
                
                
        
            $order = $tea->format_order($tea_data, $vendor_nickname);
            return $order;

            }catch(\Excceptions $e){
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);
            }

            $success =1;

            $rdata =  array(
                    'purchase_order' =>$po,
                    'ship_to' => $ship_to,
                    'items' => $items
                );

        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }
    public function purchase_order_to_order($id){

        $po = PurchaseOrder::where('id', $id)->first();
        if($po){

            return array(
                'purchase_order' =>$po,
                'ship_to' => Shipto::where('po_id', $id)->first(),
                'items' => OrderItem::where('po_id', $id)->get()
            );

        }
        return false;

    }

    public function weight($po_id){
        $items = OrderItem::where('po_id',$po_id)->get();
        $weight = 0;
        $unit = 'LB';
        $quantity = 1;
        if($items){
            foreach($items as $i){

                if($i->actual_quantity == 0){
                    $quantity = $i->quantity;
                }else{
                    $quantity = $i->actual_quantity;
                }
                $weight +=$quantity * $i->weight;
                $unit = $i->weight_unit;
            }
        }
        return array('weight' => round($weight,2), 'weight_unit'=>$unit);

    }

    public function  create_teapplix_order($inbound_id, $debug = 0){

        $success = 0;
        $message = '';
        $rdata = null;

        $po = new PurchaseOrder();
        $tea = app(TeapplixOrder::class);

        try{
            $order = $po->purchase_order_to_order_inbound($inbound_id);
           
            $tea_data = array(
                'Operation'=>'Submit',
                'Orders' => [$order]
            );

            if($debug){
                $rdata = $tea_data;
                $message = 'debug, teapplix order formated..';
                return array('success'=>1, 'response'=>$message, 'data'=>$rdata);
            }

            $result = $tea->create_order($tea_data);

            if($result['success']){

                $r = $po->update_order_id($inbound_id, $result['data']['TxnId']);
                if($r){
                    $message = 'teapplix order created and orderid  saved..<br/>';
    
                    $in = X12Inbound::where('id', $inbound_id)->first();
                    $in->status = 'Ordered';
                    $in->save();

                    $success = 1;
    
                }else{
                    $message = 'orderid not saved..<br/>';
                    $message .= $result['response'];
                }
    
            }else{
                $message = "failed to create order <br/>";
                $message .= $result['response'];
                
            }

        }catch(\Exception $e){
            $message =  'Caught exception: '.  $e->getMessage(). "\n";
            return array('success'=>0, 'response'=>$message, 'data'=>$rdata);

        }

        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }


    public function check_shipping_status($txnid, $debug = 0){

        $success = 0;
        $message ='';
        $rdata = null;
        $tea = app(TeapplixOrder::class);
        $tr = $tea->order($txnid);

        if($tr){
            $shipping = $tr[0]->ShippingDetails;
            $package = $shipping[0]->Package;

            if( $shipping[0]->ShipDate){
                $message = "Order shipped:";
                $message .= "Shipping date:". $shipping[0]->ShipDate.':';
                $r = PurchaseOrder::where('order_id', $txnid)->firstorFail();

                if($r){
                    $r->shipped_date = $shipping[0]->ShipDate;
                    $r->tracking_number = $package->TrackingInfo->TrackingNumber;
                    $r->carrier_name = $package->TrackingInfo->CarrierName;

                    $r->save();
                    $message =  "OrderID: $txnid, Updated shipped date: $r->shipped_date  and tracking info: $r->tracking_number <br/>";

                    $in =X12Inbound::where('id', $r->inbound_id)->firstorFail();
                    if($in){
                        $in->status = "Shipped";
                        $in->save();
                        $message .= "Status: updated to Shipped<br/>";
                        $success = 1;
                    }else{
                        $message .=  'failed to update inbound status';
                    }

                }else{
                    $message .=  'fail to get purchase order.';
                }
            }else{
                $message .=  'order has not been shipped yet.';
            }
        }else{
            $message =  'no orders found..';
        }

        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }

    public function check_oos($po_id){

        $j = app(Jobber::class);
        $items = OrderItem::where('po_id', $po_id)->get();
        $oos = 0;
        $message = '';
        if($items){
            foreach($items as $i){

                $params = array(
                    'pn' => $i->part_number,
                    'qty' =>$i->quantity
            
                );
                $r = $j->check_oos($params);
                if($r->success){
                    $oos = 1;
                    $i->status = 'oos';
                    $i->save();
                    $message .= "$i->sku is out of stock ($i->part_number) ";

                }else{
                    $message .= "$i->sku is $r->response";

                }

            }

        }else{
            $message = 'no items found..';
        }

        return array('oos'=> $oos, 'response'=>$message);
    }

    public function items_by_inbound($inbound_id){

        $po = PurchaseOrder::where('inbound_id', $inbound_id)->first();
     
        if($po){

            $items = OrderItem::where('po_id',$po->id)->get();


            if($items){
                return $items;
            }
            return false;

        }
        return false;


    }

    public function check_tracking($id){

        $po = PurchaseOrder::where('id', $id)->first();
        if($po){

            $t = New Tracking();

            $tracking_info = array(
                'carrier' => $po->carrier_name,
                'tracking_number'=>$po->tracking_number,
                'po_id' => $id
            );

         
            $tr = $t->check_intransit_status($tracking_info);
            
            return $tr;
        }
        return false;

    }

    public function check_tracking_update($id){

        $message = '';
        $tr = null;

        $po = PurchaseOrder::where('id', $id)->first();
        if($po){

            $t = New Tracking();

            $tracking_info = array(
                'carrier' => $po->carrier_name,
                'tracking_number'=>$po->tracking_number,
                'po_id' => $id
            );

            $tr = $t->check_intransit_for_debit_invoice($tracking_info);
            if($tr){

                $in = app(X12Inbound::class);
                $in->update_status(array('po_id'=>$tracking_info['po_id'], 'status'=> 'Shipment Notice'));
                $message = "$id, status change back to shipment notice for invoice to be sent..";

            }else{
                $message = "$id, status not updated..";
            }
            
        }

        return array('response'=>$message, 'data'=>$tr);

    }

    public function get_orders_for_transit(){

        $orders = DB::table('x12_inbounds')
                    ->join('purchase_orders','x12_inbounds.id','purchase_orders.inbound_id')
                    ->select('x12_inbounds.status','purchase_orders.*')
                    ->where('x12_inbounds.status','Shipped')
                    ->where('x12_inbounds.intransit',0)
                    ->whereNotNull('purchase_orders.tracking_number')
                    ->get();
        return $orders;
    }

    public function check_tracking_test($id){

        $po = PurchaseOrder::where('id', $id)->first();
        if($po){

            $t = New Tracking();

            $tracking_info = array(
                'carrier' => $po->carrier_name,
                'tracking_number'=>$po->tracking_number
            );
            $tr = $t->check_intransit_status($tracking_info);

        }
        return false;


    }


}
