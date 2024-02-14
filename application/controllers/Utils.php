<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require_once('vendor/autoload.php'); 
use phpseclib3\Net\SFTP; 

class Utils extends REST_Controller {

    function __construct(){

        parent::__construct();
		// $this->sftp = new SFTP('103.116.26.53');
		// $this->sftp->login('hdfc', 'HDFC@3247');
		
		$this->load->model('utils_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT,DELETE");
		header("Access-Control-Allow-Headers: *");
        $this->load->model('schemes_model');

		
    }

    public function financial_year_get(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			$financial_year=$this->utils_model->get_financial_year();

			$this->response([
	            'status' => TRUE,
	            'message' => 'financial_year fetched successfully',
	            'list'=>$financial_year
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

    public function scheme_type_get(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			$scheme_type=$this->utils_model->get_scheme_type();

			$this->response([
	            'status' => TRUE,
	            'message' => 'scheme_type fetched successfully',
	            'list'=>$scheme_type
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function beneficiary_status_get(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			$beneficiary_status=$this->utils_model->get_beneficiary_status();

			$this->response([
	            'status' => TRUE,
	            'message' => 'Beneficiary fetched successfully',
	            'list'=>$beneficiary_status
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function states_get(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			$states=$this->utils_model->get_states();

			$this->response([
	            'status' => TRUE,
	            'message' => 'State fetched successfully',
	            'list'=>$states
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function districts_get($state_id=null){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			if($state_id){
				$districts=$this->utils_model->get_districts($state_id);
			}else{
				$districts=[];
			}

			$this->response([
	            'status' => TRUE,
	            'message' => 'Districts fetched successfully',
				"count"=>count($districts),
	            'list'=>$districts
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }


	public function send_otp_post(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$mobile=$data->mobile;
		$otpIdentifier=$data->identifier;
		// print_r($data);die;
		if($mobile && $otpIdentifier){
			$post_data=[
				'mobile_number'=>$mobile,
				'otpIdentifier'=>$otpIdentifier
			];
			$otp_response=$this->sendOtp($post_data);
			$this->response($otp_response, REST_Controller::HTTP_OK);
		}else{
			$this->response([
	            'status' => FALSE,
	            'message' => 'Mobile number required.'
	        ], REST_Controller::HTTP_OK);
		}
	}

	public function verify_otp_post(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$mobile=$data->mobile;
		$otp=$data->otp;
		$otpIdentifier=$data->identifier;
		if($mobile && $otp && $otpIdentifier){
			$post_data=[
				'mobile_number'=>$mobile,
				"otpIdentifier"=>$otpIdentifier,
				'otpNumber'=>$otp
			];
			
			$otp_response=$this->verifyOtp($post_data);
			$this->response($otp_response, REST_Controller::HTTP_OK);
		}else{
			$this->response([
	            'status' => FALSE,
	            'message' => 'Invalid parameter.'
	        ], REST_Controller::HTTP_OK);
		}
	}

	public function sendOtp($data) {
        $response = array();
        if (isset($data['mobile_number'])) {
            $otp = rand(10000, 99999);
            $response_data = $this->siud->select_data('verification_code', array('mobile_number' => $data['mobile_number'], 'identifier' => $data['otpIdentifier'], 'exact_row' => 1));
            
            if ($response_data) {
                $this->siud->delete_data('verification_code', $response_data->id);
                $this->siud->insert_data('verification_code', array('mobile_number' => $data['mobile_number'], 'mobile_otp' => $otp, 'identifier' => $data['otpIdentifier']));
                if ($this->send_message($data['mobile_number'], $otp)) {
                    $response['status'] = 1;
                    $response['message'] = 'Verification code has been sent on ' . $data['mobile_number'] . '. Please enter the code below.';
                    return $response;
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Something went wrong. Please try later.';
                    return $response;
                }
            } else {
                $this->siud->insert_data('verification_code', array('mobile_number' => $data['mobile_number'], 'mobile_otp' => $otp, 'identifier' => $data['otpIdentifier']));
                if ($this->send_message($data['mobile_number'], $otp)) {
                    $response['status'] = 1;
                    $response['message'] = 'Verification code has been sent on ' . $data['mobile_number'] . '. Please enter the code below.';
                    return $response;
                } else {
                    $response['status'] = 0;
                    $response['message'] = 'Something went wrong. Please try later.';
                    return $response;
                }
            }
        } else {
            $response['status'] = 0;
            $response['message'] = 'Please input a valid mobile number.';
            return $response;
        }
    }

    public function verifyOtp($data){
		$response = array();		
		if($data['mobile_number'] && $data['otpNumber'] && $data['otpIdentifier']){
			$response_data = $this->siud->select_data('verification_code', array('mobile_number'=>$data['mobile_number'], 'mobile_otp'=>$data['otpNumber'], 'identifier'=>$data['otpIdentifier'], 'exact_row'=>1));
			if($response_data){
				$this->siud->delete_data('verification_code', $response_data->id);
				if($response_data->identifier == 'site_login'){					
					$response['status'] = 1;
					$response['message'] = 'OTP has been verified successfully.';
				}elseif($response_data->identifier == 'site_register'){
					// $this->user_model->create_user_by_mobile_no($data['mobile_number']);
					$response['status'] = 1;
					$response['message'] = 'Success';
					
				}else{
					$response['status'] = 1;
					$response['message'] = 'OTP has been verified successfully.';
				}
			}else{
				$response['status'] = 0;
				$response['message'] = 'Incorrect verification code. Please enter correct code.';
				
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Incorrect verification code. Please enter correct code.';
			
		}
		return $response;
	}


    public function send_message_old($contact, $otp = null, $message = null){
		if($contact){			
			$number = "91".$contact;
			$message = "Your OTP for UKAVP is ".$otp;
			$curl = curl_init();
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

			return true;
		}else{
			return false;
		}
	}
	public function send_message($contact, $otp = null, $message = null){
		if($contact){			
			$number = "91".$contact;
			$apikey = "PJLQQ13VuUeECtM9b03dZw";
			$apisender = "BACOMM";
			$msg ="Dear user, ".$otp." is the OTP to login to your BASIC COMMUNICATIONS account. Please Enter the OTP to verify your mobile number."; 
			$num = $number; // MULTIPLE NUMBER VARIABLES PUT HERE...! 
			$ms = rawurlencode($msg); //This for encode your message content
			$url = 'https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey='.$apikey.'&senderid='.$apisender. '&channel=2&DCS=0&flashsms=0&number='.$num.'&text='.$ms.'&route=1';
			//echo $url;
			$ch=curl_init($url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_POSTFIELDS,"");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,2);
			$data = curl_exec($ch);
			return true;
		}else{
			return false;
		}
	}


	public function demo_get(){
		try {
			$sftp = new SFTP('103.116.26.53');
			if (!$sftp->login('hdfc', 'HDFC@3247')) {
				throw new Exception('Login failed');
			}
			// $date=date("Y-m-d");
			// $schemes=$this->schemes_model->get_schemes();
			// // print_r($schemes);die;
			// $xmlFiles = [];	
			// foreach ($schemes as $key => $scheme) {
			// 	$xml_data=$this->generate_xml($scheme->id,$date,$key);
				
			// 	$xmlFiles[]=[
			// 		"name"=>($key+1)."_".$xml_data['name'],
			// 		"content"=>$xml_data['content'],
			// 	];
			// }	 
		
			echo "Connection successful";
		} catch (Exception $e) {
			print_r($e);
			echo "An error occurred: " ;
		}	
	}
	
}
?>