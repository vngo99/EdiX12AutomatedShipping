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

class X12Outbound extends Model
{

    /*
     *
    DROP TABLE IF EXISTS `x12_outbounds`;
    CREATE TABLE `x12_outbounds` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id`  int(11) NOT NULL,
        `raw` text NOT NULL,
        `edi_code` varchar(80) NOT NULL,
        `file_name` text NOT NULL,
        `date` datetime NOT NULL,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'po_id',
        'raw',
        'edi_code',
        'date',
        'file_name'
    ];

    public function text_to_x12($po_id){

        $edi = app(MyEdi::class);
        $in = X12Outbound::where('id', $po_id)->first();

        if($in){

            $x12 = $edi->parse_raw($in->raw);
            return $x12;

        }
        return false;
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

                    try{

                        $status_of = $edi->process->status_of();
                        $status_for = $edi->process->status_for();
                        $trans_status = $edi->process->trans_status();
                        $funct_status = $edi->process->funct_status();
                       
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