<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Services\MyFile\MyFile;
use App\Services\MyEdi\MyEdi;
use App\Models\PurchaseOrder;

class InboxController extends Controller
{
    public function index($vendor)
    {

        switch($vendor){
            case 'test':
            case 'home':
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
                $disk = 's3.aap';
              
                break;
            default:
                
                break;
        }
        $files = Storage::disk($disk)->files($dir);
        $new_files = null;
        $f = app(MyFile::class);
        $edi = app(MyEdi::class);
        $message_del = array();
        
        ini_set('max_execution_time', 0);
        if($files){
            foreach($files as $file){
                $c = $f->get_inbound_file($file,'sftp.'.$vendor);
                $x12 = $edi->parse_raw($c);
                $edi->process->set_x12($x12);
                $purchase_order_number = $edi->process->purchase_order_number();

                $exist = PurchaseOrder::where('po_number', $purchase_order_number)->first();
                if($exist == null){
                    $t = explode('/',$file);
                    $new_files[] = array(
                        'file' => $file,
                        'name' => $t[1]
                    );

                }else{

                    $d = Storage::disk($disk)->delete($file);
                    if($d){
                       $message_del[] = $file ."removed";
                    }
                }
            }
        }

        return view('inbox',[
            'vendor'=>$vendor,
            'files' => $new_files,
            'del' => $message_del
        ]);
    }
}
