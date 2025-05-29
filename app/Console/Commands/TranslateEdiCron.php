<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Models\X12Inbound;
use App\Models\OrderItem;
   
class TranslateEdiCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'poedi:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'translate EDI 850 to readable purchase order.';
    
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
       
        //\Log::channel('inbound')->info("inbound!");
      
        $in = New X12Inbound();
        $vendor = New Vendor();
        $vendor_id = 2;
        // 2 is turn5
       
        $detail= $vendor->get_vendor($vendor_id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $ins = $in->get_for_translate($vendor_name);

            \Log::channel('po')->info('turn5::');

            if(count($ins)){
    
                foreach($ins as $i){
                    $r = $in->text_purchase_order($i);
                    $po = $i->po_number;
                    $vendor = $i->vendor;
         
                    if($r['success']){
    
                        \Log::channel('po')->info("Purchase Order: $po added for Vender: $vendor <a href='/poinbound'>link</a> <br/>");
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
                        \Log::channel('po')->info("$po failed to add <br/>");
                        $error = $r['response'];
                        \Log::channel('po')->info(" error:$error..<br/>");
                    }
    
                }
                $vendor = New Vendor();
                $vendor->update_last($vendor_id,'po_updated_at');
    
            }

        }else{
            \Log::channel('po')->info('data no found..turn5');
        }



        $vendor_id = 4;
        // 4 is autoany
       
        $detail= $vendor->get_vendor($vendor_id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $ins = $in->get_for_translate($vendor_name);

            \Log::channel('po')->info('autoany::');

            if(count($ins)){
    
                foreach($ins as $i){
                    $r = $in->text_purchase_order($i);
                    $po = $i->po_number;
                    $vendor = $i->vendor;
         
                    if($r['success']){
    
                        \Log::channel('po')->info("Purchase Order: $po added for Vender: $vendor <a href='/poinbound'>link</a> <br/>");
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
                        \Log::channel('po')->info("$po failed to add <br/>");
                        $error = $r['response'];
                        \Log::channel('po')->info(" error:$error..<br/>");
                    }
    
                }
                $vendor = New Vendor();
                $vendor->update_last($vendor_id,'po_updated_at');
    
            }

        }else{
            \Log::channel('po')->info('data no found..autoany');
        }

        
    }

}