<?php

use Illuminate\Support\Facades\Route;
use Uhin\X12Parser\Parser\X12Parser;
use App\Services\MyEdi\MyEdi;
use Illuminate\Support\Facades\Storage;
use App\Services\MyEdi\MySegment;
use App\Services\Teapplix\TeapplixOrder;
use Illuminate\Support\Facades\DB;
use App\Services\Jobber\Jobber;
use App\Models\X12Inbound;
use App\Models\X12Outbound;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use App\Models\OrderItem;
use App\Http\Controllers\InboundController;
use App\Http\Controllers\InboundStatusController;
use App\Http\Controllers\PoInboundController;
use App\Http\Controllers\VendorController;
use App\Services\MyFile\MyFile;
use App\Services\MyEdi\Segment997;
use App\Http\Controllers\TeapplixShippingNotificationController;
use App\Http\Controllers\InboxController;
use Illuminate\Support\Facades\Log;
use App\Services\Teapplix\AddressValidation;
use App\Services\Teapplix\Tracking;
use App\Services\Notification\MyEmail;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

    Route::get('/dummp', function () {
        return view('welcome');
    });

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->middleware(['auth'])->name('dashboard');

    Route::get('/inbox/{vendor}', [InboxController::class, 'index'])->middleware(['auth'])->name('inbox');

    Route::get('/vendor', [VendorController::class, 'index'])->middleware(['auth'])->name('vendor');

    Route::get('/poinbound', [PoInboundController::class, 'index'])->middleware(['auth'])->name('poinbound');
    Route::get('/poinbound/list', [PoInboundController::class, 'getPoInbound'])->name('poinbound.list');

    Route::post('poinbound_services', [PoInboundController::class, 'services']);

    Route::get('/inbound', [InboundController::class, 'index'])->middleware(['auth'])->name('inbound');
    Route::get('/inbound/list', [InboundController::class, 'getInbound'])->name('inbound.list');

    Route::get('/vendor', [VendorController::class, 'index'])->middleware(['auth'])->name('vendor');
    Route::get('/vendor/list', [VendorController::class, 'getVendor'])->name('vendor.list');

    Route::post('/teapplixshipping_notification', [TeapplixShippingNotificationController::class, 'index']);

    Route::get('/inbound_status', [InboundStatusController::class, 'index'])->middleware(['auth'])->name('inbound_status');
    Route::get('/inbound_status/list', [InboundStatusController::class, 'getInbound'])->name('inbound_status.list');

    Route::get('/test', function () {

        
            $file_path = storage_path('files/aap_850_change_both.txt');
            $rawX12 = file_get_contents($file_path);
        
            // Create a parser object
            $parser = new X12Parser($rawX12);
        
            // Parse the file into an object data structure
            //$x12 = $parser->parse();
            //echo '<pre/>';

        

            // Parse the file into an object data structure
            $x12 = $parser->parse();
            echo '<pre/>';
            print_r(count($x12->ISA));
            print_r($x12);

            $edi = app(MyEdi::class);

        
        
        
        });

    Route::get('/edi_test', function () {

        /*
            $file_path = storage_path('files/810.txt');
            $rawX12 = file_get_contents($file_path);
        
            // Create a parser object
            $parser = new X12Parser($rawX12);
        
            // Parse the file into an object data structure
            $x12 = $parser->parse();
            echo '<pre/>';
            print_r($x12);
        
        
            $geo = app(geolocation::class);
            $d = $geo->search('abs');
            echo '<pre/>';
            print_r($d);
        
            $g = GeolocationFacade::search('test');
        
            echo '<pre/>';
            print_r($g);
        */
            $edi = app(MyEdi::class);
            $seg = app(Mysegment::class);
            $seg->test();
            $text = $edi->generate_855_single();
            //echo '<pre/>';
            print_r($text);
            
        
            $r = Storage::disk('local')->put('turn5_example.txt', $text);
            //return Storage::download('turn5_example.txt','turn5_example.txt',['Content-Type' => 'text/plain;  charset=utf-8']);
        
        
            //print_r($r);
        
        
            //return view('welcome');
    });
    
    Route::get('/856_single', function () {
    
        $edi = app(MyEdi::class);
        $text = $edi->generate_856_single();
        echo '<pre/>';
        print_r($text);
        
    
        
    });
    
    Route::get('/855_single', function () {
    
        $edi = app(MyEdi::class);
        $text = $edi->generate_855_single();
        echo '<pre/>';
        print_r($text);
        
    
        
    });
    
    Route::get('/810_single', function () {
    
        $edi = app(MyEdi::class);
        $text = $edi->generate_810_single();
        //echo '<pre/>';
        print_r($text);
        
    
        
    });
    
    
    Route::get('/850', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->generate_850();
    
        print_r($d);
    
        
    });
    
    Route::get('/855', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->generate_855();
    
        print_r($d);
    
        
    });
    
    Route::get('/856', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->generate_856();
    
        print_r($d);
    
        
    });
    
    Route::get('/810', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->generate_810();
    
        print_r($d);
    
        
    });
    
    Route::get('/850_mult', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->generate_850_multi();
        print_r($d);
    
        
    });
    
    Route::get('/855_mult', function () {
    
        $edi = app(MyEdi::class);
        $text = $edi->generate_855_mult();
        echo '<pre/>';
        print_r($text);
        
    
        
    });
    
    Route::get('/po_ack', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->po_to_po_ack();
        print_r($d);
    
        
    });
    
    Route::get('/po_856', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->po_to_856();
        print_r($d);
    
        
    });
    
    Route::get('/po_810_di', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->po_to_810_di();
        print_r($d);
    
        
    });
    
    Route::get('/po_810_cn', function () {
    
        echo '<pre/>';
        $edi = app(MyEdi::class);
        $d = $edi->po_to_810_cn();
        print_r($d);
    
        
    });
    
    Route::get('/tea_test_cancel', function () {
    
    
        $edi = app(MyEdi::class);
        $x12 = $edi->x12_order($x12_file='turn5_850_mult_exa.txt');
        
        
        echo '<pre/>';
        $tea = app(TeapplixOrder::class);
        $items = $tea->add_skus($x12['items']);
    
    
        $tea_data = array(
            'order_items' => $tea->order_items($items),
            'to' => $tea->address($x12['address']),
            'txnid' => 't512345',
            'payment_status' => 'Cancelled',
           
            
        );
    
    
        $order = $tea->format_order_cancel($tea_data);
    
        $params = array(
            'Orders' => [$order]
        ); 
    
        print_r($params);
    
        $r = $tea->cancel_order( $params );
    
        print_r($r);
    
    
    
    
    });

    Route::get('/tea_order/{txnid}', function ($txnid=429653 ) {
    
        
        echo '<pre/>';
        $tea = app(TeapplixOrder::class);

        $r = $tea->order($txnid);
    
        print_r($r);
    
    
    
       
        
    });

    Route::get('/tea_test', function () {
    
        /*
        
        $client = new \GuzzleHttp\Client();
        $request = $client->get('http://testmyapi.com');
        $response = $request->getBody();
       
        dd($response);
        */
    
        echo '<pre/>';
        $tea = app(TeapplixOrder::class);
        
        //$teapplix_config = config('teapplix');
        //print_r($teapplix_config['teapplix']);
    
        $edi = app(MyEdi::class);
        $x12 = $edi->x12_order($x12_file='turn5_850_mult_exa.txt');
        
        $items = $tea->add_skus($x12['items']);
        $total = $edi->order_total();
    
        $total_data = array(
    
            'shipping' => floatval(5.00),
            'total' =>  $total
           
          
        );
    
        $order_totals = $tea->order_totals($total_data);
    
        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
    
    
        $date = $dt->format('Y-m-d');
        $detail_data = array(
    
            'Invoice' => '',
            'payment_date' => $date,
            'Memo' => '',
            'PrivateMemo' => '',
            'WarehouseId' => '',
            'WarehouseName' => '',
            'QueueId' => '',
            'ShipClass' => '',
            'FirstName' => '',
            'LastName' => '',
            'Custom' => '',
            'Custom2' => ''
           
          
        );
    
        $order_details = $tea->order_details($detail_data);
    
        $tea_data = array(
                'order_items' => $tea->order_items($items),
                'to' => $tea->address($x12['address']),
                'txnid' => 't512345',
                'payment_status' => 'Completed',
                'order_totals' => $order_totals,
                'order_details' => $order_details
                
        );
      
        
       
    
        $order = $tea->format_order($tea_data);
    
        print_r($order);
    
        $tea_data = array(
            'Operation'=>'Submit',
            'Orders' => [$order]
        );
    
        $result = $tea->create_order( $tea_data );
    
        print_r($result);
        //die();
       
        $tea->test();
        $r = $tea->order('429653');
    
        print_r($r);
    
    
    
       
    
        
    });
    
    
    Route::get('/ftp', function () {
    
        echo '<pre/>';
    
    
        //$r =   Storage::disk('sftp.home')->files('aaptest');
        /*
    
        $edi = app(MyEdi::class);
        $t = $edi->parse_text();
    
    
        $r = Storage::disk('sftp.home')->put('aaptest/test.edi',$t);
        print_r($r);
        */
    
        $rr = Storage::disk('sftp.home')->files('aaptest');
        print_r($rr);
    
        $rrr = Storage::disk('sftp.home')->get('aaptest/test.edi');
        print_r($rrr);
    
    
        
    });
    
    
    Route::get('/db', function () {
    
        echo '<pre/>';
    
    
        //$r = DB::table('users')->get();
    
        //print_r($r);
    
        $edi = app(MyEdi::class);
        //$r = $edi->to_inbound();
        //print_r($r);
    
        $rs = $edi->from_edi_db();
    
        print_r($rs);
    
        
    });
    
    
    Route::get('/jobber', function () {
    
        echo '<pre/>';
    
    
        //$r = DB::table('users')->get();
    
        //print_r($r);
    
        $j = app(Jobber::class);
        $p = app(Jobber::class);
        //$r = $edi->to_inbound();
        //print_r($r);
      
        $params = array(
            'type' =>'mfsku',
            'pn' => 'DGRM09FK3530'
    
        );

        $params = array(
          
            'pn' => 'DGRM09FK3530',
            'qty' =>1
    
        );


        $rs = $j->check_oos($params);
    
        print_r($rs);
    
        
    });
    
    Route::get('/dodb', function () {
    
        echo '<pre/>';
    
    
        $rs= X12Inbound::where('id', 1)->get();
        print_r($rs);
    
        
    });
    
    
    Route::get('/db_query', function () {
    
        echo '<pre/>';
    
       // $po = new PurchaseOrder();
        //$r = $po->inbound_to_po();
        //print_r($r);
    
        $in = New X12Inbound();
        $rr = $in->text_purchase_order(1);
    
        print_r($rr);
    
        
    });

    Route::get('/init_vendor', function () {

        $in = New Vendor();
        $in->init();


    });

    Route::get('/test/{vendor}', function ($vendor) {

        print_r($vendor);

    });

    Route::get('/997/{vendor}', function ($vendor_name) {

        $j = app(Segment997::class);
        $j->test();
        echo '<pre/>';
        $f = app(MyFile::class);
        $edi = app(MyEdi::class);

        $files = $f->view_inbound($vendor_name);
        print_r($files);
        echo '<br/>';

        if($files){
            foreach($files as $to_get){
                $c = $f->get_inbound_file($to_get,'s3.'.$vendor_name);
                print_r($c);
                echo '<br/>';

                $x12 = $edi->parse_raw($c);

                print_r($x12);
                $edi->process->set_x12($x12);

               

             
            
            }
           
        }


    });

    Route::get('/status_997', function () {

        $edi = app(MyEdi::class);
        $file ='files/997.txt';
        $result = $edi->config_from_file($file);
        $edi->show_config_segments();

    });

    Route::get('/generate_997', function () {

        echo '<pre/>';

        $edi = app(MyEdi::class);
        $data = array(
            'sender_id' => 'ma888312',
            'sender_qualifier' => 'zz'
        );
        $raw = $edi->generate_997($data);
        print_r($raw);
       

    });

    Route::get('/send_997/{vendor}', function ($vendor) {

        echo '<pre/>';

        $edi = app(MyEdi::class);
        $f = app(MyFile::class);
        $data = array(
            'sender_id' => 'ma888312',
            'sender_qualifier' => 'zz'
        );

        $v = $edi->get_vendor_id($vendor);
        $data['receive_qualifier'] = $v['isa']['qualifier'];
        $data['receive_id'] = $v['isa']['id'];


        $raw = $edi->generate_997($data);
        print_r($raw);

        $file_name = 'test_997.x12';

        $s = $f->send($vendor, $file_name, $raw);
        if($s){

            echo '<br/>';
            echo "$vendor $file_name sent";

        }


    });

    Route::get('/send_file_test/{file_name}', function ($file_name) {

        echo '<pre/>';
        $vendor = 'test';

        $edi = app(MyEdi::class);
        $f = app(MyFile::class);

        $x12 = $edi->parse('files/'.$file_name);
        $raw = $edi->to_raw($x12);

        $s = $f->send($vendor, $file_name, $raw);
        if($s){

            echo '<br/>';
            echo "$vendor $file_name sent";

        }

    });

    Route::get('/inbound_name/{vendor_name}', function ($vendor_name) {
    
        echo '<pre/>';
        $f = app(MyFile::class);
        $edi = app(MyEdi::class);

        $files = $f->view_inbound($vendor_name);
        print_r($files);
        echo '<br/>';

        if($files){
            foreach($files as $to_get){
                $c = $f->get_inbound_file($to_get,'sftp.'.$vendor_name);
                print_r($c);
                echo '<br/>';

                $x12 = $edi->parse_raw($c);
                $edi->process->set_x12($x12);

                $code = $edi->process->edi_code();
                print_r($code);
                echo '<br/>';

                $d = $edi->process->edi_date();
                print_r($d);
                echo '<br/>';

             
                $s = $f->save_inbound_text($vendor_name,$c);
                if($s){
                    echo "$to_get saved <br/>";
                }
                
            }
           
        }
    });

    Route::get('/inbound_test/{id}', function ($id) {
    
        echo '<pre/>';
        $f = app(MyFile::class);
        $e = app(MyEdi::class);

        $in = New X12Inbound();
        $vendor = New Vendor();

        $detail= $vendor->get_vendor($id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $files = $f->view_inbound($vendor_name);
            print_r($files);
            if($files){
                foreach($files as $to_get){
                    $c = $f->get_inbound_file($to_get,'sftp.'.$vendor_name);
                    print_r($c);
                    echo '<br/>';

                    $x12 = $e->parse_raw($c);
                    print_r($x12);
                    echo '<br/>';

                    $s = $f->save_inbound_text($vendor_name,$c, $to_get);
                    if($s){
                        echo "$to_get saved <br/>";

                    }
                    
                }
                
            }

        }else{
            echo 'data no found..';
        }

    
    });

    Route::get('/to_purchase_order_test/{inbound_id}', function ($inbound_id) {

        //test to process po inbound in db to order,items, address
    
        echo '<pre/>';
      
        $in = New X12Inbound();
        
        $r = $in->text_purchase_order_get_test($inbound_id);

        if($r['success']){
            
            echo 'purchase order data created';
        }else{

            print_r($r);
            echo 'data no found..';
        }

    
    });

    Route::get('/inbound/{id}', function ($id) {
    
        echo '<pre/>';
        $f = app(MyFile::class);
        $vendor = New Vendor();

        $detail= $vendor->get_vendor($id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $files = $f->view_inbound($vendor_name);
            print_r($files);
            if($files){
                foreach($files as $to_get){
                    $c = $f->get_inbound_file($to_get,'sftp.'.$vendor_name);
                    print_r($c);
                    $s = $f->save_inbound_text($vendor_name,$c);
                    if($s){
                        echo "$to_get saved <br/>";


                        $d = Storage::disk('sftp.'.$vendor_name)->delete($to_get);

                        echo "DD:: $d <br/>";

                        if($d){
                            echo "$to_get removed <br/>";
                        }
        


                    }
                }
                $vendor = New Vendor();
                $vendor->update_last($id,'edi_updated_at');
            }

        }else{
            echo 'data no found..';
        }

      
        

    });

    Route::get('/inbound_one/{vendor}/{to_get}', function ($vendor,$to_get) {
    
        echo '<pre/>';
        $f = app(MyFile::class);
    
      
        if($to_get){

            switch($vendor){
                case 'test':
                case 'home':
                   $disk = 'sftp.home';
                   $dir = '/aaptest/';
                    break;
                case 'turn5':
                    $disk = 'sftp.turn5';
                    $dir = '/Incoming/';
                    break;
                case 'aap':
                    $disk = 'sftp.aapin';
                    $dir = '/inbox/';
                    break;
                default:
                    
                    break;
            }

            
            $c = $f->get_inbound_file($dir.$to_get,$disk);
            $s = $f->save_inbound_text($vendor,$c);
            if($s){
                echo "Vender: $vendor $to_get edi file  saved into system. <a href='/inbound'>link</a> <br/>";

                $d = Storage::disk($disk)->delete($dir.$to_get);
                if($d){
                    echo "$to_get removed <br/>";
                }


            }
          
        }else{
            echo 'No edi files to get..';
        }



    });

    Route::get('/translate/{vendor_id}', function ($vendor_id) {

        $in = New X12Inbound();
        $vendor = New Vendor();
        echo '<pre/>';

        $detail= $vendor->get_vendor($vendor_id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $ins = $in->get_for_translate($vendor_name);

            if($ins){
    
                foreach($ins as $i){
                    $r = $in->text_purchase_order($i);

                    $po = $i->po_number;
                    $vendor = $i->vendor;
         
                    if($r['success']){
    
                        echo "Purchase Order: $po added for Vender: $vendor <a href='/poinbound'>link</a> <br/>";
                        if($r['data']){
                            $po_id = $r['data'];
                            $i->po_id = $po_id;
                            $i->save();
        
                            $items = OrderItem::where('po_id',$po_id)->get();
                            if($items){
        
                                $oi = new OrderItem();
                                foreach($items as $i){
                                   
                                    $r= $oi->check_jobber($i, $vendor);
                                  
                                }
        
                            }
        
                        }
    
                    }else{
                        echo "$po failed to add <br/>";
                    }
    
                }
                $vendor = New Vendor();
                $vendor->update_last($vendor_id,'po_updated_at');
    
            }

        }else{
            echo 'data no found..';
        }
 
    
    });

    Route::get('/translate_one/{inbound_id}', function ($inbound_id) {

        $in = New X12Inbound();
        echo '<pre/>';

        $inbound = X12Inbound::where('id', $inbound_id)->get();
      
        if($inbound){
            
            $r = $in->text_purchase_order($inbound[0]);
            $po = $inbound[0]->po_number;
            $vendor = $inbound[0]->vendor;
         
            if($r['success']){

                echo "Purchase Order: $po added for Vender: $vendor <a href='/poinbound'>link</a> <br/>";
                if($r['data']){
                    $po_id = $r['data'];
                    $inbound[0]->po_id = $po_id;
                    $inbound[0]->save();

                    $items = OrderItem::where('po_id',$po_id)->get();
                    if($items){

                        $oi = new OrderItem();
                        foreach($items as $i){
                           
                            $r= $oi->check_jobber($i, $vendor);
                          
                        }

                    }

                }

            }else{
                echo " $po failed to add <br/>";
            }

        
            
        }else{
            echo 'data no found..';
        }
 
    
    });

    Route::get('/translate_one_997/{inbound_id}', function ($inbound_id) {

        $in = New X12Inbound();
        echo '<pre/>';

        $inbound = X12Inbound::where('id', $inbound_id)->get();
      
        if($inbound){
            
           $r = $in->check_status($inbound[0]);

           print_r($r);
        
            
        }else{
            echo 'data no found..';
        }
 
    
    });

    Route::get('/create_teapplix_order/{inbound_id}', function ($inbound_id) {
        echo '<pre/>';

        $po = new PurchaseOrder();
        $tea = app(TeapplixOrder::class);
        $order = $po->inbound_raw_to_order($inbound_id);
        print_r($order);
        die();

        $tea_data = array(
            'Operation'=>'Submit',
            'Orders' => [$order]
        );
       
        $result = $tea->create_order($tea_data);

    
        if($result['success']){

            $r = $po->update_order_id($inbound_id, $result['data']['TxnId']);
            if($r){
                echo 'teapplxi order created and orderid  saved..<br/>';

                $in = X12Inbound::where('id', $inbound_id)->first();
                $in->status = 'Ordered';
                $in->save();

            }else{
                echo 'orderid not saved..<br/>';
            }

        }else{
            echo "failed to create order <br/>";
            print_r($result['response']);
        }
       
    });

    Route::get('/create_teapplix_order_po/{inbound_id}', function ($inbound_id) {
        echo '<pre/>';

        $po = new PurchaseOrder();
        $tea = app(TeapplixOrder::class);
        $order = $po->purchase_order_to_order_inbound($inbound_id);
        //print_r($order);die();

        $r = PurchaseOrder::where('inbound_id', $inbound_id)->first();
        if($r){
            
            if($r->order_id){
                echo "Teapplix:$r->order_id  exist for this PO..<br/>";
            }else{

               // echo 'create teaapplix order:<br/>' die();
                    $tea_data = array(
                        'Operation'=>'Submit',
                        'Orders' => [$order]
                    );
                
                    $result = $tea->create_order($tea_data);
            
                
                    if($result['success']){
            
                        $r = $po->update_order_id($inbound_id, $result['data']['TxnId']);
                        if($r){
                            echo 'teapplix order created and orderid  saved..<br/>';
            
                            $in = X12Inbound::where('id', $inbound_id)->first();
                            $in->status = 'Ordered';
                            $in->save();
            
                        }else{
                            echo 'orderid not saved..<br/>';
                        }
            
                    }else{
                        echo "failed to create order <br/>";
                        print_r($result['response']);
                    }
            }

        }else{
            echo "order not found... <br/>";
            

        }


      
       
    });

    Route::get('/cancel_teapplix_order/{inbound_id}', function ($inbound_id) {
        echo '<pre/>';

        $po = new PurchaseOrder();
        $tea = app(TeapplixOrder::class);
        $data = $po->inbound_raw_to_order($inbound_id);
        $order = $tea->format_order_cancel_teaformat($data);
    
        $params = array(
            'Orders' => [$order]
        ); 

        $r = $tea->cancel_order( $params );
        print_r($r);
    

    });


    Route::get('/po_to_teapplix_order/{po_id}', function ($po_id) {
        echo '<pre/>';

        $po = new PurchaseOrder();
        $r = $po->purchase_order_to_order($po_id);
        print_r($r);

    });


    Route::get('/purchase_order_ack/{po_id}', function ($po_id) {
        echo '<pre/>';

        $edi = app(MyEdi::class);
        $data = $edi->po_ack($po_id);
        if($data['success']){

            $in = X12Inbound::where('id', $po_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';

                $file_name = $f->file_name($vendor_file_name, '855', $in->po_number, $pre);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "Vendor:$vendor  filename:$file_name";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }

    });

    Route::get('/purchase_order_ack_change/{po_id}', function ($po_id) {
        echo '<pre/>';

        $edi = app(MyEdi::class);
        $data = $edi->turn5_po_ack_change($po_id);
        if($data['success']){

            $in = X12Inbound::where('id', $po_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';

                $file_name = $f->file_name($vendor_file_name, '855', $in->po_number, $pre);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "Vendor:$vendor  filename:$file_name";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }

    });

    Route::get('/purchase_order_reject/{po_id}', function ($inbound_id) {
        echo '<pre/>';

        $edi = app(MyEdi::class);
        //autoany RJ, turn5 RD

        $in = X12Inbound::where('id', $inbound_id)->firstorFail();
        if($in){

            $vendor = $in->vendor;
            $po_id = $in->po_id;

            $rj_name = $vendor.'_po_ack_rej';

            $data = $edi->$rj_name($inbound_id);
       
            if($data['success']){

                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';
                $post = 'CANCELLATION';

                $file_name = $f->file_name($vendor_file_name, '855', $in->po_number, $pre, $post);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();

                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                    $in->status = 'PO Reject';
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                    
        
            
            }else{
                echo $data['response'];
            }

            

        }else{
            echo 'inbound not found..';
        }
 

    });

    Route::get('/shipment_notice/{po_id}', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);
    
        $data = $edi->po_shipment_notice($inbound_id);

        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $po_id = $in->po_id;
                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';

                $file_name = $f->file_name($vendor_file_name, '856', $in->po_number, $pre);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
               // die();
              
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }
    });

    Route::get('/shipment_notice_change/{po_id}', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);
    
        $data = $edi->turn5_po_shipment_notice_change($inbound_id);

        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $po_id = $in->po_id;
                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS-stanalone';
                $file_name = $f->file_name($vendor_file_name, '856', $in->po_numbe, $pre);

                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
              
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }
    });

    Route::get('/invoice_notice_di/{po_id}/', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);
    
        $data = $edi->po_invoice_notice($inbound_id, "DI");

        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $f = app(MyFile::class);
    
                $po_id = $in->po_id;

                $vendor_file_name = $vendor.'_file_name';


                $pre = 'SS-TEST';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);

                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }
    

    });


    Route::get('/invoice_notice_di_all/{po_id}/', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);

        $in = X12Inbound::where('id', $inbound_id)->firstorFail();
        if($in){

            $vendor = $in->vendor;
            $f = app(MyFile::class);
            $po_id = $in->po_id;
            $po_invoice_name = $vendor.'_po_invoice_notice';

            $data = $edi->$po_invoice_name($inbound_id, "DI");

            if($data['success']){
                $vendor = $in->vendor;
                $f = app(MyFile::class);
    
                $po_id = $in->po_id;

                $vendor_file_name = $vendor.'_file_name';


                $pre = 'SS';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);

                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
        
                
            }else{
                echo $data['response'];
            }

        }else{
            echo 'inbound not found..';
        }
    
    });

    Route::get('/invoice_notice_change_di/{po_id}/', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);
    
        $data = $edi->turn5_po_invoice_notice_change($inbound_id, "DI");

        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){

                $vendor = $in->vendor;
                $po_id = $in->po_id;
                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS-standalone';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);
              
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }
    

    });


    Route::get('/invoice_notice_cn_all/{po_id}/', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);

        $in = X12Inbound::where('id', $inbound_id)->firstorFail();
        if($in){

            $vendor = $in->vendor;
            $f = app(MyFile::class);
            $po_id = $in->po_id;
            $po_invoice_name = $vendor.'_po_invoice_notice';

            $data = $edi->$po_invoice_name($inbound_id, "CN");

            if($data['success']){
                $vendor = $in->vendor;
                $f = app(MyFile::class);
    
                $po_id = $in->po_id;

                $vendor_file_name = $vendor.'_file_name';


                $pre = 'SS-CN';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);

                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                    $in->status = 'Invoice Notice CN';
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
        
                
            }else{
                echo $data['response'];
            }

        }else{
            echo 'inbound not found..';
        }
    
    });




    Route::get('/invoice_notice_cn/{po_id}/', function ($po_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);
    
        $data = $edi->po_invoice_notice($po_id, "CN");

        if($data['success']){

            $in = X12Inbound::where('id', $po_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $f = app(MyFile::class);

                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS-TEST';
                $file_name = $f->file_name($vendor_file_name, '810', $in->po_number, $pre);

                $raw = $data['data'];

                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                die();
                
                
               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                    $in->status = 'Invoice Notice CN';
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }
    

    });


    Route::get('/check_inbox', function () {
    
        echo '<pre/>';

        echo "test:<br/>";
        $r = Storage::disk('s3.home')->files('aaptest');
    
        print_r($r);
        echo "<br/>";
    
        $a = Storage::disk('s3.aap')->files('inbox');
        echo "Advance Auto S# Inbox:<br/>";
        print_r($a);
        echo "<br/>";


        echo "AAP:inbound FTP<br/>";
        $rrr = Storage::disk('sftp.aapin')->files('Inbound');
        print_r($rrr);

        /*
        $b = Storage::disk('s3.turn5')->files('inbox');

        echo "Turn5:<br/>";
        print_r($b);
        */

        echo "TURN5:inbound<br/>";
        $rrr = Storage::disk('sftp.turn5')->files('Incoming');
        print_r($rrr);
        echo "<br/>";
    
    
        
    });

    Route::get('/check_ftp', function () {
    
        echo '<pre/>';

        echo "Test:<br/>";
        $rrr = Storage::disk('sftp.home')->files('aaptest');
        print_r($rrr);
      
        echo "AAP:inbound<br/>";
        $rrr = Storage::disk('sftp.aapin')->files('Inbound');
        print_r($rrr);
        echo "AAP:outbound<br/>";
        $rrro = Storage::disk('sftp.aapout')->files('Outbound');
        print_r($rrro);


        echo "TURN5:inbound<br/>";
        $rrr = Storage::disk('sftp.turn5')->files('Incoming');
        print_r($rrr);
        echo "TURN5:outbound<br/>";
        $rrro = Storage::disk('sftp.turn5')->files('Outgoing');
        print_r($rrro);

        echo "AUTOANY:inbound<br/>";
        $rrr = Storage::disk('sftp.autoany')->files('Inbound');
        print_r($rrr);
        echo "AUATOANY:outbound<br/>";
        $rrro = Storage::disk('sftp.autoany')->files('Outbound');
        print_r($rrro);

    });

    Route::get('/clear_ftp/{vendor}', function ($vendor) {
    
        echo '<pre/>';

        switch($vendor){
            case 'test':
               $disk = 'sftp.home';
               $dir = 'aaptest';
                break;
            case 'turn5':
                $disk = 'sftp.turn5';
                $dir = 'Incoming';
                break;
            case 'aap':
                $disk = 'sftp.aapin';
                $dir = 'inbox';
                break;
            default:
                
                break;
        }


        $files = Storage::disk($disk)->files($dir);
       
        if($files){

            foreach($files as $file){

                $d = Storage::disk($disk)->delete($file);
                if($d){
                    echo "$file deleted <br/>";
                }

            }
            
        }
      
        
    });

    Route::get('/check_shipping_status/{txnid}', function ($txnid=429653 ) {
    
        
        echo '<pre/>';
      
        $tea = app(TeapplixOrder::class);
        $r = $tea->order($txnid);
        //print_r($r); die();
        if($r){
            $shipping = $r[0]->ShippingDetails;
            $package = $shipping[0]->Package;
            if( $shipping[0]->ShipDate){
                echo "Order shipped <br/>";
                echo "Shipping date:". $shipping[0]->ShipDate;


                $r = PurchaseOrder::where('order_id', $txnid)->firstorFail();

                if($r){
                    $r->shipped_date = $shipping[0]->ShipDate;
                    $r->tracking_number = $package->TrackingInfo->TrackingNumber;
                    $r->carrier_name = $package->TrackingInfo->CarrierName;
                   
                    $r->save();
                    echo "Updated shipped date and tracking info<br/>";

                    $in =X12Inbound::where('id', $r->inbound_id)->firstorFail();
                    if($in){
                        $in->status = "Shipped";
                        $in->save();
                        echo "Status updated to Shipped<br/>";
                    }

                }


            }else{
                echo 'order has not been shipped yet.';
            }

    


        }else{
            echo 'no orders found..';
        }
    
      

    });

    Route::get('/fix_item_discount_price/{vendor_name}', function ($vendor_name) {
        echo '<pre/>';
      $ot = new OrderItem();
        $ot->fix_price($vendor_name);
        
    });


    Route::get('/test_log', function () {
        $config = config('edi');
    
        Log::channel('custom')->debug("test");
        Log::channel('custom')->debug($config);
        
    });

    Route::get('send-mail', function () {
   
        $em = new MyEmail();
        $address = null;
        $change = null;

        $body ='testtest';

        $message_data = [
            'title' => "Item Out of Stock",
            'body' => $body,
            'from' => 'admin@mmadauto.com',
            'subject' => 'EDI OOS',
            'to' => 'van@mmadauto.com',
             'cc'=> ['van@mmadauto.com','van099@gmail.com'],
            'address' => $address,
            'change' => $change
            
        ];
       
        $em->send($message_data);
       
       
    });



    Route::get('/test', function () {

        echo '<pre/>';
        $debug = 1;
        $po = app(PurchaseOrder::class);

        $orders_to_validate = $po->get_orders_to_validate_address();


        if($orders_to_validate){
            $edi = app(MyEdi::class);
            foreach( $orders_to_validate as $order){

                    $r = $po->validate_address($order->id);
                    if($r['success']){


                        $oos = $po->check_oos($order->id);

                        if($oos['oos']){

                            $em = new MyEmail();
                            $address = null;
                            $change = null;
    
                        
                            $body = "Purachase Order Number: $order->po_number OOS, teapplix order was not created..";
                            $body .=$oos['response'];
    
                            $message_data = [
                                'title' => "Item Out of Stock",
                                'body' => $body,
                                'from' => 'admin@mmadauto.com',
                                'subject' => 'EDI OOS',
                                'to' => 'van@mmadauto.com',
                                'address' => $address,
                                'change' => $change
                                
                            ];
    
                            print_r($message_data);
                            $em->send($message_data);
    

                        }else{

                            echo 'create order';

                            $inbound_id = $order->inbound_id;
                            //create teapplix order
                            //if success 
                            //send edi ack

                            /*
                            $cr = $po->create_teapplix_order($inbound_id, $debug);

                            print_r($cr);
                        
                            if($cr['success']){
                                $r = $edi->edi_ack($inbound_id, $debug);

                                print_r($r);
                            }
                            */


                        }


                    }else{
                        //email error with address and po number
                        $em = new MyEmail();
                        $address = null;
                        $change = null;

                        if(isset($r['address'])){
                            $address = $r['address'];
                        }

                        if(isset($r['change'])){

                            $change = $r['change'];

                        }

                        $body = "Purachase Order Number: $order->po_number failed address check, teapplix order was not created..";
                        $body .=$r['response'];
                        
                        $message_data = [
                            'title' => "Address Check failed",
                            'body' => $body,
                            'from' => 'admin@mmadauto.com',
                            'subject' => 'EDI Address Checked Failed..',
                            'to' => 'van@mmadauto.com',
                            'address' => $address,
                            'change' => $change
                            
                        ];

                        print_r($message_data);
                        $em->send($message_data);


                    }
            }

       }

        //pull order status new with po_id with shipto
        //check shipto address
        //good: create teapplix order
        //no good: email po with reject address

        //po with order_id
        //send po ack


        //check shipping status for po with po ack
        //good: send shipment notice and invoice


       

        //shipped status, trackingnumber  send shipment notice

        //shipping notice status, send invoice notice

        


    });

    Route::get('/check_shipping', function () {
        //po ack status, and order_id, check shipping status
       
        echo '<pre/>';
        $debug = 1;
        $o = new PurchaseOrder();
        $orders = $o->get_orders_to_check_shipping();

        if($orders){
            \Log::channel('shipping')->info($orders);
            foreach($orders as $order){

                \Log::channel('shipping')->info((array)$order);

                $rs = $o->check_shipping_status($order->order_id);

                \Log::channel('shipping')->info($rs);
                if($rs['success']){
                    \Log::channel('shipping')->info("$order->order_id shipping data updated.");
                }else{
                    \Log::channel('shipping')->info("no data udpated");
                } 

            }
            \Log::channel('shipping')->info("there were orders.");
        }else{
            \Log::channel('shipping')->info("no orders found..");
        }


    });

    Route::get('/automate_shipping_notice', function () {
       
       
        echo '<pre/>';
        \Log::channel('shipping_notice')->info("shipping notice");
        $o = new PurchaseOrder();
        $orders = $o->get_orders_for_shipment_notice();

        \Log::channel('shipping_notice')->info( $orders );

        if(count($orders)){
            $edi = app(MyEdi::class);
            foreach($orders as $order){
              

                $rs = $edi->send_shipping_notice($order, $debug = 1);

                \Log::channel('shipping_notice')->info($rs);
                if($rs['success']){
                    \Log::channel('shipping_notice')->info("$order->order_id shipping notice sent.");
                    $raw = $rs['data'];
                    \Log::channel('shipping_notice')->info("$raw");
                }else{
                    \Log::channel('shipping_notice')->info("shipping notice fail to send");
                } 

            }
           
        }else{
            \Log::channel('shipping_notice')->info("No Shipped Status..no orders found..");
        }


    });

    Route::get('/check_address/{id}', function ($po_id) {
       
       
        echo '<pre/>';
        $po = New PurchaseOrder();

        $r = $po->validate_address($po_id);
        print_r($r);


    });

    Route::get('/check_tracking/{id}', function ($id) {
       
       
        echo '<pre/>';
    
        $po = New PurchaseOrder();

        $r = $po->check_tracking($id);
        print_r($r);

      

    }); 

    Route::get('/check_tracking_update/{id}', function ($id) {
       
       
        echo '<pre/>';
    
        $po = New PurchaseOrder();

        $r = $po->check_tracking_update($id);
        print_r($r);

      

    }); 
    
    
    Route::get('/check_tracking_test/{id}', function ($id) {
       
       
        echo '<pre/>';
    
        $po = New PurchaseOrder();
        $r = $po->check_tracking_test($id);
       


    });

    Route::get('/fix_sku/{po_id}', function ($po_id) {
       
       
        echo '<pre/>';
    
        $o = New OrderItem();

        $r = $o->fix_sku($po_id);
        print_r($r);


    });

    Route::get('/test_order_tea', function () {


        echo '<pre/>';

        $po = app(PurchaseOrder::class);
        $orders_to_validate = $po->get_orders_to_validate_address();
        print_r($orders_to_validate);


    });

    Route::get('/purchase_order_reject_autoany/{po_id}', function ($inbound_id) {
        echo '<pre/>';

        $edi = app(MyEdi::class);
        //autoany RJ, turn5 RD
        $data = $edi->autoany_po_ack_rej($inbound_id);
       
        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $po_id = $in->po_id;
                
                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';
                $post = 'CANCELLATION';

                $file_name = $f->file_name($vendor_file_name, '855', $in->po_number, $pre, $post);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();

               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                    $in->status = 'PO Reject';
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }

    });


    Route::get('/purchase_order_delay_autoany/{po_id}', function ($inbound_id) {
        echo '<pre/>';

        $edi = app(MyEdi::class);
        //autoany RJ, turn5 RD
        $data = $edi->autoany_po_ack_delay($inbound_id);
       
        if($data['success']){

            $in = X12Inbound::where('id', $inbound_id)->firstorFail();
            if($in){
                $vendor = $in->vendor;
                $po_id = $in->po_id;
                
                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';
                $post = 'DELAY';

                $file_name = $f->file_name($vendor_file_name, '855', $in->po_number, $pre, $post);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();

               
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                    $in->status = 'PO Delay';
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
                
    
            }else{
                echo 'inbound not found..';
            }
        }else{
            echo $data['response'];
        }

    });


    Route::get('/shipment_notice_all/{po_id}', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);

        $in = X12Inbound::where('id', $inbound_id)->firstorFail();
        if($in){
            $vendor = $in->vendor;
            $po_id = $in->po_id;
            $po_shipment_name = $vendor.'_po_shipment_notice';

            $data = $edi->$po_shipment_name($inbound_id);
            if($data['success']){

                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';

                $file_name = $f->file_name($vendor_file_name, '856', $in->po_number, $pre);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
            
                
            }else{
                echo $data['response'];
            }

        }else{
            echo 'inbound not found..'; 
        }
    
       
    });



    Route::get('/shipment_notice_all_ns/{po_id}', function ($inbound_id) {
        echo '<pre/>';
        $edi = app(MyEdi::class);

        $in = X12Inbound::where('id', $inbound_id)->firstorFail();
        if($in){
            $vendor = $in->vendor;
            $po_id = $in->po_id;
            $po_shipment_name = $vendor.'_po_shipment_notice';

            $data = $edi->$po_shipment_name($inbound_id);
            if($data['success']){

                $f = app(MyFile::class);
                $vendor_file_name = $vendor.'_file_name';

                $pre = 'SS';

                $file_name = $f->file_name($vendor_file_name, '856', $in->po_number, $pre);
                
                $raw = $data['data'];
                print_r($file_name);
                echo '<br/>';
                print_r($data['data']);
                //die();
                
                
                $s = $f->send($vendor, $file_name, $raw);
                if($s){
        
                    echo '<br/>';
                    echo "$vendor $file_name sent";
                

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
                        echo 'outbound saved <br/>';
                    }else{
                        echo 'outbound was not saved <br/>'; 
                    }

                }else{
                    echo 'failed to send edi file';
                }
            
                
            }else{
                echo $data['response'];
            }

        }else{
            echo 'inbound not found..'; 
        }
    
       
    });


    Route::get('/custom_test', function () {
       
       
        echo '<pre/>';
    
        $po = new PurchaseOrder();
        //$t = $po->get_ordered_vendor('turn5');
        $inbound_id = 0;

        $t =  X12Inbound::firstWhere('id',$inbound_id);
        print_r($t);
      
        if($t){
            print_r($t);
        }else{
            echo 'no data';
        }
        


    });


    Route::get('/inbound_remove_test/{id}', function ($id) {
    
        echo '<pre/>';
        $f = app(MyFile::class);
        $vendor = New Vendor();

        $detail= $vendor->get_vendor($id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $files = $f->view_inbound($vendor_name);
            print_r($files);
           // die();
            if($files){
                foreach($files as $to_get){

                    $e = Storage::disk('sftp.'.$vendor_name)->exists($to_get);

                    if ($e) {
                        echo "E::$e:::$to_get:: EXISTS";
                    }

                    $d = Storage::disk('sftp.'.$vendor_name)->delete($to_get);

                    echo "$vendor_name::$to_get  ::::DD:: $d <br/>";

                    if($d){
                        echo "$to_get removed <br/>";
                    }
                }
               
            }

        }else{
            echo 'data no found..';
        }

      
        

    });

    Route::get('/test_create_teapplix_order_po/{inbound_id}', function ($inbound_id) {
        echo '<pre/>';

        $po = new PurchaseOrder();
        $tea = app(TeapplixOrder::class);
        $order = $po->purchase_order_to_order_inbound($inbound_id, $debug=1);
        //print_r($order);die();

        $r = PurchaseOrder::where('inbound_id', $inbound_id)->first();
        if($r){
            
            if($r->order_id){
                echo "Teapplix:$r->order_id  exist for this PO..<br/>";
            }else{

               // echo 'create teaapplix order:<br/>' die();
                    $tea_data = array(
                        'Operation'=>'Submit',
                        'Orders' => [$order]
                    );
                
                    $result = $tea->create_order($tea_data);
            
                
                    if($result['success']){
            
                        $r = $po->update_order_id($inbound_id, $result['data']['TxnId']);
                        if($r){
                            echo 'teapplix order created and orderid  saved..<br/>';
            
                            $in = X12Inbound::where('id', $inbound_id)->first();
                            $in->status = 'Ordered';
                            $in->save();
            
                        }else{
                            echo 'orderid not saved..<br/>';
                        }
            
                    }else{
                        echo "failed to create order <br/>";
                        print_r($result['response']);
                    }
            }

        }else{
            echo "order not found... <br/>";
            

        }


      
       
    });



    

    
    
require __DIR__.'/auth.php';
