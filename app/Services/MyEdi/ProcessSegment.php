<?php

namespace App\Services\MyEdi;

class ProcessSegment{

    private $x12 = null;
    private $segments = null;

    public function __construct(){}

    
    public function test(){
        echo 'process segment';
        echo '<br/>';
        print_r($this->segments);
        
    }

    public function number_of_isa(){

        if($this->x12){
                
            return count($this->x12->ISA);
                
        }
        return false;

    }

    public function get_all_property(){

        if($this->x12){
            $isas = $this->x12->ISA;
            if($isas){

                $props = null;
                foreach($isas as $i=>$isa){

                    $props[] = $isa->GS[0]->ST[0]->properties;

                }

                return $props;

            }

            return false;
        }
        return false;

    }

    public function set_x12($x12){
        if($x12){
            $this->x12 = $x12;
            $this->segments = $this->break_segments();
            return true;
        }
        return false;
       
    }

    public function break_segments(){

        if($this->x12){

            $isa = $this->x12->ISA;
            $iea = $isa[0]->IEA;
            $gs = $isa[0]->GS;
            $ge = $gs[0]->GE;
            $st = $gs[0]->ST;
            $se = $st[0]->SE;
            $properties = $st[0]->properties;
    
            return array(
                'isa' => $isa,
                'iea' => $iea,
                'gs' => $gs,
                'ge' => $ge,
                'st' => $st,
                'se' => $se,
                'props' => $properties,
               
                
            );

        }

        return false;
    }

    public function get_prop_value($seg){
        //850
        $result = null;
      
        if($this->segments['props']){
            $props = $this->segments['props'];
            foreach ($props as $i=>$p){
                  if($props[$i]->getSegmentId() == $seg){

                     $result[] =  array('val'=>$props[$i], 'index'=>$i);

                  }
                   
            }
            return $result;
        }
        return false;
    }

    public function isa(){
        return $this->segments['isa'][0]->getDataElements();
    }

    public function isa_po_ack(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $isa_data = $this->segments['isa'][0]->getDataElements();
        $isa_data[9] = $date;
        $isa_data[10]  = $time;
        return $isa_data;

    }

    public function isa_general(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $isa_data = $this->segments['isa'][0]->getDataElements();
        $isa_data[9] = $date;
        $isa_data[10]  = $time;
        return $isa_data;

    }

    public function isa_general_return($data = null){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');

        try{
            $isa_data = $this->segments['isa'][0]->getDataElements();
        }catch(\Exception $e){
            throw new \Exception('failed to get isa elements.');
        }
       
        if(isset($data['sender_id'])){
            $sender_qualifier = $data['sender_qualifier'];
            $sender_id = $data['sender_id'];
        }else{
            $sender_qualifier = $isa_data[7];
            $sender_id = $isa_data[8];
        }

        if(isset($data['receive_id'])){
            $receive_qualifier = $data['receive_qualifier'];
            $receive_id = $data['receive_id'];
    
        }else{
            $receive_qualifier = $isa_data[5];
            $receive_id = $isa_data[6]; 
        }

        if(isset($data['interchange_control_number'])){
            $interchange_control_number = $data['interchange_control_number'];
           
        }else{
            $interchange_control_number = $isa_data[13];
            
        }

        $isa_data[7] = $receive_qualifier;
        $isa_data[8] = $receive_id ;
        $isa_data[5] = $sender_qualifier;
        $isa_data[6] = $sender_id;

        $isa_data[9] = $date;
        $isa_data[10]  = $time;
        $isa_data[13]  = $interchange_control_number;
        return $isa_data;

    }

    public function gs(){
        return $this->segments['gs'][0]->getDataElements();
    }

    public function gs_po_ack(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();
        $gs_data[1] = 'PR';
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        return $gs_data;

    }

    public function gs_general(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        return $gs_data;

    }

    public function gs_general_return($data = null){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');

        try{
            $gs_data = $this->segments['gs'][0]->getDataElements();
        }catch(\Exception $e){
            throw new \Exception('failed to get gs elements.');
        }
        
        if(isset($data['sender_id'])){
            $sender_id = $data['sender_id'];
        }else{
            $sender_id =  $gs_data [3];
        
        }

        if(isset($data['receive_id'])){
            $receive_id = $data['receive_id'];
        }else{
            $receive_id = $gs_data [2];
        }

        if(isset($data['functional_control_number'])){
            $group_control_number = $data['functional_control_number'];
           
        }else{
            $group_control_number = $gs_data[6];
            
        }

        $gs_data[2] =  $sender_id;
        $gs_data[3]  = $receive_id;
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        $gs_data[6]  = $group_control_number;
        return $gs_data;
    }

    public function gs_general_return_ack($data = null){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');

        try{
            $gs_data = $this->segments['gs'][0]->getDataElements();
        }catch(\Exception $e){
            throw new \Exception('failed to get gs elements.');
        }
        
        if(isset($data['sender_id'])){
            $sender_id = $data['sender_id'];
        }else{
            $sender_id =  $gs_data [3];
        
        }

        if(isset($data['receive_id'])){
            $receive_id = $data['receive_id'];
        }else{
            $receive_id = $gs_data [2];
        }

        if(isset($data['functional_control_number'])){
            $group_control_number = $data['functional_control_number'];
           
        }else{
            $group_control_number = $gs_data[6];
            
        }
        $gs_data[1] = 'PR';
        $gs_data[2] =  $sender_id;
        $gs_data[3]  = $receive_id;
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        $gs_data[6]  = $group_control_number;
        return $gs_data;
    }

    

    public function gs_po_856(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();
        $gs_data[1] = 'SH';
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        return $gs_data;

    }

    public function gs_po_general_856($data = null){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();
       
        if(isset($data['sender_id'])){
           
            $sender_id = $data['sender_id'];
        }else{
            $sender_id =  $gs_data [3];
           
        }

        if(isset($data['receive_id'])){
            
            $receive_id = $data['receive_id'];
        }else{
            $receive_id = $gs_data [2];
        }

        if(isset($data['functional_control_number'])){
            $group_control_number = $data['functional_control_number'];
           
        }else{
            $group_control_number = $gs_data[6];
            
        }
        $gs_data[1] = 'SH';
        $gs_data[2] =  $sender_id;
        $gs_data[3]  = $receive_id;
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        $gs_data[6]  = $group_control_number;
        return $gs_data;

    }

    public function gs_po_810(){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();
        $gs_data[1] = 'IN';
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        return $gs_data;

    }

    public function gs_po_general_810($data = null){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('ymd');
        $time = $dt->format('Hi');
        $gs_data = $this->segments['gs'][0]->getDataElements();

        if(isset($data['sender_id'])){
           
            $sender_id = $data['sender_id'];
        }else{
            $sender_id =  $gs_data [3];
           
        }

        if(isset($data['receive_id'])){
            
            $receive_id = $data['receive_id'];
    
        }else{
           
            $receive_id = $gs_data [2];
        }

        if(isset($data['functional_control_number'])){
            $group_control_number = $data['functional_control_number'];
           
        }else{
            $group_control_number = $gs_data[6];
            
        }
        $gs_data[1] = 'IN';
        $gs_data[2] =  $sender_id;
        $gs_data[3]  = $receive_id;
        $gs_data[4] = $date;
        $gs_data[5]  = $time;
        $gs_data[6]  = $group_control_number;
        return $gs_data;

    }


    public function st(){
        return $this->segments['st'][0]->getDataElements();
    }

    public function st_po_ack($data = null){

        try {
            $st_data = $this->segments['st'][0]->getDataElements();
          

            if(isset($data['transaction_control_number'])){
                $transaction_control_number = $data['transaction_control_number'];
               
            }else{
                $transaction_control_number = $st_data[2];
                
            }

            $st_data[1] = '855';
            $st_data[2] = $transaction_control_number;



        } catch (\Exception $e) {
            throw new \Exception('missing st segment data.');
           
        }
        return $st_data;

    }

    public function st_po($seg ='856', $data = null){
        try {
            $st_data = $this->segments['st'][0]->getDataElements();
          

            if(isset($data['transaction_control_number'])){
                $transaction_control_number = $data['transaction_control_number'];
               
            }else{
                $transaction_control_number = $st_data[2];
                
            }

            $st_data[1] = $seg;
            $st_data[2] = $transaction_control_number;



        } catch (\Exception $e) {
            throw new \Exception('missing st segment data.');
           
        }
        return $st_data;

    }
    
    public function check_usplit($entry){

        if(is_array($entry)){

            return implode('U', $entry);
        }

        return $entry;

    }


    public function n1_po(){

        if(isset($this->segments['props']) && $this->segments){

            $items =  $this->get_prop_value('N1');
            $line_items = null;
            if($items){

                foreach($items as $item){

                    $tmp =  $item['val']->getDataElements();
                    $mseg = $this->check_usplit($tmp[1]);
                    if($mseg == 'SU'){
                        $line_items[] = $tmp;
                    }
                }

              

                return $line_items;

            }
            return false;
        }
        return false;

    }

    public function n1_po_810(){

        $n1s = $this->n1_po();
       
        if($n1s){
            $n1_data = null;
            foreach($n1s as $index => $n1){
                $sup = $this->check_usplit($n1[1]);
                $name = $this->check_usplit($n1[2]);
                $n1_data[] = array(
                    'entity_identifier_code' => $sup,  //Supplier/Mf  850 po n102
                    'id_code_qualifier' => $n1[3], //buyer's agent 850 po n101
                    'name' => $name,
                    'id_code' => $n1[4] //850 n104
                );

            }
            return $n1_data;

        }
        return false;

    }

    public function turn5_n1_po_810(){

        $n1s = $this->n1_po();

        if($n1s){
            $n1_data = null;
            foreach($n1s as $index => $n1){
                $sup = $this->check_usplit($n1[1]);
                $name = $this->check_usplit($n1[2]);
                $n1_data[] = array(
                    'entity_identifier_code' => $sup,  //Supplier/Mf  850 po n102
                    'id_code_qualifier' => $n1[3], //buyer's agent 850 po n101
                    'name' => $name,
                    'id_code' => $n1[4] //850 n104
                );

            }
            return $n1_data;

        }
        return false;

    }

    public function autoany_n1_po(){

        if(isset($this->segments['props']) && $this->segments){

            $items =  $this->get_prop_value('N1');
            $line_items = null;

            if($items){

                foreach($items as $item){

                    $tmp =  $item['val']->getDataElements();
                    $line_items[] = $tmp;
                    
                }
                return $line_items;

            }
            return false;
        }
        return false;

    }

    public function autoany_n1_po_810(){

        $n1s = $this->autoany_n1_po();

        if($n1s){
            $n1_data = null;
            foreach($n1s as $index => $n1){
                $sup = $this->check_usplit($n1[1]);
                $name = $this->check_usplit($n1[2]);
                $n1_data[] = array(
                    'entity_identifier_code' => $sup,  //Supplier/Mf  850 po n102
                    'name' => $name,
                    
                );

            }
            return $n1_data;

        }
        return false;

    }

    public function bak_po_ack($ack_type = 'AC', $set_purpose_code = 0){

        //po ack beg to bak
        //AC accept
        //RD reject

        $r = $this->get_prop_value('BEG');
        if(isset($r[0]['val']) && $r){

            try{
                $r_values = $r[0]['val']->getDataElements();
            }catch(\Exception $e){
                throw new \Exception('missing BEG elements');
            }
            $ack_values = $r_values;

            switch($ack_type){
                case 'AC':
                    
                    if($set_purpose_code){
                        $transaction_set_purpose_code = '00';
                    }else{
                        $transaction_set_purpose_code = '04';
                    }
                    break;
                case 'AD':
                    $transaction_set_purpose_code = '00';
                    break;
                case 'RD':
                    $transaction_set_purpose_code = '00';
                    break;
                case 'RJ':
                    $transaction_set_purpose_code = '01';
                    break;
                default:
                    break;

            }

            

            return array(
                0 => 'BAK',
                1 => $transaction_set_purpose_code, 
                2 => $ack_type,
                3 => $r_values[3],
                4 => $r_values[5]
            );
            
        }
        return false;

    }

    public function ref_po_ack(){

        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('REF');
            if(isset($r[0]['val']) && $r){

                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('missing REF elements');
                }

                return $r_values;

            }
            return false;
        }
        return false;

    }

    public function se_po_ack($data = null){

        if(isset($this->segments['props']) && $this->segments){

            try{
                $se_data = $this->segments['st'][0]->SE->getDataElements();
            }catch(\Exception $e){
                throw new \Exception('missing SE elements');
            }

            if(isset($data['transaction_control_number'])){
                $transaction_control_number = $data['transaction_control_number'];
               
            }else{
                $transaction_control_number = $se_data[2];
                
            }

            $se_data[2] = $transaction_control_number;
            return $se_data;

        }
        return false;

    }

    public function se_general(){

        if(isset($this->segments['props']) && $this->segments){

            $se_data = $this->segments['st'][0]->SE->getDataElements();
            return $se_data;

        }
        return false;

    }

    public function iea_po_ack($data = null){

        if(isset($this->segments['props']) && $this->segments){

            try{
                $iea_data = $this->segments['isa'][0]->IEA->getDataElements();

                if(isset($data['interchange_control_number'])){
                    $interchange_control_number = $data['interchange_control_number'];
                   
                }else{
                    $interchange_control_number = $iea_data[2];
                    
                }

                $iea_data[2] =  $interchange_control_number;


            }catch(\Exception $e){
                throw new \Exception('missing IEA elements');
            }

            return $iea_data;

        }
        return false;

    }

    public function iea_general(){

        if(isset($this->segments['props']) && $this->segments){

            try{
                $iea_data = $this->segments['isa'][0]->IEA->getDataElements();
            }catch(\Exception $e){
                throw new \Exception('missing IEA elements');
            }
            return $iea_data;

        }
        return false;

    }

    public function ge_po_ack($data = null){

        if(isset($this->segments['props']) && $this->segments){

            try{
                $ge_data = $this->segments['gs'][0]->GE->getDataElements();

                if(isset($data['functional_control_number'])){
                    $group_control_number = $data['functional_control_number'];
                   
                }else{
                    $group_control_number = $ge_data[2];
                    
                }

                $ge_data[2] = $group_control_number;


            }catch(\Exception $e){
                throw new \Exception('missing GE elements');
            }
            return $ge_data;

        }
        return false;


    }

    public function ge_general(){

        if(isset($this->segments['props']) && $this->segments){

            try{
                $ge_data = $this->segments['gs'][0]->GE->getDataElements();
            }catch(\Exception $e){
                throw new \Exception('missing GE elements');
            }
            return $ge_data;

        }
        return false;


    }

    public function td5_po_ack(){

        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('TD5');
            if(isset($r[0]['val']) && $r){

                $r_values = $r[0]['val']->getDataElements();
                return $r_values;

            }
            return false;
        }
        return false;
    }

    public function number_of_orders(){

        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('CTT');
            if(isset($r[0]['val']) && $r){

                $r_values = $r[0]['val']->getDataElements();
                return $r_values[1];

            }
            return false;
        }
        return false;

    }

    public function number_of_orders_force(){
        if(isset($this->segments['props']) && $this->segments){
            try{
                $items = $this->get_prop_value('PO1'); 
            }catch(\Exception $e){
                throw new \Exception('PO1 element missing.');
            }
          
            return count($items);
        }
        return false;
    }

    public function purchase_order_number(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('BEG');
            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('BEG element missing No Purchase Order Number.');
                }
                return $r_values[3];

            }
            return false;
        }
        return false;
    }

    public function purchase_order_type(){

        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('BEG');
            if(isset($r[0]['val']) && $r){

                $r_values = $r[0]['val']->getDataElements();

                switch ($r_values[2]){
                    case 'DR':
                            return 'DIRECT SHIP';
                        break;
                    case 'DS':
                            return 'DROP SHIP';
                        break;
                    case 'NE':
                            return 'NEW ORDER';
                        break;
                    case 'SA':
                            return 'Stand-alone Order';
                    default:
                        return false;
                        break;

                }
                
            }
            return false;
        }
        return false;

    }

    public function edi_code(){

        $st_data = $this->segments['st'][0]->getDataElements();
        if($st_data){
            return $st_data[1];
        }
        return false;
       

    }

    public function edi_date(){

        $data = $this->segments['gs'][0]->getDataElements();
        if($data){
            return $data[4];
        }
        return false;
       

    }

    public function items(){

        if(isset($this->segments['props']) && $this->segments){

            $items = $this->get_prop_value('PO1');
            $line_items = null;
            $total = 0;
            if($items){

                foreach($items as $item){

                    $line_items[] = $item['val']->getDataElements();

                }

                return $line_items;

            }
            return false;
        }
        return false;

    }

    public function items_total($aitems = null){
        $items = $this->items();

        if($items){
            $total = 0;
            foreach($items as $i=>$item){

                if($aitems){
                    if($aitems[$i]->actual_quantity !=0){
                        $quantity = $aitems[$i]->actual_quantity;
                    }else{
                        $quantity = $aitems[$i]->quantity;
                    }

                }else{
                    $quantity = $item[2];
                }

                $total += floatval($item[4]) * $quantity;

            }
            return $total;

        }
        return false;
    }

    public function po_to_lin_856($aitems = null){

        $items = $this->items();

        if($items){
           $result = null;
            foreach($items as $i=>$item){

                if($aitems){
                    if($aitems[$i]->actual_quantity !=0){
                        $quantity = $aitems[$i]->actual_quantity;
                    }else{
                        $quantity = $aitems[$i]->quantity;
                    }

                }else{
                    $quantity = $item[2];
                }

                $data = array(
                    'lin' =>array(
                        'assigned_id'=> $item[1],
                        'product_service_vendor_id_quantifier' => $item[6], //vendor part number
                        'product_service_vendor_id' => $item[7], //850 p0107
                        'product_service_buyer_id_quantifier' => $item[8], //buyer part number
                        'product_service_buyer_id' => $item[9] //850 p0109
            
                    ),
                    'sn' => array(
                        
                        'number_unit_shipped' => $quantity,
                        'unit' => $item[3]
                    )

                );

                $result[] = $data;
                
            }
            return $result;

        }
        return false;
      
    }


    public function turn5_po_to_lin_856(){

        $items = $this->items();
        if($items){
           $result = null;

            foreach($items as $item){
                $data = array(
                    'lin' =>array(
                        'assigned_id'=> $item[1],
                        'product_service_vendor_id_quantifier' => $item[6], //vendor part number
                        'product_service_vendor_id' => $item[7], //850 p0107
                        'product_service_buyer_id_quantifier' => $item[8], //buyer part number
                        'product_service_buyer_id' => $item[9] //850 p0109
            
                    ),
                    'sn' => array(
                        'number_unit_shipped' => $item[2],
                        'unit' => $item[3]
                    )

                );

                $result[] = $data;
                
            }
            return $result;

        }
        return false;
      
    }

    public function autoany_po_to_lin_856(){

        $items = $this->items();
        if($items){
           $result = null;
            foreach($items as $item){
                $data = array(
                    'lin' =>array(
                        'assigned_id'=> $item[1],
                        'quantifier' => $item[6], //vendor part number
                        'part_number' => $item[7], //850 p0107
                       
            
                    ),
                    'sn' => array(
                        'assigned_id'=> $item[1],
                        'number_unit_shipped' => $item[2],
                        'unit' => $item[3]
                    )

                );

                $result[] = $data;
                
            }
            return $result;

        }
        return false;
      
    }

    public function po_to_id1_810($aitems = null){

        $items = $this->items();
        if($items){
           $result = null;
            foreach($items as $i=>$item){

                if($aitems){
                    if($aitems[$i]->actual_quantity !=0){
                        $quantity = $aitems[$i]->actual_quantity;
                    }else{
                        $quantity = $aitems[$i]->quantity;
                    }

                }else{
                    $quantity = $item[2];
                }
                
                $result[] =  array(
                    'quantity' =>  $quantity,
                    'unit' => $item[3],  
                    'price' => $item[4],
                    'id_qualifer_buyer' => $item[8], //sellers part number
                    'id_buyer' => $item[9],
                    'id_qualifer_seller' => $item[6], //buyer part number
                    'id_seller' => $item[7],  //Po107
                    'id_qualifier_source' => 'VS', //vendor supplemental number
                    'id_source' => '0'
                );
                
            }

            return $result;

        }
        return false;
      
    
    }

    public function turn5_po_to_id1_810(){

        $items = $this->items();
        if($items){
           $result = null;
            foreach($items as $item){
                
                $result[] =  array(
                    'quantity' =>  $item[2],
                    'unit' => $item[3],  
                    'price' => $item[4],
                    'id_qualifer_buyer' => $item[8], //sellers part number
                    'id_buyer' => $item[9],
                    'id_qualifer_seller' => $item[6], //buyer part number
                    'id_seller' => $item[7],  //Po107
                    'id_qualifier_source' => 'VS', //vendor supplemental number
                    'id_source' => '0'
                );
                
            }

            return $result;

        }
        return false;
      
    
    }

    public function autoany_po_to_id1_810(){

        $items = $this->items();
        if($items){
           $result = null;
         
            foreach($items as $item){
                
                $result[] =  array(
                    'quantity' =>  $item[2],
                    'unit' => $item[3],  
                    'price' => $item[4],
                    'quantifier' => $item[6], //buyer part number
                    'id_seller' => $this->check_usplit($item[7])  //Po107
    
                );
                
            }

            return $result;

        }
        return false;
      
    
    }

    public function po_n1_group(){

        $result = null;
        if($this->segments['props']){
            $props = $this->segments['props'];
            $n1 = $n2 = $n3 = $n4 = $per= null;

            try{

                foreach ($props as $i=>$p){

                    if($props[$i]->getSegmentId() == 'N1'){
                        $n1[] =  $props[$i]->getDataElements();
                    }
                    if($props[$i]->getSegmentId() == 'N2'){
                        $n2[] = $props[$i]->getDataElements();
                    }
                    if($props[$i]->getSegmentId() == 'N3'){
                        $n3[] = $props[$i]->getDataElements();
                    }
                    if($props[$i]->getSegmentId() == 'N4'){
                        $n4[] =  $props[$i]->getDataElements();
                    }
                    if($props[$i]->getSegmentId() == 'PER'){
                        $per[] =  $props[$i]->getDataElements();
                    }
                }

            }catch(\Exception $e){
                throw  new \Exception('N1 group failed.');
            }
        
            return array('N1'=>$n1, 'N2'=>$n2, 'N3'=>$n3, 'N4'=>$n4, 'PER'=>$per);
        }
        return false;

    }


    public function check_sn(){

        $result = null;
        if($this->segments['props']){
            $props = $this->segments['props'];
            $n1 = $n2 = $n3 = $n4 = null;
           
            foreach ($props as $i=>$p){

                if($props[$i]->getSegmentId() == 'N1'){
                    $n1 =  $props[$i]->getDataElements();
                    if($h1[1]=='SN'){
                        return true;

                    }
                }
                


                
            }
            return false;
        }
        return false;

    }

    public function po_items_with_name(){

        $result = null;
        if($this->segments['props']){
            $props = $this->segments['props'];
            $p01 = null;
            $pid = null;

            try{
                foreach ($props as $i=>$p){

                    if($props[$i]->getSegmentId() == 'PO1'){
                          $p01[] =  $props[$i]->getDataElements();
                    }
                    if($props[$i]->getSegmentId() == 'PID'){
                        $pid[] = $props[$i]->getDataElements();
                    }
                }
              

            }catch(\Exception $e){
                throw new \Exception('fail to get items with name');
            }
            
            return array('P01'=>$p01, 'PID' =>$pid);
        }
        return false;

    }

    public function po_shipping_info(){
        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('TD5');

            if(isset($r[0]['val']) && $r){

                try{
                    $r_values = $r[0]['val']->getDataElements();
                  
                }catch(\Exception $e){
                    throw new \Exception('fail TD5, no shipping info..');
                }

                $rdata = $this->check_usplit($r_values[5]);
                return $rdata;

            }
            return false;
        }
        return false;
    }

    public function po_shipping_time(){
        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('DTM');
            if(isset($r[0]['val']) && $r){

                try{
                    $r_values = $r[0]['val']->getDataElements();
                  
                }catch(\Exception $e){
                    throw new \Exception('fail DTM, no shipping time..');
                }
               
                return $r_values[2];

            }
            return false;
        }
        return false;
    }

    public function po_shipping_time_message(){
        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('DTM');
            if(isset($r[0]['val']) && $r){

                try{
                    $r_values = $r[0]['val']->getDataElements();
                  
                }catch(\Exception $e){
                    throw new \Exception('fail DTM, no shipping time and message..');
                }
               

                $date = null;
                $message = 'Ship no later';

                switch($r_values[1]){
                    case '038':
                        $message = 'Ship no later';
                        break;
                    case '037':
                        $message = 'Ship Not Before';
                        break;
                    case '001':
                        $message = 'Cancel Date';
                        break;
                    case '002':
                        $message = 'Delivery Requested Date';
                        break;
                    default:
                        break;

                }
               

                return array('date'=>$r_values[2], 'message'=>$message);
                

            }
            return false;
        }
        return false;
    }

    public function prop_general($prop='AK1'){

        $r = $this->get_prop_value($prop);
        if(isset($r[0]['val']) && $r){

            $r_values = $r[0]['val']->getDataElements();
            return $r_values;

        }
        return false;

    }

    public function status_of(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK1');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK1 element missing No Purchase Order Number.');
                }

                $status = array(
                    'IN' => 'Invoice',
                    'PO' => 'Purchase Order',
                    'SH' => 'Advance Shipment Notice',
                    'PR' => 'Po Response'
                );
                return $status[$r_values[1]];

            }
            return false;
        }
        return false;
    }

    public function status_for(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK2');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK2 element missing No Purchase Order Number.');
                }

        
                return array('code'=> $r_values[1], 'number'=> $r_values[2]);

            }
            return false;
        }
        return false;
    }

    public function data_segment_997(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK3');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK3 element missing No Purchase Order Number.');
                }

        
                return  $r_values;

            }
            return false;
        }
        return false;
    }

    public function data_element_997(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK4');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK4 element missing No Purchase Order Number.');
                }

        
                return  $r_values;

            }
            return false;
        }
        return false;
    }

    public function trans_status(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK5');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK5 element missing No Purchase Order Number.');
                }

                $status = array(
                    'A' => 'Accepted',
                    'E' => 'Accepted with Errors',
                    'R' =>  'Rejected'
                );

        
                return array('status'=> $r_values[1], 'error'=> $r_values);

            }
            return false;
        }
        return false;
    }

    public function funct_status(){

        if(isset($this->segments['props']) && $this->segments){
            $r = $this->get_prop_value('AK9');

            if(isset($r[0]['val']) && $r){
                try{
                    $r_values = $r[0]['val']->getDataElements();
                }catch(\Exception $e){
                    throw new \Exception('AK9 element missing No Purchase Order Number.');
                }

                $status = array(
                    'A' => 'Accepted',
                    'E' => 'Accepted with Errors',
                    'R' =>  'Rejected'
                );

        
                return array('status'=> $r_values[1], 'error'=> $r_values);

            }
            return false;
        }
        return false;
    }

    public function turn5_po_to_po1_dtm_ack_855(){

        $items = $this->items();
        if($items){
           
           $result = null;
            foreach($items as $item){
                $data = array(
                    'po1' =>array(
                        'assigned_id'=> $item[1],
                        'quantity' => $item[2],
                        'price' => $item[4],
                        'unit' => $item[3],
                        'product_service_vendor_id_quantifier' => $item[6], //vendor part number
                        'product_service_vendor_id' => $item[7], //850 p0107
                        'product_service_buyer_id_quantifier' => $item[8], //buyer part number
                        'product_service_buyer_id' => $item[9] //850 p0109
            
                    ),
                    'ack' => array(
                        'quantity' => $item[2],
                        'code' => '',
                        'unit' => $item[3],
                    )


                );

                $result[] = $data;
                
            }
            return $result;

        }
        return false;
      
    }

    public function po1_data_ac(){
       
        if(isset($this->segments['props']) && $this->segments){

            $r = $this->get_prop_value('PO1');
            if(isset($r[0]['val']) && $r){

                try{
                    $r_values = $r[0]['val']->getDataElements();
                  
                }catch(\Exception $e){
                    throw new \Exception('fail PO1, no shipping time..');
                }

                print_r($r_values);
               // die();
               
                return $r_values[2];

            }
            return false;
        }
        return false;

        
    }

}
