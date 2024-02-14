<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Challan_model extends CI_Model{

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

	public function get_challan($id) {
		$row = $this->db->select('*')
		->from('ifms_challans')
		->where('id', $id)->get()->row();
		$attachments = $this->db->select('*')->from('challan_attachments')->where('challan_id', $id)->get()->result();
		$row->attachments = $attachments;
		return $row;
	}

    public function get_challans(){
		$this->db->select('ifms_challans.*, head_of_accounts.name as head_of_account_name, head_of_accounts.account_no as head_of_account_no, scheme_bank_account.account_name as sna_name, schemes.name as scheme_name');
		$this->db->from('ifms_challans');
        $this->db->join('schemes', 'schemes.id=ifms_challans.scheme_id', 'left');
        $this->db->join('scheme_bank_account','scheme_bank_account.scheme_id=ifms_challans.scheme_id','left');
        $this->db->join('head_of_accounts','head_of_accounts.id=ifms_challans.head_of_account_id','left');
		$this->db->where('ifms_challans.status',1);
		$data =	$this->db->order_by('ifms_challans.id', 'DESC')->get()->result();
        foreach($data as $key => $row) {
            $this->db->select('*');
            $this->db->from('challan_attachments');
            $this->db->where('challan_id', $row->id);
            $row->attachment = $this->db->get()->row();
        }
        return $data;
	}

	public function edit_challan($id, $data) {
		// print_r($data);
		$data['l2_status'] = NULL;
		$data['l3_status'] = NULL;
		$data['l2_user_id'] = NULL;
		$data['l3_user_id'] = NULL;
		$this->db->where('id', $id);
		$this->db->update('ifms_challans', $data);
		return true;
	}

    public function add_challan_attachments($attachments){
        // echo  "Hii";
    	if($attachments){
			// print_r($attachments);die;
			$this->db->where('challan_id',$attachments[0]['challan_id']);		
			// if($attachments[0]['budget_id']>0){
			// 	$this->db->where('type',2);
			// 	$this->db->where('budget_id',$attachments[0]['budget_id']);
			// }else{
			// 	$this->db->where('type',1);
			// }
			$this->db->delete('challan_attachments');
			// echo $this->db->last_query();die;
            // print_r($attachment_id);die;
			$this->db->insert_batch('challan_attachments',$attachments);
	    	$attachment_id=$this->db->insert_id();
			// print_r($attachment_id);die;
	    	return $attachments;
    	}else{
    		return false;
    	}
    }

    public function insert_data($table,$data){
        if($data && $table){
            $this->db->insert($table,$data);
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

	public function l2Approved($data) {
		$updateBody = [];
		if($data['type'] == 'L2') {
			$updateBody = ['l2_status' => $data['is_approved'], 'l2_user_id' => $data['user_id']];
		}
		if($data['type'] == 'L3') {
			$updateBody = ['l3_status' => $data['is_approved'], 'l3_user_id' => $data['user_id']];
		}
		$this->db->where('id', $data['challan_id']);
		$this->db->update('ifms_challans', $updateBody);
		return true;
	}

	// public function l3Approved($data) {
	// 	$this->db->where('id', $data['challan_id']);
	// 	$this->db->update('ifms_challans', ['is_L3_approved' => $data['is_L3_approved'], 'L3_user_id' => $data['L3_user_id']]);
	// 	return true;
	// }
}