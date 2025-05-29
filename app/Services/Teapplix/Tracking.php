<?php
namespace App\Services\Teapplix;

use Illuminate\Support\Facades\Log;
use App\Models\X12Inbound;

class Tracking{

    protected static $creds = NULL;

    public function __construct($account='teapplix') {

        $this->api_url ='https://api.teapplix.com/api2/Track';
        $this->config =  config('teapplix');
        $this->account = $this->config['teapplix'];
        $header = array(
            "APIToken:".$this->account['token'],
            "content-type:application/json",
        );
        $this->client = new \GuzzleHttp\Client($header);
        

    }

    private function creds(){
        return $this->creds;
    }

    private function client(){
        return $this->client;
    }
   
    private function version(){
        return 'v2';
    }

    public function get_account(){
        return $this->account;
    }

    public function get_call($type, $params){

        if($type && $params){
            $url = $this->api_url;
            $version = $this->version();
            $client = $this->client();
            $call = $params;
            $message ='';
            $success = FALSE;
            $data = NULL;

            try{
                $response = $client->get($url, [
                    'headers'=>['APIToken'=>$this->account['token'],'Content-Type'=>'application/json','charset'=>'UTF-8'],
                    'query' => $call
                ]);

                if($response->getStatusCode() ==200){
                    $body =  $response->getBody()->getContents();
                    $data = json_decode($body);
                    $success = TRUE;

                }

            }catch (Exception $e){
                $message = 'Caught exception: '.$e->getMessage()."\n";
               // echo $message;
            }


        }else{
            $message ='Invalid Inputs';
        }
        return array('success'=>$success,'response'=>$message,'data'=>$data);

    }

    public function put_call($params){

       if($params){

            $url =  $this->api_url;
            $client = $this->client();
            $call = $params;
            $message ='';
            $success = FALSE;
            $data = NULL;

            $data_json = json_encode($params);

            $header = array(
                "APIToken:".$this->account['token'],
                "content-type:application/json",
            );

            try{
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

                $response = curl_exec($ch);

            
                if (!$response) {
                    $message = 'failed';
                }else{

                    $data_res = json_decode($response,true);

                    $data = $data_res['OrdersResult'][0];

                    $description = '';

                    if(isset($data['Description'])){
                        $description =  $data['Description'][0];
                    }

                    if($data['Success']){

                        $success = 1;
                        $message =  $message;
                        $response_data = NULL;
                       

                    }else{

                        $success = 0;
                        $message =  $message  .''.$description;
                        $response_data = NULL;
                    }


                
                }

            }catch (Exception $e){
                $message = 'Caught exception: '.$e->getMessage()."\n";
            }


        }else{
            $message ='Invalid Inputs';
        }
        return array('success'=>$success,'response'=>$message,'data'=>$data);

    }

    public function post_call($params){

        if($params){

            $url =  $this->api_url;
            $client = $this->client();
            $message ='';
            $success = FALSE;
            $data = NULL;

            $data_json = json_encode($params);

            $header = array(
                "APIToken:".$this->account['token'],
                "content-type:application/json",
            );

            try{
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

                $response = curl_exec($ch);

                if (!$response) {
                    $message = 'failed';
                }else{

                    $data_res = json_decode($response,true);
                    $success = 1;
                    $message =  "check success";
                    $response_data = $data_res;
                    
                }

            }catch (Exception $e){
                $message = 'Caught exception: '.$e->getMessage()."\n";
            }


        }else{
            $message ='Invalid Inputs';
        }
        return array('success'=>$success,'response'=>$message,'data'=> $response_data);

    }

    public function post($params){

        $url =  $this->api_url;
        $client = $this->client();
        $request = $client->post($url,  
            [   'debug' => true, 'headers'=>['APIToken'=>$this->account['token'],'Content-Type'=>'application/json','charset'=>'UTF-8'],
                'body'=>json_encode($params)
        ]);


        $response = $request->send();
        
        dd($response);

    }

    public function get_tracking($tracking_info){

        $data = $this->get_call('tracking', array(
            'Carrier' => $tracking_info['carrier'],
            'TrackingNumber'=>$tracking_info['tracking_number']

        ));

        if($data['success']){
            return $data['data'];
        }
        return false;
    }

    public function check_status($event){

        if($event){
            $events = $event->Events;
            foreach($events as $e){

                switch($e->Type){
                    case 'PICKUP':
                    case 'DELIVERY':
                            return array('status'=>$e->Type, 'action'=>$e->Description);
                        break;
                    case 'OTHER':
                        return array('status'=>$e->Type, 'action'=>$e->Description);
                        break;
                    default:
                        return false;
                        break;
                }

            }

        }
        return false;
    }

    public function check_intransit($event){

        if($event){
            $events = $event->Events;
            foreach($events as $e){

                switch($e->Type){
                    case 'PICKUP':
                    case 'DELIVERY':
                            return true;
                        break;
                    case 'OTHER':

                        $p = array();

                        $p[0] = 'Out for Delivery';
                        $p[1] = 'In Transit';
                        $p[2] = 'Arrived at';
                        $p[3] = 'Departed USPS Regional Facility';
                        $p[4] = 'Shipment arriving early';
                        $p[5] = 'Forwarded';
                        $p[6] = 'Left FedEx origin facility';
                        $p[7] = 'Awaiting Delivery Scan';
                       // $p[8] = 'Shipping Label Created, USPS Awaiting Item';
                        $p[9] = 'On FedEx vehicle for delivery';
                        $p[10] = 'Departed FedEx location';
                        $p[11] = 'Departed USPS Regional Origin Facility';
                        $p[12] = 'Arrived at USPS Regional Origin Facility';
                        $p[13] = 'Arrived at USPS Regional Destination Facility';
                        $p[14] = 'In Transit, Arriving Late';
                        $p[15] = 'Delivered to Agent for Final Delivery';
                        $p[16] = 'Rescheduled to Next Delivery Day';


                        $regex = '/(' .implode('|', $p) .')/i'; 

                        if(preg_match($regex, $e->Description)){
                            return true;
                        }else{
                            return false;
                        }

                        break;
                    default:
                        return false;
                        break;
                }

            }

        }
        return false;
    }


    public function check_intransit_status($tracking_info){

        try{
            $r =  $this->get_tracking($tracking_info);
            if($r){
                return $this->check_status($r);
            }
            return false;


        }catch(\Exception $e){

            $in = app(X12Inbound::class);
            $in->update_status(array('po_id'=>$tracking_info['po_id'], 'status'=> 'Tracking Error'));

            //throw new \Exception('Teapplix tracking check error');
            return false;
        }

        
 
       
    }

    public function check_intransit_for_debit_invoice($tracking_info){

        try{
            $r =  $this->get_tracking($tracking_info);
            if($r){
                return $this->check_intransit($r);
            }
            return false;


        }catch(\Exception $e){

            $in = app(X12Inbound::class);
            $in->update_status(array('po_id'=>$tracking_info['po_id'], 'status'=> 'Tracking Error'));

            //throw new \Exception('Teapplix tracking check error');
            return false;
        }

 
       
    }


    public function check_transit($tracking_info){

        $r =  $this->get_tracking($tracking_info);
        if($r){
            return $this->check_intransit($r);
        }
        return false;
 
       
    }
    
    public function test(){
        $r = array(
            'carrier' => 'USPS',
            'tracking_number'=>'9405516901579092950581'
        );
        $rr = $this->check_intransit_for_debit_invoice($r);
        if($rr){
            echo  'intransit';
        }else{
            echo 'not intransit';
        }

     }
 

    
}
