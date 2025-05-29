<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
   
class UpdateShippingCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateshipping:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check if order is shipping and update shipdate and tracking number';
    
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
        $orders = $o->get_orders_to_check_shipping();

        if(!$orders->isEmpty()){
           
            \Log::channel('shipping')->info("TURN5::");
            foreach($orders as $order){
                $rs = $o->check_shipping_status($order->order_id);


            \Log::channel('shipping')->info("orderid:$order->order_id");
                \Log::channel('shipping')->info($rs);
                if($rs['success']){
                    \Log::channel('shipping')->info("$order->order_id shipping data updated.");
                }else{
                    \Log::channel('shipping')->info("no data udpated");
                } 

            }
           
        }else{
            \Log::channel('shipping')->info("TURN5::");
            \Log::channel('shipping')->info("check shipping Status..no orders found..");
        }


        $orders = $o->autoany_get_orders_to_check_shipping();

        if(!$orders->isEmpty()){
            \Log::channel('shipping')->info("Autoany::");
            foreach($orders as $order){
                $rs = $o->check_shipping_status($order->order_id);


            \Log::channel('shipping')->info("orderid:$order->order_id");
                \Log::channel('shipping')->info($rs);
                if($rs['success']){
                    \Log::channel('shipping')->info("$order->order_id shipping data updated.");
                }else{
                    \Log::channel('shipping')->info("no data udpated");
                } 

            }
           
        }else{
            \Log::channel('shipping')->info("Autoany::");
            \Log::channel('shipping')->info("check shipping Status..no orders found..");
        }
        
    }

}