<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;


class Vendor extends Model
{
   /*
     *
    DROP TABLE IF EXISTS `vendors`;
    CREATE TABLE `vendors` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(80)  NOT NULL,
        `order_id_gen` varchar(20) default 1,
        `nick_name` varchar(20)  NOT NULL,
        `quantifier` varchar(3) NOT NULL,
        `isa_id` varchar(20)  NOT NULL,
        `gs_id` varchar(20)  NOT NULL,
        `discount_per` decimal(5,1) default 0,
        `marketing_discount_per` decimal(5,1) default 0,
        `edi_updated_at` datetime default NULL,
        `po_updated_at` datetime default NULL,
        `interchange_control_number_gen` varchar(9) default '000000000',
        `functional_control_number_gen` varchar(9) default '000000000',
        `transaction_control_number_gen` varchar(9) default '000000000',
        `order_updated_at` datetime default NULL,
        `ack_updated_at` datetime default NULL,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'name',
        'nick_name',
        'order_id_gen',
        'quantifier',
        'isa_id',
        'gs_id',
        'discount_per',
        'marketing_discount_per',
        'edi_update_at',
        'po_updated_at',
        'order_update_at',
        'ack_update_at',
        'interchange_control_number_gen',
        'functional_control_number_gen',
        'transaction_control_number_gen',
        'dropship_fee',
        'teapplix_queue_id',
        'on_site'
       
     
    ];

    public function init(){
        
        $aap = array(
            'name' => 'Advance Auto Parts',
            'nick_name' =>'aap',
            'quantifier' => '01',
            'isa_id' =>'007941529000000',
            'gs_id' => '007941529000000',
            'discount_per' => 0,
            'marketing_discount_per' => 0,
            'set_purpose_code'=> 0
        );
        $turn5 = array(
            'name' => 'Turn 5, Inc.',
            'nick_name' =>'turn5',
            'quantifier' => 'ZZ',
            'isa_id' =>'DICTURN5',
            'gs_id' => 'DICTURN5',
            'discount_per' => 30,
            'marketing_discount_per' => 1.5,
            'order_id_gen' => '430000',
            'set_purpose_code'=> 1 
        );

        $autoany = array(
            'name' => 'AutoAnything',
            'nick_name' =>'autoany',
            'quantifier' => '12',
            'isa_id' =>'8008748888',
            'gs_id' => '8008748888',
            'discount_per' => 30,
            'marketing_discount_per' => 0,
            'order_id_gen' => '430000',
            'set_purpose_code'=> 0 
        );

        $test = array(
            'name' => 'Test',
            'nick_name' =>'home',
            'quantifier' => 'ZZ',
            'isa_id' =>'ma8883120000000',
            'gs_id' => 'ma8883120000000',
            'discount_per' => 0,
            'marketing_discount_per' => 0,
             'set_purpose_code'=> 0 
        );
        Vendor::firstOrCreate($autoany);
    }

    public function get_vendor($id){

        $r = Vendor::find($id);
        if($r){
            return $r;
        }
        return false;

    }

    public function update_last($id, $field){


        date_default_timezone_set('UTC');
        $d = new \DateTime();
        $current = $d->format('Y-m-d H:i:s'); 

        $r = Vendor::find($id);
        if($r){
            $r->$field = $current;
            $r->save();
            return $r;
        }
        return false;

    }

    
}
