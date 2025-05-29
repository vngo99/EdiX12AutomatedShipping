<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\PurchaseOrder;
use App\Services\MyEdi\MyEdi;
use App\Services\Teapplix\Tracking;
   
class SendInvoiceNoticeCronAutoAny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sendinvoicenoticeautoany:cron';
    
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
        $t = New Tracking();
        $channel = 'invoice_notice_autoany';
        $orders = $o->get_orders_for_invoice_notice('autoany');

        if(!$orders->isEmpty()){
            $cnt = count($orders);
            \Log::channel( $channel)->info("Number of orders:$cnt::");
            $edi = app(MyEdi::class);
            foreach($orders as $order){

                
                $tracking_info = array(
                    'carrier' => $order->carrier_name,
                    'tracking_number'=>$order->tracking_number
                );
                \Log::channel( $channel)->info("po_id: $order->po_number:");
                \Log::channel( $channel)->info($tracking_info);

                $tr = $t->check_intransit_for_debit_invoice($tracking_info);
                
                if($tr){

                    $rs = $edi->send_invoice($order, $type='DI', $debug = 0);

                    \Log::channel( $channel)->info($rs);
                    if($rs['success']){
                        \Log::channel( $channel)->info("$order->order_id invoice notice sent.");
                    }else{
                        \Log::channel( $channel)->info("invoice notice fail to send");
                    } 
                    
                }else{
                    \Log::channel( $channel)->info("package not intransit, invoice not sent");
                }

                sleep(10);
                
            }
           
        }else{
            \Log::channel( $channel)->info("No Shipped Status..no orders found..");
        }
        
    }

}