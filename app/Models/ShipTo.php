<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipTo extends Model
{
   /*
     *
    DROP TABLE IF EXISTS `ship_tos`;
    CREATE TABLE `ship_tos` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `po_id` int(11) NOT NULL,
        `name` varchar(80) NOT NULL,
        `street` varchar(80) NOT NULL,
        `street2` varchar(80) default NULL,
        `city` varchar(50) NOT NULL,
        `state` varchar(50) NOT NULL,
        `country` varchar(50) default 'united states',
        `country_code` varchar(3) default null,
         `zip` varchar(20) default null,
        `phone` varchar(50) default NULL,
        `email` varchar(50) default NULL,
        `updated_at` datetime NOT NULL,
        `created_at` datetime NOT NULL,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
    */

    protected $connection = 'mysql';
    use HasFactory;
    protected $fillable = [
        'po_id',
        'name',
        'street',
        'street2',
        'city',
        'state',
        'country',
        'phone',
        'email',
        'zip'
     
    ];

    public function format($data){

        if(isset($data['to'])){

            $street2 = '';
            if(isset($data['to']['street2'])){
                $street2 = $data['to']['street2'];
            }
            $phone = '';
            if(isset($data['to']['phone'])){
                $phone = $data['to']['phone'];
            }
            $email = '';
            if(isset($data['to']['email'])){
                $email = $data['to']['email'];
            }
            return array(
                'po_id' => $data['po_id'],
                'name' => $data['to']['name'],
                'street' => $data['to']['street'],
                'street2' => $street2,
                'city' => $data['to']['city'],
                'state' => $data['to']['state'],
                'phone' => $phone,
                'email' => $email,
                'country' => $data['to']['country'],
                'zip' => $data['to']['zip'],
                'country_code' => $data['to']['country_code']
            );
        }
        return false;
        
    }

    public function add($shipping){

        $r = Shipto::where('po_id', $shipping['po_id'])->first();
        if($r == null){
            return Shipto::firstOrCreate($shipping);
        }
        return false;

    }
}
