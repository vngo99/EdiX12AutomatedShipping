<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use App\Services\MyEdi\MyEdi;
use App\Services\MyEdi\ProcessSegment;
use App\Services\MyEdi\EdiTranslate;

use App\Models\PurchaseOrder;
use App\Models\OrderItems;
use App\Models\ShipTo;

class X12Inbound extends Model
{

    /*
     *
    DROP TABLE IF EXISTS `x12_inbounds`;
    CREATE TABLE `x12_inbounds` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id` int(11) Default NULL 
        `vendor` varchar(40) NOT NULL,
        `raw` text NOT NULL,
        `po_number` varchar(80) NOT NULL,
        `edi_code` varchar(80) NOT NULL,
        `status` varchar(40) default 'new',
        `date` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

    ALTER TABLE `edi_db`.`x12_inbounds` 
ADD COLUMN `file_name` TEXT NULL DEFAULT NULL AFTER `created_at`;

ALTER TABLE `edi_db`.`x12_inbounds` 
ADD COLUMN `intransit` TINYINT(4) NULL DEFAULT 0 AFTER `po_id`;


    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'vendor',
        'raw',
        'po_number',
        'edi_code',
        'status',
        'date',
        'file_name',
        'po_id',
        'intransit'
       
     
    ];
    

    public function update_status($info){

        $id = $info['po_id'];
        $in = X12Inbound::where('po_id', $id)->first();

        if($in){

            $in->status = $info['status'];
            $in->save();
            return true;

        }
        return false;

    }

    public function get_for_translate($vendor='turn5'){

        return X12Inbound::where('status', 'new')->where('vendor',$vendor)->where('edi_code','850')->whereNull('po_id')->get();
    }

    public function text_to_x12($id){

        $edi = app(MyEdi::class);
        $in = X12Inbound::where('id', $id)->first();

        if($in){

            $x12 = $edi->parse_raw($in->raw);
            return $x12;

        }
        return false;
    }

    public function text_purchase_order_get($id){

        $edi = app(MyEdi::class);
        $in = X12Inbound::where('id', $id)->first();

        if($in){
            $x12 = $edi->parse_raw($in->raw);
            if($x12){

                if($edi->process->set_x12($x12)){
                    $po = $edi->process->purchase_order_number();
                    $shipping = $edi->process->po_shipping_info();
                    $date =$edi->process->po_shipping_time();

                    $purchase_order = array(
                        'po_number' => $po,
                        'inbound_id' => $id,
                        'shipping_via' => $shipping,
                        'date' => $date['date']
                    );

                    $po_result = PurchaseOrder::firstOrCreate($purchase_order);
                   
                    if($po_result){
                        $po_items = $edi->process->po_items_with_name();
                        $order_items= $edi->translate->po_to_items($po_items);

                        $ot = new OrderItem();
                        $items = $ot->format($order_items, $po_result->id);

                        if($items){
                            foreach($items as $i){
                                OrderItem::firstOrCreate($i);
                            }

                        }
                        
                        $addr = $edi->process->po_n1_group();
                        $alladdr = $edi->translate->po_to_address($addr);

                        $shipto = new ShipTo();
                        $alladdr['po_id']= $po_result->id;
                        $shipping = $shipto->format($alladdr);
                      
                        $st_result = ShipTo::firstOrCreate($shipping);
                    
                        return true;

                    }
                    return false;
    
                }
                return false;
            }
        }
        return false;
    }


    public function text_purchase_order_get_test($id){

        $success = 0;
        $message = '';
        $rdata = null;

        $in = X12Inbound::where('id', $id)->first();
        if($in){
           return $this->to_purchase_order($in,1);
        }else{
            return array('success'=>$success, 'response' => $message, 'data' => $rdata);
        }
       
    }

    public function to_purchase_order($in, $debug = 0){
        $success = 0;
        $message = '';
        $edi = app(MyEdi::class);
        $rdata = null;

        if($in->raw){
         
            $x12 = $edi->parse_raw($in->raw);

            //print_r($x12);
            //die();

            if($x12){

                if($edi->process->set_x12($x12)){

                    try{

                        $po = $edi->process->purchase_order_number();
                        $shipping = $edi->process->po_shipping_info();
                        $date = $edi->process->po_shipping_time();
                        $po_type = $edi->process->purchase_order_type();
                        $shipping_time_message = $edi->process->po_shipping_time_message();
    
                        $purchase_order = array(
                            'inbound_id' => $in->id,
                            'po_number' => $po,
                            'po_type' => $po_type,
                            'shipping_via' => $shipping,
                            'shipping_date' => $shipping_time_message['date'],
                            'shipping_message' => $shipping_time_message['message'],
                            'date' => date('Y-m-d',strtotime($date))
                        );

                        if($debug){
                            echo "purchaseorder:";
                            print_r($purchase_order);
                            //die();
                        }

                        $po_result = PurchaseOrder::firstOrCreate($purchase_order);
                    
                        if($po_result){
                            $rdata =$po_result->id;
                            $po_items = $edi->process->po_items_with_name();

                            if($debug){
                                echo "items:";
                                print_r($po_items);
                                //die();
                            }

                            $order_items= $edi->translate->po_to_items($po_items);

                            if($debug){
                                echo "orderItems:";
                                print_r($order_items);
                                //die();
                            }

                            if($order_items){

                                $ot = new OrderItem();
                                $items = $ot->format($order_items, $po_result->id);
        
                                if($items){
                                    $order_item = new OrderItem();
                                    foreach($items as $i){
                                        $order_item->add($i);
                                    }
        
                                }else{
                                    $message .= 'no items..';
                                }
                                

                            }else{
                                $message .= '.no order items.';
                            }

                            $addr = $edi->process->po_n1_group();

                           

                            if($addr){

                            
                                $alladdr = $edi->translate->po_to_address($addr);

                                $shipto = new ShipTo();
                                $alladdr['po_id']= $po_result->id;
        
                                $shipping = $shipto->format($alladdr);

                                if($shipping){
                                    $st_result = $shipto->add($shipping);
                                }else{
                                    $message .= 'no shipping.';
                                }

                                $success =1;
                                $message .= '...added..';

                            }else{
                                $message .= '.no addr'; 
                            }

                        }else{
                            $message .= 'fail to create purchase order';
                        }

                    }catch(\Exception $e){
                        $message =  'Caught exception: '.  $e->getMessage(). "\n";
                        return array('success'=>$success, 'response' => $message, 'data'=>$rdata);
                    }
      
                }else{
                    $messsage .= 'fail set x12..';
                }
               
            }else{
                $message .= 'No x12..' ;
            }
        }else{
            $message .= 'No input values..';
        }

        
       return array('success'=>$success, 'response' => $message, 'data' => $rdata);

    }
    
    public function text_purchase_order($in){

        $success = 0;
        $message = '';
        $edi = app(MyEdi::class);
        $rdata = null;

        if($in){
            $x12 = $edi->parse_raw($in->raw);

            if($x12){

                if($edi->process->set_x12($x12)){

                    try{

                        $po = $edi->process->purchase_order_number();
                        $shipping = $edi->process->po_shipping_info();
                        $date = $edi->process->po_shipping_time();
                        $po_type = $edi->process->purchase_order_type();
                        $shipping_time_message = $edi->process->po_shipping_time_message();
    
                        $purchase_order = array(
                            'inbound_id' => $in->id,
                            'po_number' => $po,
                            'po_type' => $po_type,
                            'shipping_via' => $shipping,
                            'shipping_date' => $shipping_time_message['date'],
                            'shipping_message' => $shipping_time_message['message'],
                            'date' => date('Y-m-d',strtotime($date))
                        );
    

                  
                        $po_result = PurchaseOrder::firstOrCreate($purchase_order);
                    
                        if($po_result){
                            $rdata =$po_result->id;
                            $po_items = $edi->process->po_items_with_name();

                            $order_items= $edi->translate->po_to_items($po_items);

                            if($order_items){

                                $ot = new OrderItem();
                                $items = $ot->format($order_items, $po_result->id);
        
                                if($items){
                                    $order_item = new OrderItem();
                                    foreach($items as $i){
                                        $order_item->add($i);
                                    }
        
                                }else{
                                    $message .= 'no items..';
                                }
                                

                            }else{
                                $message .= '.no order items.';
                            }

                            $addr = $edi->process->po_n1_group();

                           

                            if($addr){

                            
                                $alladdr = $edi->translate->po_to_address($addr);

                                $shipto = new ShipTo();
                                $alladdr['po_id']= $po_result->id;
        
                                $shipping = $shipto->format($alladdr);

                                if($shipping){
                                    $st_result = $shipto->add($shipping);
                                }else{
                                    $message .= 'no shipping.';
                                }

                                $success =1;
                                $message .= '...added..';

                            }else{
                                $message .= '.no addr'; 
                            }

                        }else{
                            $message .= 'fail to create purchase order';
                        }

                    }catch(\Exception $e){
                        $message =  'Caught exception: '.  $e->getMessage(). "\n";
                        return array('success'=>$success, 'response' => $message, 'data'=>$rdata);
                    }
      
                }else{
                    $messsage .= 'fail set x12..';
                }
               
            }else{
                $message .= 'No x12..' ;
            }
        }else{
            $message .= 'No input values..';
        }

        
       return array('success'=>$success, 'response' => $message, 'data' => $rdata);


    }

    public function check_status($in){

        $success = 0;
        $message = '';
        $edi = app(MyEdi::class);
        $rdata = null;

        if($in){
            $x12 = $edi->parse_raw($in->raw);
            if($x12){

                if($edi->process->set_x12($x12)){

                    //print_r($x12);

                    try{

                        $status_of = $edi->process->status_of();
                       
                        $status_for = $edi->process->status_for();
                       

                        $trans_status = $edi->process->trans_status();
                      
                        $funct_status = $edi->process->funct_status();
                        
                        $rdata = array(
                            'statusof' => $status_of,
                            'statusfor' => $status_for,
                            'trans_status' => $trans_status,
                            'funct_status' => $funct_status
                        );
                        $success = 1;
                        return array('success'=>$success, 'response' => $message, 'data' => $rdata);

                       
                    }catch(\Exception $e){
                        $message =  'Caught exception: '.  $e->getMessage(). "\n";
                        return array('success'=>$success, 'response' => $message, 'data'=>$rdata);
                    }
      
                }else{
                    $messsage .= 'fail set x12..';
                }
               
            }else{
                $message .= 'No x12..' ;
            }
        }else{
            $message .= 'No input values..';
        }

        
       return array('success'=>$success, 'response' => $message, 'data' => $rdata);


    }
}