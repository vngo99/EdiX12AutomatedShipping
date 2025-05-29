<?php

namespace App\Services\MyEdi;
use Uhin\X12Parser\EDI\X12;
use Uhin\X12Parser\Parser\X12Parser;
use Uhin\X12Parser\EDI\Segments\Segment;
use Uhin\X12Parser\EDI\Segments\ISA;
use Uhin\X12Parser\EDI\Segments\GS;
use Uhin\X12Parser\EDI\Segments\ST;
use Uhin\X12Parser\EDI\Segments\HL;
use Uhin\X12Parser\Serializer\X12Serializer;
use App\Services\MyEdi\MySegment;
use App\Services\MyEdi\ProcessSegment;
use App\Services\MyEdi\EdiTranslate;
use App\Models\X12Inbound;
use App\Models\Vendor;
use App\Models\PurchaseOrder;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;
use App\Services\MyFile\MyFile;
use App\Models\X12Outbound;

class MyEdi{

    private $segment;
    public $process;
    public $translate;

    public function __construct(MySegment $seg, ProcessSegment $process, EdiTranslate $trans){
        $this->segment = $seg;
        $this->process = $process;
        $this->translate = $trans;
    }

    public function show_config_segments(){
        echo '<pre/>';
        print_r($this->process->test());
    }

    public function get_vendor_id($vendor, $test = 0){

            switch($vendor){
                case 'aap':

                    if($test){
                        return array(
                            'isa' => array(
                                    'qualifier' =>'01',
                                    'id' =>'007941529000000'
                            ),
                            'gs'=>array(
                                'qualifier'  =>'01',
                                'id' =>'007941529000000'
                            ),
                          
                        );

                    }else{
                        return array(
                            'isa' => array(
                                    'qualifier'  =>'01',
                                    'id' =>'007941529000000'
                            ),
                            'gs'=>array(
                                'qualifier'  =>'01',
                                'id' =>'007941529000000'
                            ),
                          
                        );
                    }
                    
            
                    break;
                case 'turn5':
                    return array(
                        'isa' => array(
                            'qualifier'  =>'zz',
                                'id' =>'DICTURN5'
                        ),
                        'gs'=>array(
                            'qualifier' =>'zz',
                            'id' =>'DICTURN5'
                        )
                       
                    );

                    break;
                default:

                    return array(
                        'isa' => array(
                            'qualifier'  =>'zz',
                            'id' =>'ma000312000'
                        ),
                        'gs'=>array(
                            'qualifier'  =>'zz',
                            'id' =>'ma000312000'
                        )
                    );
                    break;
            }

    }

    public function config_from_file($path){

        $x12 = $this->parse($path);
        return $this->process->set_x12($x12);

    }

    public function get_ss_id($test=false){

        if($test){
            return array(
                'isa' => array(
                    'quantifier' =>'ZZ',
                    'id' =>'ma0003120000000'
                ),
                'gs'=>array(
                    'quantifier' =>'ZZ',
                    'id' =>'ma0003120000000'
                )
            );
        }
        return array(
            'isa' => array(
                'quantifier' =>'ZZ',
                'id' =>'ma8883120000000'
            ),
            'gs'=>array(
                'quantifier' =>'ZZ',
                'id' =>'ma8883120000000'
            )
        );

    }

    public function order_total(){
        return $this->process->items_total();
    }

    public function template($segment, $vendor='turn5', $data = null){

        $segment = strtolower($segment);
        $vendor_id = $this->get_vendor_id($vendor);
        $ss_id = $this->get_ss_id();
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));


        $date = $dt->format('Ymd');
        $isa_date = $dt->format('ymd');
        $gs_date = $dt->format('Ymd');
        $time = $dt->format('Hi');
        

        if($data){
            $date = $data['date'];
            $time = $data['time'];
        }

        switch($segment){


            case 'ge':

                //transaction set header
                //855 turn5 must use
                $entry = array(
                    'seg' => 'GE',
                    'number_of_transaction_set'=>'1',
                    'group_control_number'=>'3152',
                    


                );

                $number_of_transaction_set = 1;

                if($data){
                    $number_of_transaction_set = $data['$number_of_transaction_set'];
                }

                return array(
                    'GE',
                    $number_of_transaction_set,
                    '1'
                );

                break;
            case 'p01':
                //baseline item data
                //855 ack included
                $entry = array(
                    'seg'=>'P01',
                    'blank1'=>'',
                    'quantity_ordered' => 1,
                    'unit'=>'EA',
                    'unit_price'=>'1.00',
                    'blank1'=>'',
                    'product_id_qualifer'=>'1234',
                    'product_service_id' => '20150129',
                    'product_id_qualifer1'=>'1234',
                    'product_service_id1' => '20150129',
                    
                );

                return array(
                    'P01',
                    '',
                    1,
                    'EA',
                    '1.00',
                    '',
                    '',
                    '',
                    '',

                    
                );
                break;
            case 'ack':
                //line item ack
                 //855 ack included
                $entry = array(
                    'seg'=>'ACK',
                    'status'=>'IA',
                    'quantity' => 1,
                    'unit'=>'EA',
                    
                );

                return array(
                    'ACK',
                    'IA',
                    1,
                    'EA',
                
                );
                break;
         
            case 'beg':
                //beginning segment for purchase
                $entry = array(
                    'seg'=>'BEG',
                    'purpose_code'=>'00',
                    'purpose_code_type'=>'DS',
                    'purchase_order_number' => '12345',
                    'blank' => '',
                    'date' => '20200921'
                    
                    
                );

                return Array(
                        0 => 'BEG',
                        1 => '00',
                        2 => 'DS',
                        3 => '1857989',
                        4 => '',
                        5 => '20200921'
                    );
                break;

            case 'po1':Route::get('/855', function () {

                echo '<pre/>';
                $edi = app(MyEdi::class);
                $d = $edi->generate_855();
            
                print_r($d);
            
                
            });

                //baselisen item data
    
                $entry = array(
                    'seg'=>'PO1',
                    'quantity'=>1,
                    'unit' => 'EA',

                    
                );

                $quantity = 1;
                $unit = 'EA';
                $unit_price = '12.00';
                $product_qualifer_vendor ='VP';
                $product_vendor_service_id = '111111';
                $product_qualifer_buyer = 'BP';
                $product_buyer_service_id = '222222';
                
                if($data){
                    $quantity = $data['quantity'];
                    $unit = $data['unit'];
                    $unit_price = $data['unit_price'];
                    $product_qualifer_vendor = $data['product_qualifer_vendor'];
                    $product_vendor_service_id = $data['product_vendor_service_id'];
                    $product_qualifer_buyer = $data['product_qualifer_buyer'];
                    $product_buyer_service_id = $data['product_buyer_service_i'];
                }

                return array(
                    
                    0 => 'PO1',
                    1 => '',
                    2 => $quantity,
                    3 => $unit,
                    4 => $unit_price,
                    5 => '',
                    6 => $product_qualifer_vendor,
                    7 => $product_vendor_service_id,
                    8 => $product_qualifer_buyerr,
                    9 => $product_buyer_service_id

                
                );
                    break;

            case 'n1':

                $entry = array(
                    'seg'=>'N1',
                    'code'=>1,
                    'name' => '0001',
                    'qualifer'=>1,
                    'description'=>1,

                    
                );
                $code = 1;
                $name = 1;
                $qualifer = 1;
                $description = 1;
                
                if($data){
                    $code = $data['code'];
                    $name = $data['name'];
                    $qualifer = $data['qualifier'];
                    $description = $data['description'];
                }


                return array(
                    
                    0 => 'N1',
                    1 => $code,
                    2 => $name,
                    3 => $qualifer,
                    4 => $description,
                
                );
                break;

            case 'n3':

                $entry = array(
                    'seg'=>'N3',
                    'address'=>1,
                    'address1' => '0001',
                    
                    
                );
                $address = 1;
                $address1 = 1;
              
                
                if($data){
                    $address = $data['address'];
                    $address1 = $data['address1'];
                   
                }


                return array(
                    
                    0 => 'N3',
                    1 => $address,
                    2 => $address1,
                   
                
                );
                break;

            case 'n4':

                $entry = array(
                    'seg'=>'N4',
                    'city' => '0001',
                    'state_province'=>1,
                    'postal_code'=>1,
                    'country_code'=>1,

                    
                );


                $state_province = 'CA';
                $city = 'LaMirada';
                $postal_code = '92006';
                $country_code = 'US';
                
                if($data){
                    
                    $state_province = $data['state_provicnc'];
                    $city = $data['city'];
                    $postal_code = $data['postal_code'];
                    $country_code = $data['county_code'];
                }


                return array(
                    
                    0 => 'N4',
                    1 => $city,
                    2 => $state_province,
                    3 => $postal_code,
                    4 => $country_code
                
                );
                break;


            case 'per':

                $entry = array(
                    'seg'=>'PER',
                    'contact_code' => '0001',
                    'name'=>1,
                    'communication_number_qualifier '=>1,
                    'communication_number'=>1,

                    
                );


                $contact_code = 'CA';
                $name = 'LaMirada';
                $communication_number_qualifier = '92006';
                $communication_number = 'US';
                
                if($data){
                    
                    $contact_code = $data['contact_code'];
                    $name = $data['state_provice'];
                    $communication_number_qualifier = $data['communication_number_qualifier'];
                    $communication_number = $data['communication_number'];
                }


                return array(
                    
                    0 => 'PER',
                    1 => $contact_code,
                    2 => $name,
                    3 => $communication_number_qualifier,
                    4 => $communication_number
                
                );
                break;
            //810
            case 'big':
        
                $entry = array(
                    'seg'=>'DTM',
                    'quantifer'=>1,
                    'date' => '0001',

                    
                );

                $invoice_number = '01';
                $invoice_type = 'CN';
                
                if($data){
                    $date = $data['date'];
                    $invoice_number = $data['invoice_number'];
                    $invoice_type =$data['invoice_typ'];

                }


                return array(
                    
                    0 => 'BIG',
                    1 => $date,
                    2 => $invoice_number,
                    3 => '',
                    4 => '',
                    5 => '',
                    6 => '',
                    7 => $invoice_type,
                    8 => '',
                    9 => '',
                    10 => '',

                
                );
                break;
    
            default:
                break;

        }
    }

    public function segment(array $data_element){

        return new Segment($data_element);
    }

    public function segment_obj($func, $data = null){

        try{

            $segment_data = $this->segment->$func($data);

            if(!$segment_data){
                return false;

            }

            
        }catch(\Exception $e){
            throw new \Exception($func .' failed');
        }
     
        return new Segment($segment_data);

    }

    public function segment_from_po($type, $data, $vendor){

         switch($type){
             case 'isa':

                    if($vendor == 'turn5'){
                        return new ISA($data, '~', '*', 'U', '>');
                    }
                    if($vendor == 'aap'){
                        return new ISA($data, '~', '*', 'U', '<');
                    }
                   
                    return new ISA($data, '~', '*', 'U', '>');
                    
                    
                break;
            case 'gs':
                    return new GS($data);
                break;
            case 'st':
                return new ST($data);
            break;
            default:
                return false;
                break;

         }

    }

    public function segment_general($type, $data, $vendor){

        switch($type){
            case 'isa':
                if($vendor == 'turn5'){
                    return new ISA($data, '~', '*', 'U', '>');
                }
                if($vendor == 'aap'){
                    return new ISA($data, '~', '*', 'U', '<');
                }
               break;
           case 'gs':
                   return new GS($data);
               break;
           case 'st':
               return new ST($data);
           break;
           default:
               return false;
               break;

        }

    }

    public function create_isa_segment($vendor='turn5', $data = null){

        $isa_data = null;
    
        if($data){

            $vendor_id = $this->get_vendor_id($vendor);
            $ss_id = $this->get_ss_id();
            $date = $data['date'];
            $time = $data['time'];
            $interchange_control_version_number = $data['interchange_control_version_number'];
            $interchange_control_number = $data['interchange_control_number']; 
            $pos2 = $data['pos2'];
            $pos3 = $data['pos3'];

            $isa_data = array(
                'pos2' => $pos2,
                'pos3' => $pos3,
                'interchange_id_qualifer_sender'=>$ss_id['isa']['quantifier'],
                'interchange_sender_id'=>$ss_id['isa']['id'],
                'interchange_id_qualifer_receiver'=>$vendor_id['isa']['quantifier'],
                'interchange_receiver_id'=>$vendor_id['isa']['id'],
                'date' => $date,
                'time'=>$time,
                'repetition_separator' =>'U',
                'interchange_control_version_number'=> $interchange_control_version_number,
                'interchange_control_number'=> $interchange_control_number,
                'ack_requested'=>'0',
                'usage_indicator'=>'P',
                'sub_repetition' =>'>'
            );
    
        }

        $isa = $this->segment->isa($isa_data);
        return new ISA($isa, '~', '*', 'U', '>');
    }

    public function create_gs_segment($vendor='turn5', $data = null){


        $gs_data = null;
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));

        $date = $dt->format('Ymd');
        $time = $dt->format('Hi');

        $function_id_code  = 'PR';
        $group_control_number ='89';
        $responsible_agency_code = 'X';
        $version_code = '004010';
        $interchange_sender_id ='DICTURN5';
        $interchange_receiver_id ='DICTURN599';

        if($data){

            $vendor_id = $this->get_vendor_id($vendor);
            $ss_id = $this->get_ss_id();

            $date = $data['date'];
            $time = $data['time'];
            $interchange_sender_id = $ss_id['isa']['id'];
            $interchange_receiver_id = $vendor_id['isa']['id'];
            $group_control_number =$data['group_control_number'];
            $responsible_agency_code = $data['responsible_agency_code'];
            $version_code = $data['version_code'];
            $function_id_code = $data['function_id_code'];

            $gs_data = array(
                'seg' => 'GS',
                'function_id_code' => $function_id_code,
                'interchange_sender_id'=> $interchange_sender_id,
                'interchange_receiver_id'=> $interchange_receiver_id,
                'date' => $date,
                'time'=> $time,
                'group_control_number' => $group_control_number,
                'responsible_agency_code' => $responsible_agency_code,
                'version_code' => $version_code
              
            );
    
        }

       
        $gs = $this->segment->gs($gs_data);
        return new GS($gs);
    }


    public function create_st_segment($st_data=null){

        $st = $this->segment->st($st_data);
        return new ST($st);
    }

    public function create_hl_segment($hl_data, $func='turn5_hl'){
        $hl = $this->segment->$func($hl_data);
        return new HL($hl);
    }

    public function create_segment($seg){

        try{

            $seg_data = $this->template($seg);
            return $this->segment($seg_data);
          
        }catch(Exception $e){

            echo  "failed to create $seg segment";

        }

    }


    public function main_segments($inbound_id){

        $success = 0;
        $message = '';
        $rdata = null;
        $vendor = null;
        $sender = null;

        $seg = array();
    
        $in = X12Inbound::where('id',$inbound_id)->firstorFail();

        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                
                    
                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }

                $x12_obj = $this->parse_raw($in->raw);

                $this->process->set_x12($x12_obj);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $isa_date = $dt->format('ymd');
                $gs_date = $dt->format('Ymd');
                $time = $dt->format('Hi');
                
                $config = config('edi');
                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
                
                $gs_data_ack = $this->process->gs_general_return_ack($sender);
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);

                $st_data_ack = $this->process->st_po_ack($sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);

                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;

                $rdata = $x12;
                $success = 1;
                $message = 'main segement generated..';
                

            }catch(\Exception $e){
            
                $message =  'Caught exception: main segments: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'vendor' =>$vendor, 'sender'=>$sender, 'data'=>$rdata);

            }


            
        }else{
            $message = 'no inbound data found..';
        }


        return array('success'=>$success, 'response'=>$message, 'vendor' =>$vendor, 'sender'=>$sender,'data'=>$rdata);
    }

    public function parse($path='files/810.txt'){

        $file_path = storage_path($path);
        $rawX12 = file_get_contents($file_path);
        $parser = new X12Parser($rawX12);
        $x12 = $parser->parse();
        return $x12;

    }

    public function to_raw($x12){

        if($x12){
            $serializer = new X12Serializer($x12);
            $raw_x12 = $serializer->serialize();
            if($raw_x12){
                return  $raw_x12;

            }
            return false;
        }
        return false;
       
    }

    public function generate($segment_type){

        //st
        echo '<pre/>';
       
        switch($segment_type){
     
        
            case 'REF':
            case 'CTT':
            case 'AMT':
            case 'SE':
            case 'BAK':
            case 'GE':
            case 'IEA':
            case 'DTM':
            case 'PO1':
            case 'N1':
            case 'N3':
            case 'N4':
            case 'PER':
            case 'BIG':
                
                $seg = $this->create_segment(strtolower($segment_type));
                
                break;
            default:
                break;

        }

        //print_r($seg);
        return $seg;

    }

    public function generate_n($data = null){
        //n1,n3,n4,per

        $n1_segment = $this->generate('N1', $data);
        $n3_segment = $this->generate('N3', $data);
        $n4_segment = $this->generate('N4', $data);
        $per_segment = $this->generate('PER', $data); 

        return array(
            'n1' => $n1_segment,
            'n3' => $n3_segment,
            'n4' => $n4_segment,
            'per' => $per_segment
        );


    }

    public function po_to_po_ack($x12_file='aap_850_change_both.txt'){

       $x12_file='turn5_850_mult_exa.txt';
       //$x12_file='aap_850_change_both.txt';
        echo '<pre/>';
        $x12 = $this->parse('files/'.$x12_file);

        //print_r($x12);
        $this->process->set_x12($x12);

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $isa_date = $dt->format('ymd');
        $gs_date = $dt->format('Ymd');
        $time = $dt->format('Hi');

        //$item = $this->process->items();
        //$this->process->items_total();
       
        //determine single or multiple PO
        $number_of_orders = $this->process->number_of_orders();
        $seg = array();
       
        $isa_data = $this->process->isa();
        $isa_data_ack = $this->process->isa_po_ack();
        $isa_segment = $this->segment_from_po('isa', $isa_data_ack);
        $gs_data = $this->process->gs();
        $gs_data_ack = $this->process->gs_po_ack();
        $gs_data_ack[4] = $gs_date;
        $gs_segment = $this->segment_from_po('gs', $gs_data_ack);
        $st_data = $this->process->st();
        $st_data_ack = $this->process->st_po_ack();
        $st_segment = $this->segment_from_po('st', $st_data_ack);
        $bak_data_ack = $this->process->bak_po_ack();
       
        $bak_segment = $this->segment($bak_data_ack );
        array_push($seg, $bak_segment);


        $ref_data_ack = $this->process->ref_po_ack();
        if($ref_data_ack ){
            $ref_segment = $this->segment($ref_data_ack );
            array_push($seg, $ref_segment);
        }
       
        $dtm_segment = $this->segment_obj('dtm');//default shipped
        if( $dtm_segment){
            array_push($seg, $dtm_segment);
        }

        $ctt_data = array(
            'quantifier' => $number_of_orders
        );

        $ctt_segment = $this->segment_obj('ctt', $ctt_data);
        if($ctt_segment){
            array_push($seg, $ctt_segment);
        }
       

        $amt_data = array(
            'amount_qualifier_code' => 'TT',
            'total_transaction_amount' => $this->process->items_total()
        );
        
        $amt_segment =  $this->segment_obj('amt', $amt_data);

        if($amt_segment){
            array_push($seg, $amt_segment);
        }


        $iea_data_ack = $this->process->iea_po_ack();
        $iea_segment = $this->segment($iea_data_ack );
        $ge_data_ack = $this->process->ge_po_ack();
        $ge_segment = $this->segment($ge_data_ack );

        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;

        if(count($seg) >0){
            foreach($seg as $i=>$s){
                $x12->ISA[0]->GS[0]->ST[0]->properties[$i] = $s;

            }

        }

        $se_data_ack = $this->process->se_po_ack();
        $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
        $se_segment = $this->segment($se_data_ack);
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
       
        print_r($x12);
        $serializer = new X12Serializer($x12);
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    }

    public function generate_855_single($data = null){

        $control_number ='';//control number has to match
        $number_segement ='';//total segment st,se and inbetween
        // matching ref02 and BEG from PO see corresponding segments
        $isa_segment = $this->create_isa_segment();
        $gs_segment = $this->create_gs_segment();

        $st_segment = $this->create_st_segment();

       
        $bak_segment = $this->segment_obj('bak');
        $ref_segment = $this->segment_obj('ref');
        $dtm_segment = $this->segment_obj('dtm');
        $ctt_segment = $this->segment_obj('ctt');

        $amt_segment =  $this->segment_obj('amt');

        $se_segment = $this->segment_obj('se');

        $iea_segment = $this->segment_obj('iea');
        $ge_segment = $this->segment_obj('ge');

        //x12
        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bak_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[1] = $ref_segment;

      
        $x12->ISA[0]->GS[0]->ST[0]->properties[2] = $dtm_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[3] = $ctt_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[4] = $amt_segment;
        echo '<pre/>';
        
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    
    }

    public function generate_855_mult($x12_file='turn5_850_mult_exa.txt'){

    
        echo '<pre/>';
        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);
        $this->process->test();

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));


        $date = $dt->format('Ymd');
        $isa_date = $dt->format('ymd');
        $gs_date = $dt->format('Ymd');
        $time = $dt->format('Hi');

        //$item = $this->process->items();
        //$this->process->items_total();
       
        //determine single or multiple PO
        $number_of_orders = $this->process->number_of_orders();

        $isa_data = $this->process->isa();
        $isa_data_ack = $this->process->isa_po_ack();
        $isa_segment = $this->segment_from_po('isa', $isa_data_ack);
        $gs_data = $this->process->gs();
        $gs_data_ack = $this->process->gs_po_ack();

        $gs_data_ack[4] = $gs_date;
        $gs_segment = $this->segment_from_po('gs', $gs_data_ack);
        $st_data = $this->process->st();
        $st_data_ack = $this->process->st_po_ack();
        $st_segment = $this->segment_from_po('st', $st_data_ack);


        $bak_data_ack = $this->process->bak_po_ack();
        $bak_segment = $this->segment($bak_data_ack );
        $ref_data_ack = $this->process->ref_po_ack();
        $ref_segment = $this->segment($ref_data_ack );
        $dtm_segment = $this->segment_obj('dtm');//default shipped

        $ctt_data = array(
            'quantifier' => $number_of_orders
        );

        $ctt_segment = $this->segment_obj('ctt', $ctt_data);

        $amt_data = array(
            'amount_qualifier_code' => 'TT',
            'total_transaction_amount' => $this->process->items_total()
        );
        
        $amt_segment =  $this->segment_obj('amt', $amt_data);

        $iea_data_ack = $this->process->iea_po_ack();
        $iea_segment = $this->segment($iea_data_ack );

        $ge_data_ack = $this->process->ge_po_ack();
        $ge_segment = $this->segment($ge_data_ack );

        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bak_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[1] = $ref_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[2] = $dtm_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[3] = $ctt_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[4] = $amt_segment;

        $se_data_ack = $this->process->se_po_ack();
        $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
        $se_segment = $this->segment($se_data_ack);
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
       
        print_r($x12);
        $serializer = new X12Serializer($x12);
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    
    }

    public function po_to_856($x12_file='turn5_850_mult_exa.txt'){

        $x12_file='aap_850_change.txt';
        $x12_file='turn5_850_mult_exa.txt';
        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('His');
        $isa_date = $dt->format('ymd');
        $isa_time = $dt->format('Hi');
        $gs_date = $dt->format('Ymd');
        $gs_time = $dt->format('Hi');

        $purchase_order_number = $this->process->purchase_order_number();
        $seg = array();

        $isa_data = $this->process->isa();
        $isa_data_ack = $this->process->isa_po_ack();
        $isa_segment = $this->segment_from_po('isa', $isa_data_ack);

        $gs_data = $this->process->gs();
        $gs_data_ack = $this->process->gs_po_856();

        $gs_data_ack[4] = $gs_date;
        $gs_segment = $this->segment_from_po('gs', $gs_data_ack);


        $st_data = $this->process->st();
        $st_data_ack = $this->process->st_po_856();
        $st_segment = $this->segment_from_po('st', $st_data_ack);

        $bsn_data = array(
            'transaction_purpose_code'=>'00',
            'shipment_id'=>'00', //tracking number?
        );

        $bsn_segment = $this->segment_obj('bsn', $bsn_data);

        $iea_data_ack = $this->process->iea_po_ack();
        $iea_segment = $this->segment($iea_data_ack );

        $ge_data_ack = $this->process->ge_po_ack();
        $ge_segment = $this->segment($ge_data_ack );

        //shipment

        $hl_data1 = array(
            'id_number' => 1,
            'parent_id_number' =>'0',
            'code' => 'S'
        );
        $hl_segment_1 = $this->create_hl_segment($hl_data1);


        //order
        $hl_data2 = array(
            'id_number' => 2,
            'parent_id_number' =>'1',
            'code' =>'O'
        );

        $hl_segment_2 = $this->create_hl_segment($hl_data2);

        $prf_data = array(
            'po_number' => $purchase_order_number
          
        );

        $prf_segment = $this->segment_obj('prf', $prf_data);
        $hl_segment_2->properties[0] = $prf_segment;


        //weight and unit
        $td1_segment = $this->segment_obj('td1');

        if($td1_segment){
            array_push($seg, $td1_segment);
        }

        $ref_data_ack = $this->process->ref_po_ack();
        if($ref_data_ack ){
            $ref_segment = $this->segment($ref_data_ack ); $seg = array();
            array_push($seg, $ref_segment);
        }

        foreach($seg as $i=>$s){
            $hl_segment_1->properties[$i] = $s;
        }

        $hl_segment_1->HL[0] =  $hl_segment_2;
     
        //package
        $hl_data3 = array(
            'id_number' => 3,
            'parent_id_number' =>'2',
            'code' => 'P'
        );
            
        $hl_segment_3 = $this->create_hl_segment($hl_data3);
        $hl_segment_2->HL[0] =  $hl_segment_3;


        $td5_po_data = $this->process->td5_po_ack();

        $routing = 'STANDARD SHIPPING';
        if($td5_po_data ){
            $routing =  $td5_po_data[5];
        }
        $service_code = 'G2';
        $td5_data = array(
            'routing' => $routing,
            'service_code' => $service_code
        );

        $td5_segment = $this->segment_obj('td5', $td5_data);

        $man_data = array(
            'mark_number_qualifier' => 'CP',  //carrier package 
            'mark_number' => '1', //UPC ID
            
        );
        $man_segment = $this->segment_obj('man', $man_data);

        $dtm_data = array(
            'quantifier' => '011',  //shipped
            'date' => $date,
            
        );

        $dtm_segment = $this->segment_obj('dtm', $dtm_data);
        $hl_segment_3->properties[0] = $td1_segment;
        $hl_segment_3->properties[1] = $td5_segment;
        $hl_segment_3->properties[2] = $man_segment;
        $hl_segment_3->properties[3] = $dtm_segment;

        
       
        
        $lins = $this->process->po_to_lin_856();
        if($lins){

            foreach($lins as $index =>$lin){

                $hl_data_item = array(
                    'id_number' => $index+4,
                    'parent_id_number' =>'3',
                    'code' => 'I'
                );
        
                $hl_segment = $this->create_hl_segment($hl_data_item );

                $lin_segment = $this->segment_obj('lin', $lin['lin']);
                $sn1_segment = $this->segment_obj('sn1',$lin['sn']);
               
                array_push($hl_segment->properties,$lin_segment);
                array_push($hl_segment->properties,$sn1_segment);

                $hl_segment_3->HL[$index] =  $hl_segment;

            }

        }

        $se_data_ack = $this->process->se_po_ack();
        $se_data_ack[1] = count($hl_segment_3->HL)*3 +13;
        $se_segment = $this->segment($se_data_ack);

        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

        $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;
       
        echo '<pre/>';
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    }

    public function generate_856_single($data = null){

        //ship notice
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('His');

        $po = '1857989';

        $control_number ='000000089';//control number has to match
        $number_segement ='';//total segment st,se and inbetween
        // matching ref02 and BEG from PO see corresponding segments
        $st_data = array(
            'seg' => 'ST',
            'code'=>'856',
            'code_number'=>$control_number
        
        );

        $isa_date = $dt->format('ymd');
        $isa_time = $dt->format('Hi');
        $gs_time = $dt->format('Hi');


        $isa_data = array(
            'pos2' => '          ',
            'pos3' => '          ',
            'date' => $isa_date,
            'time' => $isa_time,
            'repetition_separator' =>'U',
            'interchange_control_version_number'=>'00401',
            'interchange_control_number' =>  $control_number,
            'ack_requested'=>'0',
            'usage_indicator'=>'P',
            'sub_repetition' =>'>'
        );
      
        $isa_segment = $this->create_isa_segment('turn5', $isa_data);

        $gs_date = $dt->format('Ymd');
        $gs_data = array(
            'function_id_code'=>'SH',
            'interchange_sender_id'=>'DICTURN5',
            'interchange_receiver_id'=>'DICTURN599',
            'date' =>  $gs_date,
            'time'=>$gs_time,
            'group_control_number' => $control_number,
            'responsible_agency_code'=>'X',
            'version_code'=>'004010',
        );

        $gs_segment = $this->create_gs_segment('turn5',$gs_data);

        $st_segment = $this->create_st_segment($st_data);

        $bsn_data = array(
            'transaction_purpose_code'=>'00',
            'shipment_id'=>'00',
        );

        $bsn_segment = $this->segment_obj('bsn', $bsn_data);

        $td1_segment = $this->segment_obj('td1');
        $ref_segment = $this->segment_obj('ref');


        $se_data = array(
            'number_of_included_segment' => 16,
            'transaction_set_control_number' =>$control_number
            );

        $se_segment = $this->segment_obj('se', $se_data);

        $iea_data = array(
            'number_of_included_functional_groups' =>1,
            'group_control_number' => $control_number
        );

        $iea_segment = $this->segment_obj('iea', $iea_data);


        $ge_data = array(
            'number_of_transaction_sets_included' =>1,
            'group_control_number' => $control_number,
        );
        $ge_segment = $this->segment_obj('ge', $ge_data);

        $hl_data1 = array(
            'id_number' => 1,
            'parent_id_number' =>'0',
            'code' => 'S'
        );
        $hl_segment_1 = $this->create_hl_segment($hl_data1);

        $hl_data2 = array(
            'id_number' => 2,
            'parent_id_number' =>'1',
            'code' =>'O'
        );

        $hl_segment_2 = $this->create_hl_segment($hl_data2);

        $prf_data = array(
            'po_number' => $po,
        );

        $prf_segment = $this->segment_obj('prf', $prf_data);
        $hl_segment_2->properties[0] = $prf_segment;

        $hl_segment_1->HL[0] =  $hl_segment_2;
        $hl_segment_1->properties[0] = $td1_segment;
        $hl_segment_1->properties[1] = $ref_segment;

        $hl_data3 = array(
            'id_number' => 3,
            'parent_id_number' =>'2',
            'code' => 'P'
        );
            
        $hl_segment_3 = $this->create_hl_segment($hl_data3);
        $hl_segment_2->HL[0] =  $hl_segment_3;

        $td1_segment = $this->segment_obj('td1');

        $routing = 'STANDARD SHIPPING';
        $service_code = 'G2';

        $td5_data = array(
            'routing' => $routing,
            'service_code' => $service_code
        );

        $td5_segment = $this->segment_obj('td5', $td5_data);

        $man_data = array(
            'mark_number_qualifier' => 'CP',  //shipped
            'mark_number' => '1',
            
        );


        $man_segment = $this->segment_obj('man', $man_data);

        $dtm_data = array(
            'quantifier' => '011',  //shipped
            'date' => $date,
            
        );

        $dtm_segment = $this->segment_obj('dtm', $dtm_data);
        $hl_segment_3->properties[0] = $td1_segment;
        $hl_segment_3->properties[1] = $td5_segment;
        $hl_segment_3->properties[2] = $man_segment;
        $hl_segment_3->properties[3] = $dtm_segment;


        $hl_data4 = array(
            'id_number' => 4,
            'parent_id_number' =>'3',
            'code' => 'I'
        );

        $hl_segment_4 = $this->create_hl_segment($hl_data4);

        $assigned_id = '1';//850 PO101
        $product_service_vendor_id_quantifier = 'VP'; //vendor part number
        $product_service_vendor_id = '1';
        $product_service_buyer_id_quantifier = 'BP'; //buyer part number
        $product_service_buyer_id = '1';


        $lin_data = array(
            'assigned_id'=> $assigned_id,
            'product_service_vendor_id_quantifier' => 'VP', //vendor part number
            'product_service_vendor_id' => '19401A', //850 p0107
            'product_service_buyer_id_quantifier' => 'BP', //buyer part number
            'product_service_buyer_id' => 'S112693' //850 p0109

        );
       

        $lin_segment = $this->segment_obj('lin', $lin_data);

        $sn1_data = array(
            'number_unit_shipped' => 1,
            'unit' => 'EA'
        );

        $sn1_segment = $this->segment_obj('sn1',$sn1_data);
        $hl_segment_3->HL[0] =  $hl_segment_4;
        $hl_segment_4->properties[0] = $lin_segment;
        $hl_segment_4->properties[1] = $sn1_segment;
       

        //x12
        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

        $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;
        //$x12->ISA[0]->GS[0]->ST[0]->properties[1] = $ref_segment;

        echo '<pre/>';
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    
    }

    public function po_to_810_di($x12_file='turn5_850_mult_exa.txt'){


        $x12_file='aap_850_change.txt';

        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('His');
        $isa_date = $dt->format('ymd');
        $isa_time = $dt->format('Hi');
        $gs_date = $dt->format('Ymd');
        $gs_time = $dt->format('Hi');

        $purchase_order_number = $this->process->purchase_order_number();
        $invoice_number = '000010';
        $invoice_type = 'DI';
        $number_of_orders = $this->process->number_of_orders();
        
        $seg = array();


        $isa_data = $this->process->isa();
        $isa_data_ack = $this->process->isa_po_ack();
        $isa_segment = $this->segment_from_po('isa', $isa_data_ack);

        $gs_data = $this->process->gs();
        $gs_data_ack = $this->process->gs_po_810();

        $gs_data_ack[4] = $gs_date;
        $gs_segment = $this->segment_from_po('gs', $gs_data_ack);


        $st_data = $this->process->st();
        $st_data_ack = $this->process->st_po_810();
        $st_segment = $this->segment_from_po('st', $st_data_ack);

        $iea_data_ack = $this->process->iea_po_ack();
        $iea_segment = $this->segment($iea_data_ack );

        $ge_data_ack = $this->process->ge_po_ack();
        $ge_segment = $this->segment($ge_data_ack );

       

       

        $big_data = array(
            'invoice_number' => $invoice_number,
            'invoice_type' => $invoice_type,
            'date' => $date,
          
        );

        $big_segment = $this->segment_obj('big', $big_data);

        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
      


        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);

        $ref_data_ack = $this->process->ref_po_ack();
        if($ref_data_ack){
            $ref_segment = $this->segment($ref_data_ack );
            array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$ref_segment);
        }
        
        $n1s = $this->process->n1_po_810();
        if($n1s){
            foreach($n1s as $n1){

                $n1_segment = $this->segment_obj('n1', $n1);

                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$n1_segment);

                $itd_data = array(
                    'type_code' => '01',  //basic
                    'date_code' => '3',   //invoice
                    'due_date' => $date, //ccyynmmdd
                    'net_days' => '1',    //number of days until invoice is due
                    'description' => '0'
                );

                $itd_segment = $this->segment_obj('itd', $itd_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$itd_segment);
            }

        }

        $it1_data = $this->process->po_to_id1_810();
        if($it1_data){
            foreach($it1_data as $it1){
                $it1_segment = $this->segment_obj('it1', $it1);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);

                $ref_data = $this->process->ref_po_ack();
                $ref_data[1] = 'PO';

                $ref_segment = $this->segment($ref_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ref_segment);

            }

        }

        $tds_data = array(
            'amount' => str_replace('.', "", $this->process->items_total())
           
        );

        $tds_segment = $this->segment_obj('tds', $tds_data);

        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

        $ctt_data = array(
            'quantifier' => $number_of_orders 
    
        );

        $ctt_segment = $this->segment_obj('ctt', $ctt_data);
        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $ctt_segment);

        $se_data_ack = $this->process->se_po_ack();
        $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
        $se_segment = $this->segment($se_data_ack);
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

        echo '<pre/>';
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;
      




    }
    public function po_to_810_cn($x12_file='turn5_850_mult_exa.txt'){

        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('His');
        $isa_date = $dt->format('ymd');
        $isa_time = $dt->format('Hi');
        $gs_date = $dt->format('Ymd');
        $gs_time = $dt->format('Hi');

        $purchase_order_number = $this->process->purchase_order_number();
        $invoice_number = '000010';
        $invoice_type = 'CN';
        $number_of_orders = $this->process->number_of_orders();


        $isa_data = $this->process->isa();
        $isa_data_ack = $this->process->isa_po_ack();
        $isa_segment = $this->segment_from_po('isa', $isa_data_ack);

        $gs_data = $this->process->gs();
        $gs_data_ack = $this->process->gs_po_810();

        $gs_data_ack[4] = $gs_date;
        $gs_segment = $this->segment_from_po('gs', $gs_data_ack);


        $st_data = $this->process->st();
        $st_data_ack = $this->process->st_po_810();
        $st_segment = $this->segment_from_po('st', $st_data_ack);

        $iea_data_ack = $this->process->iea_po_ack();
        $iea_segment = $this->segment($iea_data_ack );

        $ge_data_ack = $this->process->ge_po_ack();
        $ge_segment = $this->segment($ge_data_ack );

       

       

        $big_data = array(
            'invoice_number' => $invoice_number,
            'invoice_type' => $invoice_type,
            'date' => $date,
          
        );

        $big_segment = $this->segment_obj('big', $big_data);

        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
      


        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);

        $ref_data_ack = $this->process->ref_po_ack();
        $ref_segment = $this->segment($ref_data_ack );

        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$ref_segment);

        $n1s = $this->process->n1_po_810();
        if($n1s){
            foreach($n1s as $n1){

                $n1_segment = $this->segment_obj('n1', $n1);

                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$n1_segment);

                $itd_data = array(
                    'type_code' => '01',  //basic
                    'date_code' => '3',   //invoice
                    'due_date' => $date, //ccyynmmdd
                    'net_days' => '1',    //number of days until invoice is due
                    'description' => '0'
                );

                $itd_segment = $this->segment_obj('itd', $itd_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$itd_segment);
            }

        }

        $it1_data = $this->process->po_to_id1_810();
        if($it1_data){
            foreach($it1_data as $it1){
                $it1_segment = $this->segment_obj('it1', $it1);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);

                $ref_data = $this->process->ref_po_ack();
                $ref_data[1] = 'PO';

                $ref_segment = $this->segment($ref_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ref_segment);

            }

        }

        $tds_data = array(
            'amount' => str_replace('.', "", $this->process->items_total())
           
        );

        $tds_segment = $this->segment_obj('tds', $tds_data);

        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

        $ctt_data = array(
            'quantifier' => $number_of_orders 
    
        );

        $ctt_segment = $this->segment_obj('ctt', $ctt_data);
        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $ctt_segment);

        $se_data_ack = $this->process->se_po_ack();
        $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
        $se_segment = $this->segment($se_data_ack);
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

        echo '<pre/>';
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;
      




    }

    public function generate_810_single($data = null){

        //invoice

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        $time = $dt->format('His');

        $control_number ='000000089';//control number has to match
        $number_segment = 11;//total segment st,se and inbetween
        $invoice_type = 'DI';
        $invoice_number = '000010';
      
        
        $isa_date = $dt->format('ymd');
        $isa_time = $dt->format('Hi');
        $gs_time = $dt->format('Hi');


        $isa_data = array(
            'pos2' => '          ',
            'pos3' => '          ',
            'date' => $isa_date,
            'time' => $isa_time,
            'repetition_separator' =>'U',
            'interchange_control_version_number'=>'00401',
            'interchange_control_number' =>  $control_number,
            'ack_requested'=>'0',
            'usage_indicator'=>'P',
            'sub_repetition' =>'>'
        );
      
        $isa_segment = $this->create_isa_segment('turn5', $isa_data);

        $gs_date = $dt->format('Ymd');
        $gs_data = array(
            'function_id_code'=>'IN',
            'interchange_sender_id'=>'DICTURN5',
            'interchange_receiver_id'=>'DICTURN599',
            'date' =>  $gs_date,
            'time'=>$gs_time,
            'group_control_number' => $control_number,
            'responsible_agency_code'=>'X',
            'version_code'=>'004010',
        );

        $gs_segment = $this->create_gs_segment('turn5',$gs_data);

        $st_data = array(
            'seg' => 'ST',
            'code'=>'810',
            'code_number'=> $control_number
        
        );

        $st_segment = $this->create_st_segment($st_data);

        $big_data = array(
            'invoice_number' => $invoice_number,
            'invoice_type' => $invoice_type,
            'date' => $date,
          
        );

        $big_segment = $this->segment_obj('big', $big_data);

        $ref_segment = $this->segment_obj('ref');

        $n1_data = array(
            'entity_identifier_code' => 'SU',  //Supplier/Mf  850 po n102
            'id_code_qualifier' => '92', //buyer's agent 850 po n101
            'name' => 'ROUGH COUNTRY',
            'id_code' => '435' //850 n104
        );

        $n1_segment = $this->segment_obj('n1', $n1_data);

        $itd_data = array(
            'type_code' => '01',  //basic
            'date_code' => '3',   //invoice
            'due_date' => $date, //ccyynmmdd
            'net_days' => '1',    //number of days until invoice is due
            'description' => '0'
        );

        $itd_segment = $this->segment_obj('itd', $itd_data);


        $it1_data = array(
            'quantity' =>  1, 
            'unit' => 'EA',   
            'price' => '157.47',
            'id_qualifer_buyer' => 'BP', //sellers part number
            'id_buyer' => 'S112693',
            'id_qualifer_seller' => 'VP', //buyer part number
            'id_seller' => '19401A',  //Po107
            'id_qualifier_source' => 'VS', //vendor supplemental number
            'id_source' => '0'
        );

        $it1_segment = $this->segment_obj('it1', $it1_data);

        $tds_data = array(
            'amount' => '15735'
           
        );

        $tds_segment = $this->segment_obj('tds', $tds_data);

        $sac_data = array(
            'indicator' =>  'A',  //allowance, c charge 
            'code' => 'G821',   //dropship
            'amount' => '12',
            'description' => '0'
        );

        $sac_segment = $this->segment_obj('sac', $sac_data);

        $ctt_data = array(
            'quantifier' => '1', //850 po cct01
           
           
        );

        $ctt_segment = $this->segment_obj('ctt', $ctt_data);


        $se_data = array(
                
                'number_of_included_segment' => $number_segment,
                'transaction_set_control_number' =>$control_number
                
                
            );

        $se_segment = $this->segment_obj('se', $se_data);

        $iea_data = array(
           
            'number_of_included_functional_groups' =>1,
            'group_control_number' => $control_number
        );

        $iea_segment = $this->segment_obj('iea', $iea_data);


        $ge_data = array(
           
            'number_of_transaction_sets_included' =>1,
            'group_control_number' => $control_number,

            

        );
        $ge_segment = $this->segment_obj('ge', $ge_data);

        //x12
        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $big_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[1] = $ref_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[2] = $n1_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[3] = $itd_segment;

        $x12->ISA[0]->GS[0]->ST[0]->properties[4] = $it1_segment;
        
        $refi_data = array(
            'ref_id_qualifier' => 'PO',
            'ref_id' => 435
        );


        $refi_segment = $this->segment_obj('ref', $refi_data);
        $x12->ISA[0]->GS[0]->ST[0]->properties[5] = $refi_segment;

        $x12->ISA[0]->GS[0]->ST[0]->properties[6] = $tds_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[7] = $sac_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[8] = $ctt_segment;


        echo '<pre/>';
        print_r($x12);
        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    
    }
   

    public function po_to_edi_file($x12){

        $serializer = new X12Serializer($x12);
                // Generate the raw X12 string
        $rawX12 = $serializer->serialize();
        print_r($rawX12);

    }


    public function generate_850(){

        $path = 'files/turn5_850.txt';
        $path = 'files/app_850_change.txt';
        $tmp = $this->parse($path);
       
        if($tmp){
            
            return $tmp;
            /*

                $serializer = new X12Serializer($tmp);

                

                // Generate the raw X12 string
                $rawX12 = $serializer->serialize();

                print_r($rawX12);
            */
        }
        return false;

    }

    public function generate_855(){

        $path = 'files/turn5_850ack.txt';
        $tmp = $this->parse($path);
       
        if($tmp){
            
            return $tmp;
            /*

                $serializer = new X12Serializer($tmp);

                

                // Generate the raw X12 string
                $rawX12 = $serializer->serialize();

                print_r($rawX12);
            */
        }
        return false;

    }

    public function generate_856(){

        $path = 'files/856_exa.txt';
        $tmp = $this->parse($path);

        print_r($tmp);
       
        if($tmp){
            
            return $tmp;
            /*

                $serializer = new X12Serializer($tmp);

                

                // Generate the raw X12 string
                $rawX12 = $serializer->serialize();

                print_r($rawX12);
            */
        }
        return false;

    }

    public function generate_810(){

        $path = 'files/810.txt';
        $tmp = $this->parse($path);

        print_r($tmp);
       
        if($tmp){
            
            return $tmp;
            /*

                $serializer = new X12Serializer($tmp);

                

                // Generate the raw X12 string
                $rawX12 = $serializer->serialize();

                print_r($rawX12);
            */
        }
        return false;

    }


    public function generate_850_multi(){

        $path = 'files/turn5_850_mult_exa.txt';
        $path = 'files/aap_850_change.txt';
        $tmp = $this->parse($path);

        print_r($tmp);
       
        if($tmp){
            
            return $tmp;
            /*

                $serializer = new X12Serializer($tmp);

                

                // Generate the raw X12 string
                $rawX12 = $serializer->serialize();

                print_r($rawX12);
            */
        }
        return false;

    }


    public function to_inbound($x12_file='turn5_850_mult_exa.txt'){

        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);
        $purchase_order_number = $this->process->purchase_order_number();

        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();


        $inbound = X12Inbound::firstOrCreate([
            'po_number' => $purchase_order_number,
            'vendor' => 'turn5',
            'raw' => $raw_x12
        ]);

        return $inbound;

    }

    public function parse_raw($raw){
        try{
            $parser = new X12Parser($raw);
            $x12 = $parser->parse();

        }catch(\Exception $e){
            throw new \Exception('failed to parse text.');
        }
        if($x12){
            return $x12;
        }
        return false;
    }

    public function parse_text($x12_file='turn5_850_mult_exa.txt'){

        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);
        $purchase_order_number = $this->process->purchase_order_number();

        $serializer = new X12Serializer($x12);
       
        // Generate the raw X12 string
        $raw_x12 = $serializer->serialize();
        return $raw_x12;

    }

    public function from_edi_db(){

        $rs= X12Inbound::where('id', 1)->get();
        if($rs){
            $edis = null;
            foreach ($rs as $r) {
                $edis[] = $this->parse_raw($r->raw);
            }
        }
        return $edis;

    }
    public function x12_order($x12_file){

        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);
        $items = $this->process->po_items_with_name();
        $allitems = $this->translate->po_to_items($items);
        $addr = $this->process->po_n1_group();
        $alladdr = $this->translate->po_to_address($addr);
        return array(
            'items' => $allitems,
            'address' => $alladdr
        );


    }

    public function x12_text_order($id){

        $success = 0;
        $message ='';
        $memo ='';
        $rdata = null;

        $rs= X12Inbound::where('id', $id)->firstorFail();
        if($rs){

            try{

                $x12 = $this->parse_raw($rs->raw);
                $this->process->set_x12($x12);
                $items = $this->process->po_items_with_name();
                $total = $this->order_total();
                $allitems = $this->translate->po_to_items($items);
                $addr = $this->process->po_n1_group();
                $alladdr = $this->translate->po_to_address($addr);

                $site = '';
                if(isset($alladdr['store'])){
                    $site = $alladdr['store'];
                }

                $memo .= "\r\nTransaction: Net60 \r\n";
                $memo .= "Site:".$site." \r\n";
                $memo .= "Purchase Order:".$rs->po_number." \r\n";

            }catch(\Exception $e){
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>$success,'response'=>$message,'data'=>$rdata);

            }

            $rdata =  array(
                'items' => $allitems,
                'address' => $alladdr,
                'total' =>$total,
                'vendor' => $rs->vendor,
                'po_number' =>$rs->po_number,
                'memo' => $memo
            );
            $success = 1;
            $message = "order data generated.";
            
        }else{
            $message ='no inbound data found..';
        }

        return array('success'=>$success,'response'=>$message,'data'=>$rdata);

    }

    public function turn5_po_ack_rej($inbound_id = 1, $type='RD'){

        return $this->po_ack($inbound_id, $type);

    }

    public function autoany_po_ack_delay($inbound_id = 1){

        return $this->autoany_po_ack($inbound_id, 'AC');

    }

    public function autoany_po_ack_rej($inbound_id = 1){

        return $this->autoany_po_ack($inbound_id, 'RJ');

    }

    public function autoany_po_ack($inbound_id = 1, $type='RJ'){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
        $se_count = 0;

        try{

            $x12_result = $this->main_segments($inbound_id);
            $se_count = 1;

            if($x12_result['success']){

                $x12 = $x12_result['data'];
                $vendor = $x12_result['vendor'];
                $sender = $x12_result['sender'];
                $number_of_orders = $this->process->number_of_orders_force();

                $bak_data_ack = $this->process->bak_po_ack($type, $vendor->set_purpose_code);
                $bak_segment = $this->segment($bak_data_ack);
                if($bak_segment){
                    array_push($seg, $bak_segment);
                }
            
                $ref_data_ack = $this->process->ref_po_ack();
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                if($type == 'AC'){
                    $icode = 'IB';
                }

                if($type == 'RJ'){
                    
                    $icode = 'IR';
                }

                $po = new PurchaseOrder();
                $items = $po->items_by_inbound($inbound_id);

                if($items){

                    $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                    $dt->modify('+3 months');
                    $eta = $dt->format('Ymd');

                    foreach($items as $i=>$item){
                        
                        $idata = array(
                            'line_number' =>$i+1,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'part_number' => $item->part_number
                        );


                        $po1_seg = $this->segment_obj('po1', $idata);

                        if( $po1_seg ){
                            array_push($seg,$po1_seg );
                        }


                        $idata['date'] =  $eta;
                        $idata['status_code'] = $icode;

                        if($item->status == 'oos'){

                            $idata['date'] =  $eta;
                            $idata['status_code'] = $icode;

                        }

                        $ack_seg = $this->segment_obj('ack', $idata);

                        if($ack_seg ){
                            array_push($seg,$ack_seg );
                        }

                    }

                }

                $ctt_data = array(
                    'quantifier' => $number_of_orders
                );
        
                $ctt_segment = $this->segment_obj('ctt', $ctt_data);

                if($ctt_segment){
                    array_push($seg, $ctt_segment);
                }
            
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );
            
                if(count($seg) >0){
                    foreach($seg as $i=>$s){
                        $x12->ISA[0]->GS[0]->ST[0]->properties[$i] = $s;
        
                    }
        
                }

                $se_data_ack = $this->process->se_po_ack($sender);
                $se_count++;
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) + $se_count;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

                $serializer = new X12Serializer($x12);
                if($serializer){
                    // Generate the raw X12 string
                    $rdata = $serializer->serialize();
                    if($rdata){
                        $success = 1;
                        $message = 'edi successfully generated..';
                    }else{
                        $message = 'no raw edi data.';
                    }
                   
                }else{
                    $message = 'serializer failed..';
                }


            }else{
                $message = 'no main segments..';
            }

            
        }catch(\Exception $e){
              
            $message =  'Caught exception: autoany ack rej:'.  $e->getMessage(). "\n";
            return array('success'=>0, 'response'=>$message, 'data'=>$rdata);

         }

        return array('success'=>$success,'response'=>$message,'data'=>$rdata);
   
    }

    public function po_ack($inbound_id = 1, $type='AC' ){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
       
        $in = X12Inbound::where('id',$inbound_id)->firstorFail();

        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                
                    
                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }

                $x12_obj = $this->parse_raw($in->raw);

                $this->process->set_x12($x12_obj);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $isa_date = $dt->format('ymd');
                $gs_date = $dt->format('Ymd');
                $time = $dt->format('Hi');
                
                $number_of_orders = $this->process->number_of_orders_force();

                $config = config('edi');
                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
                
                $gs_data_ack = $this->process->gs_general_return_ack($sender);
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);

                $st_data_ack = $this->process->st_po_ack($sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);


                
                $bak_data_ack = $this->process->bak_po_ack($type, $vendor->set_purpose_code);
                $bak_segment = $this->segment($bak_data_ack);
                if($bak_segment){
                    array_push($seg, $bak_segment);
                }
               
                $ref_data_ack = $this->process->ref_po_ack();
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                $dtm_segment = $this->segment_obj('dtm');//default shipped
                if( $dtm_segment){
                    array_push($seg, $dtm_segment);
                }
        
                $ctt_data = array(
                    'quantifier' => $number_of_orders
                );
        
                $ctt_segment = $this->segment_obj('ctt', $ctt_data);
                if($ctt_segment){
                    array_push($seg, $ctt_segment);
                }
            
                $amt_data = array(
                    'amount_qualifier_code' => 'TT',
                    'total_transaction_amount' => $this->process->items_total()
                );
                
                $amt_segment =  $this->segment_obj('amt', $amt_data);
        
                if($amt_segment){
                    array_push($seg, $amt_segment);
                }
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        
                if(count($seg) >0){
                    foreach($seg as $i=>$s){
                        $x12->ISA[0]->GS[0]->ST[0]->properties[$i] = $s;
        
                    }
        
                }
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
                

            }catch(\Exception $e){
              
               $message =  'Caught exception: '.  $e->getMessage(). "\n";
               return array('success'=>0, 'response'=>$message, 'data'=>$rdata);

            }

            $serializer = new X12Serializer($x12);
            if($serializer){
                // Generate the raw X12 string
                $rdata = $serializer->serialize();
                if($rdata){
                    $success = 1;
                    $message = 'edi successfully generated..';
                }else{
                    $message = 'no raw edi data.';
                }
               
            }else{
                $message = 'serializer failed..';
            }
          
            
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }

    public function turn5_po_ack_change($inbound_id = 1, $type='AC' ){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
       
        $in = X12Inbound::where('id',$inbound_id)->firstorFail();
        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }

                $x12_obj = $this->parse_raw($in->raw);

                $this->process->set_x12($x12_obj);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $isa_date = $dt->format('ymd');
                $gs_date = $dt->format('Ymd');
                $time = $dt->format('Hi');

                $number_of_orders = $this->process->number_of_orders_force();

                $config = config('edi');
                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
                
                $gs_data_ack = $this->process->gs_general_return_ack($sender);
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);

                $st_data_ack = $this->process->st_po_ack($sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);


                
                $bak_data_ack = $this->process->bak_po_ack($type, $vendor->set_purpose_code);
                $bak_segment = $this->segment($bak_data_ack);
                if($bak_segment){
                    array_push($seg, $bak_segment);
                }
               
                $ref_data_ack = $this->process->ref_po_ack();
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                $order =  PurchaseOrder::where('inbound_id',$inbound_id)->firstorFail();
               
                if($order){

                    $items = OrderItem::where('po_id',$order->id)->get();

                    if($items){

                     
                        foreach($items as $i=>$item){

                            $po1 = $this->process->turn5_po_to_po1_dtm_ack_855();

                            if($item->actual_quantity ==0){
                                $po1[$i]['po1']['quantity'] = $item->quantity;
                            }else{
                                $po1[$i]['po1']['quantity'] = $item->actual_quantity;
                            }
                           
                            $po1_seg = $this->segment_obj('turn5_po1', $po1[$i]['po1']);

                            if($po1_seg){
                                array_push($seg,$po1_seg );
                            }

                            $dtm_segment = $this->segment_obj('dtm');//default shipped
                            if( $dtm_segment){
                                array_push($seg, $dtm_segment);
                            }

                            $po1[$i]['ack']['code'] = 'IQ';

                            if($item->actual_quantity ==0){
                                $po1[$i]['ack']['quantity'] = $item->quantity;
                            }else{
                                $po1[$i]['ack']['quantity'] = $item->actual_quantity;
                            }
                           
                            if($item->status == 'oos'){

                                $po1[$i]['ack']['code'] = 'IQ';

                            }

                            $ack_seg[] = $this->segment_obj('turn5_ack',   $po1[$i]['ack']);

                           

                        }

                        $change_total = $this->process->items_total($items);

                    }else{
                        $change_total = $this->process->items_total();
                    }
                    

                }else{
                    $change_total = $this->process->items_total();
                }


                if($ack_seg && count($ack_seg) >0 ){

                    foreach($ack_seg as $ack){
                        array_push($seg,$ack );
                    }
                    
                }
        
                $ctt_data = array(
                    'quantifier' => $number_of_orders
                );
        
                $ctt_segment = $this->segment_obj('ctt', $ctt_data);
                if($ctt_segment){
                    array_push($seg, $ctt_segment);
                }
            
                $amt_data = array(
                    'amount_qualifier_code' => 'TT',
                    'total_transaction_amount' => $change_total
                );

                $amt_segment =  $this->segment_obj('amt', $amt_data);
        
                if($amt_segment){
                    array_push($seg, $amt_segment);
                }
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
        
                if(count($seg) >0){
                    foreach($seg as $i=>$s){
                        $x12->ISA[0]->GS[0]->ST[0]->properties[$i] = $s;
        
                    }
        
                }
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
                

            }catch(\Exception $e){
              
               $message =  'Caught exception: '.  $e->getMessage(). "\n";
               return array('success'=>0, 'response'=>$message, 'data'=>$rdata);

            }

            $serializer = new X12Serializer($x12);
            if($serializer){
                // Generate the raw X12 string
                $rdata = $serializer->serialize();
                if($rdata){
                    $success = 1;
                    $message = 'edi successfully generated..';
                }else{
                    $message = 'no raw edi data.';
                }
               
            }else{
                $message = 'serializer failed..';
            }
          
            
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }


    public function po_invoice_notice($inbound_id = 1, $invoice_type = 'DI'){

       
        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
        
        $in= X12Inbound::where('id',$inbound_id)->first();
        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();

                    $dropship_fee = $vendor->dropship_fee;
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                    $dropship_fee = 10.00;
                }

                $x12 = $this->parse_raw($in->raw);
                $this->process->set_x12($x12);

                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();
                $oi = new OrderItem();
                $invoice_number = '100000';
                if($po){

                    if($po->order_id){
                        $invoice_number = $po->order_id;
                    }
                    $po_id = $po->id;
                    
                    $total_value = $oi->order_total($po->id);

                    if($total_value){
                       if($total_value['discount_price'] >0){
                        $item_total = $total_value['discount_price'];
                       }else{
                        $item_total = $total_value['price'];
                       }
                    }else{
                        $item_total =  $this->process->items_total();
                    }
                }else{
                    $item_total =  $this->process->items_total();
                }
 
                    
                $drop_fee = 0;
                $subtotal = $item_total;
                if( $item_total > 40){
                    $item_total += $dropship_fee;
                    $drop_fee = $dropship_fee;
                   
                }
              
                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
        
                $purchase_order_number = $this->process->purchase_order_number();

                $number_of_orders = $this->process->number_of_orders_force();
                
                $seg = array();
                $config =  config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
        
        
                $gs_data_ack = $this->process->gs_po_general_810($sender);
        
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);
        
        
                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('810', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );
        
                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                $big_name = $vendor->nick_name.'_big';

                $big_data = array(
                    'invoice_number' => $invoice_number,
                    'invoice_type' => $invoice_type,
                    'date' => $date,1,
                    'po_number' => $purchase_order_number
                
                );
        
                $big_segment = $this->segment_obj($big_name, $big_data);
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;

                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);
                
                $ref_data_ack = $this->process->ref_po_ack();

                if($ref_data_ack){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$ref_segment);
                }

                $n1_name = $vendor->nick_name.'_n1_po_810';

                $n1s = $this->process->$n1_name();

        
                if($n1s){

                    $n1_seg_name = $vendor->nick_name.'_n1';
                    foreach($n1s as $n1){
        
                        $n1_segment = $this->segment_obj($n1_seg_name, $n1);
        
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$n1_segment);
        
                        $itd_data = array(
                            'type_code' => '01',  //basic
                            'date_code' => '3',   //invoice
                            'due_date' => $date, //ccyynmmdd
                            'net_days' => '1',    //number of days until invoice is due
                            'description' => '0'
                        );
        
                        $itd_segment = $this->segment_obj('itd', $itd_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$itd_segment);
                    }
        
                }


                $id1_name = $vendor->nick_name.'_po_to_id1_810';
        
                $it1_data = $this->process->$id1_name();

                if($it1_data){

                    foreach($it1_data as &$it1){

                        $i = OrderItem::where('po_id', $po_id)->where('part_number',$it1['id_seller'])->first();
                        if($i){

                            
                            if($i->discount_price){
                                $it1['price'] = $i->discount_price;
                            }
                           
                        }
                    
                    }


                    $it1_name = $vendor->nick_name.'_it1';

                    foreach($it1_data as $i=>&$it1a){

                       
                        $it1a['line_number'] = $i+1;
                        $it1a['part_number'] =  $it1a['id_seller'];
                       
                        $it1_segment = $this->segment_obj($it1_name, $it1a);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);
        
                        $ref_data = $this->process->ref_po_ack();
                        $ref_data[1] = 'PO';
                        $ref_data[2] = $purchase_order_number;
        
                        $ref_segment = $this->segment($ref_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ref_segment);
        
                    }
        
                }


                $tds_name = $vendor->nick_name.'_tds';
        
                $tds_data = array(
                    'amount' => $item_total,
                    'subtotal' =>$subtotal
                );
        
                $tds_segment = $this->segment_obj($tds_name, $tds_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

                if($drop_fee){

                    $sac_data = array(
                        'indicator' =>  'C',  //allowance, c charge 
                        'code' => 'G821',   //dropship
                        'amount' =>  $drop_fee,
                        'description' => 'Dropship Fee'
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                /*
                not include vendor discount for turn5, the po price already included it

                if($vendor->discount_per >0.0){

                    $discount_per = $vendor->discount_per/100;

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }


                    if($vendor->marketing_discount_per >0.0){

                        $marketing_discount_per = $vendor->marketing_discount_per/100;
                        $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;

                        $d_item_total =  $c_total -  $marketing_discount_amt;
                        $discount_amt = ($d_item_total/(1-$discount_per)) - $d_item_total;


                    }else{
                      
                        $discount_amt = ($item_total/(1-$discount_per)) - $item_total;
                    }

                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $discount_amt,
                        'description' => 'Account Discount',
                        'discount_per' =>  $discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                */

                if($vendor->marketing_discount_per >0.0){

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }

                   
                    $marketing_discount_per = $vendor->marketing_discount_per/100;
                    $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;
                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $marketing_discount_amt,
                        'description' => 'Marketing Discount',
                        'discount_per' =>  $marketing_discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                $ctt_data = array(
                    'quantifier' => $number_of_orders 
            
                );
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
        
                $serializer = new X12Serializer($x12);
                if($serializer){
                    // Generate the raw X12 string
                    $rdata = $serializer->serialize();
                    if($rdata){
                        $success = 1;
                        $message = 'edi successfully generated..';
                    }
                
                }else{
                    $message = 'serializer failed..';
                }
          
                
            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }

    public function turn5_po_invoice_notice($inbound_id = 1, $invoice_type = 'DI'){

       
        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
        
        $in= X12Inbound::where('id',$inbound_id)->first();
        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();

                    $dropship_fee = $vendor->dropship_fee;
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                    $dropship_fee = 10.00;
                }

                $x12 = $this->parse_raw($in->raw);
                $this->process->set_x12($x12);

                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();
                $oi = new OrderItem();
                $invoice_number = '100000';
                if($po){

                    if($po->order_id){
                        $invoice_number = $po->order_id;
                    }
                    $po_id = $po->id;
                    
                    $total_value = $oi->order_total($po->id);

                    if($total_value){
                       if($total_value['discount_price'] >0){
                        $item_total = $total_value['discount_price'];
                       }else{
                        $item_total = $total_value['price'];
                       }
                    }else{
                        $item_total =  $this->process->items_total();
                    }
                }else{
                    $item_total =  $this->process->items_total();
                }
 
                    
                $drop_fee = 0;
                $subtotal = $item_total;
                if( $item_total > 40){
                    $item_total += $dropship_fee;
                    $drop_fee = $dropship_fee;
                   
                }
              
                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
        
                $purchase_order_number = $this->process->purchase_order_number();

                $number_of_orders = $this->process->number_of_orders_force();
                
                $seg = array();
                $config =  config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
        
        
                $gs_data_ack = $this->process->gs_po_general_810($sender);
        
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);
        
        
                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('810', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );
        
                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                $big_name = $vendor->nick_name.'_big';

                $big_data = array(
                    'invoice_number' => $invoice_number,
                    'invoice_type' => $invoice_type,
                    'date' => $date,1,
                    'po_number' => $purchase_order_number
                
                );
        
                $big_segment = $this->segment_obj($big_name, $big_data);
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;

                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);
                
                $ref_data_ack = $this->process->ref_po_ack();

                if($ref_data_ack){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$ref_segment);
                }

                $n1_name = $vendor->nick_name.'_n1_po_810';

                $n1s = $this->process->$n1_name();

        
                if($n1s){

                    $n1_seg_name = $vendor->nick_name.'_n1';
                    foreach($n1s as $n1){
        
                        $n1_segment = $this->segment_obj($n1_seg_name, $n1);
        
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$n1_segment);
        
                        $itd_data = array(
                            'type_code' => '01',  //basic
                            'date_code' => '3',   //invoice
                            'due_date' => $date, //ccyynmmdd
                            'net_days' => '1',    //number of days until invoice is due
                            'description' => '0'
                        );
        
                        $itd_segment = $this->segment_obj('itd', $itd_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$itd_segment);
                    }
        
                }


                $id1_name = $vendor->nick_name.'_po_to_id1_810';
        
                $it1_data = $this->process->$id1_name();

                if($it1_data){

                    foreach($it1_data as &$it1){

                        $i = OrderItem::where('po_id', $po_id)->where('part_number',$it1['id_seller'])->first();
                        if($i){

                            
                            if($i->discount_price){
                                $it1['price'] = $i->discount_price;
                            }
                           
                        }
                    
                    }


                    $it1_name = $vendor->nick_name.'_it1';

                    foreach($it1_data as $i=>&$it1a){

                       
                        $it1a['line_number'] = $i+1;
                        $it1a['part_number'] =  $it1a['id_seller'];
                       
                        $it1_segment = $this->segment_obj($it1_name, $it1a);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);
        
                        $ref_data = $this->process->ref_po_ack();
                        $ref_data[1] = 'PO';
                        $ref_data[2] = $purchase_order_number;
        
                        $ref_segment = $this->segment($ref_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ref_segment);
        
                    }
        
                }


                $tds_name = $vendor->nick_name.'_tds';
        
                $tds_data = array(
                    'amount' => $item_total,
                    'subtotal' =>$subtotal
                );
        
                $tds_segment = $this->segment_obj($tds_name, $tds_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

                if($drop_fee){

                    $sac_data = array(
                        'indicator' =>  'C',  //allowance, c charge 
                        'code' => 'G821',   //dropship
                        'amount' =>  $drop_fee,
                        'description' => 'Dropship Fee'
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                /*
                not include vendor discount for turn5, the po price already included it

                if($vendor->discount_per >0.0){

                    $discount_per = $vendor->discount_per/100;

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }


                    if($vendor->marketing_discount_per >0.0){

                        $marketing_discount_per = $vendor->marketing_discount_per/100;
                        $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;

                        $d_item_total =  $c_total -  $marketing_discount_amt;
                        $discount_amt = ($d_item_total/(1-$discount_per)) - $d_item_total;


                    }else{
                      
                        $discount_amt = ($item_total/(1-$discount_per)) - $item_total;
                    }

                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $discount_amt,
                        'description' => 'Account Discount',
                        'discount_per' =>  $discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                */
 
                if($vendor->marketing_discount_per >0.0){

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }

                   
                    $marketing_discount_per = $vendor->marketing_discount_per/100;
                    $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;
                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $marketing_discount_amt,
                        'description' => 'Marketing Discount',
                        'discount_per' =>  $marketing_discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                $ctt_data = array(
                    'quantifier' => $number_of_orders 
            
                );
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
        
                $serializer = new X12Serializer($x12);
                if($serializer){
                    // Generate the raw X12 string
                    $rdata = $serializer->serialize();
                    if($rdata){
                        $success = 1;
                        $message = 'edi successfully generated..';
                    }
                
                }else{
                    $message = 'serializer failed..';
                }
          
                
            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }
    public function autoany_po_invoice_notice($inbound_id = 1, $invoice_type = 'DI'){

       
        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();
        $se_count = 0;
        
        $in= X12Inbound::where('id',$inbound_id)->first();
        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();

                    $dropship_fee = $vendor->dropship_fee;
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                    $dropship_fee = 10.00;
                }

                $x12 = $this->parse_raw($in->raw);
                $this->process->set_x12($x12);

                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();
                $oi = new OrderItem();
                $invoice_number = '100000';
                $discount_taken = false;
                if($po){

                    if($po->order_id){
                        $invoice_number = $po->order_id;
                    }
                    $po_id = $po->id;
                    
                    $total_value = $oi->order_total($po->id);
                    if($total_value){
                       if($total_value['discount_price'] >0){
                        $discount_taken = true;
                        $item_total = $total_value['discount_price'];
                       }else{
                        $item_total = $total_value['price'];
                       }
                    }else{
                        $item_total =  $this->process->items_total();
                    }
                }else{
                    $item_total =  $this->process->items_total();
                }
 
                    
                $drop_fee = 0;
                $subtotal = $item_total;

                if( $item_total > 40){
                    $item_total += $dropship_fee;
                    $drop_fee = $dropship_fee;
                   
                }
              
                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
        
                $purchase_order_number = $this->process->purchase_order_number();

                $number_of_orders = $this->process->number_of_orders_force();
                
                $seg = array();
                $config =  config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
        
        
                $gs_data_ack = $this->process->gs_po_general_810($sender);
        
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);
        
        
                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('810', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );
        
                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                $big_name = $vendor->nick_name.'_big';

                $big_data = array(
                    'invoice_number' => $invoice_number,
                    'invoice_type' => $invoice_type,
                    'date' => $date,1,
                    'po_number' => $purchase_order_number
            

                );
        
                $big_segment = $this->segment_obj($big_name, $big_data);
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
                $se_count ++;

                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);

                $id1_name = $vendor->nick_name.'_po_to_id1_810';
                $it1_data = $this->process->$id1_name();
                $new_sub_total = 0;
                if($it1_data){

                   

                    foreach($it1_data as &$it1){
                        $i = OrderItem::where('po_id', $po_id)->where('part_number',$it1['id_seller'])->first();

                        if($i){

                            if($i->discount_price == 0.00){

                                if($vendor->discount_per >0.0){

                                    $discount_per = $vendor->discount_per/100;
                                    $discount_amt =  $i->price - ($i->price * $discount_per);
                                    $it1['price'] =$i->price; 
                                    $it1['discount'] = ($i->price * $discount_per) * $i->quantity;
                                    $new_sub_total +=$discount_amt * $i->quantity;

            
                                }else{
                                    $it1['price'] = $i->price; 
                                    $it1['discount'] = 0;
                                }
                             
                            }else{
                                $it1['price'] = $i->price; 
                                $it1['discount'] = 0;
                            }
                           
                        }
                    
                    }

                    $it1_name = $vendor->nick_name.'_it1';
                    $sac_name = $vendor->nick_name.'_sac';
                    foreach($it1_data as $i=>&$it1a){

                        $it1a['line_number'] = $i+1;
                        $it1a['part_number'] =  $it1a['id_seller'];
                       
                        $it1_segment = $this->segment_obj($it1_name, $it1a);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);


                        /*

                        if($vendor->discount_per >0.0){

                            $sac_data = array(
                                'indicator' =>  'A',  //allowance, c charge 
                                'code' => 'C310',   //discount
                                'amount' =>   $it1a['discount'],
                                'description' => 'Account Discount',
                                'discount_per' =>  $vendor->discount_per,
                                'discount_quantifier' => 3
                            );
        
                            $sac_segment = $this->segment_obj($sac_name, $sac_data);
                            array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                        }

                        */

                        
        
                    
                    }
        
                }
            
                $tds_name = $vendor->nick_name.'_tds';

                if($new_sub_total >0.0){

                    $sub = $new_sub_total;

                }else{

                    $sub = $subtotal;

                }

                $total = $sub;
                if( $sub > 40){
                    $total += $dropship_fee;
                    $drop_fee = $dropship_fee;
                   
                }else{
                    $total = $sub;
                }
        
                $tds_data = array(
                    'amount' => $total,
                    'subtotal' =>$sub
                );
        
                $tds_segment = $this->segment_obj($tds_name, $tds_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

                $txi_name = $vendor->nick_name.'_txi';
        
                $txi_data = array(
                    'amount' => 0.00,
                    'code' =>'ST'
                );
        
                $txi_segment = $this->segment_obj($txi_name, $txi_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $txi_segment);


                $amt_name = $vendor->nick_name.'_amt';
        
                $amt_data = array(
                    'amount' =>  $drop_fee,
                    'code' =>'OH'
                );
        
                $amt_segment = $this->segment_obj($amt_name, $amt_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $amt_segment);

                /*

                if($vendor->discount_per >0.0){

                    $discount_per = $vendor->discount_per/100;

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }

                    echo "ctotal $c_total<br/>";


                    if($vendor->marketing_discount_per >0.0){

                        $marketing_discount_per = $vendor->marketing_discount_per/100;
                        $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;

                        $d_item_total =  $c_total -  $marketing_discount_amt;
                        $discount_amt = ($d_item_total/(1-$discount_per)) - $d_item_total;


                    }else{
                      
                        $discount_amt = $c_total * $discount_per;

                        echo "itemtotal: $item_total<br/>";
                        echo "dis: $discount_amt<br/>";
                    }

                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $discount_amt,
                        'description' => 'Account Discount',
                        'discount_per' =>  $vendor->discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj($sac_name, $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

            
                if($vendor->marketing_discount_per >0.0){

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }

                   
                    $marketing_discount_per = $vendor->marketing_discount_per/100;
                    $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;
                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $marketing_discount_amt,
                        'description' => 'Marketing Discount',
                        'discount_per' =>  $marketing_discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj($sac_name, $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }
                */
                $ctt_seg_name = $vendor->nick_name.'_ctt';

                $ctt_data = array(
                    'quantifier' => $number_of_orders
                );
        
                $ctt_segment = $this->segment_obj($ctt_seg_name, $ctt_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ctt_segment);
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_count++;
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +$se_count;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
        
                $serializer = new X12Serializer($x12);
                if($serializer){
                    // Generate the raw X12 string
                    $rdata = $serializer->serialize();
                    if($rdata){
                        $success = 1;
                        $message = 'edi successfully generated..';
                    }
                
                }else{
                    $message = 'serializer failed..';
                }
          
                
            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }

    public function po_shipment_notice($inbound_id){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();

        $in= X12Inbound::where('id',$inbound_id)->firstorFail();
        Log::channel('custom')->debug("inbound at shipment notice:");
        Log::channel('custom')->debug($in);
       
        if($in){

            $vendor = $in->vendor;
            try{
                $x12 = $this->parse_raw($in->raw);
           
                $this->process->set_x12($x12);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }


                $purchase_order_number = $this->process->purchase_order_number();
                $seg = array();

                $config = config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );
                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();

                $weight = 1;
                $weight_unit = 'LB';

                if($po){
                    if($po->shipped_date){

                        $sdt = new \DateTime($po->shipped_date, new \DateTimeZone('UTC'));
                        $sdate = $sdt->format('Ymd');
                        $ship_date = $sdate;
                    }else{
                        $ship_date = $date;
                    }
                    if($po->tracking_number){
                        $tracking_number = $po->tracking_number;
                    }else{
                        $tracking_number = '11111111111';
                    }

                    $po_ob = new PurchaseOrder();
                    $weight_value = $po_ob->weight($po->id);
                    if($weight_value){
                        $weight = $weight_value['weight'];
                        if($weight_value['weight_unit'] != 'lbs'){
                            $weight_unit = strtoupper($weight_value['weight_unit']);
                        }
                        
                    }

                }else{
                    $tracking_number = '11111111111';
                    $ship_date = $date;
                }

                $shipment_id = $tracking_number;
                $routing = 'STANDARD SHIPPING';
                $service_code = 'G2';
                $ship_quantifier = '011'; //shipped
                $mark_number_qualifier = 'CP';  //carrier package 
                $mark_number =$tracking_number;

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);

                $gs_data_ack = $this->process->gs_po_general_856($sender);
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);


                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('856', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);



                //need to set shipment id, use tracking number?+date?
                $bsn_data = array(
                    'transaction_purpose_code'=>'00',
                    'shipment_id'=> $shipment_id
                );
                $bsn_segment = $this->segment_obj('bsn', $bsn_data);

                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack);

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                //shipment

                $hl_data1 = array(
                    'id_number' => 1,
                    'parent_id_number' =>'0',
                    'code' => 'S'
                );
                $hl_segment_1 = $this->create_hl_segment($hl_data1);
            
                //order
                $hl_data2 = array(
                    'id_number' => 2,
                    'parent_id_number' =>'1',
                    'code' =>'O'
                );

                $hl_segment_2 = $this->create_hl_segment($hl_data2);

                $prf_data = array(
                    'po_number' => $purchase_order_number
                
                );

                $prf_segment = $this->segment_obj('prf', $prf_data);
                $hl_segment_2->properties[0] = $prf_segment;


                //weight and unit
                //need to assign weight from jobber, default 1

                $weight_data = array(
                    'weight' => $weight,
                    'unit' => $weight_unit
                );


                $td1_name = $vendor->nick_name.'_td1';

                $td1_segment = $this->segment_obj($td1_name, $weight_data);

                if($td1_segment){
                    array_push($seg, $td1_segment);
                }

                $ref_data_ack = $this->process->ref_po_ack();

               
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                foreach($seg as $i=>$s){
                    $hl_segment_1->properties[$i] = $s;
                }

                $hl_segment_1->HL[0] =  $hl_segment_2;
               
                //package
                $hl_data3 = array(
                    'id_number' => 3,
                    'parent_id_number' =>'2',
                    'code' => 'P'
                );
                    
                $hl_segment_3 = $this->create_hl_segment($hl_data3);
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
                $hl_segment_2->HL[0] =  $hl_segment_3;


                $td5_po_data = $this->process->td5_po_ack();
                $td5_name = $vendor->nick_name.'_td5';

                $td5_segment = $this->segment_obj($td5_name, $td5_po_data);


                $man_name = $vendor->nick_name.'_man';
            
                //use gtin for mark?
                $man_data = array(
                    'mark_number_qualifier' => $mark_number_qualifier,  //carrier package 
                    'mark_number' => $mark_number, //UPC ID
                    
                );

                $man_segment = $this->segment_obj($man_name, $man_data);

                //need shipdate
                $dtm_data = array(
                    'quantifier' => $ship_quantifier,
                    'date' => $ship_date,
                    
                );

                $dtm_segment = $this->segment_obj('dtm', $dtm_data);

                $h1_seg_3 = array();


                $refship_name = $vendor->nick_name.'_refship';

                if($td1_segment){
                    array_push($h1_seg_3 ,  $td1_segment);
                }
                if($td5_segment){
                    array_push($h1_seg_3 ,  $td5_segment);
                }
                if($man_segment){
                    array_push($h1_seg_3 ,  $man_segment);
                }else{

                    $refship_data = array(
                        'ref_id_qualifier' => 'CN',
                        'ref_id' => $tracking_number
                        
                    );


                    $refship_segment = $this->segment_obj($refship_name, $refship_data);
                    array_push($h1_seg_3 ,  $refship_segment);
                }
                if( $dtm_segment){
                    array_push($h1_seg_3 ,  $dtm_segment);
                }
                $hl_segment_3->properties = array();
                foreach($h1_seg_3 as $s){
                    array_push( $hl_segment_3->properties,  $s);
                    
                }

                $lin_name = $vendor->nick_name.'_po_to_lin_856';

                $lins = $this->process->$lin_name();

                //print_r($lins);
                if($lins){

                    foreach($lins as $index =>$lin){

                        $hl_data_item = array(
                            'id_number' => $index+4,
                            'parent_id_number' =>'3',
                            'code' => 'I'
                        );
                
                        $hl_segment = $this->create_hl_segment($hl_data_item );

                        $lin_seg_name = $vendor->nick_name.'_lin';

                        $lin_segment = $this->segment_obj($lin_seg_name, $lin['lin']);
                        $sn1_segment = $this->segment_obj('sn1',$lin['sn']);
                    
                        array_push($hl_segment->properties,$lin_segment);
                        array_push($hl_segment->properties,$sn1_segment);

                        $hl_segment_3->HL[$index] =  $hl_segment;

                    }

                }
             
                $se_data_ack = $this->process->se_po_ack($sender);

                $se_data_ack[1] = count($hl_segment_3->HL)*3 +13;
                $se_segment = $this->segment($se_data_ack);
                $x12 = new X12();
              
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
                $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
                $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;
             

            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

       
    }

    public function outbound($file_name, $edi_text, $vendor='test'){

        switch($vendor){

            case 'turn5':
                $path = 'Inbound/'.$file_name;
                $disk ='sftp.turn5';
                break;
            case 'aap':
                $path = 'Inbound/'.$file_name;
                $disk ='sftp.aapin';
              
                break;
            case 'test':
                $path = 'aaptest/'.$file_name;
                $disk ='sftp.home';
                break;
            default:
                $path = 'aaptest/'.$file_name;
                $disk ='sftp.home';
                break;

        }

        return  Storage::disk($disk)->put($path,$edi_text);

    }

    public function turn5_po_shipment_notice_change($inbound_id){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();

        $in= X12Inbound::where('id',$inbound_id)->firstorFail();
        Log::channel('custom')->debug("inbound at shipment notice:");
        Log::channel('custom')->debug($in);
       
        if($in){

            $vendor = $in->vendor;
            try{
                $x12 = $this->parse_raw($in->raw);
           
                $this->process->set_x12($x12);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }


                $purchase_order_number = $this->process->purchase_order_number();
                $seg = array();

                $config = config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );
                
                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();

                $weight = 1;
                $weight_unit = 'LB';

                if($po){
                    if($po->shipped_date){

                        $sdt = new \DateTime($po->shipped_date, new \DateTimeZone('UTC'));
                        $sdate = $sdt->format('Ymd');
                        $ship_date = $sdate;
                    }else{
                        $ship_date = $date;
                    }
                    if($po->tracking_number){
                        $tracking_number = $po->tracking_number;
                    }else{
                        $tracking_number = '11111111111';
                    }

                    $po_ob = new PurchaseOrder();
                    $weight_value = $po_ob->weight($po->id);
                    if($weight_value){
                        $weight = $weight_value['weight'];
                        if($weight_value['weight_unit'] != 'lbs'){
                            $weight_unit = strtoupper($weight_value['weight_unit']);
                        }
                        
                    }

                }else{
                    $tracking_number = '11111111111';
                    $ship_date = $date;
                }

                $shipment_id = $tracking_number;
                $routing = 'STANDARD SHIPPING';
                $service_code = 'G2';
                $ship_quantifier = '011'; //shipped
                $mark_number_qualifier = 'CP';  //carrier package 
                $mark_number =$tracking_number;

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);

                $gs_data_ack = $this->process->gs_po_general_856($sender);
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);


                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('856', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);


                //need to set shipment id, use tracking number?+date?
                $bsn_data = array(
                    'transaction_purpose_code'=>'00',
                    'shipment_id'=> $shipment_id
                );
                $bsn_segment = $this->segment_obj('bsn', $bsn_data);

                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack);

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                //shipment

                $hl_data1 = array(
                    'id_number' => 1,
                    'parent_id_number' =>'0',
                    'code' => 'S'
                );
                $hl_segment_1 = $this->create_hl_segment($hl_data1);
               

                //order
                $hl_data2 = array(
                    'id_number' => 2,
                    'parent_id_number' =>'1',
                    'code' =>'O'
                );

                $hl_segment_2 = $this->create_hl_segment($hl_data2);

                $prf_data = array(
                    'po_number' => $purchase_order_number
                
                );

                $prf_segment = $this->segment_obj('prf', $prf_data);
                $hl_segment_2->properties[0] = $prf_segment;


                //weight and unit
                //need to assign weight from jobber, default 1

                $weight_data = array(
                    'weight' => $weight,
                    'unit' => $weight_unit
                );
                $td1_segment = $this->segment_obj('td1', $weight_data);

                if($td1_segment){
                    array_push($seg, $td1_segment);
                }

                $ref_data_ack = $this->process->ref_po_ack();
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                foreach($seg as $i=>$s){
                    $hl_segment_1->properties[$i] = $s;
                }

                $hl_segment_1->HL[0] =  $hl_segment_2;
               
                //package
                $hl_data3 = array(
                    'id_number' => 3,
                    'parent_id_number' =>'2',
                    'code' => 'P'
                );
                    
                $hl_segment_3 = $this->create_hl_segment($hl_data3);
                $hl_segment_2->HL[0] =  $hl_segment_3;


                $td5_po_data = $this->process->td5_po_ack();

                if($td5_po_data ){
                    $routing =  $td5_po_data[5];
                }

                $td5_data = array(
                    'routing' => $routing,
                    'service_code' => $service_code
                );

                $td5_segment = $this->segment_obj('td5', $td5_data);
            
                //use gtin for mark?
                $man_data = array(
                    'mark_number_qualifier' => $mark_number_qualifier,  //carrier package 
                    'mark_number' => $mark_number, //UPC ID
                    
                );

                $man_segment = $this->segment_obj('man', $man_data);


                //need shipdate
                $dtm_data = array(
                    'quantifier' => $ship_quantifier,
                    'date' => $ship_date,
                    
                );

                $dtm_segment = $this->segment_obj('dtm', $dtm_data);
                $hl_segment_3->properties[0] = $td1_segment;
                $hl_segment_3->properties[1] = $td5_segment;
                $hl_segment_3->properties[2] = $man_segment;
                $hl_segment_3->properties[3] = $dtm_segment;

                $aitems = OrderItem::where('po_id',$po->id)->get();

                $lins = $this->process->po_to_lin_856($aitems);

                //print_r($lins);
                if($lins){

                    foreach($lins as $index =>$lin){

                        $hl_data_item = array(
                            'id_number' => $index+4,
                            'parent_id_number' =>'3',
                            'code' => 'I'
                        );
                
                        $hl_segment = $this->create_hl_segment($hl_data_item );

                        $lin_segment = $this->segment_obj('lin', $lin['lin']);
                        $sn1_segment = $this->segment_obj('sn1',$lin['sn']);
                    
                        array_push($hl_segment->properties,$lin_segment);
                        array_push($hl_segment->properties,$sn1_segment);

                        $hl_segment_3->HL[$index] =  $hl_segment;

                    }

                }
             
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($hl_segment_3->HL)*3 +13;
                $se_segment = $this->segment($se_data_ack);
                $x12 = new X12();
              
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
                $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
                $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;
             

            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

       
    }


    public function turn5_po_invoice_notice_change($inbound_id = 1, $invoice_type = 'DI'){

       
        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();

        $in= X12Inbound::where('id',$inbound_id)->first();
        if($in){

            $vendor = $in->vendor;
            try{

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }

                $x12 = $this->parse_raw($in->raw);
                $this->process->set_x12($x12);

                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();
                $oi = new OrderItem();
                $invoice_number = '000000';
                if($po){

                    if($po->order_id){
                        $invoice_number = $po->order_id;
                    }
                    $po_id = $po->id;
                    
                    $total_value = $oi->order_total($po->id);
                    if($total_value){
                       if($total_value['discount_price']){
                        $item_total = $total_value['discount_price'];
                       }else{
                        $item_total = $total_value['price'];
                       }
                    }else{
                        $item_total =  $this->process->items_total();
                    }

                }else{
                   $item_total =  $this->process->items_total();
                }
               

                $drop_fee = 0;
                if( $item_total > 40){
                    $item_total += 5.00;
                    $drop_fee = 5.00;
                   
                }
              
                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
        
                $purchase_order_number = $this->process->purchase_order_number();

                //turn5 order_id so make it for all
               
                
                $number_of_orders = $this->process->number_of_orders_force();
                
                $seg = array();
                $config =  config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);
        
        
                $gs_data_ack = $this->process->gs_po_general_810($sender);
        
                $gs_data_ack[4] = $gs_date;
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);
        
        
                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('810', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);
        
                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack );
        
                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );
        
                $big_data = array(
                    'invoice_number' => $invoice_number,
                    'invoice_type' => $invoice_type,
                    'date' => $date,
                
                );
        
                $big_segment = $this->segment_obj('big', $big_data);
        
                $x12 = new X12();
        
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
            
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$big_segment);
        
                $ref_data_ack = $this->process->ref_po_ack();
                if($ref_data_ack){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$ref_segment);
                }

                $n1s = $this->process->n1_po_810();

                if($n1s){
                    foreach($n1s as $n1){
        
                        $n1_segment = $this->segment_obj('n1', $n1);
        
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$n1_segment);
        
                        $itd_data = array(
                            'type_code' => '01',  //basic
                            'date_code' => '3',   //invoice
                            'due_date' => $date, //ccyynmmdd
                            'net_days' => '1',    //number of days until invoice is due
                            'description' => '0'
                        );
        
                        $itd_segment = $this->segment_obj('itd', $itd_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,$itd_segment);
                    }
        
                }


                $aitems = OrderItem::where('po_id',$po->id)->get();
                $it1_data = $this->process->po_to_id1_810($aitems);

                if($it1_data){

                    foreach($it1_data as &$it1){

                        $i = OrderItem::where('po_id', $po_id)->where('part_number',$it1['id_seller'])->first();
                        if($i){

                            
                            if($i->discount_price){
                                $it1['price'] = $i->discount_price;
                            }
                           
                        }
                    
                    }

                    foreach($it1_data as $it1a){

                        $it1_segment = $this->segment_obj('it1', $it1a);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $it1_segment);
        
                        $ref_data = $this->process->ref_po_ack();
                        $ref_data[1] = 'PO';
                        $ref_data[2] = $purchase_order_number;
        
                        $ref_segment = $this->segment($ref_data);
                        array_push($x12->ISA[0]->GS[0]->ST[0]->properties,  $ref_segment);
        
                    }
        
                }
        
                $tds_data = array(
                    'amount' => $item_total
                );
        
                $tds_segment = $this->segment_obj('tds', $tds_data);
        
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $tds_segment);

                if($drop_fee){

                    $sac_data = array(
                        'indicator' =>  'C',  //allowance, c charge 
                        'code' => 'G821',   //dropship
                        'amount' =>  $drop_fee,
                        'description' => 'Dropship Fee'
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                /*
                not include vendor discount for turn5, the po price already included it

                if($vendor->discount_per >0.0){

                    $discount_per = $vendor->discount_per/100;

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }


                    if($vendor->marketing_discount_per >0.0){

                        $marketing_discount_per = $vendor->marketing_discount_per/100;
                        $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;

                        $d_item_total =  $c_total -  $marketing_discount_amt;
                        $discount_amt = ($d_item_total/(1-$discount_per)) - $d_item_total;


                    }else{
                      
                        $discount_amt = ($item_total/(1-$discount_per)) - $item_total;
                    }

                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $discount_amt,
                        'description' => 'Account Discount',
                        'discount_per' =>  $discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                */
 
                if($vendor->marketing_discount_per >0.0){

                    if($drop_fee){
                        $c_total = $item_total - $drop_fee;
                    }else{
                        $c_total = $item_total;
                    }

                   
                    $marketing_discount_per = $vendor->marketing_discount_per/100;
                    $marketing_discount_amt = ($c_total/(1-$marketing_discount_per)) - $c_total;
                    $sac_data = array(
                        'indicator' =>  'A',  //allowance, c charge 
                        'code' => 'C310',   //discount
                        'amount' =>   $marketing_discount_amt,
                        'description' => 'Marketing Discount',
                        'discount_per' =>  $marketing_discount_per,
                        'discount_quantifier' => 3
                    );

                    $sac_segment = $this->segment_obj('sac', $sac_data);
                    array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $sac_segment);

                }

                $ctt_data = array(
                    'quantifier' => $number_of_orders 
            
                );
        
                $ctt_segment = $this->segment_obj('ctt', $ctt_data);
                array_push($x12->ISA[0]->GS[0]->ST[0]->properties, $ctt_segment);
        
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties) +2;
                $se_segment = $this->segment($se_data_ack);
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
        
                $serializer = new X12Serializer($x12);
                if($serializer){
                    // Generate the raw X12 string
                    $rdata = $serializer->serialize();
                    if($rdata){
                        $success = 1;
                        $message = 'edi successfully generated..';
                    }else{
                        $message = 'no raw edi data.';
                    }
                
                }else{
                    $message = 'serializer failed..';
                }
          
                
            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }
    public function generate_997($data = null){
        $x12_file='997.txt';
        $x12 = $this->parse('files/'.$x12_file);
        $this->process->set_x12($x12);
       
       
        $isa_data = $this->process->isa();
        $isa_data = $this->process->isa_general_return($data);
        $isa_segment = $this->segment_general('isa', $isa_data);

        $gs_data = $this->process->gs();
        $gs_data = $this->process->gs_general_return($data);
        $gs_data[1] = 'FA';
        $gs_segment = $this->segment_general('gs', $gs_data);


        $st_data = $this->process->st();
        $st_data[1] = '997';
        $st_segment = $this->segment_general('st', $st_data);


        $iea_data = $this->process->iea_general();
        $iea_segment = $this->segment($iea_data);


        $ge_data = $this->process->ge_general();
        $ge_segment = $this->segment($ge_data);


        $ak1_data = $this->process->prop_general('AK1');
        $ak1_segment = $this->segment($ak1_data);

        $ak2_data = $this->process->prop_general('AK2');
        $ak2_segment = $this->segment($ak2_data);

        $ak5_data = $this->process->prop_general('AK5');
        $ak5_segment = $this->segment($ak5_data);

        $ak9_data = $this->process->prop_general('AK9');
        $ak9_segment = $this->segment($ak9_data);


        $x12 = new X12();

        $x12->ISA[0] = $isa_segment;
        $x12->ISA[0]->IEA = $iea_segment;
        $x12->ISA[0]->GS[0] = $gs_segment;
        $x12->ISA[0]->GS[0]->GE = $ge_segment;
        $x12->ISA[0]->GS[0]->ST[0] = $st_segment;


        $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $ak1_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[1] = $ak2_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[2] = $ak5_segment;
        $x12->ISA[0]->GS[0]->ST[0]->properties[3] = $ak9_segment;

         // Generate the raw X12 string
         $raw_x12 = $serializer->serialize();

         if($raw_x12){
            return $raw_x12;
         }
         return false;


        

    }


    public function edi_ack($order, $debug = 0){

        $success = 0;
        $message = '';
        $rdata = null;

        $inbound_id = $order->inbound_id;
        $po_id = $order->id;


        try{
            $data = $this->po_ack($inbound_id);
            if($data['success']){

                $in = X12Inbound::where('id', $inbound_id)->firstorFail();
                if($in){
                    $vendor = $in->vendor;
                    $f = app(MyFile::class);
                    $file_name = 'SS'.$in->po_number.'-'.'855.x12';
                    $rdata = $raw = $data['data'];

                    if($debug){
                        $message = 'edi created but not sent or saved..';
                        $vendor ='test';
                        //return array('success'=>1, 'response'=>$message, 'data'=>$rdata);

                    }

                    $s = $f->send($vendor, $file_name, $raw);
                    if($s){
            
                        $message = "Vendor:$vendor  filename:$file_name";
                        $in->status = 'PO Ack';
                        $in->save();

                        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                        $date = $dt->format('Y-m-d H:i:s');
        
                        $outbound_data = array(
                            'po_id'=> $po_id,
                            'raw' => $raw,
                            'edi_code' => '855',
                            'date' => $date,
                            'file_name' => $file_name
                        );
        
                        $o = X12Outbound::firstOrCreate($outbound_data);
                        if($o){
                            $success = 1;
                            $message =  'outbound saved <br/>';
                        }else{
                            $message = 'outbound was not saved <br/>'; 
                        }

                    }else{
                        $message = 'failed to send edi file';
                    }
                    
        
                }else{
                    $message = 'inbound not found..';
                }
            }else{
                $message =  $data['response'];
            }

        }catch(\Exception $e){
            $message =  'Caught exception: '.  $e->getMessage(). "\n";
            return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);
        
    }

    public function send_shipping_notice($order, $debug = 0){

        $success = 0;
        $message = '';
        $rdata = null;

        $inbound_id = $order->inbound_id;
        $po_id = $order->id;


        $in= X12Inbound::firstWhere('id',$inbound_id);

        Log::channel('custom')->debug("inbound at shipment notice:");
        Log::channel('custom')->debug($in);
       
        if($in){

            $name = $in->vendor.'_po_shipment_notice';
            $data = $this->$name($inbound_id);

            if($data['success']){


                $vendor = $in->vendor;
                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';
                $file_name = $f->file_name($vendor_file_name, '856', $in->po_number, $pre);


                $raw = $data['data'];
                $rdata = $raw;

                if($debug){

                    print_r($data['data']);
                    $vendor = 'test';
                    echo $vendor;
                    
                }
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    $message = "$vendor $file_name sent";
                    $in->status = 'Shipment Notice';
                    $in->save();

                    $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                    $date = $dt->format('Y-m-d H:i:s');
    
                    $outbound_data = array(
                        'po_id'=> $po_id,
                        'raw' => $raw,
                        'edi_code' => '856',
                        'date' => $date,
                        'file_name' => $file_name
                    );
                    
                    $o = X12Outbound::firstOrCreate($outbound_data);
                    if($o){
                        $success =1;
                        $message =  'outbound saved <br/>';
                    }else{
                        $message = 'outbound was not saved <br/>'; 
                    }

                }else{
                    $message =  'failed to send edi file';
                }
                
               
            }else{
                $message =  $data['response'];
    
            }

        }else{
            $message =  'inbound not found..';
        }
    
  
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

    }

    public function send_invoice($order, $type='DI', $debug = 0){

        $success = 0;
        $message = '';
        $rdata = null;

        $inbound_id = $order->inbound_id;
        $po_id = $order->id;

        $in =  X12Inbound::firstWhere('id',$inbound_id);
        if($in){

            $name = $in->vendor.'_po_invoice_notice';
            $data = $this->$name($inbound_id, $type);

            if($data['success']){
    
               
                $vendor = $in->vendor;
                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);

                $raw = $data['data'];
                $rdata = $raw;
    
                if($debug){
                    print_r($data['data']);
                    $vendor = 'test';
                    
                }
    
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    $message =  "$vendor $file_name sent;";
                    $in->status = 'Invoice Notice DI';
                    $in->save();
    
                    $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                    $date = $dt->format('Y-m-d H:i:s');
    
                    $outbound_data = array(
                        'po_id'=> $po_id,
                        'raw' => $raw,
                        'edi_code' => '810',
                        'date' => $date,
                        'file_name' => $file_name
                    );
    
                    $o = X12Outbound::firstOrCreate($outbound_data);
                    if($o){
                        $success =1;
                        $message .=  'outbound saved;';
                    }else{
                        $message .= 'outbound was not saved;'; 
                    }
    
                }else{
                    $message .=  'failed to send edi file';
                    }
                    
        
               
            }else{
                $message =  $data['response'];
            }

        }else{
            $message .= 'inbound not found..';
        }

      
       
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);
    }



    public function turn5_po_shipment_notice($inbound_id){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();

        $in= X12Inbound::where('id',$inbound_id)->firstorFail();
        Log::channel('custom')->debug("inbound at shipment notice:");
        Log::channel('custom')->debug($in);
       
        if($in){

            $vendor = $in->vendor;
            try{
                $x12 = $this->parse_raw($in->raw);
           
                $this->process->set_x12($x12);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }


                $purchase_order_number = $this->process->purchase_order_number();
                $seg = array();

                $config = config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );
                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();

                $weight = 1;
                $weight_unit = 'LB';

                if($po){
                    if($po->shipped_date){

                        $sdt = new \DateTime($po->shipped_date, new \DateTimeZone('UTC'));
                        $sdate = $sdt->format('Ymd');
                        $ship_date = $sdate;
                    }else{
                        $ship_date = $date;
                    }
                    if($po->tracking_number){
                        $tracking_number = $po->tracking_number;
                    }else{
                        $tracking_number = '11111111111';
                    }

                    $po_ob = new PurchaseOrder();
                    $weight_value = $po_ob->weight($po->id);
                    if($weight_value){
                        $weight = $weight_value['weight'];
                        if($weight_value['weight_unit'] != 'lbs'){
                            $weight_unit = strtoupper($weight_value['weight_unit']);
                        }
                        
                    }

                }else{
                    $tracking_number = '11111111111';
                    $ship_date = $date;
                }

                $shipment_id = $tracking_number;
                $routing = 'STANDARD SHIPPING';
                $service_code = 'G2';
                $ship_quantifier = '011'; //shipped
                $mark_number_qualifier = 'CP';  //carrier package 
                $mark_number =$tracking_number;

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);

                $gs_data_ack = $this->process->gs_po_general_856($sender);
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);


                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('856', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);



                //need to set shipment id, use tracking number?+date?
                $bsn_data = array(
                    'transaction_purpose_code'=>'00',
                    'shipment_id'=> $shipment_id
                );
                $bsn_segment = $this->segment_obj('bsn', $bsn_data);

                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack);

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                //shipment

                $hl_data1 = array(
                    'id_number' => 1,
                    'parent_id_number' =>'0',
                    'code' => 'S'
                );
                $hl_segment_1 = $this->create_hl_segment($hl_data1);

                //order
                $hl_data2 = array(
                    'id_number' => 2,
                    'parent_id_number' =>'1',
                    'code' =>'O'
                );

                $hl_segment_2 = $this->create_hl_segment($hl_data2);

                $prf_data = array(
                    'po_number' => $purchase_order_number
                
                );

                $prf_segment = $this->segment_obj('prf', $prf_data);
                $hl_segment_2->properties[0] = $prf_segment;


                //weight and unit
                //need to assign weight from jobber, default 1

                $weight_data = array(
                    'weight' => $weight,
                    'unit' => $weight_unit
                );


                $td1_name = $vendor->nick_name.'_td1';

                $td1_segment = $this->segment_obj($td1_name, $weight_data);
                
                if($td1_segment){
                    array_push($seg, $td1_segment);
                }

                $ref_data_ack = $this->process->ref_po_ack();

               
                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                foreach($seg as $i=>$s){
                    $hl_segment_1->properties[$i] = $s;
                }

                $hl_segment_1->HL[0] =  $hl_segment_2;
               
                //package
                $hl_data3 = array(
                    'id_number' => 3,
                    'parent_id_number' =>'2',
                    'code' => 'P'
                );
                    
                $hl_segment_3 = $this->create_hl_segment($hl_data3);
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
                $hl_segment_2->HL[0] =  $hl_segment_3;


                $td5_po_data = $this->process->td5_po_ack();
                $td5_name = $vendor->nick_name.'_td5';

                $td5_segment = $this->segment_obj($td5_name, $td5_po_data);


                $man_name = $vendor->nick_name.'_man';
            
                //use gtin for mark?
                $man_data = array(
                    'mark_number_qualifier' => $mark_number_qualifier,  //carrier package 
                    'mark_number' => $mark_number, //UPC ID
                    
                );

                $man_segment = $this->segment_obj($man_name, $man_data);

                //need shipdate
                $dtm_data = array(
                    'quantifier' => $ship_quantifier,
                    'date' => $ship_date,
                    
                );

                $dtm_segment = $this->segment_obj('dtm', $dtm_data);

                $h1_seg_3 = array();


                $refship_name = $vendor->nick_name.'_refship';

                if($td1_segment){
                    array_push($h1_seg_3 ,  $td1_segment);
                }
                if($td5_segment){
                    array_push($h1_seg_3 ,  $td5_segment);
                }
                if($man_segment){
                    array_push($h1_seg_3 ,  $man_segment);
                }else{

                    $refship_data = array(
                        'ref_id_qualifier' => 'CN',
                        'ref_id' => $tracking_number
                        
                    );


                    $refship_segment = $this->segment_obj($refship_name, $refship_data);
                    array_push($h1_seg_3 ,  $refship_segment);
                }
                if( $dtm_segment){
                    array_push($h1_seg_3 ,  $dtm_segment);
                }
                $hl_segment_3->properties = array();
                foreach($h1_seg_3 as $s){
                    array_push( $hl_segment_3->properties,  $s);
                    
                }

                $lin_name = $vendor->nick_name.'_po_to_lin_856';

                $lins = $this->process->$lin_name();

                //print_r($lins);
                if($lins){

                    foreach($lins as $index =>$lin){

                        $hl_data_item = array(
                            'id_number' => $index+4,
                            'parent_id_number' =>'3',
                            'code' => 'I'
                        );
                
                        $hl_segment = $this->create_hl_segment($hl_data_item );

                        $lin_seg_name = $vendor->nick_name.'_lin';

                        $lin_segment = $this->segment_obj($lin_seg_name, $lin['lin']);
                        $sn1_segment = $this->segment_obj('sn1',$lin['sn']);
                    
                        array_push($hl_segment->properties,$lin_segment);
                        array_push($hl_segment->properties,$sn1_segment);

                        $hl_segment_3->HL[$index] =  $hl_segment;

                    }

                }
             
                $se_data_ack = $this->process->se_po_ack($sender);

                $se_data_ack[1] = count($hl_segment_3->HL)*3 +13;
                $se_segment = $this->segment($se_data_ack);
                $x12 = new X12();
              
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;
                $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
                $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;
             

            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

       
    }


    public function autoany_po_shipment_notice($inbound_id){

        $success = 0;
        $message = '';
        $rdata = null;
        $seg = array();

        $in= X12Inbound::where('id',$inbound_id)->firstorFail();
        Log::channel('custom')->debug("inbound at shipment notice:");
        Log::channel('custom')->debug($in);
       
        if($in){

            $vendor = $in->vendor;
            try{
                $x12 = $this->parse_raw($in->raw);
           
                $this->process->set_x12($x12);

                $dt = new \DateTime(null, new \DateTimeZone('UTC'));
                $date = $dt->format('Ymd');
                $time = $dt->format('His');
                $isa_date = $dt->format('ymd');
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');

                $vendor = Vendor::where('nick_name',$vendor)->firstorFail();
                if($vendor){
                    $interchange_control_number = str_pad($vendor->interchange_control_number_gen, 9, "0", STR_PAD_LEFT);
                    $functional_control_number = $vendor->functional_control_number_gen;
                    $transaction_control_number = str_pad($vendor->transaction_control_number_gen, 9, "0", STR_PAD_LEFT);
                
                    $vendor->interchange_control_number_gen +=1;
                    $vendor->functional_control_number_gen +=1;
                    $vendor->transaction_control_number_gen +=1;
                    $vendor->save();
                

                }else{
                    $interchange_control_number = '00000001';
                    $functional_control_number = '00000001';
                    $transaction_control_number = '00000001';
                }


                $purchase_order_number = $this->process->purchase_order_number();
                $seg = array();

                $config = config('edi');

                $sender_isa = array(
                    'sender_id'=>str_pad($config['supreme']['id'],15," ",STR_PAD_RIGHT),
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );

                $sender = array(
                    'sender_id'=>$config['supreme']['id'],
                    'sender_qualifier'=> $config['supreme']['qualifier'],
                    'interchange_control_number' => $interchange_control_number,
                    'functional_control_number' => $functional_control_number,
                    'transaction_control_number' => $transaction_control_number
                );
                $po = PurchaseOrder::where('inbound_id', $inbound_id)->firstorFail();

                $weight = 1;
                $weight_unit = 'LB';

                if($po){
                    if($po->shipped_date){

                        $sdt = new \DateTime($po->shipped_date, new \DateTimeZone('UTC'));
                        $sdate = $sdt->format('Ymd');
                        $ship_date = $sdate;
                    }else{
                        $ship_date = $date;
                    }
                    if($po->tracking_number){
                        $tracking_number = $po->tracking_number;
                    }else{
                        $tracking_number = '11111111111';
                    }

                    $po_ob = new PurchaseOrder();
                    $weight_value = $po_ob->weight($po->id);
                    if($weight_value){
                        $weight = $weight_value['weight'];
                        if($weight_value['weight_unit'] != 'lbs'){
                            $weight_unit = strtoupper($weight_value['weight_unit']);
                        }
                        
                    }

                }else{
                    $tracking_number = '11111111111';
                    $ship_date = $date;
                }

                $shipment_id = $tracking_number;
                $routing = 'STANDARD SHIPPING';
                $service_code = 'G2';
                $ship_quantifier = '011'; //shipped
                $mark_number_qualifier = 'CP';  //carrier package 
                $mark_number =$tracking_number;

                $isa_data_ack = $this->process->isa_general_return($sender_isa);
                $isa_segment = $this->segment_from_po('isa', $isa_data_ack, $vendor);

                $gs_data_ack = $this->process->gs_po_general_856($sender);
                $gs_segment = $this->segment_from_po('gs', $gs_data_ack, $vendor);


                $st_data = $this->process->st();
                $st_data_ack = $this->process->st_po('856', $sender);
                $st_segment = $this->segment_from_po('st', $st_data_ack, $vendor);

                $se_count = 1;



                //need to set shipment id, use tracking number?+date?
                $bsn_data = array(
                    'transaction_purpose_code'=>'00',
                    'shipment_id'=> $shipment_id
                );
                $bsn_segment = $this->segment_obj('bsn', $bsn_data);

                $iea_data_ack = $this->process->iea_po_ack($sender);
                $iea_segment = $this->segment($iea_data_ack);

                $ge_data_ack = $this->process->ge_po_ack($sender);
                $ge_segment = $this->segment($ge_data_ack );

                //shipment

                $hl_data1 = array(
                    'id_number' => 1,
                    'parent_id_number' =>'',
                    'code' => 'S',
                    'child_code' => 1
                );
                $hl_segment_1 = $this->create_hl_segment($hl_data1,$vendor->nick_name.'_hl');
                $se_count++;

                //order
                $hl_data2 = array(
                    'id_number' => 2,
                    'parent_id_number' =>'',
                    'code' =>'O',
                    'child_code' => 1
                );

                $hl_segment_2 = $this->create_hl_segment($hl_data2,$vendor->nick_name.'_hl');
                $se_count++;

                $prf_data = array(
                    'po_number' => $purchase_order_number
                
                );

                $prf_segment = $this->segment_obj('prf', $prf_data);
                $hl_segment_2->properties[0] = $prf_segment;


                //weight and unit
                //need to assign weight from jobber, default 1

                $weight_data = array(
                    'weight' => $weight,
                    'unit' => $weight_unit
                );


                $td1_name = $vendor->nick_name.'_td1';

                $td1_segment = $this->segment_obj($td1_name, $weight_data);

                if($td1_segment){
                    array_push($seg, $td1_segment);
                }

                $td5_po_data = $this->process->td5_po_ack();
                $td5_name = $vendor->nick_name.'_td5';

                $td5_segment = $this->segment_obj($td5_name, $td5_po_data);

                if($td5_segment){
                    array_push($seg, $td5_segment);
                }


                $refship_data = array(
                    'ref_id_qualifier' => 'CN',
                    'ref_id' => $tracking_number
                    
                );

                $refship_name = $vendor->nick_name.'_refship';

                $refship_segment = $this->segment_obj($refship_name, $refship_data);

                if($refship_segment ){
                    array_push($seg ,  $refship_segment);
                }
               
                $ref_data_ack = $this->process->ref_po_ack();

                if($ref_data_ack ){
                    $ref_segment = $this->segment($ref_data_ack );
                    array_push($seg, $ref_segment);
                }

                //need shipdate
                $dtm_data = array(
                    'quantifier' => $ship_quantifier,
                    'date' => $ship_date,
                    
                );

                $dtm_segment = $this->segment_obj('dtm', $dtm_data);

                if($dtm_segment ){
                    array_push($seg, $dtm_segment);
                }

                $addr = $this->process->po_n1_group();
                $alladdr = $this->translate->po_to_address($addr);

                $n_names = array($vendor->nick_name.'_n1',$vendor->nick_name.'_n2',$vendor->nick_name.'_n3',$n4_name = $vendor->nick_name.'_n4');

                foreach($n_names as $i => $n){
                  

                    $n1_segment = $this->segment_obj($n, $alladdr['to']);

                    if($n1_segment ){
                        array_push($seg, $n1_segment);
                    }

                }

                foreach($seg as $i=>$s){
                    $hl_segment_1->properties[$i] = $s;
                }

                $hl_segment_1->HL[0] =  $hl_segment_2;
               
    
                $isa_time = $dt->format('Hi');
                $gs_date = $dt->format('Ymd');
                $gs_time = $dt->format('Hi');
               
                $lin_name = $vendor->nick_name.'_po_to_lin_856';

                $lins = $this->process->$lin_name();

                //print_r($lins);
                if($lins){

                    foreach($lins as $index =>$lin){

                        $hl_data_item = array(
                            'id_number' => $index+3,
                            'parent_id_number' =>'2',
                            'code' => 'I',
                            'child_code' => 0
                        );
                
                        $hl_segment = $this->create_hl_segment($hl_data_item, $vendor->nick_name.'_hl');
                        $se_count++;

                        $lin_seg_name = $vendor->nick_name.'_lin';
                        $sn1_seg_name = $vendor->nick_name.'_sn1';

                        $lin_segment = $this->segment_obj($lin_seg_name, $lin['lin']);
                        $sn1_segment = $this->segment_obj($sn1_seg_name,$lin['sn']);
                    
                        array_push($hl_segment->properties,$lin_segment);
                        $se_count++;
                        array_push($hl_segment->properties,$sn1_segment);
                        $se_count++;

                        $hl_segment_2->HL[$index] =  $hl_segment;

                    }

                }

                $number_of_orders = $this->process->number_of_orders_force();
                $ctt_seg_name = $vendor->nick_name.'_ctt';

            
                $ctt_data = array(
                    'quantifier' => $number_of_orders
                );
        
                $ctt_segment = $this->segment_obj($ctt_seg_name, $ctt_data);

            
                $se_data_ack = $this->process->se_po_ack($sender);
                $se_count++;
                $x12 = new X12();
              
                $x12->ISA[0] = $isa_segment;
                $x12->ISA[0]->IEA = $iea_segment;
                $x12->ISA[0]->GS[0] = $gs_segment;
                $x12->ISA[0]->GS[0]->GE = $ge_segment;
                $x12->ISA[0]->GS[0]->ST[0] = $st_segment;
               
                $x12->ISA[0]->GS[0]->ST[0]->HL[0] =  $hl_segment_1;
                $x12->ISA[0]->GS[0]->ST[0]->properties[0] = $bsn_segment;

                //$ctt_segment[1] = $number_of_orders;
                $x12->ISA[0]->GS[0]->ST[0]->CTT = $ctt_segment;
                $se_count++;

                $se_data_ack[1] = count($x12->ISA[0]->GS[0]->ST[0]->properties)+count($hl_segment_1->properties)+count($hl_segment_2->properties)+$se_count;
                $se_segment = $this->segment($se_data_ack);

                $x12->ISA[0]->GS[0]->ST[0]->SE = $se_segment;

    
            }catch(\Exception $e){
              
                $message =  'Caught exception: '.  $e->getMessage(). "\n";
                return array('success'=>0, 'response'=>$message, 'data'=>$rdata);
 
             }
 
             $serializer = new X12Serializer($x12);
             if($serializer){
                 // Generate the raw X12 string
                 $rdata = $serializer->serialize();
                 if($rdata){
                     $success = 1;
                     $message = 'edi successfully generated..';
                 }else{
                     $message = 'no raw edi data.';
                 }
                
             }else{
                 $message = 'serializer failed..';
             }
           
        }else{
            $message = 'no inbound data found..';
        }
        return array('success'=>$success, 'response'=>$message, 'data'=>$rdata);

       
    }

    
}