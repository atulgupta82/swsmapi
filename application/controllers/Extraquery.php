<?php
require APPPATH . '/libraries/REST_Controller.php';

class Extraquery extends REST_Controller
{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
        header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
	}

    public function delete_t_get($table=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
        
        if($table){
            $this->db->where('id >', 1);
            $this->db->delete($table);
            echo "$table deleted";
        }
    }


    public function demo_mail_get(){
        
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        $otp=rand(1000,9999);    				    
        $curl = curl_init();
        $mobile=9953469082;
        $number = "91".$mobile;
        $message = "Your OTP for UKAVP is ".$otp;
        
        $message_encode = urlencode($message);
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey=7jdGYN41EU61MYMUlvWOjQ&senderid=UKAAVP&channel=2&DCS=0&flashsms=0&number=".$number."&text=".$message_encode."&route=1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
            "cache-control: no-cache",
            "postman-token: deb8aaf6-ec4d-938d-124c-0d5d3b55370c"
            ),
        ));
        
        $response = curl_exec($curl);        
        $err = curl_error($curl);
        
        curl_close($curl);

        // echo "dfsds";
        // $this->load->library('email');
        // $this->email->from('munimraiganj@gmail.com', 'Munim'); 
        // $this->email->to('asifraiganj@gmail.com');
        // $this->email->subject('Email Test'); 
        // $this->email->message('Testing the email class.'); 
        // $is_send=$this->email->send();
        // print_r($is_send);die;
    }
    
}


?>