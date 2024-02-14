<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Headofaccounts extends REST_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
		$this->load->model('v2/Headofaccounts_model');
		$this->load->model('users_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT,DELETE");
		header("Access-Control-Allow-Headers: *");
		$this->load->library('siud');
		
    }

    public function index_get(){
    	$head_of_accounts=$this->Headofaccounts_model->get_headofaccounts();
		$this->response([
			'status' => TRUE,
			'result' => $head_of_accounts,
		] 
		, REST_Controller::HTTP_OK
		);
		// print_r($users);
    }

}


?>