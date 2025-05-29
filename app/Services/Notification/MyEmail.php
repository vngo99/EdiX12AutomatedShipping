<?php
namespace App\Services\Notification;

use Illuminate\Support\Facades\Storage;
use App\Mail\EdiNotice;


class MyEmail{

    public function __construct() {}

    /*
    Mail::to($request->user())
    ->cc($moreUsers)
    ->bcc($evenMoreUsers)
    ->send(new OrderShipped($order));
    */


    public function send($params, $view ='emails.ediNotice'){

        $details = [
            'title' => $params['title'],
            'body' => $params['body'],
            'from' => $params['from'],
            'subject' => $params['subject'],
            'view_html' => $view,
            'address' =>$params['address'],
            'change' =>$params['change']
            
        ];
        
        $notice = New EdiNotice($details);
        $to = $params['to'];
        $cc = $params['cc'];

        $r = \Mail::to($to)->cc($cc)->send($notice);

    }



}