<?php

namespace App\Services\MyEdi;

class Segment997 extends MySegment{

    public function __construct(){}
    
    public function test(){
        echo '997';
        
    }

    public function ak1($data = null){

        //functional group header
        $def = array(
            'functional_code_identifier' =>  '00', //GS01
            'group_control_number' => '0',
          
        
        );

        $functional_code_identifier = 'CD';
        $group_control_number = '0';

        if($data){
            $functional_code_identifier =  $data['functional_code_identifier'];
            $group_control_number =  $data['group_control_number'];
           
        }

        return array(
            0 =>'AK1',
            1 => $functional_code_identifier,
            2 => $group_control_number
          
        );

    }

    public function ak2($data = null){

        //transaction set response header
        $def = array(
            'transaction_set_code_identifier' =>  '000', //ST01
            'transaction_set_control_number' => '0000',
          
        
        );

        $transaction_set_code_identifier = '812';
        $transaction_set_control_number = '0000';

        if($data){
            $transaction_set_code_identifier =  $data['transaction_set_code_identifier'];
            $transaction_set_control_number =  $data['transaction_set_control_number'];
           
        }

        return array(
            0 =>'AK2',
            1 => $transaction_set_code_identifier,
            2 => $transaction_set_control_number
          
        );

    }

    public function ak3($data){
        //data segment note

    }

    public function ak4($data){
        //data element note
    }

    public function ak5($data){
        //transaction set response trailer

        //transaction set response header
        $def = array(
            'transaction_set_ack_code' =>  'A' //A,E,R          
        
        );

        $transaction_set_ack_code = 'A';
       

        if($data){
            $transaction_set_ack_code =  $data['transaction_set_ack_code'];
           
           
        }

        return array(
            0 =>'AK5',
            1 => $transaction_set_ack_code 
         
          
        );

    }

    public function ak9($data = null){

        //transaction set response header
        $def = array(
            'ack_code' =>  'A', //A, E, R
            'number_of_transaction_sets' => '1',
            'number_of_received_transaction_sets' => '1',
            'number_of_accepted_transaction_sets' => '1',
          
        
        );

        $ack_code = 'A';
        $number_of_transaction_sets = '1';
        $number_of_received_transaction_sets = '1';
        $datanumber_of_accepted_transaction_sets = '1';

        if($data){
          
            $ack_code = $data['transaction_set_code_identifier'];
            $number_of_transaction_sets = $data['number_of_transaction_sets'];
            $number_of_received_transaction_sets = $data['number_of_received_transaction_sets'];
            $number_of_accepted_transaction_sets = $data['number_of_accepted_transaction_sets'];
            
        }

        return array(
            0 =>'AK9',
            1 => $ack_code,
            2 => $number_of_transaction_sets,
            3 => $number_of_received_transaction_sets,
            4 => $number_of_accepted_transaction_sets 
          
        );

    }



   

}
