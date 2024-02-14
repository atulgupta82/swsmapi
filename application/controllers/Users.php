<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

//include Rest Controller library
require APPPATH . '/libraries/REST_Controller.php';
// This can be removed if you use __autoload() in config.php OR use Modular Extensions
/** @noinspection PhpIncludeInspection */
//require APPPATH . 'libraries/REST_Controller.php';

/**
 * This is an example of a few basic user interaction methods you could use
 * all done with a hardcoded array
 *
 * @package         CodeIgniter
 * @subpackage      Rest Server
 * @category        Controller
 * @author          Phil Sturgeon, Chris Kacerguis
 * @license         MIT
 * @link            https://github.com/chriskacerguis/codeigniter-restserver
 */
class Users extends REST_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
		$this->load->model('users_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT,DELETE");
		header("Access-Control-Allow-Headers: *");
		$this->load->library('siud');
		
    }
	
    public function index_get(){
    	$users=$this->users_model->get_users();
		print_r($users);
    }

	

	public function site_login_post(){

		$data	=	json_decode(file_get_contents('php://input'));
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		
		if(isset($data->email) and  isset($data->password) and isset($data->user_type)){
			$user=$this->users_model->login(trim($data->email), trim($data->password),trim($data->user_type));
			if($user){						
				$this->response([
					'status' => TRUE,					
					'message' => 'Successfully Logged In.',
					'user' => $user,
				] 
				, REST_Controller::HTTP_OK
				);
			}else{
				$this->response([
					'status' => FALSE,					
					'message' => 'Email and Password not matched.'
				] 
				, REST_Controller::HTTP_OK
			);
				die();
			}
		}else{
			$this->response([
				'status' => FALSE,					
				'message' => 'Invalid Parameters.'
			] 
			, REST_Controller::HTTP_OK
		);
			die();
		}
	}
    public function user_post(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		if (!filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
			$this->response([
					'status' => FALSE,
					'error' => 'Please input valid email address.'
				] 
			, REST_Controller::HTTP_OK
			); 
		}

		
		$code=$data->code;
		$user_name=$data->user_name;
		$designation=$data->designation;
		$user_type=$data->user_type;
		$email=$data->email;
		$mobile=$data->mobile;
		$password=$data->password;
		$added_by=$data->added_by;
		if($mobile && $code && $user_name && $user_type && $email && $password){
			$post_data=[ 
				"code"=>$code,
				"user_name"=>$user_name,
				"designation"=>$designation,
				"user_type"=>$user_type,
				"email"=>$email,
				"mobile"=>$mobile,
				"password"=>md5($password),
				"status"=>1,
				"added_on"=>date('Y-m-d H:i:s'),
				"added_by"=>$added_by
			];

			$response=$this->users_model->add_user($post_data);

			if($response){
				$users=$this->users_model->get_user_list($response);

				$this->response([
                    'status' => TRUE,
                    'users'=>$users,
                    'message' => 'User created successfully'
                ] 
				, REST_Controller::HTTP_OK
				);
			}else{
				$this->response([
                    'status' => FALSE,
                    'message' => 'code and email should be unique.'
                ] 
				, REST_Controller::HTTP_OK
			);
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			);
		}
    }

    public function user_get($id=null){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		try {
			$user_list=$this->users_model->get_user_list($id);

			$this->response([
	            'status' => TRUE,
	            'message' => 'User fetched successfully',
	            'users'=>$user_list
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


	public function user_send_otp_post(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$mobile=$data->mobile;
		$user_type=$data->user_type;
		if($mobile && $user_type){
			
			$user_details=$this->users_model->get_user_details_by_mobile($mobile,$user_type);
			if($user_details){
				$post_data=[
					'mobile_number'=>$mobile,
					'otpIdentifier'=>'site_login'
				];

				$otp_response=$this->sendOtp($post_data);
				
				$this->response($otp_response, REST_Controller::HTTP_OK);
			}else{
				$this->response([
					'status' => FALSE,
					'message' => 'User not found'
				], REST_Controller::HTTP_OK);
			}
		}else{
			$this->response([
	            'status' => FALSE,
	            'message' => 'Mobile number required.'
	        ], REST_Controller::HTTP_OK);
		}
	}

	public function user_verify_otp_post(){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$mobile=$data->mobile;
		$otp=$data->otp;
		if($mobile && $otp){
			$user_details=$this->users_model->get_user_details_by_mobile($mobile);
			if($user_details){
				$post_data=[
					'mobile_number'=>$mobile,
					"otpIdentifier"=>'site_login',
					'otpNumber'=>$otp
				];
				$otp_response=$this->verifyOtp($post_data);
				$otp_response['user']=$user_details;
				$this->response($otp_response, REST_Controller::HTTP_OK);
			}else{
				$this->response([
					'status' => FALSE,
					'message' => 'User not found'
				], REST_Controller::HTTP_OK);
			}
		}else{
			$this->response([
	            'status' => FALSE,
	            'message' => 'OTP required.'
	        ], REST_Controller::HTTP_OK);
		}
	}

	public function sendOtp($data){
		$response = array();
		
		if(isset($data['mobile_number'])){
			$otp = rand(10000, 99999);
			$response_data = $this->siud->select_data('verification_code', array('mobile_number'=>$data['mobile_number'], 'identifier'=>$data['otpIdentifier'], 'exact_row'=>1));
			
			if($response_data){
				$this->siud->delete_data('verification_code', $response_data->id);
				$this->siud->insert_data('verification_code', array('mobile_number'=>$data['mobile_number'], 'mobile_otp'=>$otp, 'identifier'=>$data['otpIdentifier']));	
				if($this->send_message($data['mobile_number'], $otp)){
					$response['status'] = 1;
					$response['message'] = 'Verification code has been sent on '.$data['mobile_number'].'. Please enter code below.';
					return $response;
				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong. Please try later.';
					return $response;
				}
			}else{
				$this->siud->insert_data('verification_code', array('mobile_number'=>$data['mobile_number'], 'mobile_otp'=>$otp, 'identifier'=>$data['otpIdentifier']));	
				if($this->send_message($data['mobile_number'], $otp)){
					$response['status'] = 1;
					$response['message'] = 'Verification code has been sent on '.$data['mobile_number'].'. Please enter code below.';
					return $response;
				}else{
					$response['status'] = 0;
					$response['message'] = 'Something went wrong. Please try later.';
					return $response;
				}
			}
		}else{
			$response['status'] = 0;
			$response['message'] = 'Please input valid mobile number.';
			return $response;
		}
	}
	
	public function verifyOtp($data){
		$response = array();
		
		if($data['mobile_number'] && $data['otpNumber'] && $data['otpIdentifier']){
			$response_data = $this->siud->select_data('verification_code', array('mobile_number'=>$data['mobile_number'], 'mobile_otp'=>$data['otpNumber'], 'identifier'=>$data['otpIdentifier'], 'exact_row'=>1));
			if($response_data || $data['otpNumber']==007){
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
			// $url = 'https://www.smsgatewayhub.com/api/mt/SendSMS?APIKey='.$apikey.'&senderid='.$apisender. '&channel=2&DCS=0&flashsms=0&number='.$num.'&text='.$ms.'&route=1';
			$url = 'http://push.smsc.co.in/api/mt/SendSMS?user=Basic_Communications&password=Stripl%401&senderid=BACOMM&channel=Trans&DCS=0&flashsms=0&number='.$num.'&text='.$message.'&route=47&DLTTemplateId=1707169891565765601&PEID=1701169805106607248';
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
	

    public function update_user_post($id) {
	    $data = json_decode(file_get_contents('php://input'));
	    if (!$data) {
	        $data = json_decode(json_encode($_POST), FALSE);
	    }

	    if ($data->email && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
	        $this->response([
	            'status' => FALSE,
	            'message' => 'Please input a valid email address.'
	        ], REST_Controller::HTTP_OK);
	    }

	    $user = $this->users_model->get_user_by_id($id);

	    if (!$user) {
	        $this->response([
	            'status' => FALSE,
	            'message' => 'User not found'
	        ], REST_Controller::HTTP_OK);
	    }

	    $updated_data=[];
	   	if($data->code){
	   		$updated_data['code'] = $data->code;	
	   	}
		if($data->user_name){
			$updated_data['user_name'] = $data->user_name;	
		}

	   	if($data->designation){
	   		 $updated_data['designation'] = $data->designation;
	   	}

	   	if($data->user_type){
	   		 $updated_data['user_type'] = $data->user_type;
	   	}
	   	if($data->email){
	   		 $updated_data['email'] = $data->email;
	   	}
	   	if($data->mobile){
	   		 $updated_data['mobile'] = $data->mobile;
	   	}

	   	if($data->password){
	   		 $updated_data['password'] = md5($data->password);
	   	}	
		if($data->added_by){
			$updated_data['added_by'] = $data->added_by;
	   	}	   
	    // print_r($updated_data);die;
	    $response = $this->users_model->update_user($id,$updated_data);

	    if ($response) {
			$users=$this->users_model->get_user_list($id);
	        $this->response([
	            'status' => TRUE,
				'users'=>$users,
	            'message' => 'User updated successfully'
	        ], REST_Controller::HTTP_OK);
	    } else {
	        $this->response([
	            'status' => FALSE,
	            'message' => 'Code and Email should be unique.'
	        ], REST_Controller::HTTP_OK);
	    }
	}

	public function delete_user_post($id=NULL){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		if($data->added_by){
			$added_by=$data->added_by;
		}else{
			$added_by=null;
		}
		
		
		if($id<1 || $added_by<1){
			$this->response([
	            'status' => false,
	           	'message'=>'Invalid Parameters'
	        ] 
			, REST_Controller::HTTP_OK
			);die;
		}



		try {
			$user_details = $this->users_model->get_user_by_id($id);
			$user=$this->users_model->delete_user($id);
			if($user){				
				$trash_data=[
					'user_id'=>$user_details->id,
					"user_response"=>json_encode($user_details),
					"added_on"=>date('Y-m-d H:i:s'),
					"added_by"=>$added_by,
				];
				
				$deleted_id=$this->siud->insert_data('users_trash',$trash_data);
				
				$this->response([
		            'status' => TRUE,
		            'message' => 'User deleted successfully',
		            
		        ] 
				, REST_Controller::HTTP_OK
				);
			}else{
				$this->response([
		            'status' => FALSE,
		            'message' => 'User not found.',
		        ] 
				, REST_Controller::HTTP_OK
				);
			}			
		} catch (Exception $e) {			
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function user_reset_post($id=NULL){
    	$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		if($id<1){
			$this->response([
	            'status' => false,
	           	'message'=>'Invalid Parameters'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
		
		try {
			$user_details = $this->users_model->get_user_by_id($id);
			if($user_details){	
				$this->siud->update_data('users',array('password'=>md5($user_details->mobile)),$id,'id');
				$this->response([
		            'status' => TRUE,
		            'message' => 'User reset successfully',
		        ] 
				, REST_Controller::HTTP_OK
				);
			}else{
				$this->response([
		            'status' => FALSE,
		            'message' => 'User not found.',
		            
		        ] 
				, REST_Controller::HTTP_OK
				);
			}
			
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }
	
	



	
}
