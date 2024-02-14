<?php

class Invoice_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    public function add_challan_attachments($attachments){
    	if($attachments){
			$this->db->where('challan_id',$attachments[0]['challan_id']);
			$this->db->delete('tds_challan_attachments');
			$this->db->insert_batch('tds_challan_attachments',$attachments);
	    	$attachment_id=$this->db->insert_id();
	    	return $attachments;
    	}else{
    		return false;
    	}
    }

    public function create_tds_challan($data, $url) {
        $payload = [];
        $data->invoices = strval($data->invoices);
        $invoices = (str_contains($data->invoices, ',')) ? $data->invoices.explode(',') : [$data->invoices];
        foreach($invoices as $key => $value) {
            $payload[] = [
                'invoice_id' => $value,
                'tds_code_id' => $data->tds_code_id,
                'bsr_code' => $data->bsr_code,
                'tds_challan_value' => $data->tds_challan_value,
                'challan_date' => $data->challan_date,
                'challan_url' => $url,
                'challan_no' => $data->challan_no
            ];
        }
        $this->db->insert_batch('tds_challans', $payload);
        return true;
    }

    public function get_tds_codes() {
        $result = $this->db->select('*')
        ->from("tds_codes tc")
        ->where("status=1")->get()->result_array();
        if($result){
            return $result;
        }
    }

    public function get_tds_invoices($fromDate=null, $toDate=null) {
        // CONCAT(fy.start_year,'-',fy.end_year) as financial_year, is.added_on as date, is.sanction_order_no, v.voucher_no, i.invoice_no, i.payable_amount, i.total_deduction, i.invoice_value, i.sanction_amount, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as approval_type, i.invoice_date as date_of_invoice_approval, i.invoice, v.voucher, Date(v.voucher_date) as voucher_date, so.sanction_order, b.company_name
        $this->db->select("i.*, v.voucher, Date(v.voucher_date) as voucher_date, v.voucher_no, b.company_name, b.pan_no")
		// ->from("invoice_schemes is")
		->from("invoices i")
		->join("beneficiary b", "b.id = i.vendor_id")
		// ->join("invoice_scheme_subheads iss","iss.invoice_id = is.invoice_id and iss.scheme_id=is.scheme_id")
		// ->join("financial_year fy","fy.id = is.financial_year")
		->join("vouchers v","v.id = i.voucher_id")
		// ->join("sanction_orders so","so.id = i.sanction_order_id")
		// ->where("iss.subhead_id", $id)
		->where('i.approval_status=1 and i.l3_approval_status=1 and i.tds_it_amount!="0" and i.id NOT IN (select invoice_id from tds_challans)');
        // ->where("");
        // ->where("i. id NOT IN (select invoice_id from tds_challans)");
		if(!empty($fromDate)) {
			$this->db->where("DATE(v.voucher_date) >= '".$fromDate."'");
		}
		if(!empty($toDate)){
			$this->db->where("DATE(v.voucher_date) <= '". $toDate."'");
		}
		$result = $this->db->get()
		->result_array();

		if($result){
            return $result;
        }
    }

    public function get_tds_challans_invoices($form, $fromDate, $toDate) {
        // CONCAT(fy.start_year,'-',fy.end_year) as financial_year, is.added_on as date, is.sanction_order_no, v.voucher_no, i.invoice_no, i.payable_amount, i.total_deduction, i.invoice_value, i.sanction_amount, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as approval_type, i.invoice_date as date_of_invoice_approval, i.invoice, v.voucher, Date(v.voucher_date) as voucher_date, so.sanction_order, b.company_name
        $this->db->select("i.*, v.voucher, Date(v.voucher_date) as voucher_date, v.voucher_no, b.company_name, b.pan_no, tcd.code, tc.bsr_code, tc.challan_date, tc.challan_no, tc.tds_challan_value")
		// ->from("invoice_schemes is")
		->from("invoices i")
		->join("beneficiary b", "b.id = i.vendor_id")
		// ->join("invoice_scheme_subheads iss","iss.invoice_id = is.invoice_id and iss.scheme_id=is.scheme_id")
		
		->join("vouchers v","v.id = i.voucher_id")
        ->join("tds_challans tc","tc.invoice_id = i.id")
        ->join("tds_codes tcd","tcd.id = tc.tds_code_id")
		// ->join("sanction_orders so","so.id = i.sanction_order_id")
		// ->where("iss.subhead_id", $id)
		->where("i.approval_status=1 and i.l3_approval_status=1");
        if(!empty($form)) {
            $this->db->where("tcd.form_code = '".$form."'");
        }
		if(!empty($fromDate)) {
			$this->db->where("DATE(v.voucher_date) >= ".$fromDate);
		}
		if(!empty($toDate)){
			$this->db->where("DATE(v.voucher_date) <= ". $toDate);
		}
		$result = $this->db->get()
		->result_array();

		if($result){
            return $result;
        }else{
            return [];
        }
    }

}

?>