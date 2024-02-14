<?php 

trait OtpTrait {

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


    public function send_message($contact, $otp = null, $message = null){
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
}


?>