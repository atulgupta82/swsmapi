<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Headofaccounts_model extends CI_Model{
    /**
	 * __construct function.
	 * 
	 * @access public
	 * @return void
	 */

     public function __construct() {		
		parent::__construct();
		$this->load->database();
		date_default_timezone_set('Asia/Kolkata');
		$this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");

	}

    public function get_headofaccounts(){
		
		$this->db->select('*');
		$this->db->from('head_of_accounts');
		$this->db->where('status',1);
		// if($user_type){
		// 	$this->db->where('user_type',$user_type);
		// }
		// $this->db->where('mobile', $mobile);
		$row	=	$this->db->get()->result();
		// print_r($row);die;
		if($row){
			return $row;
		}else{
			return false;
		}
	}

}


?>