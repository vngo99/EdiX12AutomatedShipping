<?php
namespace App\Services\Teapplix;

use Illuminate\Support\Facades\Log;

class AddressValidation{

    protected static $creds = NULL;

    public function __construct($account='teapplix') {

        $this->api_url ='https://api.teapplix.com/api2/AddressValidation';
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

    public function test(){
        echo "teapplix class test.";
        print_r($this->account);
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

    public function format_address($data){
    
        return array(
            'Street' => $data['street'],
            'Street2' => $data['street2'],
            'State' => $data['state'],
            'City' => $data['city'],
            'ZipCode' => $data['zip'],
            'Country' => $data['country'],
            'CountryCode' => $data['country_code']
        
        );
    }

    public function format_address_check($data){
    
        return array(
            'Address' => $data['address'],
            'Provider' => $data['provider']
        
        );
    }

    public function check_address($data, $provider = "TEAPPLIX"){

        $fdata = array(
            'address' => $this->format_address($data),
            'provider' => $provider
        );
        $params = $this->format_address_check($fdata);
        return $this->post_call($params);
        
    }

    
}
