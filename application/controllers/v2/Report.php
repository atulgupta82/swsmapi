<?php
require APPPATH . '/libraries/REST_Controller.php';

class Report extends REST_Controller{

    function __construct(){
        // Construct the parent class
        parent::__construct();
		$this->load->model('v2/Report_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT,DELETE");
		header("Access-Control-Allow-Headers: *");
		$this->load->library('siud');
		
    }

    public function interest_get() {
        $result = $this->Report_model->getInterestReport();
        $this->response([
            'status' => TRUE,
			'result' => $result,
        ],REST_Controller::HTTP_OK);
    }

}

?>