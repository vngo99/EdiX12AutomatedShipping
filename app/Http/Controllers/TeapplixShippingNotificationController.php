<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\PostTest;

class TeapplixShippingNotificationController extends Controller
{
    public function index(Request $request){
        $data = $request->all();
        if($data){
            if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_USER'])) {
                $entry = PostTest::firstOrCreate(array( 'name'=>'teapplix','data'=>'no data','note'=>'teapplix shipping notification no username and password'));
                header('WWW-Authenticate: Basic realm="basic authenticate"');
                header('HTTP/1.0 401 Unauthorized');
                $arr = array('status' => 'Unauthorized');
                echo json_encode($arr);
                exit;
            } else {
                $config =  config('teapplix');
                $account_setup =$config['shipping_notification'];
                $user = $_SERVER['PHP_AUTH_USER'];
                $pass = $_SERVER['PHP_AUTH_PW'];
                if($user == $account_setup['username'] && $pass = $account_setup['password']){
                    PostTest::firstOrCreate(array('name'=>'teapplix','data'=>json_encode($data),'note'=>'teapplix shipping notification auth'));
                    $data_obj = json_decode(json_decode(json_encode($data),true, 512));
                    header('HTTP/1.1 200 Ok');
                    $arr = array('status' => 'Accepted');
                    echo json_encode($arr);
                }else{
                    PostTest::firstOrCreate(array('name'=>'teapplix','data'=>json_encode($data),'note'=>'teapplix failed Authorization'));
                    header('HTTP/1.0 401 Unauthorized');
                    $arr = array('status' => 'Unauthorized');
                    echo json_encode($arr);
                    exit();
                }

            }

        }else{
            $entry = PostTest::firstOrCreate(array('name'=>'teapplix','data'=>'no data','note'=>'teapplix shipping notification no data'));
            header('HTTP/1.0 401 Unauthorized');
            $arr = array('status' => 'Unauthorized');
            echo json_encode($arr);
            exit();
        }   
    }
}
