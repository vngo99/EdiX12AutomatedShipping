<?php
namespace App\Services\MyFile;

use Illuminate\Support\Facades\Storage;
use App\Models\X12Inbound;
use App\Models\Vendor;
use App\Services\MyEdi\MyEdi;
use Uhin\X12Parser\Serializer\X12Serializer;

class MyFile{

    public function __construct() {}

    public function test(){
        echo '<pre/>';
    
        //$r =   Storage::disk('s3');
       
        $r = Storage::disk('s3.home')->files('aaptest');
        print_r($r);
    
        $a = Storage::disk('s3.aap')->files('inbox');
    
        print_r($a);
        $b = Storage::disk('s3.turn5')->files('inbox');
    
        print_r($b);
    
    }

    public function view_inbound($type = 'home'){
        $config =  config('sftp');
        $vendor =$config[$type];
        $files = Storage::disk('sftp.'.$type)->files($vendor['in']);
        return $files;

    }


    public function view_inbound_s3($type = 'home'){

        $files = null;
        switch($type){
            case 'home':
                $files = Storage::disk('s3.home')->files('aaptest');
                break;
            case 'aap':
                $files =  Storage::disk('s3.aap')->files('inbox');
                break;
            case 'turn5':
                $files = Storage::disk('s3.turn5')->files('inbox');
                break;
            default:
                break;
        }

        return $files;

    }

    public function get_inbound_file($name, $disk="sftp.home"){
        if($name){
            $content = null;
            if (Storage::disk($disk)->exists($name)) {
                $content = Storage::disk($disk)->get($name);
            }
            return $content;

        }
        return false;

    }

    public function get_all_inbound_files($file_names, $disk="sftp.home"){
        if($file_names){
            $content = null;
            foreach($file_names as $names){

                if (Storage::disk($disk)->exists($name)) {
                    $content[] = Storage::disk($disk)->get($name);
                }

            }
            return $content;

        }
        return false;

    }

    public function save_inbound_text($vendor, $text, $file_name = null){

        $edi = app(MyEdi::class);

        try{
            if(preg_match('/^ISA/i', $text)){
                $x12 = $edi->parse_raw($text);
                $edi->process->set_x12($x12);
                $purchase_order_number = $edi->process->purchase_order_number();
                $edi_code = $edi->process->edi_code();
                $text_date = $edi->process->edi_date();
                $serializer = new X12Serializer($x12);
            
                // Generate the raw X12 string
                $raw_x12 = $serializer->serialize();
    
                $entry = X12Inbound::firstOrCreate([
                    'po_number' => $purchase_order_number,
                    'edi_code' => $edi_code,
                    'vendor' => $vendor,
                    'raw' => $raw_x12,
                    'file_name' =>$file_name,
                    'date' => date("y-m-d", strtotime($text_date))
                ]);
                if($entry){
                    $v = Vendor::where('nick_name', $vendor)->firstorFail();
                    if($v){

                        $v->interchange_control_number +=1;
                        $v->functional_control_number +=1;
                        $v->transaction_control_number +=1;
                    }
                    return $entry;
                }
                return false;
                

            }
            return false;

           
        }catch(\Exception $e){

            return false;

        }
        

    }

    public function send($vendor, $file_name, $text){

        switch($vendor){
            case 'test':
                $path = '/aaptest/'.$file_name;
                $r = Storage::disk('sftp.home')->put($path,$text);
                break;
            case 'turn5':
                $path = '/Outgoing/'.$file_name;
                $r = Storage::disk('sftp.turn5')->put($path,$text);
                break;
            case 'aap':
                $path = '/Outbound/'.$file_name;
                $r = Storage::disk('sftp.aap')->put($path,$text);
                break;
            case 'autoany':
                $path = '/Inbound/'.$file_name;
                $r = Storage::disk('sftp.autoany')->put($path,$text);
                break;
            default:
                return false;
                break;

        }

       return $r;
    }

    public function turn5_file_name($type, $po, $pre='SS'){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Y-m-dTH-i-s');
        switch($type){
            case '810':
                $file_name = $pre.'_'.$po.'_'.$date.'_'.'810.x12';
                break;
            case '856':
                $file_name = $pre.'_'.$po.'_'.$date.'_'.'856.x12';
                break;
            case '855':
                $file_name = $pre.'_'.$po.'_'.$date.'_'.'855.x12';
                break;
            default:
                break;
        }

        return $file_name;
        
       
    }

    public function autoany_file_name($type, $po, $pre="SS", $post){

        $dt = new \DateTime(null, new \DateTimeZone('UTC'));
        $date = $dt->format('Ymd');
        switch($type){
            case '810':
                $file_name = $pre.'-'.$date.'-'.'Invoice.x12';
                break;
            case '856':
                $file_name = $pre.'-'.$date.'-'.'ASN.x12';
                break;
            case '855':
                if($post){
                    $post = $post.'.x12';
                }else{
                    $post = '855.x12';
                }
                $file_name = $pre.'-'.$date.'-'.$post;
                break;
            default:
                break;
        }

        return $file_name;
        
       
    }

    public function file_name($func, $type, $po, $pre ='SS', $post = NULL){
        return $file_name = $this->$func($type, $po, $pre, $post);
          
    }

}
