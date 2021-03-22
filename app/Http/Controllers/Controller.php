<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SendAdminEmailNotification;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function api_chatbot_universal_gateway($postdata,$type)
    {

        if($type == 'template'){
            $url = 'https://api.chatbotsghana.com/api/send/text/msg/template';
        }else if($type == 'text'){
            $url = 'https://api.chatbotsghana.com/api/send/text/msg';
        }else if($type == 'image'){
            $url = 'https://api.chatbotsghana.com/api/send/image/msg';
        }else if($type == 'file'){
            $url = 'https://api.chatbotsghana.com/api/send/file/msg';
        }else if($type == 'audio'){
            $url = 'https://api.chatbotsghana.com/api/send/audio/msg';
        }else if($type == 'video'){
            $url = 'https://api.chabotsghana.com/api/send/video/msg';
        }else if($type == 'location'){
            $url = 'https://api.chatbotsghana.com/api/send/location/msg';
        }else if($type == 'optin'){
            $url = 'https://api.Chatbotsghana.com/api/opt/in';
        }else if($type == 'optout'){
            $url = 'https://api.chatbotsghana.com/api/opt/out';
        }else if($type == 'users'){
            $url = 'https://api.chatbotsghana.com/api/opt/users';
        }else{
            $url = null;
        }
        // dd($postdata);
        if($postdata &&  $url){
            $curl = curl_init();
                curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 300,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => json_encode($postdata),
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));
            //dd($curl);
            $response = curl_exec($curl);
            $err = curl_error($curl);

            curl_close($curl);
            
            // $this->writelog("Send Template Message Gateway Response: ".$response."\n",1);
            $response = json_decode($response, true);
            // dd($response);
            if(!$err){
                return $response;
            }else{
                return "error";
            }
        }else{
            return "empty";
        }
    }
    
    public function chatbot_callback_api(Request $request)
    {
        // $this->writelog("Callback Payload: ".json_encode($request->all())."\n",1);
        try{
            $message_results = json_decode(json_encode($request->all()));
            // dd($message_results);
            Notification::route('mail', 'ebenezer.ashiakwei@wigal.com.gh')->notify(new SendAdminEmailNotification(json_encode($message_results)));
            // foreach($message_results as $input)
            // {
            //     mysqli_query($localcon,"update sent_messages set status ='{$input->status}',sent_time = UTC_TIMESTAMP, reason='".($input->status == 'DELIVRD' ? 'Message Successfully Delivered' : 'Message Failed To Send.')."',routemsgid='".($input->routemsgid ?? null)."',smsstatuscode='".($input->statuscode ?? null)."' where  service_id=1 and smsmessageid='{$input->queueid}'");
            // }

            // mysqli_query($localcon,"COMMIT");

            return array("code" => 200, "message"=> "MESSAGE ACCEPTED", "result"=> []);
        }catch(\Exception $e){     
            // dd($e);  
            Notification::route('mail', (env("EXCEPTION_EMAIL", 'ebenezer.ashiakwei@wigal.com.gh')))->notify(new SendAdminEmailNotification(json_encode($e)));
            // $this->writelog("Error Received: ".$e."\n",1);
            return array("code" => 400, "message"=> "MESSAGE REJECTED", "result"=> []);
        }
    }


}
