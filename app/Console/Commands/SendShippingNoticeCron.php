<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\MyEdi\MyEdi;
   
class SendShippingNoticeCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendshippingnotice:cron';
    
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
       
        
        $o = app(PurchaseOrder::class);
        $edi = app(MyEdi::class);
        $orders = $o->get_orders_for_shipment_notice();
        
        \Log::channel('shipping_notice')->info('shipping_notice::');

        if(!$orders->isEmpty()){
           
            foreach($orders as $order){
    
                $rs = $edi->send_shipping_notice($order, $debug = 0);

                \Log::channel('shipping_notice')->info($rs);
                if($rs['success']){
                    \Log::channel('shipping_notice')->info("$order->order_id shipping notice sent.");
                }else{
                    \Log::channel('shipping_notice')->info("shipping notice fail to send");
                } 
            }
           
        }else{
            \Log::channel('shipping_notice')->info("No Shipped Status..no orders found..");
        }
        
    }

}