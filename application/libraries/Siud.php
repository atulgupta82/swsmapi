<?php
class Siud{
	private $CI;
	public function __construct(){
		$this->CI = &get_instance();
	}
	
	public function select_data($table, $conditions = array(), $limit = null, $start = null, $order_by = 'id'){
		
		//$this->CI->db->cache_on();
		$this->CI->db->select('*');
		$this->CI->db->from($table);	
		
		$is_id = false;
		if(count($conditions)){
			$exact = @$conditions['exact'];
			$exact_row = @$conditions['exact_row'];
			unset($conditions['exact']);
			unset($conditions['exact_row']);
			foreach($conditions as $field => $value){
				if($field){
					if(is_numeric($value)){
						$this->CI->db->where($field, $value);
						
						if($field == 'id'){	
							$is_id = true;
						}elseif($field == 'phone'){
							$is_id = true;
						}
					}else{
						if($field == 'slug'){
							$is_id = true;
							$this->CI->db->where($field, $value);
						}elseif($exact == 1){
							$this->CI->db->where($field, $value);
						}else{
							$this->CI->db->like($field, $value);
						}
					}
				}
				
			}
		} 
		
		if($limit){
			$this->CI->db->limit($limit, $start);
		}
		
		if($order_by == 'random'){
			$this->CI->db->order_by('rand()');
		}elseif($order_by == 'id'){
			$this->CI->db->order_by($order_by, 'DESC');
		}else{
			$this->CI->db->order_by($order_by, 'ASC');
		}
		
		$query = $this->CI->db->get();
		//$this->CI->db->cache_off();
		//echo $this->CI->db->last_query();die;
		if($is_id){
			return $query->row();
		}elseif($exact_row){
			return $query->row();
		}else{
			return $query->result();
		}
	}
	
	public function insert_data($table, $data) {
		$this->CI->db->insert($table, $data);
		$this->CI->db->cache_delete_all();//echo $this->CI->db->last_query();die;
		return $this->CI->db->insert_id();
    }
	
	public function update_data($table, $data, $id, $field = 'id') {
		$this->CI->db->where($field, $id);
		$this->CI->db->update($table, $data);//echo $this->CI->db->last_query();die;
		
		
		$this->CI->db->cache_delete($table);
		return true;
    }
	
	public function delete_data($table, $value, $field = 'id') {
		$this->CI->db->where($field, $value);
		$this->CI->db->delete($table);
		/*  */
		$this->CI->db->cache_delete($table);
		
		//$this->CI->db->cache_delete_all();
		return true;
    }
    
   
	
	
	
	public function generate_slug($table, $value, $id = null){
		$slug 	= url_title($value, 'dash', true);
		
		$slug  	= substr($slug , 0, 100);
		
		$this->CI->db->select('id');
		$this->CI->db->from($table);
		if($id)
			$this->CI->db->where('id !=', $id);
		$this->CI->db->where('slug', $slug);
		$query = $this->CI->db->get();

		if($query->num_rows() > 0){
			$slug = $slug.'-'.rand(1,9);
		}

		return $slug;
	}
	
	
	
	
	
	
	function get_password() {
		$alphabet = 'ABCDE123456789012345678901234567890';
		$pass 	= array(); //remember to declare $pass as an array
		$alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
		for ($i = 0; $i < 6; $i++) {
			$n = rand(0, $alphaLength);
			$pass[] = $alphabet[$n];
		}
		return implode($pass); //turn the array into a string
	}
	
	function send_email($to, $subject, $message){
		$config = Array(
			'protocol' => 'smtp',
			'smtp_host' => SMTP_HOST,
			'smtp_port' => SMTP_PORT,
			'smtp_user' => SMTP_USER,
			'smtp_pass' => SMTP_PASS,
			'mailtype'  => 'html', 
			'charset'   => 'iso-8859-1',
			'tls' 		=> true
		);
		
		/* $config['protocol'] = 'mail';
		//$config['mailpath'] = '/usr/sbin/sendmail';
		$config['charset'] = 'iso-8859-1';
		$config['wordwrap'] = TRUE;
		$config['mailtype'] = 'html'; */
		
		$this->CI->load->library('email');
		$this->CI->email->initialize($config); 
		$this->CI->email->set_newline("\r\n");	
		
		$this->CI->email->to($to);
		$this->CI->email->from(ADMIN_EMAIL, SITE_NAME);		
		$this->CI->email->subject($subject);
		$this->CI->email->message($message);
		
		return $this->CI->email->send();
		
		
	}
	
	function send_message($phone, $message){
		$apiUrl = 'http://bulkpush.mytoday.com/BulkSms/SingleMsgApi';
		$postFields = "feedid=347188&username=9873870114&password=Gs@score1&To=".$contact."&Text=".$message;

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $apiUrl);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $postFields);
		
		//get response
		$output = curl_exec($ch);

		//Print error if any
		if(curl_errno($ch)){
			return curl_error($ch);
		}

		curl_close($ch);
		return $output;
	}
	
	function sendRequest($param){
		$url 		= $param['url'];
		$postData 	= $param['postData'];

		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $postData
			//,CURLOPT_FOLLOWLOCATION => true
		));


		//Ignore SSL certificate verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


		//get response
		$output = curl_exec($ch);

		//Print error if any
		if(curl_errno($ch))
		{
			return curl_error($ch);
		}

		curl_close($ch);

		return $output;
	}
	
	
	function isMobileDevice(){
		$aMobileUA = array(
			'/iphone/i' => 'iPhone', 
			'/ipod/i' => 'iPod', 
			'/ipad/i' => 'iPad', 
			'/android/i' => 'Android', 
			'/blackberry/i' => 'BlackBerry', 
			'/webos/i' => 'Mobile'
		);

		//Return true if Mobile User Agent is detected
		foreach($aMobileUA as $sMobileKey => $sMobileOS){
			if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
				return true;
			}
		}
		//Otherwise return false..  
		return false;
	}
	
	function isAppDevice(){
		$aAppUA = array(
			'/iphone/i' => 'iPhone', 
			'/ipod/i' => 'iPod', 
			'/ipad/i' => 'iPad', 
			'/android/i' => 'Android', 
			'/blackberry/i' => 'BlackBerry', 
			'/webos/i' => 'Mobile'
		);

		//Return true if Mobile User Agent is detected
		foreach($aMobileUA as $sMobileKey => $sMobileOS){
			if(preg_match($sMobileKey, $_SERVER['HTTP_USER_AGENT'])){
				return true;
			}
		}
		//Otherwise return false..  
		return false;
	}
	
	
	public function add_log($user_id, $message){
        $this->CI->db->insert('logs',	array('user_id'=>$user_id, 'message'=> $message, 'date'=> date('Y-m-d H:i:s'), 'ip'=> $this->get_client_ip()));
    }
	
	function get_client_ip() {
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
		   $ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
		return $ipaddress;
	}


	
	

	
	

	
	

	 

}
