<?php
namespace App\Services\Teapplix;

use App\Services\Jobber\Jobber;
use App\Models\Vendor;
use Illuminate\Support\Facades\Log;

class TeapplixOrder{

    protected static $creds = NULL;

    public function __construct($account='teapplix') {

        $this->api_url ='https://api.teapplix.com/api2/OrderNotification';
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

                    //print_r( $data_res); die();

                    $data = $data_res['OrdersResult'][0];

                    $description = '';

                    if(isset($data['Description'])){
                        $description =  $data['Description'][0];
                    }

                    if(isset($data['Message'])){
                       $message =  $data['Message'];
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

    public function address($data = null, $company = null){

        $names = explode(" ",$data['to']['name']);

        print_r($data);

        return array(

            'Name' =>$data['to']['name'],
            //'FirstName' => $names[0],
            //'LastName' => $names[1],
            'Company' =>  ($company)?$company:$data['store'],
            'Street' => $data['to']['street'],
            'Street2' => $data['to']['street2'],
            'State' => $data['to']['state'],
            'City' => $data['to']['city'],
            'ZipCode' => $data['to']['zip'],
            'Country' => $data['to']['country'],
            'CountryCode' => $data['to']['country_code'],
            'PhoneNumber' => $data['to']['phone'],
            'Email' => ''
        

        );
    }

    public function address_po($data = null, $company = null){

        $names = explode(" ",$data['to']['name']);
        return array(

            'Name' =>$data['to']['name'],
            //'FirstName' => $names[0],
            //'LastName' => $names[1],
            'Company' =>  ($company)?$company:$data['store'],
            'Street' => $data['to']['street'],
            'Street2' => $data['to']['street2'],
            'State' => $data['to']['state'],
            'City' => $data['to']['city'],
            'ZipCode' => $data['to']['zip'],
            'Country' => $data['to']['country'],
            'CountryCode' => $data['to']['country_code'],
            'PhoneNumber' => $data['to']['phone'],
            'Email' => ''
        

        );
    }

    public function bill_address($data = null){

        return array(

            'Name' => '',
            'FirstName' => '',
            'LastName' => '',
            'Company' => '',
            'Street' => '',
            'Street2' => '',
            'State' => '',
            'City' => '',
            'ZipCode' => '',
            'Country' => '',
            'CountryCode' => ''
          
        );
    }

    public function order_totals($data = null){

        return array(

            'Shipping' => $data['shipping'], //shipping cost
            'Handling' => 0.00,
            'Discount' => 0.00,
            'Tax' => 0.00,
            'InsuranceType' => 'none', //nono, teapplix, carrier
            'Currency' => 'USD',
            'PostageCurrency' => 'USD',
            'Fee' => 0.00,
            'Total' => $data['total']
           
          
        );

    }

    public function order_details($data = null){

        return array(

            'Invoice' => '',
            'PaymentDate' => $data['payment_date'],
            'Memo' => $data['Memo'],
            'PrivateMemo' => '',
            'WarehouseId' => 0,
            'WarehouseName' => '',
            'QueueId' => $data['QueueId'],
            'ShipClass' => '',
            'FirstName' => '',
            'LastName' => '',
            'Custom' => '',
            'Custom2' => ''
           
          
        );

    }

    public function order_item($data = null){

    
        return array(

            'Name' => $data['sku'],
            'ItemId' => $data['vendor_part_number'],
            'ItemSKU' => $data['sku'],  //jobber to find sku
            'ItemLocation' => '',
            'Description' => $data['description'],
            'Quantity' => $data['quantity'],
            'Amount' => $data['discount_price'],
            'Shipping' => $data['shipping'], //5 except for d ring shackles
            'Tax' => 0.00,
            'Shipping Tax' => 0.00
        
        );

    }

    public function order_items($orders = null){

        if($orders){
            $items = null;
            foreach($orders as $o){
                $items[] = $this->order_item($o);

            }
            return $items;

        }
        return false;

    }

    public function order_shipment($data = null){

        return array(

            'Package' => array(
                'Method'=>$data['method'],
                
            
            ),
            //'ShipDate' => '',
            //'PostageAccount' => ''
        
        );

    }

    public function add_sku($data = null, $vendor){

        $v = Vendor::where('nick_name', $vendor)->firstorFail();
       
        if($v){
            $j = app(Jobber::class);

            $params = array(
                'type' =>'mfsku',
                'pn' => $data['vendor_part_number']
        
            );
        
            $rs = $j->to_sku($params);

        
            if($rs->success){

               
                $data['sku'] =$rs->data->mfsku;

                $new_price = $rs->data->retail * (1-($v->discount_per)/100);
                $new_price = $new_price * (1-($v->marketing_discount_per)/100);

                $data['discount_price'] = number_format($new_price, 2, '.', '');

                if ($rs->data->retail < 40) {
                    $data['shipping'] = 0; 
                } else if(strtolower($rs->data->account) == 'supremerecovery' && $rs->data->retail < 40) {
                    $data['shipping'] = 0; //dring shackes no fee
            
                } else {
                    $data['shipping'] = $v->dripship_fee;
                }

                $data['description'] = $rs->data->order_description;


            
            }else{
                $data['sku'] =' ';
                $data['shipping'] =$v->dripship_fee;
                $data['discount_price'] = 0;
               
               
            }

            return $data;

        }
        $data['sku'] =' ';
        $data['shipping'] = $v->dripship_fee;
        $data['discount_price'] = 0;
      

        return $data;

        

    }

    public function add_skus($data = null){

        if(isset($data['items'])){
            $items = null;
            foreach($data['items'] as $e){
                $items[] = $this->add_sku($e, $data['vendor']);

            }

            return $items;
        }
        return false;
    }

    public function store_key($vendor){
        $key = 'gen';
        switch($vendor){
            case 'turn5':
                    $key = 'tr5';
                break;
            case 'autoany':
                    $key = 'any';
                break;
            default:
                    $key = 'gen';
                break;
        }

        return $key;

    }

    public function format_order($data, $vendor){

        Log::channel('custom')->debug("teapplix format order:");
        Log::channel('custom')->debug($data);

        $vendor_key = $this->store_key($vendor);

        if($vendor =='turn5'){
            return array(
                'TxnId' => $data['txnid'],  //required
                'StoreType' => 'generic',
                'StoreKey' =>  $vendor_key,
                'SellerID' => $vendor,
                'PaymentStatus' => $data['payment_status'], //completed, cancelled, refunded, partialrefund required
                'To' => $data['to'],
                'OrderTotals' => $data['order_totals'],
                'OrderItems' => $data['order_items'],
                'OrderDetails' => $data['order_details'],
               
              
            );

        }else{

            return array(
                'TxnId' => $data['txnid'],  //required
                'StoreType' => 'generic',
                'StoreKey' =>  $vendor_key,
                'SellerID' => $vendor,
                'PaymentStatus' => $data['payment_status'], //completed, cancelled, refunded, partialrefund required
                'To' => $data['to'],
                'OrderTotals' => $data['order_totals'],
                'OrderItems' => $data['order_items'],
                'OrderDetails' => $data['order_details'],
                'ShippingDetails' => [$data['shipping_details']]
              
            );
        }

       
    }

    public function format_order_cancel($data = null){

        return array(
            'TxnId' => $data['txnid'],  //required
            'PaymentStatus' => $data['payment_status'], //completed, cancelled, refunded, partialrefund required
            'To' => $data['to'],
            'OrderItems' => $data['order_items'],
        
        );
    }

    public function format_order_cancel_teaformat($data = null){


        $name = $data['To']["FirstName"] . ' '.$data['To']["LastName"];
        $data['To']['Name'] = $name;
        unset( $data['To']["FirstName"]);
        unset( $data['To']["LastName"]);
       
        return array(
            'TxnId' => $data['TxnId'],  //required
            'PaymentStatus' => 'Cancelled',//completed, cancelled, refunded, partialrefund required
            'To' => $data['To'],
            'OrderItems' => $data['OrderItems'],
        
        );
    }

    public function order($txnid){
        $data = $this->get_call('order', array(
            'DetailLevel' => 'shipping|inventory',
            'TxnId'=>$txnid

        ));

        if($data['success']){
            return $data['data']->Orders;
        }
        return false;
    }

    public function orders($params){
        $data = $this->get_call('order', $params);
        if($data['success']){
            return $data['data'];
           
        }
        return false;
    }

    public function order_update_memo($txnid, $memo, $mode = TRUE){
        $data = $this->put_call('order', array(
            'Orders'=> [array(
                'Memo' =>array(
                    'Text'=>$memo,
                    'AppendMode'=>$mode
                ),
                'TxnId'=>$txnid
            )])

        );
        if($data['success']){
            return true;
        }
        return false;
    }

    public function create_order($params){
        // Operation: Submit
        // Orders: array of orders
        return $this->post_call($params);
        

    }

    public function cancel_order($params){
        return $this->put_call($params);
    }

    
   

}
