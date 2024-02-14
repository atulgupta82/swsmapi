<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';

class Challans extends REST_Controller {

    function __construct(){
        // Construct the parent class
        parent::__construct();
		$this->load->model('v2/Challan_model');
		$this->load->model('users_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT,DELETE");
		header("Access-Control-Allow-Headers: *");
		$this->load->library('siud');
		
    }

    private function getUploadConfig()
    {
        $config['upload_path'] = './uploads/challans/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }

    public function uploadFiles($uplaod_files)
    {
        // echo "Hii";
        $this->load->library('upload');

        $files = $uplaod_files;
        $fileCount = count($files['name']);
        // echo $fileCount;
        $uploadData = array();

        for ($i = 0; $i < $fileCount; $i++) {
            $_FILES['userFile']['name'] = $files['name'][$i];
            $_FILES['userFile']['type'] = $files['type'][$i];
            $_FILES['userFile']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['userFile']['error'] = $files['error'][$i];
            $_FILES['userFile']['size'] = $files['size'][$i];

            $this->upload->initialize($this->getUploadConfig());
            if ($this->upload->do_upload('userFile')) {
                $data = $this->upload->data();
                $uploadData[$i]=$data;
                // print_r($uploadData);
                $uploadData[$i]['file_url'] = base_url()."uploads/challans/".$data['file_name'];

            } else {
                $error = $this->upload->display_errors();
                // echo $error;
            }
        }
        return $uploadData;
    }

    public function single_get($id) {
        // echo $id;
        // die;
        $result = $this->Challan_model->get_challan($id);
        $this->response([
            'status' => TRUE,
			'result' => $result,
        ],REST_Controller::HTTP_OK);
    }

    public function index_get() {
        $challans=$this->Challan_model->get_challans();
		$this->response([
			'status' => TRUE,
			'result' => $challans,
		] 
		, REST_Controller::HTTP_OK
		);
    }

    

    public function edit_post($id) {
        // $id = $this->put('id');
        // echo $id;
        $post = $this->post();
        $data = [
            'scheme_id' => $post['scheme_id'],
            'head_of_account_id' => $post['head_of_account_id'],
            'challan_date' => $post['challan_date'],
            'amount' => $post['amount'],
            'challan_no' => $post['challan_no']
        ];
        $challan = $this->Challan_model->edit_challan($id, $data);
        
        if($_FILES['attachment']){
            $uploaded_files=$this->uploadFiles($_FILES['attachment']);
            // print_r($uploaded_files);
            $uploaded_files_post_data=[];
            foreach ($uploaded_files as $key => $uploaded_file) {
                $uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
                $uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
                $uploaded_files_post_data[$key]['challan_id']=$challan;
                $uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
                // $uploaded_files_post_data[$key]['added_by']=$added_by;
            }
            $scheme_attachments=$this->Challan_model->add_challan_attachments($uploaded_files_post_data);
        }
        $this->response([
            'status' => TRUE,
            'message' => 'Challan Updated Successfully',
			'result' => $this->Challan_model->get_challan($id),
        ],REST_Controller::HTTP_OK);
    }

    public function index_post() {
        $data =	json_decode(file_get_contents('php://input'));
        if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
        $challan = $this->Challan_model->insert_data('ifms_challans', $data);
        if($_FILES['attachment']){
            $uploaded_files=$this->uploadFiles($_FILES['attachment']);
            // print_r($uploaded_files);
            $uploaded_files_post_data=[];
            foreach ($uploaded_files as $key => $uploaded_file) {
                $uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
                $uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
                $uploaded_files_post_data[$key]['challan_id']=$challan;
                $uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
                // $uploaded_files_post_data[$key]['added_by']=$added_by;
            }
            $scheme_attachments=$this->Challan_model->add_challan_attachments($uploaded_files_post_data);
        }
        
        $this->response([
            'status' => TRUE,
            'message' => 'Challan Added Successfully',
			'result' => $challan,
        ],REST_Controller::HTTP_OK);
    }

    public function l2_l3_Approval_post() {
        $post = $this->post();
        $data = [
            'challan_id' => $post['challan_id'],
            'is_approved' => $post['is_approved'],
            'user_id' => $post['user_id'],
            'type' => $post['type']
        ];
        $challan = $this->Challan_model->l2Approved($data);
        $message = ($post['type'] == 'L2') ? 'L2 Approved Successfully' : 'L3 Approved Successfully';
        $this->response([
            'status' => TRUE,
            'message' => $message,
			'result' => $challan,
        ],REST_Controller::HTTP_OK);
    }

    // public function l3Approval_post() {
    //     // $data =	json_decode(file_get_contents('php://input'));
    //     // if(!$data){
	// 	// 	$data = json_decode(json_encode($_POST), FALSE);
	// 	// }
    //     $data = [
    //         'challan_id' => $this->post('challan_id'),
    //         'is_L3_approved' => $this->post('is_L3_approved'),
    //         'L3_user_id' => $this->post('L3_user_id')
    //     ];
    //     $challan = $this->Challan_model->l3Approved($data);
    //     $this->response([
    //         'status' => TRUE,
    //         'message' => 'L3 Approved Successfully',
	// 		'result' => $challan,
    //     ],REST_Controller::HTTP_OK);
    // }


}