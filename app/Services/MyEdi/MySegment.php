<?php

namespace App\Services\MyEdi;

class MySegment{

    public function __construct(){}
    
    public function test(){
        echo 'test';
        
    }

    public function isa($data = null){

        $def = array(
            'seg' => 'ISA',
            'auth_qualifier'=>'00',
            'security_qualifer'=>'',
            'interchange_id_qualifer'=>'ZZ',
            'interchange_sender_id'=>'DICTURN5',
            'interchange_id_qualifer_receiver'=>'ZZ',
            'interchange_receiver_id'=>'DICTURN599',
            'date' => '210202',
            'time'=>'1015',
            'repetition_separator' =>'U',
            'interchange_control_version_number'=>'00401',
            'interchange_control_number'=>'0000089',
            'ack_requested'=>'0',
            'usage_indicator'=>'P',
            'sub_repetition' =>'>'

        );

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $interchange_control_version_number ='00401';
        $interchange_control_number = '0000089';
        $pos2 ='          ';
        $pos3 ='          ';

        $interchange_control_version_number ='00401';
        $interchange_control_number = '0000089';
        $interchange_id_qualifer_sender =' ZZ';
        $interchange_sender_id ='DICTURN5';
        $interchange_id_qualifer_receiver ='ZZ';
        $interchange_receiver_id ='DICTURN599';

      
        if($data){

            $date = $data['date'];
            $time = $data['time'];
            $interchange_control_version_number = $data['interchange_control_version_number'];
            $interchange_control_number = $data['interchange_control_number'];
            $interchange_id_qualifer_sender = $data['interchange_id_qualifer_sender'];
            $interchange_sender_id = $data['interchange_sender_id'];
            $interchange_id_qualifer_receiver = $data['interchange_id_qualifer_receiver'];
            $interchange_receiver_id = $data['interchange_receiver_id'];
            $pos2 = $data['pos2'];
            $pos3 = $data['pos3'];

        }

        return array(
            0 => 'ISA',
            1 => '00',
            2 => $pos2,
            3 => '00',
            4 => $pos3,
            5 => $interchange_id_qualifer_sender,
            6 => $interchange_sender_id,
            7 => $interchange_id_qualifer_receiver,
            8 => $interchange_receiver_id,
            9 => $date,
            10 => $time,
            11 => 'U',
            12 => $interchange_control_version_number,
            13 => $interchange_control_number,
            14 => 0,
            15 => 'P',
            16 => '>'
        );

    }

    public function gs($data = null){

        $def = array(
            'seg' => 'GS',
            'function_id_code'=>'PO',
            'interchange_sender_id'=>'DICTURN5',
            'interchange_receiver_id'=>'DICTURN599',
            'date' => '210202',
            'time'=>'1015',
            'group_control_number'=>'89',
            'responsible_agency_code'=>'X',
            'version_code'=>'004010',
          

        );

        //PO purchase order
        //PR purchase order ack

        $date = '';
        $time = '';
        $function_id_code  = 'PR';
        $group_control_number ='89';
        $responsible_agency_code = 'X';
        $version_code = '004010';
        $interchange_sender_id ='DICTURN5';
        $interchange_receiver_id ='DICTURN599';

        if($data){
            $date = $data['date'];
            $time = $data['time'];
            $group_control_number =$data['group_control_number'];
            $responsible_agency_code = $data['responsible_agency_code'];
            $version_code = $data['version_code'];
            $function_id_code = $data['function_id_code'];
            $interchange_sender_id = $data['interchange_sender_id'];
            $interchange_receiver_id = $data['interchange_receiver_id'];

        }

        return array(
            0 => 'GS',
            1 => $function_id_code,
            2 => $interchange_sender_id,
            3 => $interchange_receiver_id,
            4 => $date,
            5 => $time,
            6 => $group_control_number,
            7 => $responsible_agency_code,
            8 => $version_code
        );

    }

    public function st($data = null){
        //transaction set header
       
        $code = '855';
        $code_number = '100000799';

        if($data){
            $code = $data['code'];
            $code_number = $data['code_number'];
        }

        return array(
            0 => 'ST',
            1 => $code,
            2 => $code_number
        );

    }

    public function se($data = null){
        
         //transaction set trailer manditory

        $entry = array(
            'seg'=>'SE',
            'number_of_included_segments'=>10,
            'transaction_set_control_number'=>'1234'
            
            
        );

        $number_of_included_segment = 7;
        $transaction_set_control_number = '1234';

        if($data){
            $number_of_included_segment = $data['number_of_included_segment'];
            $transaction_set_control_number = $data['transaction_set_control_number'];
        }

        return array(
            0 => 'SE',
            1 => $number_of_included_segment,
            2 => $transaction_set_control_number
        );
    }

    public function iea($data = null){
        
        $def = array(
            'seg'=>'IEA',
            'number_of_included_functional_groups'=>1,
            'interchange_control_number' => '0001',

            
        );

        $number_of_included_functional_groups = 1;
        $interchange_control_number = '100000799';

        if($data){
            $number_of_included_functional_groups = $data['number_of_included_functional_groups'];
            $interchange_control_number = $data['group_control_number'];
        }


        return array(
            
            0 => 'IEA',
            1 => $number_of_included_functional_groups,
            2 => $interchange_control_number
        
        );

   }

   public function ge($data = null){
        
        $def = array(
            'seg'=>'GE',
            'number_of_transaction_sets_included'=>1,
            'group_control_number' => '0001',

            
        );

        $number_of_transaction_sets_included = 1;
        $group_control_number = '0001';

        if($data){
            $number_of_transaction_sets_included = $data['number_of_transaction_sets_included'];
            $group_control_number = $data['group_control_number'];
        }


        return array(
            
            0 => 'GE',
            1 => $number_of_transaction_sets_included,
            2 => $group_control_number
        
        );
}

    public function bak($data = null){

        //beginning segment for purchase order ack
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $transaction_set_purpose_code = '00';
        $ack_type = 'AC';
        $purchase_order_number = '1857989';//beg03

        if($data){
            $transaction_set_purpose_code = $data['transaction_set_purpose_code'];
            $ack_type = $data['ack_type'];
            $purchase_order_number = $data['purchase_order_number'];
            $date = $data['date'];
        }

        $date ='20200921'; //beg05
        
        return array(
            0 => 'BAK',
            1 => $transaction_set_purpose_code, 
            2 => $ack_type,
            3 => $purchase_order_number,
            4 => $date
        );

    }

    public function dtm($data = null){

        //date time referemce

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        //011 shipped

        $quantifier = '011';
        
        if($data){
            $quantifier = $data['quantifier'];
            $date = $data['date'];
        }


        return array(
            
            0 => 'DTM',
            1 => $quantifier,
            2 => $date
        
        );

    }

    public function amt($data = null){

        //monetary amount 855

        $def = array(
            'amount_qualifer_code' => 'TT',
            'total_transaction_amount' => '0.00'
        );

        $amount_qualifier_code = 'TT';
        $total_transaction_amount = '0.00';

        if($data){
            $amount_qualifier_code =  $data['amount_qualifier_code'];
            $total_transaction_amount =  $data['total_transaction_amount'];
        }

        return array(
            0 =>'AMT',
            1 => $amount_qualifier_code,
            2 => $total_transaction_amount
        );

    }

    public function turn5_amt($data = null){

        //monetary amount 855

        $def = array(
            'amount_qualifer_code' => 'TT',
            'total_transaction_amount' => '0.00'
        );

        $amount_qualifier_code = 'TT';
        $total_transaction_amount = '0.00';

        if($data){
            $amount_qualifier_code =  $data['amount_qualifier_code'];
            $total_transaction_amount =  $data['total_transaction_amount'];
        }

        return array(
            0 =>'AMT',
            1 => $amount_qualifier_code,
            2 => $total_transaction_amount
        );

    }


    public function autoany_amt($data = null){

        //810 for handling fee

       
        $code = 'OH';
        $amount = '0.00';

        if($data){
            $code =  $data['code'];
            $amount =  $data['amount'];
        }

        return array(
            0 =>'AMT',
            1 => $code,
            2 => $amount
        );

    }

    public function autoany_txi($data = null){

        //810 for handling fee

       
        $code = 'ST';
        $amount = '0.00';

        if($data){
            $code =  $data['code'];
            $amount =  $data['amount'];
        }

        return array(
            0 =>'TXI',
            1 => $code,
            2 => $amount
        );

    }

    public function ctt($data = null){

        //transacction total
        $quantifier = '017'; //estimated delivery
        
        if($data){
            $quantifier = $data['quantifier'];
           
        }

        return array(
            0 => 'CTT',
            1 => $quantifier,
           
        );

    }
    public function turn5_ctt($data = null){

        //transacction total
        $quantifier = '017'; //estimated delivery
        
        if($data){
            $quantifier = $data['quantifier'];
           
        }

        return array(
            0 => 'CTT',
            1 => $quantifier,
           
        );

    }
    public function autoany_ctt($data = null){

        //transacction total
        $quantifier = '017'; //estimated delivery
        
        if($data){
            $quantifier = $data['quantifier'];
           
        }

        return array(
            0 => 'CTT',
            1 => $quantifier,
          
           
        );

    }

    public function ref($data = null){


        $def = array(
            'ref_id_qualifier' => 'IA',
            'ref_id' => 435
        );

        $ref_id_qualifier = 'IA';
        $ref_id = 435;

        if($data){
            $ref_id = $data['ref_id'];
            $ref_id_qualifier = $data['ref_id_qualifier'];
        }

        return array(
            0 => 'REF',
            1 => $ref_id_qualifier,
            2 => $ref_id
            
        );

    }

    public function turn5_refship($data = null){

       return false;

    }

    public function autoany_refship($data = null){


        $def = array(
            'ref_id_qualifier' => 'IA',
            'ref_id' => '11111111'
        );

        $ref_id_qualifier = 'CN';
        $ref_id = '111111111';

        if($data){
            $ref_id = $data['ref_id'];
            $ref_id_qualifier = $data['ref_id_qualifier'];
        }

        return array(
            0 => 'REF',
            1 => $ref_id_qualifier,
            2 => $ref_id
            
        );

    }


    public function bsn($data = null){

        //beginning segment for ship notice 856
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('Hi');

        $transaction_purpose_code ='01';
        $shipment_id ='01';

        if($data){

            $date = (isset($data['date']))?$data['date']:$date;
            $time = (isset($data['time']))?$data['time']:$time;
            $transaction_purpose_code =$data['transaction_purpose_code'];
            $shipment_id = $data['shipment_id'];

        }

        return array(
            
            0 => 'BSN',
            1 => $transaction_purpose_code,
            2 => $shipment_id,
            3 => $date,
            4 => $time,
           
        );

    }

    public function hl($data = null){

         //heirarchical level
         $id_number = '01';
         $parent_id_number = '';
         $code= 'S';

         if($data){
             $id_number = $data['id_number'];
             $parent_id_number = (isset($data['parent_id_number']))?$data['parent_id_number']:$parent_id_number;
             $code= $data['code'];
         }

         return array(
             0 => 'HL',
             1 => $id_number,
             2 => $parent_id_number,
             3 => $code
            
         );

    }

    public function turn5_hl($data = null){

        //heirarchical level
        $id_number = '01';
        $parent_id_number = '';
        $code= 'S';

        if($data){
            $id_number = $data['id_number'];
            $parent_id_number = (isset($data['parent_id_number']))?$data['parent_id_number']:$parent_id_number;
            $code= $data['code'];
        }

        return array(
            0 => 'HL',
            1 => $id_number,
            2 => $parent_id_number,
            3 => $code
           
        );

   }

   public function autoany_hl($data = null){

    //heirarchical level
    $id_number = '01';
    $code= 'S';
    $child_code = 1;
    $parent_id_number = 1;

    if($data){
        $id_number = $data['id_number'];
        $code= $data['code'];
        $parent_id_number = $data['parent_id_number'];
        $child_code= $data['child_code'];
    }

    return array(
        0 => 'HL',
        1 => $id_number,
        2 => $parent_id_number,
        3 => $code,
        4 => $child_code
       
    );

}

   public function prf($data = null){

        //heirarchical level
        //match 850 PO number
        $po_number = '01';
    
        if($data){
            $po_number = $data['po_number'];
        
        }

        return array(
            0 => 'PRF',
            1 => $po_number,
        
        );


    }

    public function td5($data = null){

        //carrier details routing sequence
        //match 850 shipping info
        $routing = 'STANDARD SHIPPING';
        $service_code = 'G2';
    
        if($data){
            $routing = $data['routing'];
            $service_code = $data['service_code'];
        
        }

         return Array(
            0 => 'TD5',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => $routing ,
            6 => '',
            7 => '',
            8 => '',
            9 => '',
            10 => '',
            11 => '',
            12 =>  $service_code
        );
    }

    public function turn5_td5($data = null){

        //carrier details routing sequence
        //match 850 shipping info
        $routing = 'STANDARD SHIPPING';
        $service_code = 'G2';
    
        if($data){
            $routing = $data[5];
            $service_code  = 'G2';
        
        }

         return Array(
            0 => 'TD5',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => $routing ,
            6 => '',
            7 => '',
            8 => '',
            9 => '',
            10 => '',
            11 => '',
            12 =>  $service_code
        );
    }

    public function autoany_td5($data = null){

        return $data;
    }

    public function man($data = null){

        //marks and numbers

        $mark_number_qualifier ='CP';
        $mark_number = '1';

        if($data){

            $mark_number_qualifier = $data['mark_number_qualifier'];
            $mark_number = $data['mark_number'];

        }

        return array(
            
            0 => 'MAN',
            1 => $mark_number_qualifier,
            2 => $mark_number 
            
            
        );
  

    }

    public function turn5_man($data = null){

        //marks and numbers

        $mark_number_qualifier ='CP';
        $mark_number = '1';

        if($data){

            $mark_number_qualifier = $data['mark_number_qualifier'];
            $mark_number = $data['mark_number'];

        }

        return array(
            
            0 => 'MAN',
            1 => $mark_number_qualifier,
            2 => $mark_number 
            
            
        );
  

    }

    public function autoany_man($data = null){

        //marks and numbers

       return 0;
  

    }

    public function lin($data = null){

        //item identification 856

        $assigned_id = '0';//850 PO101
        $product_service_vendor_id_quantifier = 'VP'; //vendor part number
        $product_service_vendor_id = '1';
        $product_service_buyer_id_quantifier = 'BP'; //buyer part number
        $product_service_buyer_id = '1';

        if($data){
            $assigned_id = $data['assigned_id'];
            $product_service_vendor_id_quantifier = $data['product_service_vendor_id_quantifier'];
            $product_service_vendor_id = $data['product_service_vendor_id'];
            $product_service_buyer_id_quantifier = $data['product_service_buyer_id_quantifier'];
            $product_service_buyer_id = $data['product_service_buyer_id'];

        }

        return array(
            
            0 => 'LIN',
            1 => $assigned_id,
            2 => $product_service_vendor_id_quantifier,
            3 => $product_service_vendor_id,
            4 => $product_service_buyer_id_quantifier,
            5 => $product_service_buyer_id 
            
         );

  

    }

    public function turn5_lin($data = null){

        //item identification 856

        $assigned_id = '0';//850 PO101
        $product_service_vendor_id_quantifier = 'VP'; //vendor part number
        $product_service_vendor_id = '1';
        $product_service_buyer_id_quantifier = 'BP'; //buyer part number
        $product_service_buyer_id = '1';

        if($data){
            $assigned_id = $data['assigned_id'];
            $product_service_vendor_id_quantifier = $data['product_service_vendor_id_quantifier'];
            $product_service_vendor_id = $data['product_service_vendor_id'];
            $product_service_buyer_id_quantifier = $data['product_service_buyer_id_quantifier'];
            $product_service_buyer_id = $data['product_service_buyer_id'];

        }

        return array(
            
            0 => 'LIN',
            1 => $assigned_id,
            2 => $product_service_vendor_id_quantifier,
            3 => $product_service_vendor_id,
            4 => $product_service_buyer_id_quantifier,
            5 => $product_service_buyer_id 
            
         );

  

    }

    public function autoany_lin($data = null){

        //item identification 856

        $assigned_id = '0';//850 PO101
        $quantifier = 'SK';
        $sku = '';

        if($data){
            $assigned_id = $data['assigned_id'];
            $quantifier = 'SK';
            $sku = $data['part_number'];
        }

        return array(
            
            0 => 'LIN',
            1 => $assigned_id,
            2 => $quantifier,
            3 => $sku
          
            
         );

  

    }

    public function sn1($data = null){

        //item details shipment 856
        $number_unit_shipped ='1';
        $unit = 'EA';

        if($data){
            $number_unit_shipped = $data['number_unit_shipped'];
            $unit = $data['unit'];

        }

        return array(
            
            0 => 'SN1',
            1 => '',
            2 => $number_unit_shipped,
            3 => $unit
            
            
        );
  

    }

    public function turn5_sn1($data = null){

        //item details shipment 856
        $number_unit_shipped ='1';
        $unit = 'EA';
      
        if($data){
            $number_unit_shipped = $data['number_unit_shipped'];
            $unit = $data['unit'];

        }

        return array(
            
            0 => 'SN1',
            1 => '',
            2 => $number_unit_shipped,
            3 => $unit
            
            
        );
  

    }

    public function autoany_sn1($data = null){

        //item details shipment 856
        $number_unit_shipped ='1';
        $unit = 'EA';
        $assigned_id = 1;

        if($data){
            $number_unit_shipped = $data['number_unit_shipped'];
            $unit = $data['unit'];
            $assigned_id = $data['assigned_id'];
        }

        return array(
            
            0 => 'SN1',
            1 => $assigned_id,
            2 => $number_unit_shipped,
            3 => $unit
            
            
        );
  

    }

    //810

    public function big($data = null){

        //beginning segemnt for invoice
    
        $date ='';
        $invoice_number ='';
        $invoice_type = 'CN';
      
        if($data){
           
            $invoice_number = $data['invoice_number'];
            $invoice_type =  $data['invoice_type'];
            $date = $data['date'];

        }

        return array(
            
            0 => 'BIG',
            1 => $date,
            2 => $invoice_number,
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => $invoice_type
            
            
        );
  
    }

    public function turn5_big($data = null){

        //beginning segemnt for invoice
    
        $date ='';
        $invoice_number ='';
        $invoice_type = 'CN';
     

        if($data){
           
            $invoice_number = $data['invoice_number'];
            $invoice_type =  $data['invoice_type'];
            $date = $data['date'];

        }

        return array(
            
            0 => 'BIG',
            1 => $date,
            2 => $invoice_number,
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => $invoice_type
            
            
        );
  

    }

    public function autoany_big($data = null){

        //beginning segemnt for invoice
    
        $date ='';
        $invoice_number ='';
        $invoice_type = 'CN';
        $po = '';

        if($data){
           
            $invoice_number = $data['invoice_number'];
            $invoice_type =  $data['invoice_type'];
            $date = $data['date'];
            $po = $data['po_number'];

        }

        return array(
            
            0 => 'BIG',
            1 => $date,
            2 => $invoice_number,
            3 => '',
            4 => $po,
        
        );
  

    }



    public function n1($data = null){

        //Name for 810
    
       $def = array(
           'entity_identifier_code' => 'SU',  //Supplier/Mf
           'id_code_qualifier' => '92', //buyer's agent
           'name' => 'name',
           'id_code' => '00'
       );

        if($data){
           
            $entity_identifier_code = $data['entity_identifier_code'];
            $id_code_qualifier = $data['id_code_qualifier'];
            $name = $data['name'];
            $id_code = $data['id_code'];

        }

        return array(
            
            0 => 'N1',
            1 => $entity_identifier_code,
            2 => $name,
            3 => $id_code_qualifier,
            4 => $id_code,
           
        );
  
    }

    public function turn5_n1($data = null){

        //Name for 810
    
       $def = array(
           'entity_identifier_code' => 'SU',  //Supplier/Mf
           'id_code_qualifier' => '92', //buyer's agent
           'name' => 'name',
           'id_code' => '00'
       );

        if($data){
           
            $entity_identifier_code = $data['entity_identifier_code'];
            $id_code_qualifier = $data['id_code_qualifier'];
            $name = $data['name'];
            $id_code = $data['id_code'];

        }

        return array(
            
            0 => 'N1',
            1 => $entity_identifier_code,
            2 => $name,
            3 => $id_code_qualifier,
            4 => $id_code,
           
        );
  
    }

    public function autoany_n1($data = null){

        //Name for 810  856  st ship to

        $entity_identifier_code = 'ST';
        $name = '';
        if($data){
           
            $name = $data['name'];
        
        }

        return array(
            
            0 => 'N1',
            1 => $entity_identifier_code,
            2 => $name,
        
        );
  
    }

    public function autoany_n2($data = null){

        //Name for 856 company name

        $name = '';
        if($data){
            $name = $data['aname'];
        
        }

        return array(
            
            0 => 'N2',
            1 => $name,
        
        );
  
    }

    public function autoany_n3($data = null){

        //Name for 856 primary address secondary address

        $prime = '';
        $second = '';
        if($data){
            $prime = $data['street'];
            $second = $data['street2'];
        
        }

        return array(
            
            0 => 'N3',
            1 => $prime,
            2 => $second,
        
        );
  
    }

    public function autoany_n4($data = null){

        //Name for 856 geographic city,state, postal, country

        $city ='';
        $state = '';
        $post = '';
        $country ='';
        if($data){
            $city = $data['city'];
            $state = $data['state'];
            $post = $data['zip'];
            $country = $data['country_code'];
        
        }

        return array(
            
            0 => 'N4',
            1 => $city,
            2 => $state,
            3 => $post,
            4 => $country
        
        );
  
    }

    public function itd($data = null){

        //term of sales 810

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');

        $def = array(
            'type_code' => '01',  //basic
            'date_code' => '3',   //invoice
            'due_date' => $date, //ccyynmmdd
            'net_days' => '1',    //number of days until invoice is due
            'description' => '0'
        );

        if($data){
           
            $type_code = $data['type_code'];
            $date_code = $data['date_code'];
            $due_date = $data['due_date'];
            $net_days = $data['net_days'];
            $description = $data['description'];
        
        }

         return Array(
            0 => 'ITD',
            1 => $type_code,
            2 => $date_code,
            3 => '',
            4 => '',
            5 => '',
            6 => $due_date,
            7 => $net_days,
            8 => '',
            9 => '',
            10 => '',
            11 => '',
            12 => $description
        );
    }

    public function it1($data = null){

        //baseline item data invoice 810

        $def = array(
            'quantity' =>  1, 
            'unit' => 'EA',   
            'price' => '1.00',
            'id_qualifer_buyer' => 'VP', //sellers part number
            'id_buyer' => '0',
            'id_qualifer_seller' => 'BP', //buyer part number
            'id_seller' => '0',
            'id_qualifier_source' => 'VS', //vendor supplemental number
            'id_source' => '0'
        );

        if($data){
        
            $quantity =  $data['quantity'];
            $unit = $data['unit'];
            $price = $data['price'];
            $id_qualifier_buyer = $data['id_qualifer_buyer'];
            $id_buyer = $data['id_buyer'];
            $id_qualifier_seller = $data['id_qualifer_seller'];
            $id_seller = $data['id_seller'];
            $id_qualifier_source = $data['id_qualifier_source'];
            $id_source = $data['id_source'];
        
        }

         return Array(
            0 => 'IT1',
            1 => '',
            2 => $quantity,
            3 => $unit,
            4 => $price,
            5 => '',
            6 => $id_qualifier_seller,
            7 => $id_seller,
            8 => $id_qualifier_buyer,
            9 => $id_buyer,
            10 => $id_qualifier_source,
            11 => $id_source
            
        );
    }


    public function turn5_it1($data = null){

        //baseline item data invoice 810

        $def = array(
            'quantity' =>  1, 
            'unit' => 'EA',   
            'price' => '1.00',
            'id_qualifer_buyer' => 'VP', //sellers part number
            'id_buyer' => '0',
            'id_qualifer_seller' => 'BP', //buyer part number
            'id_seller' => '0',
            'id_qualifier_source' => 'VS', //vendor supplemental number
            'id_source' => '0'
        );

        if($data){
        
            $quantity =  $data['quantity'];
            $unit = $data['unit'];
            $price = $data['price'];
            $id_qualifier_buyer = $data['id_qualifer_buyer'];
            $id_buyer = $data['id_buyer'];
            $id_qualifier_seller = $data['id_qualifer_seller'];
            $id_seller = $data['id_seller'];
            $id_qualifier_source = $data['id_qualifier_source'];
            $id_source = $data['id_source'];
        
        }

         return Array(
            0 => 'IT1',
            1 => '',
            2 => $quantity,
            3 => $unit,
            4 => $price,
            5 => '',
            6 => $id_qualifier_seller,
            7 => $id_seller,
            8 => $id_qualifier_buyer,
            9 => $id_buyer,
            10 => $id_qualifier_source,
            11 => $id_source
            
        );
    }

    public function autoany_it1($data = null){

        //baseline item data invoice 810

        $line_number = 1;
        $sku = '';
        $price = '';
        $unit = 'EA';
        $quantity = '';
        $quantifier = 'SK';

        

        if($data){
        
            $quantity =  $data['quantity'];
            $unit = $data['unit'];
            $price = $data['price'];
            $quantifier = $data['quantifier'];
            $sku = $data['part_number'];
            $line_number = $data['line_number'];
           
        }

         return Array(
            0 => 'IT1',
            1 => $line_number,
            2 => $quantity,
            3 => $unit,
            4 => $price,
            5 => '',
            6 => $quantifier,
            7 => $sku,
           
        );
    }

    public function tds($data = null){

        //total monetary value 810
        $amount = '0.00';

        if($data){
            $amount =  $data['amount'];
           
        }
        $amount =  number_format($amount, 2, '.', '');
        $amount =  str_replace('.', "",  $amount);

        return array(
            0 =>'TDS',
            1 => $amount
          
        );

    }

    public function turn5_tds($data = null){

        //total monetary value 810
        $amount = '0.00';

        if($data){
            $amount =  $data['amount'];
           
        }
        $amount =  number_format($amount, 2, '.', '');
        $amount =  str_replace('.', "",  $amount);

        return array(
            0 =>'TDS',
            1 => $amount
          
        );

    }

    public function autoany_tds($data = null){

        //total monetary value 810
        $amount = '0.00';
        $sub = '0.00';

        if($data){
            $amount =  $data['amount'];
            $sub = $data['subtotal'];
           
        }
       

        return array(
            0 =>'TDS',
            1 => $amount,
            2 => $sub
          
        );

    }

    public function sac($data = null){

        //service, promotion, allowance

        $def = array(
            'indicator' =>  'A',  //allowance, c charge 
            'code' => 'G821',   //dropship
            'amount' => '1.00',
            'description' => '0'
        );

        if($data){

            $amount =  number_format($data['amount'], 2, '.', '');
            $amount =  str_replace('.', "",  $amount);

            $indicator =  $data['indicator'];
            $code = $data['code'];
           
            if($code =='C310'){
                $amount = '-'.$amount;
            }
            $description = $data['description'];

            $discount_per = (isset($data['discount_per']))?$data['discount_per']:'';
            $discount_quantifier = (isset($data['discount_quantifier']))?$data['discount_quantifier']:'';
        
        }

         return Array(
            0 => 'SAC',
            1 => $indicator,
            2 => $code,
            3 => '',
            4 => '',
            5 => $amount,
            6 => $discount_quantifier,
            7 => $discount_per,
            8 => '',
            9 => '',
            10 => '',
            11 => '',
            12 => '',
            13 => '',
            14 => '',
            15 => $description
            
        );
    }

    public function turn5_sac($data = null){

        //service, promotion, allowance

        $def = array(
            'indicator' =>  'A',  //allowance, c charge 
            'code' => 'G821',   //dropship
            'amount' => '1.00',
            'description' => '0'
        );

        if($data){

            $amount =  number_format($data['amount'], 2, '.', '');
            $amount =  str_replace('.', "",  $amount);

            $indicator =  $data['indicator'];
            $code = $data['code'];
           
            if($code =='C310'){
                $amount = '-'.$amount;
            }
            $description = $data['description'];

            $discount_per = (isset($data['discount_per']))?$data['discount_per']:'';
            $discount_quantifier = (isset($data['discount_quantifier']))?$data['discount_quantifier']:'';
        
        }

         return Array(
            0 => 'SAC',
            1 => $indicator,
            2 => $code,
            3 => '',
            4 => '',
            5 => $amount,
            6 => $discount_quantifier,
            7 => $discount_per,
            8 => '',
            9 => '',
            10 => '',
            11 => '',
            12 => '',
            13 => '',
            14 => '',
            15 => $description
            
        );
    }

    public function autoany_sac($data = null){

        //service, promotion, allowance

        $def = array(
            'indicator' =>  'A',  //allowance, c charge 
            'code' => 'G821',   //dropship
            'amount' => '1.00',
            'description' => '0'
        );

        if($data){

            $indicator =  $data['indicator'];
            $code = $data['code'];
            $amount = $data['amount'];
           
            if($code =='C310'){
                $amount = '-'.$amount;
            }
          

            $discount_per = (isset($data['discount_per']))?$data['discount_per']:'';
            $discount_quantifier = (isset($data['discount_quantifier']))?$data['discount_quantifier']:'';
        
        }

         return Array(
            0 => 'SAC',
            1 => $indicator,
            2 => $code,
            3 => '',
            4 => '',
            5 => $amount,
            6 => $discount_quantifier,
            7 => $discount_per,
           
            
        );
    }

    public function turn5_po1($data = null){


        $product_service_vendor_id_quantifier = 'VP'; //vendor part number
        $product_service_vendor_id = '1';
        $product_service_buyer_id_quantifier = 'BP'; //buyer part number
        $product_service_buyer_id = '1';

        if($data){
            $quantity =  $data['quantity'];
            $unit = $data['unit'];
            $price = $data['price'];
            $product_service_vendor_id_quantifier = $data['product_service_vendor_id_quantifier'];
            $product_service_vendor_id = $data['product_service_vendor_id'];
            $product_service_buyer_id_quantifier = $data['product_service_buyer_id_quantifier'];
            $product_service_buyer_id = $data['product_service_buyer_id'];

        }

         return Array(
            0 => 'PO1',
            1 => '',
            2 => $quantity,
            3 => $unit,
            4 => $price,
            5 => '',
            6 => $product_service_vendor_id_quantifier,
            7 => $product_service_vendor_id,
            8 => $product_service_buyer_id_quantifier,
            9 => $product_service_buyer_id 
            
        );
    }

    public function turn5_ack($data = null){
        
        $quantity = '';
        $status_code = '';
        // DR item accept reschedule
        // IA item acccepted
        // IP item accepted price
        // IQ item accepted qty
        // IR item rejected

        if($data){
            $status_code = $data['code'];
            $quantity = $data['quantity'];
        
        }

         return Array(
            0 => 'ACK',
            1 => $status_code,
            2 => $quantity,
            3 => 'EA',
           
        );
    }

    public function po1($data = null){

        $quantity = '';
        $price = '';
        $sku = '';
        $line_number = 1;

        if($data){

            $price = $data['price'];
            $quantity = $data['quantity'];
            $sku = $data['part_number'];
            $line_number = $data['line_number'];
        
        }

         return Array(
            0 => 'PO1',
            1 => $line_number,
            2 => $quantity,
            3 => 'EA',
            4 => $price,
            5 => '',
            6 => 'SK',
            7 => $sku,
          
            
        );
    }

    public function ack($data = null){
        //date YYYYMMDD
        //status code IB, ID IR R4
        $time = 369;
        $date = '';
        $quantity = '';
        $status_code = '';
        $sku = '';

        if($data){

            $status_code = $data['status_code'];
            $quantity = $data['quantity'];
            $sku = $data['part_number'];
            $date = $data['date'];
        
        }

         return Array(
            0 => 'ACK',
            1 => $status_code,
            2 => $quantity,
            3 => 'EA',
            4 => $time,
            5 => $date,
            6 => '',
            7 => $sku,
          
        );
    }

    public function td1($data = null){
        
        //carrier details quantity and weight
        //856
        $weight = 1;
        $unit = 'LB';

        if($data){
            $weight= $data['weight'];
            $unit= $data['unit'];
        }

        return array(
            0 => 'TD1',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => $weight,
            8 => $unit
           
           
        );

   }

   public function turn5_td1($data = null){
        
        //carrier details quantity and weight
        //856
        $weight = 1;
        $unit = 'LB';

        if($data){
            $weight= $data['weight'];
            $unit= $data['unit'];
        }

        return array(
            0 => 'TD1',
            1 => '',
            2 => '',
            3 => '',
            4 => '',
            5 => '',
            6 => '',
            7 => $weight,
            8 => $unit
           
           
        );

   }

   public function autoany_td1($data = null){
        
        return null;

   }

}
