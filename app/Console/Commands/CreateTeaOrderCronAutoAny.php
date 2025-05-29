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
   
class CreateTeaOrderCronAutoAny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'createteaorderautoany:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check address and OOS, create Teapplix order';
    
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
       
        $channel_name = 'teapplix_notice_autoany';
        $from = 'admin@mmadauto.com';
        $to = 'van@mmadauto.com';
        $cc =  ['daniel@mmadauto.com','danica@mmadauto.com'];

        \Log::channel($channel_name)->info("teapplix!");
      
        $debug = 0;
        $po = app(PurchaseOrder::class);
        

        $orders_to_validate = $po->get_orders_to_validate_address('autoany');
       
        if(!$orders_to_validate->isEmpty()){
            \Log::channel($channel_name)->info("got orders to validate!");
                $edi = app(MyEdi::class);
                $em = new MyEmail();
                foreach( $orders_to_validate as $order){
                   
                    $r = $po->validate_address($order->id);
                  
                    $inbound_id = $order->inbound_id;
                    $in = X12Inbound::where('id', $inbound_id)->first();
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
                                'from' => $from,
                                'subject' => 'EDI OOS',
                                'to' => $to,
                                'cc'=> $cc,
                                'address' => $address,
                                'change' => $change
                                
                            ];
                            \Log::channel($channel_name)->info($body);
                            \Log::channel($channel_name)->info($message_data);

                            $em->send($message_data);

                            $in->status = 'oos';
                            $in->save();
    
                          
                           
                           
                            \Log::channel($channel_name)->info('update status to oos');
    

                        }else{ 
                            
                    
                            $cr = $po->create_teapplix_order($inbound_id, $debug);
                           
                            if($cr['success']){
                                \Log::channel($channel_name)->info('teapplix Order created..');
                                \Log::channel($channel_name)->info($cr);
                                
                                if(!$in->isEmpty()){
                                    $in->status = 'Ordered';
                                    $in->save();
        
                                }else{
                                    \Log::channel($channel_name)->info('inbound data not found to saved Ordered status');
                                }
                                if($re['success']){
                                    \Log::channel($channel_name)->info($re);
                                    \Log::channel($channel_name)->info('Po Ack sent');
                                }else{
                                    \Log::channel($channel_name)->info($re);
                                    \Log::channel($channel_name)->info("$inbound_id PO Ack fail to send");
                                }

                               
                            }else{
                                \Log::channel($channel_name)->info("inbound_id::teapplix Order Failed to created.");
                                \Log::channel($channel_name)->info($cr);
                            }
                            
                        }

                    }else{
                        
                        \Log::channel($channel_name)->info('email error with address and po number');
                      
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
                            'from' => $from,
                            'subject' => 'EDI Address Checked Failed..', 
                            'to' => $to,
                            'cc'=> $cc,
                            'address' => $address,
                            'change' => $change
                            
                        ];
                        \Log::channel($channel_name)->info($body);
                        \Log::channel($channel_name)->info($message_data);
                        $em->send($message_data);

                        if(!$in->isEmpty()){
                            $in->status = 'bad address';
                            $in->save();

                        }else{
                            \Log::channel($channel_name)->info('inbound data not found to saved Ordered status');
                        }
                        \Log::channel($channel_name)->info('update status to bad address');
                

                    }
                
                }

        }else{
            \Log::channel($channel_name)->info('no order to check addresss and oos, to create teapplix order..');
        }
        
    }

}