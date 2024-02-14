<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Utils_model extends CI_Model {
	
	public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    public function get_financial_year(){
        $this->db->select('*,CONCAT(start_year,"-",end_year) as year');
		$this->db->from('financial_year');
		$this->db->where('status',1);	
		$this->db->order_by('start_year','asc');	
		$result	=	$this->db->get()->result();
		if($result){
			return $result;
		}else{
			return [];
		}
    }

    public function get_scheme_type(){
        $this->db->select('*');
		$this->db->from('scheme_type');
		$this->db->where('status',1);		
		$result	=	$this->db->get()->result();
		if($result){
			return $result;
		}else{
			return [];
		}
    }


	public function get_beneficiary_status(){
        $this->db->select('*');
		$this->db->from('beneficiary_status');
		$this->db->where('status',1);		
		$result	=	$this->db->get()->result();
		if($result){
			return $result;
		}else{
			return [];
		}
    }

	public function get_states(){
        $this->db->select('*');
		$this->db->from('states');
		$this->db->where('country_id',105);		
		$result	=	$this->db->get()->result();
		if($result){
			return $result;
		}else{
			return [];
		}
    }

	
	public function get_districts($state_id){
        $this->db->select('*');
		$this->db->from('cities');
		if($state_id){
			$this->db->where('state_id',$state_id);		
		}
		$result	=	$this->db->get()->result();
		if($result){
			return $result;
		}else{
			return [];
		}
    }
}
?>