<?php
   
namespace App\Console\Commands;
use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Services\MyEdi\MyEdi;
use App\Services\MyFile\MyFile;
use Illuminate\Support\Facades\Storage;
use App\Models\PurchaseOrder;
use App\Services\Notification\MyEmail;
use App\Models\X12Inbound;
   
class CreateTeaOrderCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createteaorder:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check address and OOS, create Teapplix order and follow po ACK';
    
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * Execute the console command.
     *
     * @return mixed
     *     * * * * * cd /var/www/edi.dealeraccess.org/ && php artisan schedule:run >> /dev/null 2>&1
     */
    public function handle()
    {
       
        
        \Log::channel('teapplix_notice')->info("teapplix!");
      
        $debug = 0;
        $po = app(PurchaseOrder::class);
        $edi = app(MyEdi::class);
        
        //default get turn5 orders
        $orders_to_validate = $po->get_orders_to_validate_address();
       
        if(!$orders_to_validate->isEmpty()){
            \Log::channel('teapplix_notice')->info("got orders to validate!");
                
                $em = new MyEmail();
                foreach( $orders_to_validate as $order){
                   
                    $r = $po->validate_address($order->id);
                  
                    $inbound_id = $order->inbound_id;
                    if($r['success']){
                        $oos = $po->check_oos($order->id);
                        

                        if($oos['oos']){
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
                                'cc'=> ['daniel@mmadauto.com','danica@mmadauto.com'],
                                'address' => $address,
                                'change' => $change
                                
                            ];
                            \Log::channel('teapplix_notice')->info($body);
                            \Log::channel('teapplix_notice')->info($message_data);
                            $em->send($message_data);
                            $in = X12Inbound::where('id', $inbound_id)->first();
                            $in->status = 'oos';
                            $in->save();
                            \Log::channel('teapplix_notice')->info('update status to oos');
    

                        }else{ 
                            
                    
                            $cr = $po->create_teapplix_order($inbound_id, $debug);
                           
                            if($cr['success']){
                                \Log::channel('teapplix_notice')->info('teapplix Order created..');
                                \Log::channel('teapplix_notice')->info($cr);
                                $re = $edi->edi_ack($order, $debug);
                                if($re['success']){
                                    \Log::channel('teapplix_notice')->info($re);
                                    \Log::channel('teapplix_notice')->info('Po Ack sent');
                                }else{
                                    \Log::channel('teapplix_notice')->info($re);
                                    \Log::channel('teapplix_notice')->info("$inbound_id PO Ack fail to send");
                                }

                               
                            }else{
                                \Log::channel('teapplix_notice')->info("inbound_id::teapplix Order Failed to created.");
                                \Log::channel('teapplix_notice')->info($cr);
                            }
                            
                        }

                    }else{
                        
                        \Log::channel('teapplix_notice')->info('email error with address and po number');
                      
                        $address = null;
                        $change = null;

                        if(isset($r['address'])){
                            $address = $r['address'];
                        }

                        if(isset($r['change'])){

                            $change = $r['change'];

                        }

                        $body = "Purachase Order Number: $order->po_number failed address check, teapplix order was not created..";
                        $body .="..teapplix has no ".$r['response'];

                        $message_data = [
                            'title' => "Address Check failed",
                            'body' => $body,
                            'from' => 'admin@mmadauto.com',
                            'subject' => 'EDI Address Checked Failed..',
                            'to' => 'van@mmadauto.com',
                            'cc'=> ['daniel@mmadauto.com','danica@mmadauto.com'],
                            'address' => $address,
                            'change' => $change
                            
                        ];
                        \Log::channel('teapplix_notice')->info($body);
                        \Log::channel('teapplix_notice')->info($message_data);
                        $em->send($message_data);

                        $in = X12Inbound::where('id', $inbound_id)->first();
                        $in->status = 'bad address';
                        $in->save();
                        \Log::channel('teapplix_notice')->info('update status to bad address');
                

                    }
                
                }

        }else{
            \Log::channel('teapplix_notice')->info('no order to check addresss and oos, to create teapplix order..');
        }
        
    
        $ordered = $po->get_ordered();
        //only po ack turn5 orderes

        if(!$ordered->isEmpty()){
            foreach($ordered as $ordero){
                $inbound_id = $ordero->inbound_id;
                $reo = $edi->edi_ack($ordero, $debug);
                if($reo['success']){
                    \Log::channel('teapplix_notice')->info($reo);
                    \Log::channel('teapplix_notice')->info('Po Ack sent for ordered..');
                }else{
                    \Log::channel('teapplix_notice')->info($reo);
                    \Log::channel('teapplix_notice')->info("$inbound_id PO Ack fail to send");
                }

            }
        }
    }

}