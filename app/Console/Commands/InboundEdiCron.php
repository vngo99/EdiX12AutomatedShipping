<?php
   
namespace App\Console\Commands;
   
use Illuminate\Console\Command;
use App\Models\Vendor;
use App\Services\MyFile\MyFile;
use Illuminate\Support\Facades\Storage;
   
class InboundEdiCron extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pulledi:cron';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull Turn5 EDI 850 from Incoming folder';
    
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
      
        $f = app(MyFile::class);
        $vendor = New Vendor();
        //id 2 is turn5 automated
        $id = 2;

        $detail= $vendor->get_vendor($id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $files = $f->view_inbound($vendor_name);
            \Log::channel('inbound')->info("turn5::");
            \Log::channel('inbound')->info($files);
            if($files){
                foreach($files as $to_get){
                    $c = $f->get_inbound_file($to_get,'sftp.'.$vendor_name);
                    \Log::channel('inbound')->info($c);
                    $s = $f->save_inbound_text($vendor_name,$c,$to_get);
                    if($s){
                       
                        \Log::channel('inbound')->info("$to_get saved <br/>");
                        
                        $d = Storage::disk('sftp.'.$vendor_name)->delete($to_get);
                        if($d){

                            \Log::channel('inbound')->info("$to_get removed <br/>");
                        }
                    }
                }
                $vendor = New Vendor();
                $vendor->update_last($id,'edi_updated_at');
            }

        }else{
            
            \Log::channel('inbound')->info('data no found..turn5');
        
        }


        $id = 4;

        $detail= $vendor->get_vendor($id);
        if($detail){
            $vendor_name = $detail->nick_name;
            $files = $f->view_inbound($vendor_name);
            \Log::channel('inbound')->info("autoany::");
            \Log::channel('inbound')->info($files);
            if($files){
                foreach($files as $to_get){
                    $c = $f->get_inbound_file($to_get,'sftp.'.$vendor_name);
                    \Log::channel('inbound')->info($c);
                    $s = $f->save_inbound_text($vendor_name,$c,$to_get);
                    if($s){
                       
                        \Log::channel('inbound')->info("$to_get saved <br/>");
                        
                        $d = Storage::disk('sftp.'.$vendor_name)->delete($to_get);
                        if($d){

                            \Log::channel('inbound')->info("$to_get removed <br/>");
                        }
                    }
                }
                $vendor = New Vendor();
                $vendor->update_last($id,'edi_updated_at');
            }

        }else{
            
            \Log::channel('inbound')->info('data no found..autoany');
        
        }

        
    }

}