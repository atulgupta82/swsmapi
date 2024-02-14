<?php

require APPPATH . '/libraries/REST_Controller.php';

class Dashboard extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('schemes_model');
        $this->load->model('dashboard_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }

    public function dashboard_get(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {

			$scheme_list=$this->schemes_model->get_schemes();
            $total_budget=0;
            $total_provisional_budget=0;
            $balance=0;
            $payable_expanses=0;
            $utilised_budget=0;
            $total_payment=0;
            $total_interest=72;
            foreach ($scheme_list as $key => $scheme) {
                if($scheme->l2_status==1 && $scheme->l3_status==1 ){
                    $total_budget+=$scheme->total_budget;
                    $total_provisional_budget+=$scheme->total_provisional_budget;
                    $balance+=$scheme->balance;
                    $payable_expanses+=$scheme->payable_expanses;
                    $utilised_budget+=$scheme->utilised_budget;
                    $total_payment+=$scheme->total_payment;
                }
            }
            
			$this->response([
	            'status' => TRUE,
	            'message' => 'Scheme fetched successfully',
                "total_budget"=>$total_budget,
                "total_provisional_budget"=>$total_provisional_budget,
                "balance"=>$balance,
                "payable_expanses"=>$payable_expanses,
                "utilised_budget"=>$utilised_budget,
                "total_payment"=>$total_payment,
                "total_interest"=>$total_interest,
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

    public function add_account_balance_interest_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
            $account_no=$data->account_no;
            $account_balance=$data->account_balance;
            $balance_date=$data->balance_date;
            $interest=$data->interest;
            $interest_date=$data->interest_date;
            $added_by=$data->added_by;
           
            if($account_no && $account_balance && $balance_date && $interest && $interest_date && $added_by){
                
                $post_data=[
                    'account_no'=>$account_no,
                    'account_balance'=>$account_balance,
                    'balance_date'=>$balance_date,
                    'interest'=>$interest,
                    'interest_date'=>$interest_date,
                    'added_on'=>date('Y-m-d H:i:s'),
                    'added_by'=>$added_by
                ];

                $response=$this->dashboard_model->add_balance_interest($post_data);
                if($response){
                    $post_data['id']=$response;
                    $this->response([
                        'status' => true,
                        'data'=>$post_data,
                        'message'=>'Data added successfully.'
                    ] 
                    , REST_Controller::HTTP_OK
                    );
                }else{
                    $this->response([
                        'status' => false,
                        'message'=>'something went wrong.'
                    ] 
                    , REST_Controller::HTTP_OK
                    );
                }
            }else{
               
                $this->response([
                    'status' => false,
                    'message'=>'Invalid parameter'
                ] 
                , REST_Controller::HTTP_OK
                );
            }
        } catch (\Throwable $th) {
            //throw $th;
            $this->response([
	            'status' => false,
	           	'message'=>'Code Error. Try Later!'
	        ] 
			, REST_Controller::HTTP_OK
			);
        }
    }

    public function account_balance_interest_get(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
        
        try {
            $response=$this->dashboard_model->get_balance_interest();   
            $balance_date=$response->balance_date;
            // print_r($balance_date);die;
            if($balance_date){
                $invoices=$this->dashboard_model->get_paid_invoices($balance_date);   
                if($invoices){
                    $response->total_payable_amount=$invoices->total_payable_amount?$invoices->total_payable_amount:0;
                }else{
                    $response->total_payable_amount=0;
                }
            }
            $bd = $this->dashboard_model->get_unapproved_rejected_budgets();
            $response->account_balance = intval($response->account_balance) - intval($bd->p_budget);
            $this->response([
                'status' => true,
                'message'=>'data fetched successfully.',
                'data'=>$response,
            ] 
            , REST_Controller::HTTP_OK
            );

        } catch (\Throwable $th) {
            $this->response([
                'status' => false,
                'message'=>'System Error. Try Later!!'
            ] 
            , REST_Controller::HTTP_OK
            );
        }
    }    
}

?>