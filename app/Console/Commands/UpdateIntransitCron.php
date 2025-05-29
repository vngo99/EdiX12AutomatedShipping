<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\X12Inbound;
use App\Models\PurchaseOrder;
use App\Services\Teapplix\Tracking;
   
class UpdateIntransitCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'updateintransit:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check shipping if in transit';
    
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
        $t = New Tracking();
      
        $orders = $o->get_orders_for_transit();

        if(!$orders->isEmpty()){
           
            \Log::channel('shipping')->info("INTRANSIT::");
            foreach($orders as $order){

                $tracking_info = array(
                    'carrier' => $order->carrier_name,
                    'tracking_number'=>$order->tracking_number
                );

                \Log::channel('shipping')->info("po_Id: $order->po_number:");
                \Log::channel('shipping')->info($tracking_info);

                $tr = $t->check_intransit_for_debit_invoice($tracking_info);
                
                if($tr){

                    $inbound_id = $order->inbound_id;
                    $in = X12Inbound::where('id', $inbound_id)->first();
                    $in->intransit = 1;
                    $in->save();
                    \Log::channel('shipping')->info("package update intransit");

                    
                }else{
                    \Log::channel('shipping')->info("package not intransit");
                }

                sleep(10);

            }
           
        }else{
          
            \Log::channel('shipping')->info("check shipping Status..no orders found..");
        }

        
    }

}