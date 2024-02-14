<?php

class Dashboard_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    public function insert_data($table,$data){
        if($data && $table){
            $this->db->insert($table,$data);
            return $this->db->insert_id();
        }else{
            return false;
        }
    }

    public function add_balance_interest($data){
        if($data){
            $this->db->select('*');
            $this->db->from('balance_interest');
            $row=$this->db->get()->row();
            // print_r($data);die;
            if($row->id){
                $this->db->where('id',$row->id);
                $this->db->update('balance_interest',$data);
                $inserted_id=$row->id;
            }else{
                $inserted_id= $this->insert_data('balance_interest',$data);
            }
           return $inserted_id;
        }else{
            return false;
        }
    }

    public function get_balance_interest(){
        $this->db->select('*');
        $this->db->from('balance_interest');
        $row=$this->db->get()->row();
        return $row;
    }
    public function get_unapproved_rejected_budgets() {
        $this->db->select('sum(budget) as p_budget');
        $this->db->from('subhead_budgets');
        $this->db->where("l2_status IN (0,2) or l3_status IN (0,2)");
        $row=$this->db->get()->row();
        return $row;
    }
    public function get_paid_invoices($date){
        // echo $date;die;
        if($date){
            $this->db->select('sum(invoices.payable_amount) as total_payable_amount');
            $this->db->from('invoices');
            $this->db->join('invoice_payment','invoice_payment.invoice_id=invoices.id','right');
            $this->db->where('invoices.l3_payment_status',1);
            $this->db->where('invoices.payment_status',1);
            $this->db->where('invoice_payment.added_on >=',$date);
            // $this->db->group_by('invoices.id');
            $result=$this->db->get()->row();
            // echo $this->db->last_query();die;
            return $result;
        }else{
            // echo 'fds';die;
            return false;
        }
    }
}

?>