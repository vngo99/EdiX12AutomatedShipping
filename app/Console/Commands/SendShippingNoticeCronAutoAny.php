<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\MyEdi\MyEdi;
   
class SendShippingNoticeCronAutoAny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendshippingnoticeautoany:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'check for shipped status send shipping notice with tracking';
    
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
       
        $o = new PurchaseOrder();
        $orders = $o->get_orders_for_shipment_notice('autoany');
        $channel = 'shipping_notice_autoany';

        if(!$orders->isEmpty()){
            $edi = app(MyEdi::class);

            \Log::channel($channel)->info('shipping_notice::');
            
            foreach($orders as $order){
    
                $rs = $edi->send_shipping_notice($order, $debug = 0);

                \Log::channel( $channel)->info($rs);
                if($rs['success']){
                    \Log::channel( $channel)->info("$order->order_id shipping notice sent.");
                }else{
                    \Log::channel( $channel)->info("shipping notice fail to send");
                } 
            }
           
        }else{
            \Log::channel( $channel)->info("No Shipped Status..no orders found..");
        }
        
    }

}