<?php
namespace App\Services\Jobber;

class Jobber{

    protected static $creds = NULL;

    public function __construct($account='teapplix') {

        
        $this->config =  config('jobber');
        $this->api_url = $this->config['url'];
        $this->client = new \GuzzleHttp\Client();
        

    }

    private function client(){
        return $this->client;
    }
   

    public function get_call($params){

        if($params){
            $url = $this->api_url;
            $client = $this->client();
            $call = $params;
            $message ='';
            $success = FALSE;
            $data = NULL;

        
            try{
                $response = $client->get($url, [
                    'headers'=>['Authorization'=>$this->config['bearer'],'Content-Type'=>'application/json','charset'=>'UTF-8'],
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
                "Authorization:".$this->config['bearer'],
                "content-type:application/json"
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
                    $success =1;
                    $message = json_decode($response,true)['OrdersResult'][0]['Status'];

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
                "Authorization:".$this->config['bearer'],
                "content-type:application/json"
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

                    $data = json_decode($response,true);

                    if(empty($data['Success']) && (isset($data['Code']) && $data['Code'] == 10)){
                        $success = 0;
                        $message = $data['Message'] .''. $data['Description'][0];
                        $response_data = NULL;

                    }else{
                        
                        $success = 1;
                        $message = $data['Message'];
                        $response_data = NULL;
                    }
                    
                }

            }catch (Exception $e){
                $message = 'Caught exception: '.$e->getMessage()."\n";
            }


        }else{
            $message ='Invalid Inputs';
        }
        return array('success'=>$success,'response'=>$message,'data'=>$response_data);

    }

    public function post($params){

        $url =  $this->api_url;
        $client = $this->client();
        $request = $client->post($url,  
            [   'debug' => true, 'headers'=>['Authorization'=>$this->config['bearer'],'Content-Type'=>'application/json','charset'=>'UTF-8'],
                'body'=>json_encode($params)
        ]);


        $response = $request->send();
        
        dd($response);

    }

    public function to_sku($params){
        $params['type'] ='mfsku';

        $data = $this->get_call($params);
        if($data['success']){
            return $data['data'];
           
        }
        return false;

    }

    public function check_oos($params){

        $params['type'] ='oos';

        $data = $this->get_call($params);

        if($data['success']){
            return $data['data'];
           
        }
        return false;

    }
   

}
