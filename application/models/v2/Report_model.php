<?php

class Report_model extends CI_Model{

    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    public function getInterestReport() {
        $query = $this->db->query('select t1.id, DATE_FORMAT(t1.challan_date, "%d-%m-%Y") as transaction_date, t1.challan_no as transaction_no, t1.purpose as particulars, t1.amount as deposite_amount, "" as interest_earned, "" as verification_status, t1.created_at from ifms_challans as t1 where t1.l2_status=1 and t1.l3_status=1 UNION select t1.id, DATE_FORMAT(t1.transaction_date, "%d-%m-%y") as transaction_date, t1.transaction_no, t1.particulars, "" as deposite_amount, t1.interest_earned, "" as verification_status, t1.created_at from interest_report as t1 order by transaction_date asc');
        return $query->result_array();
    }

}