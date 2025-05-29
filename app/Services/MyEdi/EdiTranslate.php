<?php

namespace App\Services\MyEdi;

class EdiTranslate{

    public function __construct(){}


    public function check_sn($sns){
        if($sns){

            foreach($sns as $sn){
                if($sn[1] == 'SN'){
                    return true;

                }

            }
            return false;
        }

        return false;

    }

    public function check_usplit($entry){

        if(is_array($entry)){

            return implode('U', $entry);
        }

        return $entry;

    }

    public function address($n_info, $info){
        
        $ship_from = $to = $supplier = $bill_to = $store = null;

        foreach($n_info as $i=>$in){

            if($in[1] == 'SN'){
                if(is_array($info['N1'][$i][2])){
                    $store = $info['N1'][$i][2][0].'U'.$info['N1'][$i][2][1];
                }else{
                    $store = $info['N1'][$i][1];
                }
            }

            $name = $this->check_usplit($info['N1'][$i][2]);
            
            if(isset($info['N3'][$i])){

                $street  = $this->check_usplit($info['N3'][$i][1]);

                if(isset($info['N3'][$i][2])){
                    $n3_2 = $this->check_usplit($info['N3'][$i][2]);
                    
                }

                if(isset($info['N3'][$i][2])){
                    $street2 = $this->check_usplit($info['N3'][$i][2]);
                    
                }else{
                    $street2 ='';
                }

            }
            
            if(isset($info['N2'][$i]) && $info['N2'][$i] !=''){

                $aname = $this->check_usplit($info['N2'][$i][1]);

            }else{
                $aname = '';
            }

            if(isset($info['N4'][$i])){


                $city = $this->check_usplit($info['N4'][$i][1]);
                $state = $this->check_usplit($info['N4'][$i][2]);

                $zip = $info['N4'][$i][3];
                $country_code = $this->check_usplit($info['N4'][$i][4]);

            }

            $su = $this->check_usplit($info['N1'][$i][1]);
            if($su =='SU'){

                $supplier = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' => "United States",
                    'country_code' => $info['N4'][$i][3]
                );

            }

            $st = $this->check_usplit($info['N1'][$i][1]);
            if($st == 'ST'){

                 $phone = '';
                $email = '';
                if(isset($info['PER'][$i])){

                    if($info['PER'][$i][1] =='DC' && $info['PER'][$i][3] =='TE'){
                       
                        $phone = $this->check_usplit($info['PER'][$i][4]);
                       
                    }
                    if($info['PER'][$i][1] =='DC' && isset( $info['PER'][$i][5]) && $info['PER'][$i][5] =='EM'){
                        $email = $this->check_usplit($info['PER'][$i][6]);

                    }

                    if($info['PER'][$i][1] =='IC' && $info['PER'][$i][3] =='TE'){
                       
                        $phone = $this->check_usplit($info['PER'][$i][4]);
                       
                    }
                    if($info['PER'][$i][1] =='IC' && $info['PER'][$i][5] =='EM'){
                        
                        $email = $this->check_usplit($info['PER'][$i][6]);

                    }

                }
                
                $to = array(
                    'name' =>$name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'phone' => $phone,
                    'email' => $email,
                    'country' => "United States",
                    'country_code' => $country_code
                );

              
            }

            if($info['N1'][$i][1] == 'SF'){

                $ship_from = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $info['N3'][$i][1],
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' => "United States",
                    'country_code' => $info['N4'][$i][3]
                );

            }

            if($info['N1'][$i][1] == 'BT'){

                $bill_to = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' => "United States",
                    'country_code' => $info['N4'][$i][3]
                );

            }

        }
        return array(
            'to' => $to,
            'from' => $ship_from,  'phone' => $phone,
            'email' => $email,
            'supplier' => $supplier
        );

    }

    public function address_off($n_info, $info){

        $ship_from = $to = $supplier = $bill_to = $store = null;

        foreach($n_info as $i=>$in){

            if($info['N1'][0][1]== 'SN'){
                if(is_array($info['N1'][0][2])){
                    $store = $info['N1'][0][2][0].'U'.$info['N1'][0][2][1];
                }else{
                    $store = $info['N1'][0][2];
                }
            }

            $name = $this->check_usplit($info['N1'][$i+1][2]);
            
            if(isset($info['N3'][$i])){

                $street  = $this->check_usplit($info['N3'][$i][1]);

                if(isset($info['N3'][$i][2])){
                    $street2 = $this->check_usplit($info['N3'][$i][2]);
                    
                }else{
                    $street2 ='';
                }

            }
            
            if(isset($info['N2'][$i]) && $info['N2'][$i] !=''){

                $aname = $this->check_usplit($info['N2'][$i][1]);

            }else{
                $aname = '';
            }

            if(isset($info['N4'][$i])){


                $city = $this->check_usplit($info['N4'][$i][1]);
                $state = $this->check_usplit($info['N4'][$i][2]);

                $zip = $info['N4'][$i][3];

            }

            $su = $this->check_usplit($info['N1'][$i+1][1]);
            $country_code = $this->check_usplit($info['N4'][$i][4]);
            $country = '';
            if($country_code && $country_code =='US'){
                $country= "United States";
            }

            if($su =='SU'){

                $supplier = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' => $country,
                    'country_code' => $country_code
                );

            }

            $st = $this->check_usplit($info['N1'][$i+1][1]);
            if($st == 'ST'){

                $phone = '';
                $email = '';
                if(isset($info['PER'][$i])){

                    if($info['PER'][$i][1] =='DC' && $info['PER'][$i][3] =='TE'){
                       
                        $phone = $this->check_usplit($info['PER'][$i][4]);
                       
                    }
                    if($info['PER'][$i][1] =='DC' && isset( $info['PER'][$i][5]) && $info['PER'][$i][5] =='EM'){
                        $email = $this->check_usplit($info['PER'][$i][6]);

                    }

                    if($info['PER'][$i][1] =='IC' && $info['PER'][$i][3] =='TE'){
                       
                        $phone = $this->check_usplit($info['PER'][$i][4]);
                       
                    }
                    if($info['PER'][$i][1] =='IC' && $info['PER'][$i][5] =='EM'){
                        $email = $this->check_usplit($info['PER'][$i][6]);

                    }

                }
                
                $to = array(
                    'name' =>$name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'phone' => $phone,
                    'email' => $email,
                    'country' =>  $country,
                    'country_code' => $country_code
                );

            }

            if($info['N1'][$i+1][1] == 'SF'){

                $ship_from = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' =>  $country,
                    'country_code' => $country_code
                );

            }

            if($info['N1'][$i+1][1] == 'BT'){

                $bill_to = array(
                    'name' => $name,
                    'aname' =>  $aname,
                    'street' => $street,
                    'street2' => $street2,
                    'city' => $city,
                    'state' => $state,
                    'zip' => $zip,
                    'country' =>  $country,
                    'country_code' => $country_code
                );

            }

        }
        return array(
            'to' => $to,
            'from' => $ship_from,
            'bill_to' => $bill_to,
            'store' => $store,
            'supplier' => $supplier
        );

    }

    public function po_to_address($x12_array = null){

        $info = $x12_array;
        if($info){

            $check_sn = $this->check_sn($info['N1']);

            if($check_sn){
               
                $n_info = $info['N3'];
                return $this->address_off($n_info, $info);

            }else{
                $n_info = $info['N1'];
                return $this->address($n_info, $info);
            }
        }

        return false;
       

    }
    public function po_to_items($x12_array = null){

        $infos = $x12_array;

        if($infos){

            print_r($infos);

          
            $result = null;
            $pid_en = 0;
            $desc = '';
            
            if(isset($infos['PID'])){
                $pid_en = 1;
            }
            foreach($infos['P01'] as $i=>$info){

                if($pid_en){
                    if(is_array($infos['PID'][$i][5])){
                        $desc = implode('U', $infos['PID'][$i][5]);
                    }else{
                        $desc = $infos['PID'][$i][5];
                    }
                }
                if(is_array($info[7])){
                    $vpn = implode('U',  $info[7]);
                }else{
                    $vpn =  $info[7];
                }

                if(isset($info[9])){
                    if(is_array($info[9])){
                        $bpn =  implode('U',  $info[9]);
                
                    }else{
                        $bpn = $info[9];
                    }
                }else{
                    $bpn = '';
                }
                

            
                $result[] = array(
                    'quantity' => $info[2],
                    'vendor_part_number' => $vpn,
                    'buyer_part_number' => $bpn,
                    'name' => $desc,
                    'description' => $desc,
                    'price' => $info[4],
                    'unit' => $info[3]

                );
            }
            return $result;
        }
        return false;

    }
    



}