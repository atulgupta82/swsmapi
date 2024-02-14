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
class User extends REST_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
		$this->load->model('api_model');
		$this->load->model('user_model');		
		
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }
	
	public function testing_get(){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,"https://iasscore.in/signup.php");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS,"name=test&email=test@gmail.com&contact=9540520788&city=delhi&register=Register");

		
		// Receive server response ...
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$server_output = curl_exec($ch);

		curl_close ($ch);
		echo '<pre>';
		print_r($server_output);
		die;
	}
    public function register_post(){        
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
				); // OK (200) being the HTTP response code
		}

		if(isset($data->name) and isset($data->email) and isset($data->password)){
			if($salt =	$this->user_model->create_user($data->name, $data->email, $data->password, @$data->contact, @$data->domain)){
				if(@$data->domain != 3){
					$this->load->library('email');				
					
					
					if(@$data->domain == 2){
						$mail_detail	=	$this->user_model->get_email_template(1);
						$subject 	= $mail_detail->template_subject;
						$from_email = 'info@iasscore.in';
						$site_name 	= 'IAS Score';
						$message = $mail_detail->template_message;
						$message = str_replace("{std_name}",$data->name,$message);
						$message = str_replace("{username}",$data->email,$message);
						$message = str_replace("{password}",$data->password,$message);
					}else{
						$from_email = 'info@10pointer.com';
						$site_name 	= '10 Pointer';
						$site_url 	= 'www.10pointer.com';
						$subject 	= 'Account created successfully!';
						$email_data['name']			=	$data->name;
						$email_data['site_name']	=	$site_name;
						$email_data['site_url']		=	$site_url;
						$email_data['url']			=	'https://10pointer.com/verify-account/'.$salt;
						$message					=	$this->load->view('email/register',	$email_data,TRUE);
					}
					$this->email->from($from_email, $site_name);
					$this->email->to($data->email);
					$this->email->subject($subject);
					$this->email->message($message);
					$this->email->send();

					$this->response([
						'status' => TRUE,
						'message' => 'Congratulations, your account has been created successfully. Please check your email (spam as well) to activate your account.'
						] 
						, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}else{
					$this->response([
						'status' => TRUE,
						'message' => 'Congratulations, your account has been created successfully. Please login to your account.'
						] 
						, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}
			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Email already exists. Please use a different email.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}
		
    }
	
	public function verify_post(){
       
		$data	=	json_decode(file_get_contents('php://input'));
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
		if(isset($data->salt)){
			if($salt =	$this->user_model->verify_user($data->salt)){
				

				$this->response([
                    'status' => TRUE,
                    'message' => 'Congratulations, your account has been verified successfully. Please login to continue.'
					] 
					, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Email address is already verified. Please login to continue.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}
		
        
    }
	
	public function login_post(){
      
		$data	=	json_decode(file_get_contents('php://input'));
		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		if(isset($data->email) and  isset($data->password)){
			$salt = md5(mt_rand(100000,999999)); 
			if($this->user_model->login($data->email, $data->password)){		
				$user_detail	=	$this->user_model->get_user(null, $data->email);				
				$this->user_model->update_salt($user_detail->id, $salt);
				$this->response([
					'status' 			=> 	TRUE,
					'message' 			=> 	'Success.',
					'userId' 			=> 	$user_detail->id,
					'userImage' 		=> 	$user_detail->user_avatar_thumb,
					'userName' 			=> 	$user_detail->fullname,
					'salt' 				=> 	$salt
					] 
					, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code

			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Invalid email or password. Please try again.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}		
        
    }
	
	public function social_login_post(){
      
		$data	=	json_decode(file_get_contents('php://input'));
		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		if(isset($data->name) and  isset($data->email) and  isset($data->id) and  isset($data->provider)){
			$salt = md5(mt_rand(100000,999999)); 
			$post_date['name']			=	$data->name;
			$post_date['email']			=	$data->email;
			if($data->provider == 'facebook'){				
				$post_date['facebook_id']	=	$data->id;
			}else{
				$post_date['google_id']		=	$data->id;			
			}
			
			$post_date['salt']			=	$salt;
			
			if($user_detail = $this->user_model->social_login($post_date)){	
				$this->response([
					'status' 			=> 	TRUE,
					'message' 			=> 	'Success.',
					'userId' 			=> 	$user_detail->id,
					'userImage' 		=> 	$user_detail->user_avatar_thumb,
					'userName' 			=> 	$user_detail->fullname,
					'salt' 				=> 	$salt
					] 
					, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code

			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Error. Please try again.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}		
        
    }
	
	/* 
		
	*/
	
	public function forgot_password_post(){
       
		$data	=	json_decode(file_get_contents('php://input'));
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
		if(isset($data->email)){
			if(@$data->domain == 2){
				$password = mt_rand(10000000,99999999);
				if($this->user_model->reset_password_iasscore($password, $data->email)){
					$user_detail	=	$this->user_model->get_user(null, $data->email);
					$this->load->library('email');				
					/* $config = array(
						'mailtype' => 'html',
						'charset'  => 'utf-8',
						'priority' => '1'
					);
					$this->email->initialize($config); */
					$mail_detail	=	$this->user_model->get_email_template(2);
					$subject 	= $mail_detail->template_subject;
					$from_email = 'info@iasscore.in';
					$site_name 	= 'IAS Score';
					$message = $mail_detail->template_message;
					$message = str_replace("{std_name}",$user_detail->fullname,$message);
					$message = str_replace("{username}",$data->email,$message);
					$message = str_replace("{password}",$password,$message);
					$this->email->from($from_email, $site_name);
					$this->email->to($data->email);
					$this->email->subject($subject);
					$this->email->message($message);
					$this->email->send();
					$this->response([
							'status' => TRUE,
							'message' => 'We have sent an email with password. Please check your email (spam as well) for password.'
						] 
					, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}else{
					$this->response([
							'status' => FALSE,
							'error' => 'No account found with this email address.'
						] 
					, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}
			}else{
				$password=substr(str_shuffle("0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ@#$%&*"), 0, 10);
				//$salt = mt_rand(10000000,99999999); 
				if($this->user_model->forgot_password($data->email , $password)){
					$user_detail	=	$this->user_model->get_user(null, $data->email);
					$this->load->library('email');
					$config = Array(
						'protocol' => 'smtp',
						'smtp_host' => 'ssl://smtp.googlemail.com',
						'smtp_port' => 465,
						'smtp_user' => 'myprojectqa@gmail.com',
						'smtp_pass' => '7ujm&UJM',
						'mailtype'  => 'html', 
						'charset'   => 'iso-8859-1'
					);
					$this->load->library('email');
					$this->email->initialize($config);
					$this->email->set_newline("\r\n");

					//$this->email->set_newline("\r\n");
					/* $config = array(
						'mailtype' => 'html',
						'charset'  => 'utf-8',
						'priority' => '1'
					);
					$this->email->initialize($config); */
					
					$this->email->from('info@10pointer.com');
					$this->email->to($data->email);
					
					$this->email->subject('Forgot Password Request 10 Pointer');
					
					$email_data['name']		=	$user_detail->fullname;
					$email_data['password']	=	$password;
					$message				=	$this->load->view('email/forgot_password',$email_data,TRUE);
					//$this->email->message($message);
					$this->email->message("We have sent an email with new password. Please check your email");
					
					$this->email->send();
					
					echo $this->email->print_debugger();
					die;
					$this->response([
							'status' => TRUE,
							'message' => 'We have sent an email with new password. Please check your email (spam as well).'
						] 
					, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}else{
					$this->response([
							'status' => FALSE,
							'error' => 'No account found with this email address.'
						] 
					, REST_Controller::HTTP_OK
					); // OK (200) being the HTTP response code
				}
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}		
        
    }
	
	/* 
		function to check whether user is logged in with current salt or not
	*/
	
	public function reset_password_post(){
       
		$data	=	json_decode(file_get_contents('php://input'));
		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
		if(isset($data->password) and isset($data->salt)){
			if($this->user_model->reset_password($data->password , $data->salt)){
				
				$this->response([
						'status' => TRUE,
						'message' => 'Password updated successfully.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Invalid URL.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}		
        
    }
	
	/* 
		function to check whether user is logged in with current salt or not
	*/
	public function is_logedin(){
        
		$data	=	json_decode(file_get_contents('php://input'));
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
		if(isset($data->user_id) and  isset($data->salt)){
			
			if($this->user_model->is_logedin($data->user_id, $data->salt)){		
				
				$this->response([
                    'status' 			=> 	TRUE,
                    'message' 			=> 	'Success.'
					] 
					, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}else{
				$this->response([
						'status' => FALSE,
						'error' => 'Invalid User Id and Salt.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}		
        
    }
    
    public function details_get(){
        $user_id 	= 	$this->get('userId');
        $salt 		= 	$this->get('salt');
		if(isset($user_id) and isset($salt)){
			$user_detail	=	$this->user_model->get_user_by_id($user_id, $salt);
       
			if ($user_detail){
				$this->response([
					'status' => TRUE,
					'details' => $user_detail
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}else{
				$this->response([
					'status' => FALSE,
					'error' => 'No user were found'
				], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}
        
    } 
	
	public function states_get(){
        
		$state_list	=	$this->user_model->get_state_list();
        if ($state_list){
            $this->response([
                'status' => TRUE,
                'list' => $state_list
            ], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
        }else{
            $this->response([
                'status' => FALSE,
                'error' => 'No state were found'
            ], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
        }
    } 
   
    public function update_profile_post(){
    	$data	=	json_decode(file_get_contents('php://input'));	
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
    	if(!$this->user_model->is_logedin($data->userId, $data->salt)){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{	
    		if(isset($data->userId)){
				$post_data  =   array();
				if(isset($data->name)){
					$post_data['fullname']  =  $data->name; 
				}
				if(isset($data->mobile)){
					$post_data['contact']  	=  $data->mobile; 
				}
				if(isset($data->dob)){
					$post_data['dob']  		=  date('Y-m-d', strtotime($data->dob)); 
				}
				if(isset($data->gender)){
					$post_data['gender']  	=  $data->gender; 
				}
				if(isset($data->state)){
					$post_data['state']  	=  $data->state; 
				}
				if(isset($data->city)){
					$post_data['city']  	=  $data->city; 
				}
				
    			$this->user_model->update_profile($data->userId, $post_data);
    			$this->response([
                    'status' => TRUE,
                    'message' => 'Your profile has been updated successfully.'
    				] 
    				, REST_Controller::HTTP_OK
    			); // // CREATED (201) being the HTTP response code
    		}else{
    			$this->response([
                        'status' => FALSE,
                        'error' => 'Invalid Parameters'
                    ] 
    				, REST_Controller::HTTP_OK
    			); // OK (200) being the HTTP response code
    		}
        }
	}
	
	public function update_profile_image_post(){
    	$data	=	json_decode(file_get_contents('php://input'));	
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
    	if(!$this->user_model->is_logedin($data->userId, $data->salt)){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{	
    		if(isset($data->image)){
				$post_data['user_avatar_thumb'] = $this->upload_image($data->image);	
				
    			$this->user_model->update_profile($data->userId, $post_data);
    			$this->response([
                    'status' => TRUE,
                    'userImage' => $post_data['user_avatar_thumb'],
                    'message' => 'Your profile image has been updated successfully.'
    				] 
    				, REST_Controller::HTTP_OK
    			); // // CREATED (201) being the HTTP response code
    		}else{
    			$this->response([
                        'status' => FALSE,
                        'error' => 'Invalid Parameters'
                    ] 
    				, REST_Controller::HTTP_OK
    			); // OK (200) being the HTTP response code
    		}
        }
	}
	
	private function upload_image($image){ 
		$pos  = strpos($image, ';');
		$type = explode(':', substr($image, 0, $pos))[1];
		
		if($type == 'image/png'){
			$data = str_replace('data:image/png;base64,', '', $image);
			$image_name = md5(time()).'.png';
			$ext = 'png';
		}elseif($type == 'image/jpeg'){
			$data = str_replace('data:image/jpeg;base64,', '', $image);
			$image_name = md5(time()).'.jpeg';
			$ext = 'jpeg';
		}elseif($type == 'image/jpg'){
			$data = str_replace('data:image/jpg;base64,', '', $image);
			$image_name = md5(time()).'.jpg';
			$ext = 'jpg';
		}elseif($type == 'image/gif'){
			$data = str_replace('data:image/gif;base64,', '', $image);
			$image_name = md5(time()).'.gif';
			$ext = 'gif';
		}		
		
		$data = str_replace(' ', '+', $data);
		$data = base64_decode($data);
		
		$file = '/var/www/10pointer/uploads/'.$image_name;		
		file_put_contents($file, $data);
		
		list($source_image_width, $source_image_height) = getimagesize($file);
		
		$source_aspect_ratio = $source_image_width / $source_image_height;
			
		$image_max_width = 128;
		$image_max_height = 128;
		$thumbnail_aspect_ratio = $image_max_width / $image_max_height;
		if ($source_image_width <= $image_max_width && $source_image_height <= $image_max_height) {
			$width = $source_image_width;
			$height = $source_image_height;
		} elseif ($thumbnail_aspect_ratio > $source_aspect_ratio) {
			$width = (int) ($image_max_height * $source_aspect_ratio);
			$height = $image_max_height;
		} else {
			$width = $image_max_width;
			$height = (int) ($image_max_width / $source_aspect_ratio);
		}
		
		$this->image_resize($file, $width, $height);
		return 'https://10pointer.com/uploads/'.$image_name;
		
	}
	
	private function image_resize($image_url, $thumb_w, $thumb_h){		

		// CREATE THE THUMBNAIL IMAGE RESOURCE
		$thumb = imageCreateTrueColor($thumb_w, $thumb_h);

		// FILL THE THUMBNAIL WITH TRANSPARENT
		imageSaveAlpha($thumb, TRUE);
		$empty = imageColorAllocateAlpha($thumb,0x00,0x00,0x00,127);
		imageFill($thumb, 0, 0, $empty);

		// TRY TO GET ORIGINAL IMAGE DIMENSIONS
		$array = @getImageSize($image_url);
		if ($array)
		{
			list($image_w, $image_h) = $array;
		}
		else
		{
			trigger_error("NO IMAGE $image_url", E_USER_ERROR);
		}

		// ACQUIRE THE ORIGINAL IMAGE EXTENSION
		$ext = explode('.', $image_url);
		$ext = end($ext);
		$ext = strtoupper($ext);
		$ext = trim($ext);

		// USING THE EXTENSION, ACQUIRE THE IMAGE RESOURCE
		switch($ext)
		{
			case 'JPG' :
			case 'JPEG' :
				$image = imagecreatefromjpeg($image_url);
				break;

			case 'PNG' :
				$image = imagecreatefrompng($image_url);
				break;

			default : trigger_error("UNKNOWN IMAGE TYPE: $image_url", E_USER_ERROR);
		}

		// GET THE LESSER OF THE RATIO OF THUMBNAIL H OR W DIMENSIONS
		$ratio_w = ($thumb_w / $image_w);
		$ratio_h = ($thumb_h / $image_h);
		$ratio   = ($ratio_w < $ratio_h) ? $ratio_w : $ratio_h;

		// COMPUTE THUMBNAIL IMAGE DIMENSIONS
		$thumb_w_resize = $image_w * $ratio;
		$thumb_h_resize = $image_h * $ratio;

		// COMPUTE THUMBNAIL IMAGE CENTERING OFFSETS
		$thumb_w_offset = ($thumb_w - $thumb_w_resize) / 2.0;
		$thumb_h_offset = ($thumb_h - $thumb_h_resize) / 2.0;

		// COPY THE IMAGE TO THE CENTER OF THE THUMBNAIL
		imageCopyResampled( 
			$thumb              // DESTINATION IMAGE
			, $image              // SOURCE IMAGE
			, $thumb_w_offset     // DESTINATION X-OFFSET
			, $thumb_h_offset     // DESTINATION Y-OFFSET
			, 0                   // SOURCE X-OFFSET
			, 0                   // SOURCE Y-OFFSET
			, $thumb_w_resize     // DESTINATION WIDTH
			, $thumb_h_resize     // DESTINATION HEIGHT
			, $image_w            // SOURCE WIDTH
			, $image_h            // SOURCE HEIGHT
		);
		// SHARPEN THE THUMBNAIL SEE php.net/imageconvolution#104006
		$sharpenMatrix = array( 
			array( -1.2, -1.0, -1.2)
			, array( -1.0, 20.0, -1.0)
			, array( -1.2, -1.0, -1.2)
		);
		
		$divisor = array_sum(array_map('array_sum', $sharpenMatrix));
		$offset  = 0;
		imageConvolution($thumb, $sharpenMatrix, $divisor, $offset);

		// SAVE THE THUMBNAIL IMAGE IN A "THUMBS" DIRECTORY
		$bname = ($image_url);
		imagePNG($thumb, $bname);

		
		// RELEASE THE MEMORY USED BY THE IMAGE RESOURCES
		imageDestroy($thumb);
		imageDestroy($image);
	}
	
	public function update_password_post(){
	    $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
    	if(!$this->user_model->is_logedin($data->userId, $data->salt)){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{
    		if(isset($data->userId) && $data->password){
				$this->user_model->update_password($data->userId, $data->password);
				$this->response([
					'status' => TRUE,
					'message' => 'Your password has been updated successfully.'
					] 
					, REST_Controller::HTTP_OK
				);
    		}else{
    			$this->response([
                        'status' => FALSE,
                        'error' => 'Invalid Parameters'
                    ] 
    				, REST_Controller::HTTP_OK
    			); // OK (200) being the HTTP response code
    		}
		}
	}
	
	public function update_profilepic_post(){
	    $user_id = $this->input->post('user_id');
	    $salt = $this->input->post('salt');
    	if(!$this->user_model->is_logedin($user_id, $salt)){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{	
    		if(isset($user_id)){
    		    if (empty($_FILES['profilepic']['name'])){
                    $this->response([
                            'status' => FALSE,
                            'error' => 'Choose Image Required!'
                        ] 
        				, REST_Controller::HTTP_OK
        			);
                }else{
                    $temp = explode(".", $_FILES["profilepic"]["name"]);
                    $file_name = md5($user_id) . '.' . end($temp);
                    $config['file_name'] = $file_name;
                    $config['upload_path']          = './uploads/user_img/';
                    $config['allowed_types']        = 'jpg|png|gif';
                    //$config['overwrite'] = TRUE;
                    $this->load->library('upload');
                    $this->upload->initialize($config);
                    if ( ! $this->upload->do_upload('profilepic')){
                        $this->response([
                                'status' => FALSE,
                                'error' => $this->upload->display_errors()
                            ] 
            				, REST_Controller::HTTP_OK
            			);
                    }else{
                        $user_detail	=	$this->user_model->get_user_by_id($user_id,$salt);
                        if($user_detail->userImg){
                            $old_file_name = explode('/',$user_detail->userImg);
                            $imag = end($old_file_name);
                            unlink($config['upload_path'].$imag);
                        }
                        $upload_file_data =   $this->upload->data();
                        $configer =  array(
                          'image_library'   => 'gd2',
                          'source_image'    =>  $upload_file_data['full_path'],
                          'maintain_ratio'  =>  TRUE,
                          'width'           =>  250,
                          'height'          =>  250,
                        );
                        $this->load->library('image_lib');
                        $this->image_lib->clear();
                        $this->image_lib->initialize($configer);
                        $this->image_lib->resize();
                        $profilepic_url = site_url().'/uploads/user_img/'.$upload_file_data['file_name'];
            			$this->user_model->update_profilepic($user_id, $profilepic_url);
            			$this->response([
                            'status' => TRUE,
                            'message' => 'Your profile pic has been updated successfully.',
                            'userImg' => $profilepic_url
            				] 
            				, REST_Controller::HTTP_CREATED
            			); // // CREATED (201) being the HTTP response code
                    }
                }
    		}else{
    			$this->response([
                        'status' => FALSE,
                        'error' => 'Invalid Parameters'
                    ] 
    				, REST_Controller::HTTP_OK
    			); // OK (200) being the HTTP response code
    		}
        }
	}
	
	public function register_iasscore_post(){        
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);			
		}
		
		if(isset($data->name) and isset($data->email) and isset($data->password) and isset($data->userId)){
			if($this->user_model->register_user_iasscore($data->name, $data->email, $data->password, $data->userId)){
				
				$this->response([
                    'status' => TRUE,
                    'message' => 'Account created successfully.'
					] 
					, REST_Controller::HTTP_CREATED
				); // // CREATED (201) being the HTTP response code
			}else{
				$this->response([
						'status' => TRUE,
						'error' => 'User already exists.'
					] 
				, REST_Controller::HTTP_OK
				); // OK (200) being the HTTP response code
			}
		}else{
			$this->response([
                    'status' => FALSE,
                    'error' => 'Invalid Parameters'
                ] 
				, REST_Controller::HTTP_OK
			); // OK (200) being the HTTP response code
		}
		
    }
	
	public function transaction_history_get(){
        $user_id	= 	(int) $this->get('userId');
        $salt 		= 	$this->get('salt');
       
        if($salt == 'mobile'){
			$is_logedin = true;
		}else{
			$is_logedin	=	$this->user_model->is_logedin($user_id, $salt);
		}
		
        if(!$is_logedin){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{
			$transactions	=	$this->user_model->get_transaction_history($user_id);
			if ($transactions){
				$this->response([
					'status' => TRUE,
					'transactions' => $transactions
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}else{
				$this->response([
					'status' => FALSE,
					'error' => 'No transaction found.'
				], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
			}
		}
    }
	
	public function program_list_get(){
        $user_id	= 	(int) $this->get('userId');
        $type		= 	(int) $this->get('type');
        $salt 		= 	$this->get('salt');
       
        if($salt == 'mobile'){
			$is_logedin = true;
		}else{
			$is_logedin	=	$this->user_model->is_logedin($user_id, $salt);
		}
		
        if(!$is_logedin){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_OK); // BAD_REQUEST (400) being the HTTP response code
		}else{
			$program_list	=	$this->user_model->get_program_list($user_id, $type);
			if ($program_list){
				$this->response([
					'status' => TRUE,
					'list' => $program_list
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}else{
				$this->response([
					'status' => FALSE,
					'error' => 'No transaction found.'
				], REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
			}
		}
    }
	
	public function my_videos_get(){      
        
		$salt 					= 	$this->get('salt');
        $user_id 				= 	(int) 	$this->get('userId');
        $program_id 			= 	(int) 	$this->get('programId');		
		
		if(!$this->user_model->is_logedin($user_id, $salt)){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid login, Please logout and login again.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{
			$this->load->model('video_model');
			$videos		=	$this->video_model->get_my_videos($program_id);	
			if ($videos){
				// Set the response and exit
				$this->response([
					'status' 		=> 	TRUE,
					'list' 			=> 	$videos
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}else{
				// Set the response and exit
				$this->response([
					'status' => FALSE,
					'error' => 'No test found.'
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}
		}
    }
	
	public function my_posts_get(){
		$data		=	json_decode(file_get_contents('php://input'));
		$user_id	= 	(int) $this->get('userId');
		$exam_id	= 	(int) $this->get('examId');
		$salt		= 	$this->get('salt');
		if($this->get('page')){
		    $page = $this->get('page');
        }else{
            $page = 1;
        }
		if (!$user_id or !$salt){
			$this->response([
                    'status' => false,
                    'error' => 'Invalid Parameter.'
                ], REST_Controller::HTTP_BAD_REQUEST); // BAD_REQUEST (400) being the HTTP response code
		}else{
			$posts	=	$this->user_model->get_my_post($user_id, $page);
			if ($posts){
				// Set the response and exit
				$this->response([
					'status' => TRUE,
					'message' => 'Success',
					'list' 			=> 	$posts['list'],
					'pagination'	=> 	$posts['pagination']
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}else{
				// Set the response and exit
				$this->response([
					'status' => FALSE,
					'error' => 'No post found.'
				], REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
			}	
		}
    }
}
