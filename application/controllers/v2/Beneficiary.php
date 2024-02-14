<?php
require APPPATH . '/libraries/REST_Controller.php';

class Beneficiary extends REST_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('v2/beneficiary_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }

    function beneficiary_report_get(){
        try {
            $beneficiary_id = $this->input->get('beneficiary_id');
            $fromDate = $this->input->get('from_date');
            $toDate = $this->input->get('to_date');
			$get_beneficiary_invoice=$this->beneficiary_model->get_beneficiary_invoice($beneficiary_id, $fromDate, $toDate);
            if(is_array($get_beneficiary_invoice) && count($get_beneficiary_invoice) > 0){
                $response_data_arr = array();
                foreach($get_beneficiary_invoice as $Key => $val){
                    $sanction_order_id = $val['sanction_order_id'];
                    // unset($val['sanction_order_id']);
                    
                    $invoice_amount_balance = round(($val['invoice_amount'] - $val['sanction_amount']),2);
                    $invoice_sanction_balance = $val['sanction_amount'];
                    
                    $invoice_amount_balance_percentage = round((($invoice_amount_balance / $val['invoice_amount']) * 100),2);
                    $invoice_sanction_balance_percentage = round((($invoice_sanction_balance / $val['invoice_amount']) * 100),2);
                    if(!array_key_exists($sanction_order_id,$response_data_arr)){
                        $response_data_arr[$sanction_order_id] = $val;
                        
                        $response_data_arr[$sanction_order_id]['invoice_amount_balance'] = $invoice_amount_balance;
                        $response_data_arr[$sanction_order_id]['invoice_sanction_balance'] = $invoice_sanction_balance;
                        $response_data_arr[$sanction_order_id]['invoice_amount_balance_percentage'] = $invoice_amount_balance_percentage . '%';
                        $response_data_arr[$sanction_order_id]['invoice_sanction_balance_percentage'] = $invoice_sanction_balance_percentage . '%';
                        $response_data_arr[$sanction_order_id]['invoice_details'] = array();
                    }else{
                        $invoices_arr[$val['id']] = $val;
                        $invoices_arr[$val['id']]['invoice_amount_balance'] = $invoice_amount_balance;
                        $invoices_arr[$val['id']]['invoice_sanction_balance'] = $invoice_sanction_balance;
                        $invoices_arr[$val['id']]['invoice_amount_balance_percentage'] = $invoice_amount_balance_percentage . '%';
                        $invoices_arr[$val['id']]['invoice_sanction_balance_percentage'] = $invoice_sanction_balance_percentage . '%';

                        // $response_data_arr[$sanction_order_id]['invoice_details'][] = array_values($invoices_arr);
                        
                    }
                    // $val['invoice_details'] = $this->beneficiary_model->get_invoice_breakup_data($sanction_order_id, $fromDate, $toDate);
                }
                $this->response([
                    'status' => TRUE,
                    'message' => 'Beneficiary report fetched successfully',
                    'count'=>count($response_data_arr),
                    'list'=>array_values($response_data_arr)
                ] 
                , REST_Controller::HTTP_OK
                );
            }else{
                $this->response([
                    'status' => TRUE,
                    'message' => 'Beneficiary report fetched successfully',
                    'count'=>0,
                    'list'=>array(),
    
                ] 
                , REST_Controller::HTTP_OK
                );
            }
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>$e->getMessage(),
                'last_query'=>$this->db->last_query()
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

}
?>