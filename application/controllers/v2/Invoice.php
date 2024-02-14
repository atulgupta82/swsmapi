<?php
require APPPATH . '/libraries/REST_Controller.php';

class Invoice extends REST_Controller{
    function __construct(){
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('v2/beneficiary_model');
        $this->load->model('v2/invoice_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }

    private function getUploadConfig()
    {
        $config['upload_path'] = './uploads/tds-challans/';
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
        $url = "";
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
                $uploadData[$i]['file_url'] = base_url()."uploads/tds-challans/".$data['file_name'];
                $url = base_url()."uploads/tds-challans/".$data['file_name'];

            } else {
                $error = $this->upload->display_errors();
                // echo $error;
            }
        }
        return $url;
    }

    function create_tds_challan_post() {
        try {
            $data	=	json_decode(file_get_contents('php://input'));		
            if(!$data){
                $data = json_decode(json_encode($_POST), FALSE);
            }
                $url;
            if($_FILES['attachment']){
                $url=$this->uploadFiles($_FILES['attachment']);
                // print_r($uploaded_files);
                // $uploaded_files_post_data=[];
                // foreach ($uploaded_files as $key => $uploaded_file) {
                //     $uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
                //     $uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
                //     $uploaded_files_post_data[$key]['challan_id']=$challan;
                //     $uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
                //     // $uploaded_files_post_data[$key]['added_by']=$added_by;
                // }
                // $scheme_attachments=$this->invoice_model->add_challan_attachments($uploaded_files_post_data);
            }
            $result=$this->invoice_model->create_tds_challan($data, $url);
            $this->response([
                'status' => TRUE,
                'message' => 'Challan added successfully',
                'result' => $result,
                'data' =>  $data
            ] 
            , REST_Controller::HTTP_OK
            );
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

    function tdc_codes_get() {
        try{
            $get_tds_codes=$this->invoice_model->get_tds_codes();
            $this->response([
                'status' => TRUE,
                'message' => 'Tds codes fetched successfully',
                'count'=>count($get_tds_codes),
                'list'=>array_values($get_tds_codes)
            ] 
            , REST_Controller::HTTP_OK
            );
        }catch(e) {
            $this->response([
	            'status' => false,
	           	'error'=>$e->getMessage(),
                'last_query'=>$this->db->last_query()
	        ] 
			, REST_Controller::HTTP_OK
			);
        }
    }

    function invoice_tds_get() {
        try {
            $fromDate = $this->input->get('from_date');
            $toDate = $this->input->get('to_date');
            $get_tds_invoice=$this->invoice_model->get_tds_invoices($fromDate, $toDate);
            $this->response([
                'status' => TRUE,
                'message' => 'Invoice tds fetched successfully',
                'count'=>(!empty($get_tds_invoice)) ? count($get_tds_invoice) : 0,
                'list'=>(!empty($get_tds_invoice)) ? array_values($get_tds_invoice) : []
            ] 
            , REST_Controller::HTTP_OK
            );
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

    function invoice_tds_challans_get() {
        try {
            $fromDate = $this->input->get('from_date');
            $toDate = $this->input->get('to_date');
            $form = $this->input->get('from');
            $get_tds_invoice=$this->invoice_model->get_tds_challans_invoices($form, $fromDate, $toDate);
            $this->response([
                'status' => TRUE,
                'message' => 'Invoice tds fetched successfully',
                'count'=>count($get_tds_invoice),
                'list'=>array_values($get_tds_invoice),
                // 'list' => $get_tds_invoice
            ] 
            , REST_Controller::HTTP_OK
            );
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