<?php

class Schemes_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    function get_schemes_subhead($scheme_id){
        $result = $this->db->select("ssh.id, ssh.code as scheme_subhead_no, ssh.name  as scheme_subhead_name, ssh.added_on as date, CONCAT(fy.start_year,'-',fy.end_year) as financial_year, ssh.budget as balance")
        ->from("scheme_sub_head ssh")
		->join("financial_year fy","fy.id = ssh.financial_year")
		->where("ssh.scheme_id",$scheme_id)
        ->get()->result_array();

        if($result){
            return $result;
        }
    }

	function get_scheme_subhead_breakup_data($id, $from=null, $to=null){
		$this->db->select("CONCAT(fy.start_year,'-',fy.end_year) as financial_year, is.added_on as date, is.sanction_order_no, v.voucher_no, i.invoice_no, i.payable_amount, i.total_deduction, i.invoice_value, i.sanction_amount, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as approval_type, i.invoice_date as date_of_invoice_approval, i.invoice, v.voucher, Date(v.voucher_date) as voucher_date, so.sanction_order, b.company_name")
		->from("invoice_schemes is")
		->join("invoices i","i.id = is.invoice_id")
		->join("beneficiary b", "b.id = i.vendor_id")
		->join("invoice_scheme_subheads iss","iss.invoice_id = is.invoice_id and iss.scheme_id=is.scheme_id")
		->join("financial_year fy","fy.id = is.financial_year")
		->join("vouchers v","v.id = i.voucher_id")
		->join("sanction_orders so","so.id = i.sanction_order_id")
		->where("iss.subhead_id", $id)
		->where("i.approval_status=1 and i.l3_approval_status=1");
		if(!empty($from)) {
			$this->db->where("DATE(v.voucher_date) >= '".$from."'");
		}
		if(!empty($to)){
			$this->db->where("DATE(v.voucher_date) <= '". $to."'");
		}
		$result = $this->db->get()
		->result_array();

		if($result){
            return $result;
        }
	}
}

?>