<?php

class Beneficiary_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}


    // function get_beneficiary_invoice(){
    //     $result = $this->db->select("`i`.`id`, `i`.`invoice_no`, `i`.`invoice_date`, `i`.`invoice_value` as `invoice_amount`, `i`.`sanction_amount`, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as Approval_type, `i`.`total_deduction` as `invoice_deduction` , (SELECT sanction_order_no FROM sanction_orders WHERE id = i.sanction_order_id) as sanction_order_no, (SELECT added_on FROM sanction_orders WHERE id = i.sanction_order_id LIMIT 1) as sanction_order_date, (SELECT voucher_no FROM vouchers WHERE id = i.voucher_id LIMIT 1) as voucher_no, (SELECT voucher_date FROM vouchers WHERE id = i.voucher_id LIMIT 1) as voucher_date, (SELECT s.code FROM schemes AS s LEFT JOIN invoice_schemes AS `is` ON `is`.invoice_id = i.id AND s.id = `is`.scheme_id LIMIT 1) as scheme_no, (SELECT ssh.code FROM scheme_sub_head AS ssh LEFT JOIN invoice_scheme_subheads AS iss ON iss.invoice_id = i.id AND ssh.id = iss.subhead_id LIMIT 1) as sub_head_no, (SELECT added_on FROM invoice_payment WHERE invoice_id = i.id LIMIT 1) as date_of_payment, `i`.`invoice as invoice_attachment`, (select voucher from vouchers where i.voucher_id=id) as voucher_attachement, (select sanction_order from sanction_orders where sanction_orders.id=i.sanction_order_id) as sanction_order_attachment")
    //     ->from("invoices i")
    //     ->get()->result_array();
    //     // if(count($result) > 0) {

    //     // }
    //     return $result;
        
    // }


    function get_beneficiary_invoice($id=null,$from=null, $to=null){
        $this->db->select("`i`.`id`,i.sanction_order_id, `i`.`invoice_no`, `i`.`invoice_date`, , `i`.`invoice_value` as `invoice_amount`, `i`.`sanction_amount`, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as Approval_type, `i`.`total_deduction` as `invoice_deduction` , so.sanction_order_no, DATE(so.sanction_order_date) as sanction_order_date, v.voucher_no, Date(v.voucher_date) as voucher_date, (SELECT s.code FROM schemes AS s LEFT JOIN invoice_schemes AS `is` ON `is`.invoice_id = i.id AND s.id = `is`.scheme_id LIMIT 1) as scheme_no, (SELECT ssh.code FROM scheme_sub_head AS ssh LEFT JOIN invoice_scheme_subheads AS iss ON iss.invoice_id = i.id AND ssh.id = iss.subhead_id LIMIT 1) as sub_head_no, (SELECT added_on FROM invoice_payment WHERE invoice_id = i.id LIMIT 1) as date_of_payment, v.voucher, `i`.`invoice`, so.sanction_order")
        ->from("invoices i")
        ->join("vouchers v","v.id = i.voucher_id")
        ->join("sanction_orders so","so.id = i.sanction_order_id")
        ->where("i.approval_status=1 and i.l3_approval_status=1");
        // $this->db->join('states','states.id=beneficiary.state_id','left');
        if($id != null) {
            $this->db->where('i.vendor_id',$id);
        }
        if(!empty($from)) {
			$this->db->where("DATE(v.voucher_date) >= '".$from."'");
		}
		if(!empty($to)){
			$this->db->where("DATE(v.voucher_date) <= '". $to."'");
		}
        $result = $this->db->get()->result_array();

        if($result){
            return $result;
        }
    }

    function get_invoice_breakup_data($sanction_order_id, $from=null, $to=null){
        $this->db->select("`i`.`id`, `i`.`invoice_no`, `i`.`invoice_date`, `i`.`invoice_value` as `invoice_amount`, `i`.`sanction_amount`, (CASE WHEN i.payment_type = 1 THEN 'Full Payment' ELSE 'Part Payment' END) as Approval_type, `i`.`total_deduction` as `invoice_deduction` , (SELECT sanction_order_no FROM sanction_orders WHERE id = i.sanction_order_id) as sanction_order_no, (SELECT added_on FROM sanction_orders WHERE id = i.sanction_order_id LIMIT 1) as sanction_order_date, (SELECT voucher_no FROM vouchers WHERE id = i.voucher_id LIMIT 1) as voucher_no, (SELECT voucher_date FROM vouchers WHERE id = i.voucher_id LIMIT 1) as voucher_date, (SELECT s.code FROM schemes AS s LEFT JOIN invoice_schemes AS `is` ON `is`.invoice_id = i.id AND s.id = `is`.scheme_id LIMIT 1) as scheme_no, (SELECT ssh.code FROM scheme_sub_head AS ssh LEFT JOIN invoice_scheme_subheads AS iss ON iss.invoice_id = i.id AND ssh.id = iss.subhead_id LIMIT 1) as sub_head_no, (SELECT added_on FROM invoice_payment WHERE invoice_id = i.id LIMIT 1) as date_of_payment, (SELECT voucher FROM vouchers WHERE id = i.voucher_id LIMIT 1) as voucher, `i`.`invoice`, (SELECT sanction_order FROM sanction_orders WHERE id = i.sanction_order_id) as sanction_order")
        ->from("invoices i")
        ->where('sanction_order_id', $sanction_order_id);
        if(!empty($from)) {
			$this->db->where("DATE(v.voucher_date) >= '".$from."'");
		}
		if(!empty($to)){
			$this->db->where("DATE(v.voucher_date) <= '". $to."'");
		}
        $result = $this->db->get()->result_array();

        if($result){
            return $result;
        }
    }
}
?>