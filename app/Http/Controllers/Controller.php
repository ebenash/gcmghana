<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Contact;
use App\Models\Counsellor;
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
        // dd(json_encode($postdata));
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
    
    public function api_chatbot_send_message($message,$phone)
    {
        $postdata = [
            "apikey"=> "c9b0dd70-b0ff-4ce8-9c9d-a3bbb2947fe5",
            "sender"=> "233506758586",
            "destination"=> $phone,
            "botname"=> "GreatCommissionOfGhana",
            "message"=> $message ?? 'NO MESSAGE FOUND'
        ];

        return $this->api_chatbot_universal_gateway($postdata,'text');
    }
    
    public function chatbot_callback_api(Request $request)
    {
        // $this->writelog("Callback Payload: ".json_encode($request->all())."\n",1);
        try{
            $message_results = json_decode(json_encode($request->all()));
            
            if($message_results->type == 'message'){
                $payload = $message_results->payload ?? null;

                if($payload){
                    $sender = $payload->sender ?? null;
                    if($sender){
                        $existing_contact = Contact::where('phone',($sender->phone ?? null))->first();

                        if($existing_contact){
                            $last_contact = $existing_contact->last_contact;
                            if($sender->name != $existing_contact->name){
                                $existing_contact->name = $sender->name;
                                $existing_contact->last_contact = date('Y-m-d H:i:s');
                                $existing_contact->save();
                            }
                            if(date('Y-m-d',strtotime($last_contact)) < date('Y-m-d')){
                                $message = "Welcome back ".($sender->name ?? 'Dear One').", we are happy to have you here and thank you 🙏for  💬messaging us again. \nAkwaaba!!! 🤝 to the Great Commission Movement of Ghana Film 🎞 Project. \n\nWe hope you have seen the Jesus Film, if you want to watch it again or if you haven't watched so far here is the link.\nhttps://www.jesusfilm.org/watch/jesus.html/english.html\n\nYou can type the number you see before the menu to navigate!\n1. Did you Like what you saw on Tv?\n2. I want to chat with a Counsellor";
                                $existing_contact->last_contact = date('Y-m-d H:i:s');
                                $existing_contact->save();
                            }else{
                                if($payload->type == 'text'){
                                    $response = $payload->payload->text ?? null;
                                    if($response == "1"){
                                        $message ="We are happy to hear that you have feedback for us. Please enter your feedback after this message.";
                                    }else if($response == "2"){
                                        $message ="Searching for a counsellor for you...";
                                        $this->api_chatbot_send_message($message,$sender->phone);
                                        $message ="Please note that we will be sending send your contact details to one of our Counsellors. \nPlease type 'SEND TO Counsellor' to procceed and 'CANCEL' to cancel the sending";
            
                                    }else if(strtoupper($response) == "SEND TO Counsellor"){
                                        if(!$existing_contact->counselor_id){
                                            $counsellor = DB::table('contacts as contacts')->rightJoin('counsellors as counsellors','counsellors.id','=','contacts.counselor_id')->select(DB::raw('counsellors.id, counsellors.name, counsellors.phone,IFNULL(count(contacts.counselor_id), 0) as counter'))->groupBy('counsellors.id')->orderBy('counter','desc')->first();
                                        }else{
                                            $counsellor = Counsellor::find();
                                        }
                                        // dd($counsellor);
                                        if($counsellor){
                                            $message = ($sender->name ?? 'Dear One').". Your WhatsApp Number has been sent to Counsellor ".($counsellor->name)." who will be reaching out to you soon. Thank you for reaching out to us!";

                                            $existing_contact->counselor_id = $counsellor->id;
                                            $existing_contact->save();                                    
                                            $counsellor_message = "Hello Counsellor".($counsellor->name ?? '').", A new user has been assigned to you. Details Below. \n\nName: ".($sender->name)." \nWhatsApp Number:".($sender->phone)."\n\n Please reach out to them as soon as possible. \n\nThank you!";
                                            $this->api_chatbot_send_message($counsellor_message,$counsellor->phone);                            
                                        }
                                    }else if(strtoupper($response) == "CANCEL" || strtoupper($response) == "BACK"){
                                        $message ="Process Cancelled.";
                                        $this->api_chatbot_send_message($message,$sender->phone);
                                        $message = "Welcome back ".($sender->name ?? 'Dear One').", we are happy to have you here and thank you 🙏for  💬messaging us again. \nAkwaaba!!! 🤝 to the Great Commission Movement of Ghana Film 🎞 Project. \n\nWe hope you have seen the Jesus Film, if you want to watch it again or if you haven't watched so far here is the link.\nhttps://www.jesusfilm.org/watch/jesus.html/english.html\n\nYou can type the number you see before the menu to navigate!\n1. Did you Like what you saw on Tv?\n2. I want to chat with a Counsellor";
                                    }else{
                                        $message = "Message Recieved.";

                                        $new = new Message;
                                        $new->message = $response;
                                        $new->contact_id = $existing_contact->id;
                                        $new->save();
                                    }
                                }else{
                                    $message = "Response not recognized. Your message must be a text in resopnse to the last message.2";
                                }
                            }

                        }else{
                            $existing_contact = new Contact;
                            $existing_contact->phone = $sender->phone;
                            $existing_contact->name = $sender->name;
                            $existing_contact->country_code = $sender->country_code;
                            $existing_contact->last_contact = date('Y-m-d H:i:s');
                            $existing_contact->save();

                            $message = "Welcome ".($sender->name ?? 'Dear One').", we are happy to have you here and thank you 🙏for  💬messaging us. \nAkwaaba!!! 🤝 to the Great Commission Movement of Ghana Film 🎞 Project. \n\nWe hope you have seen the Jesus Film, if you want to watch it again or if you haven't watched so far here is the link.\nhttps://www.jesusfilm.org/watch/jesus.html/english.html\n\nYou can type the number you see before the menu to navigate!\n1. Did you Like what you saw on Tv?\n2. I want to chat with a Counsellor";
                        }

                        $result = $this->api_chatbot_send_message($message,$sender->phone);

                        // Notification::route('mail', 'ebenezer.ashiakwei@wigal.com.gh')->notify(new SendAdminEmailNotification(json_encode($result)));
                        // dd($message_results);
                        // foreach($message_results as $input)
                        // {
                        //     mysqli_query($localcon,"update sent_messages set status ='{$input->status}',sent_time = UTC_TIMESTAMP, reason='".($input->status == 'DELIVRD' ? 'Message Successfully Delivered' : 'Message Failed To Send.')."',routemsgid='".($input->routemsgid ?? null)."',smsstatuscode='".($input->statuscode ?? null)."' where  service_id=1 and smsmessageid='{$input->queueid}'");
                        // }

                        // mysqli_query($localcon,"COMMIT");
                    }else{
                        return array("code" => 400, "message"=> "Sender Not Found in Payload", "result"=> []);
                        Notification::route('mail', 'ebenezer.ashiakwei@wigal.com.gh')->notify(new SendAdminEmailNotification(json_encode($message_results)));
                    }
                }else{
                    return array("code" => 400, "message"=> "Payload Not Found in Payload", "result"=> []);
                    Notification::route('mail', 'ebenezer.ashiakwei@wigal.com.gh')->notify(new SendAdminEmailNotification(json_encode($message_results)));
                }
            }

            return array("code" => 200, "message"=> "MESSAGE ACCEPTED", "result"=> []);
        }catch(\Exception $e){     
                // dd($e);  
            Notification::route('mail', (env("EXCEPTION_EMAIL", 'ebenezer.ashiakwei@wigal.com.gh')))->notify(new SendAdminEmailNotification(json_encode($e)));
            // $this->writelog("Error Received: ".$e."\n",1);
            return array("code" => 400, "message"=> "MESSAGE REJECTED", "result"=> []);
        }
    }

    public function formatphonenumber($phone,$code=null)
    {
        //Remove any parentheses and the numbers they contain:
        $phone = preg_replace("/\([0-9]+?\)/", "", $phone);

        //Strip spaces and non-numeric characters:
        $phone = preg_replace("/[^0-9]/", "", $phone);

        //Strip out leading zeros:
        $phone = ltrim($phone, '0');

        //Set default country code if the none is provided:
        if (!$code){
            $code = '233';
        }

        //Check if the number doesn't already start with the correct dialling code:
        if ( !preg_match('/^'.$code.'/', $phone)  ) {
            if(strlen($phone) < 10){
                //check if length is large enough
                $phone = $code.$phone;
            }
        }else{
            if(strlen($phone) < 10){
                //check if length is large enough
                $phone = $code.$phone;
            }
        }
        return $phone;
    }


}
