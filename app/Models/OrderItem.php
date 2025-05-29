<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Services\Teapplix\TeapplixOrder;
use App\Services\Jobber\Jobber;
use App\Models\Vendor;

class OrderItem extends Model
{
    /*
     *
    DROP TABLE IF EXISTS `order_items`;
    CREATE TABLE `order_items` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id` int(11) NOT NULL,
        `part_number` varchar(80) NOT NULL,
        `sku` varchar(80) NOT NULL,
        `local_sku` varchar(80) default NULL,
        `gtin` varchar(20) default null,
        `weight`decimal(5,1) default null,
        `weight_unit` varchar(4) default 'lb',
        `price` decimal(5,1) default null,
        `discount_price` decimal(5,1) default null,
        `quantity` int(3) default 1,
        `description` text default null,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'actual_quantity',
        'po_id',
        'part_number',
        'sku',
        'local_sku',
        'price',
        'discount_price',
        'quantity',
        'description',
        'gtin',
        'weight',
        'weight_unit',
        'status',
        'eta'
     
    ];

    public function format($datas, $po_id){

        if($datas){
            $result = null;
            foreach($datas as $data){
                $result[] = array(
                    'po_id' => $po_id,
                    'part_number' => $data['vendor_part_number'],
                    'sku' => '',
                    'local_sku' => null,
                    'price' => $data['price'],
                    'discount_price' => 0,
                    'quantity' => $data['quantity'],
                    'description' => $data['description']
                );

            }
            return $result;

        }
        return false;
    }

    public function add($item){

        $r = OrderItem::where('po_id', $item['po_id'])->where('part_number', $item['part_number'])->first();
        if($r == null){
            return OrderItem::firstOrCreate($item);
        }
        return false;

    }

    public function check_jobber($data = null, $vendor){

        $v = Vendor::where('nick_name', $vendor)->firstorFail();
       
        if($v){
            $j = app(Jobber::class);
            $params = array(
                'type' =>'mfsku',
                'pn' => $data['part_number']
        
            );

            $rs = $j->to_sku($params);
            if($rs->success){


                $new_price = $rs->data->retail * (1-($v->discount_per)/100);
                $new_price = $new_price * (1-($v->marketing_discount_per)/100);
                $data->sku =$rs->data->mfsku;
                $data->discount_price = number_format($new_price, 2, '.', '');
                $data->gtin = $rs->data->gtin;
                $data->weight = $rs->data->weight;
                $data->description = $rs->data->order_description;
            }else{
                $data->sku =' ';
                $data->discount_price= 0;
               
            }
            $data->save();
            return $data;

        }
        $data->sku =' ';
        $data->discount_price= 0;
        $data->save();
        return $data;


    }

    public function order_total($po_id){

        $items = OrderItem::where('po_id',$po_id)->get();
        
        $price = 0;
        $discount_price = 0;
        $quantity =1;
        if($items){
            foreach($items as $i){
                if($i->actual_quantity == 0){
                    $quantity = $i->quantity;
                }else{
                    $quantity = $i->actual_quantity;
                }
                $price +=$i->price * $quantity;
                $discount_price += $i->discount_price *$quantity;
            }
        }
        return array('price' => number_format($price, 2, '.', ''), 'discount_price'=>number_format($discount_price, 2, '.', ''));
    }

    public function fix_price($vendor){

        $items = OrderItem::all();
        $price = 0;
        $discount_price = 0;
        $v = Vendor::where('nick_name', $vendor)->firstorFail();
        $j = app(Jobber::class);
        if($items){
            foreach($items as $i){

                $params = array(
                    'type' =>'mfsku',
                    'pn' => $i->part_number
            
                );

                $rs = $j->to_sku($params);

                if($rs->success){

                    $old =  $i->discount_price;
                    $new_price = $rs->data->retail * (1-($v->discount_per)/100);
                    $new_price = $new_price * (1-($v->marketing_discount_per)/100);
        
                    $i->discount_price =  $new_price = number_format($new_price, 2, '.', '');
                    $i->save();

                   echo "$old updated to $new_price<br/>";
                   
                }else{
                    
                    echo 'not updated<br/>';
                   
                }
            }
        }
       
    }

    public function fix_sku($po_id){

        $items = OrderItem::where('po_id',$po_id)->get();
        
        if($items){
            $j = app(Jobber::class);
            foreach($items as $i){

                $params = array(
                    'type' =>'mfsku',
                    'pn' => $i->part_number
            
                );
    
                $rs = $j->to_sku($params);
                if($rs->success){
                    $sku = $i->sku =$rs->data->mfsku;
                }else{
                   $sku =  $i->sku =' ';
                   
                }
                $i->save();
                echo "$sku, updated <br/>";
                
            }
        }else{
            echo 'no sku to fix..';
        }

    }

   
}
