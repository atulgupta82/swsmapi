<?php
require APPPATH . '/libraries/REST_Controller.php';

class Schemes extends REST_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('v2/schemes_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }

    function schemes_subhead_report_get($scheme_id=null){
        try {
            $scheme_id = $this->input->get('scheme_id');
            $fromDate = $this->input->get('from_date');
            $toDate = $this->input->get('to_date');
			$get_schemes_subhead=$this->schemes_model->get_schemes_subhead($scheme_id);
            if(is_array($get_schemes_subhead) && count($get_schemes_subhead) > 0){
                $response_data_arr = array();
                foreach($get_schemes_subhead as $Key => $val){
                    $id = $val['id'];
                    $get_scheme_subhead_breakup_data = $this->schemes_model->get_scheme_subhead_breakup_data($id, $fromDate, $toDate);
                    $val['scheme_subhead_details'] = (is_array($get_scheme_subhead_breakup_data) && count($get_scheme_subhead_breakup_data) > 0) ? $get_scheme_subhead_breakup_data : array();
                    $response_data_arr[] = $val;
                }
                $this->response([
                    'status' => TRUE,
                    'message' => 'Scheme subhead report fetched successfully',
                    'count'=>count($response_data_arr),
                    'list'=>$response_data_arr,
    
                ] 
                , REST_Controller::HTTP_OK
                );
            }else{
                $this->response([
                    'status' => TRUE,
                    'message' => 'Scheme subhead report fetched successfully',
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