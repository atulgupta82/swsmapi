<?php

require APPPATH . '/libraries/REST_Controller.php';

class Sanctions_order extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('sanctions_order_model','sanctions_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }

	public function get_current_financial_year(){
		$currentDate = new DateTime();
		$currentMonth = $currentDate->format('n');
		if ($currentMonth >= 4) {
			$financialYearStart = $currentDate->format('Y');
			$financialYearEnd = $currentDate->format('Y') + 1;
		} else {
			$financialYearStart = $currentDate->format('Y') - 1;
			$financialYearEnd = $currentDate->format('Y');
		}

		$this->db->select('*');
		$this->db->from('financial_year');
		$this->db->where('start_year',$financialYearStart);
		$this->db->where('end_year',$financialYearEnd);
		$row=$this->db->get()->row();
		return $row;
	}

    public function sanction_order_get(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$sanction_orders=$this->sanctions_model->get_sanction_orders();
			$this->response([
	            'status' => TRUE,
	            'message' => 'Sanction order fetched successfully',
                'count'=>count($sanction_orders),
	            'list'=>$sanction_orders,
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


	public function invoice_list_by_invoice_no_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$invoice_no=$data->invoice_no;
		$invoice_date=$data->invoice_date;
		$vendor_id=$data->vendor_id;
		$payment_type=$data->payment_type;
		if($invoice_no){
			$search_data=[
				'invoice_no'=>$invoice_no,
				'invoice_date'=>$invoice_date,
				'vendor_id'=>$vendor_id,
				'payment_type'=>$payment_type,
			];

			$invoices_list=$this->sanctions_model->invoice_list_by_invoice_no($search_data);
			$this->response([
	            'status' => TRUE,
	            'message' => 'invoices fetched successfully',
                'count'=>count($invoices_list),
	            'list'=>$invoices_list,
	        ] 
			, REST_Controller::HTTP_OK
			);
		}else{
			$this->response([
				'status' => false,
				'message' => "Invalid parameters."
			], REST_Controller::HTTP_OK
			);
		}
	}

	public function sanction_order_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		
		$sanction_required_fields=['sanction_order_value','sanction_order_no','sanction_order_date'];		
		foreach ($sanction_required_fields as $key => $required_field) {
			if(!$data->$required_field){
				$this->response([
					'status' => false,
					'message' => "Invalid parameters. $required_field field required."
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}

		$files_fields=['sanction_order',];
		foreach ($files_fields as $key => $required_field) {
			if(!$_FILES[$required_field]){
				$this->response([
					'status' => false,
					'message' => "Invalid parameters. $required_field field required."
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}
        try {	
			$current_financial_year=$this->get_current_financial_year();
			$voucher_financial_year=$current_financial_year->id;
			$added_by=$data->added_by;
			$sanction_order=$this->uploadSanctionOrderFile($_FILES['sanction_order']);
			$sanction_orders_data=[
				'financial_year'=>$voucher_financial_year,
				'sanction_order_no'=>$data->sanction_order_no,
				'sanction_order_date'=>date('Y-m-d',strtotime($data->sanction_order_date)),
				'sanction_order_value'=>$data->sanction_order_value,
				'sanction_order'=>$sanction_order,
				'added_on'=>date('Y-m-d H:i:s'),
				'added_by'=>$added_by
			];	

			$isSanctionExist=$this->sanctions_model->check_sanction_order_exist($data->sanction_order_no);
			if($isSanctionExist){
				$this->response([
					'status' => false,
					'message'=>'sanction order no already exist.!!!'
				] 
				, REST_Controller::HTTP_OK
				);die;
			}

			
			$vouchers=$data->vouchers;
			// $financial_year=$data->financial_year;
			

			$voucher_data=[];
			foreach ($vouchers as $key => $voucher) {
				$voucher_no=$voucher->voucher_no;
				$isVoucherExist=$this->sanctions_model->check_voucher_exist($voucher_no,$voucher_financial_year);
				if($isVoucherExist){
					$this->response([
						'status' => false,
						'message'=>'Voucher no already exist.!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}

				$vouchers_file=$this->uploadVoucherFiles($_FILES['vouchers'],$key,'voucher');
				$supporting_docs=$this->uploadVoucherFiles($_FILES['vouchers'],$key,'supporting_docs');

				$voucher_data[$key]['voucher_no']=$voucher_no;
				$voucher_data[$key]['voucher_date']=$voucher->voucher_date;
				$voucher_data[$key]['total_voucher_value']=$voucher->total_voucher_value;
				$voucher_data[$key]['financial_year']=$voucher_financial_year;
				$voucher_data[$key]['voucher']=$vouchers_file;
				$voucher_data[$key]['supporting_docs']=$supporting_docs;
				$voucher_data[$key]['added_on']=date('Y-m-d H:i:s');
				$voucher_data[$key]['added_by']=$added_by;
				$invoices=($voucher->invoices);
				
				foreach ($invoices as $key2 => $invoice) {
					$invoice=json_decode($invoice);
					// print_r($invoice);die;
					$payment_type=$invoice->payment;
					$vendor_id=$invoice->vendor_id;
					$invoice_no=$invoice->invoice_no;
					$invoice_array = json_decode(json_encode($invoice), true);
					$schemes=$invoice_array['schemes'];
					// $financial_year=null;
					// foreach ($schemes as $key => $scheme) {
					// 	$financial_year=$scheme['financial_year'];
					// }					

					unset($invoice_array['payment']);					
					$invoice_file=$this->uploadInvoiceFiles($_FILES['invoices'],$key,$key2,'invoice');
					$invoice_ref_file=$this->uploadInvoiceFiles($_FILES['invoices'],$key,$key2,'invoice_ref');

					$voucher_data[$key]['invoices'][$key2]=$invoice_array;
					$voucher_data[$key]['invoices'][$key2]['added_on']=date('Y-m-d H:i:s');
					$voucher_data[$key]['invoices'][$key2]['added_by']=$added_by;
					$voucher_data[$key]['invoices'][$key2]['payment_type']=$payment_type;
					$voucher_data[$key]['invoices'][$key2]['financial_year']=$voucher_financial_year;
					$voucher_data[$key]['invoices'][$key2]['invoice']=$invoice_file;
					$voucher_data[$key]['invoices'][$key2]['invoice_ref']=$invoice_ref_file;
					// print_r($voucher_data);die;
					if($payment_type==1){
						$isInvoiceExist=$this->sanctions_model->check_invoice_exist($vendor_id,$invoice_array['invoice_date'],$invoice_no,$payment_type);
						if($isInvoiceExist){
							$this->response([
								'status' => false,
								'message'=>'vendor, invoice_date, invoice no. and payment type already exist.!!!'
							] 
							, REST_Controller::HTTP_OK
							);die;
						}
					}elseif($payment_type==2){
						$isfull_paymentInvoiceExist=$this->sanctions_model->check_invoice_exist($vendor_id,$invoice_array['invoice_date'],$invoice_no,1);
						if($isfull_paymentInvoiceExist){
							$this->response([
								'status' => false,
								'message'=>'vendor, invoice_date, invoice no. and payment type full already exist.!!!'
							] 
							, REST_Controller::HTTP_OK
							);die;
						}
						$p_invoice_filter=[
							'invoice_no'=>$invoice_no,
							'invoice_date'=>$invoice_array['invoice_date'],
							'vendor_id'=>$vendor_id,
							'payment_type'=>$payment_type,
						];
						$part_payment_invoice_list=$this->sanctions_model->invoice_list_by_invoice_no($p_invoice_filter);
						$added_sanction_amount=0;
						foreach ($part_payment_invoice_list as $p_invoice) {
							if($p_invoice->sanction_amount){
								$added_sanction_amount+=$p_invoice->sanction_amount;
							}
						}
						$added_sanction_amount+=$invoice_array['sanction_amount'];
						if($added_sanction_amount>$invoice_array['invoice_value']){
							$this->response([
								'status' => false,
								'message'=>'total of sanction amount can not be greater than invoice value.!!!'
							] 
							, REST_Controller::HTTP_OK
							);die;
						}
						if($invoice_array['sanction_amount']==$invoice_array['invoice_value']){
							$this->response([
								'status' => false,
								'message'=>'Equal sanction amount and invoice value can not be partial payment .!!!'
							] 
							, REST_Controller::HTTP_OK
							);die;
						}
					}
					
					$taxable_amount=$invoice->taxable_amount;
					if($payment_type==2){
						$pro_rata_basis=($invoice->sanction_amount*100)/($invoice->invoice_value);
						$taxable_amount=($pro_rata_basis*$taxable_amount)/100;
					}

					$tds_it_amount=$this->get_rate_amount($taxable_amount,$invoice->tds_it_rate);
					$s_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->s_gst_rate);
					$c_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->c_gst_rate);
					$i_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->i_gst_rate);
					$total_deduction=0;
					$total_deduction=$tds_it_amount+$s_gst_amount+$c_gst_amount+$i_gst_amount;
					
					if($invoice->other_deduction>0){
						$total_deduction+=$invoice->other_deduction;
					}

					if($invoice->gis>0){
						$total_deduction+=$invoice->gis;
					}

					if($invoice->nps>0){
						$total_deduction+=$invoice->nps;
					}

					$payable_amount=0;
					$payable_amount=$invoice->sanction_amount-$total_deduction;
					$voucher_data[$key]['invoices'][$key2]['tds_it_amount']=$tds_it_amount;
					$voucher_data[$key]['invoices'][$key2]['s_gst_amount']=$s_gst_amount;
					$voucher_data[$key]['invoices'][$key2]['c_gst_amount']=$c_gst_amount;
					$voucher_data[$key]['invoices'][$key2]['i_gst_amount']=$i_gst_amount;					
					$voucher_data[$key]['invoices'][$key2]['total_deduction']=$total_deduction;					
					$voucher_data[$key]['invoices'][$key2]['payable_amount']=$payable_amount;					
				}
			}
			// print_r($voucher_data);die;
			$sanction_order=$this->sanctions_model->add_sanction_order($sanction_orders_data,$voucher_data);
			$this->response([
	            'status' => TRUE,
	            'message' => 'Sanction order added successfully',
				'sanction_order'=>$sanction_order,
				// 'files'=>$_FILES,
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function edit_invoice_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}		
		// print_r($data);die;
        try {	
			$post_data=[];
			$current_financial_year=$this->get_current_financial_year();
			$voucher_financial_year=$current_financial_year->id;
			$added_by=$data->added_by;			
			$vouchers=$data->vouchers;			
			$invoice=$data->invoice;
			$invoice_id=$data->invoice_id;
			$invoice=json_decode($invoice);
			// print_r($invoice_id);die;
			$payment_type=$invoice->payment;
			$vendor_id=$invoice->vendor_id;
			$invoice_no=$invoice->invoice_no;
			$invoice_array = json_decode(json_encode($invoice), true);
			unset($invoice_array['invoice']);
			$schemes=$invoice_array['schemes'];			
			// print_r($_FILES);die;
			unset($invoice_array['payment']);					
			$invoice_file=$this->uploadEditInvoiceFiles($_FILES['invoice']);
			$invoice_ref_file=$this->uploadEditInvoiceFiles($_FILES['invoice_ref']);
			// print_r($invoice_file);die;
			$post_data=$invoice_array;
			$post_data['added_on']=date('Y-m-d H:i:s');
			$post_data['added_by']=$added_by;
			$post_data['payment_type']=$payment_type;
			$post_data['financial_year']=$voucher_financial_year;
			$post_data['invoice']=$invoice_file;
			$post_data['invoice_ref']=$invoice_ref_file;
			$post_data['sanction_order_no']=$data->sanction_order_no;
			$post_data['sanction_order_id']=$data->sanction_order_id;
			$post_data['voucher_no']=$data->voucher_no;
			$post_data['voucher_id']=$data->voucher_id;
			
			// print_r($post_data);die;
			if($payment_type==1){ //full payment
				$isInvoiceExist=$this->sanctions_model->check_invoice_exist($vendor_id,$invoice_array['invoice_date'],$invoice_no,$payment_type,$invoice_id);
				if($isInvoiceExist){
					$this->response([
						'status' => false,
						'message'=>'vendor, invoice_date, invoice no. and payment type already exist.!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}
				if($invoice_array['sanction_amount']!=$invoice_array['invoice_value']){
					$this->response([
						'status' => false,
						'message'=>'sanction amount and invoice value should be equal in full payment .!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}
			}elseif($payment_type==2){//part payment
				$isfull_paymentInvoiceExist=$this->sanctions_model->check_invoice_exist($vendor_id,$invoice_array['invoice_date'],$invoice_no,1,$invoice_id);
				if($isfull_paymentInvoiceExist){
					$this->response([
						'status' => false,
						'message'=>'vendor, invoice_date, invoice no. and payment type full already exist.!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}
				$p_invoice_filter=[
					'invoice_no'=>$invoice_no,
					'invoice_date'=>$invoice_array['invoice_date'],
					'vendor_id'=>$vendor_id,
					'payment_type'=>$payment_type,
				];
				$part_payment_invoice_list=$this->sanctions_model->invoice_list_by_invoice_no($p_invoice_filter);
				$added_sanction_amount=0;
				foreach ($part_payment_invoice_list as $p_invoice) {
					if($p_invoice->sanction_amount){
						$added_sanction_amount+=$p_invoice->sanction_amount;
					}
				}
				$added_sanction_amount+=$invoice_array['sanction_amount'];
				if($added_sanction_amount>$invoice_array['invoice_value']){
					$this->response([
						'status' => false,
						'message'=>'total of sanction amount can not be greater than invoice value.!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}
				if($invoice_array['sanction_amount']==$invoice_array['invoice_value']){
					$this->response([
						'status' => false,
						'message'=>'Equal sanction amount and invoice value can not be partial payment .!!!'
					] 
					, REST_Controller::HTTP_OK
					);die;
				}
			}
			
			$taxable_amount=$invoice->taxable_amount;
			if($payment_type==2){
				$pro_rata_basis=($invoice->sanction_amount*100)/($invoice->invoice_value);
				$taxable_amount=($pro_rata_basis*$taxable_amount)/100;
			}

			$tds_it_amount=$this->get_rate_amount($taxable_amount,$invoice->tds_it_rate);
			$s_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->s_gst_rate);
			$c_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->c_gst_rate);
			$i_gst_amount=$this->get_rate_amount($taxable_amount,$invoice->i_gst_rate);
			$total_deduction=0;
			$total_deduction=$tds_it_amount+$s_gst_amount+$c_gst_amount+$i_gst_amount;
			
			if($invoice->other_deduction>0){
				$total_deduction+=$invoice->other_deduction;
			}

			if($invoice->gis>0){
				$total_deduction+=$invoice->gis;
			}

			if($invoice->nps>0){
				$total_deduction+=$invoice->nps;
			}

			$payable_amount=0;
			$payable_amount=$invoice->sanction_amount-$total_deduction;
			$post_data['tds_it_amount']=$tds_it_amount;
			$post_data['s_gst_amount']=$s_gst_amount;
			$post_data['c_gst_amount']=$c_gst_amount;
			$post_data['i_gst_amount']=$i_gst_amount;					
			$post_data['total_deduction']=$total_deduction;					
			$post_data['payable_amount']=$payable_amount;
			$post_data['approval_status']=0;					
			$post_data['l3_approval_status']=0;					
			// print_r($post_data);die;
			$sanction_order=$this->sanctions_model->update_invoice_data($post_data,$invoice_id);
			$this->response([
	            'status' => TRUE,
	            'message' => 'Invoice updated successfully',
				// 'files'=>$_FILES,
	        ] 
			, REST_Controller::HTTP_OK
			);
		} catch (Exception $e) {
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function uploadVoucherFiles($uplaod_files,$index,$key)
    {
        $this->load->library('upload');
        $files = $uplaod_files;
		$_FILES['userFile']['name'] = $files['name'][$index][$key];
		$_FILES['userFile']['type'] = $files['type'][$index][$key];
		$_FILES['userFile']['tmp_name'] = $files['tmp_name'][$index][$key];
		$_FILES['userFile']['error'] = $files['error'][$index][$key];
		$_FILES['userFile']['size'] = $files['size'][$index][$key];
        $this->upload->initialize($this->getvoucherUploadConfig());
        if ($this->upload->do_upload('userFile')) {
            $data = $this->upload->data();            
            $file_url = base_url()."uploads/vouchers/".$data['file_name'];
            return $file_url;
        } else {
            $error = $this->upload->display_errors();
            return null;
        }  
    }

    private function getvoucherUploadConfig()
    {
        $config['upload_path'] = './uploads/vouchers/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }

	public function uploadInvoiceFiles($uplaod_files,$i,$index,$key)
    {
        $this->load->library('upload');
        $files = $uplaod_files;
		// print_r($files['name'][$i][$index][$key]);die;
		$_FILES['userFile']['name'] = $files['name'][$i][$index][$key];
		$_FILES['userFile']['type'] = $files['type'][$i][$index][$key];
		$_FILES['userFile']['tmp_name'] = $files['tmp_name'][$i][$index][$key];
		$_FILES['userFile']['error'] = $files['error'][$i][$index][$key];
		$_FILES['userFile']['size'] = $files['size'][$i][$index][$key];

        $this->upload->initialize($this->getInvoiceUploadConfig());
        if ($this->upload->do_upload('userFile')) {
            $data = $this->upload->data();            
            $file_url = base_url()."uploads/invoices/".$data['file_name'];
            return $file_url;
        } else {
            $error = $this->upload->display_errors();
            return null;
        }  
    }

	function uploadEditInvoiceFiles($uplaod_files){
		$this->load->library('upload');
		$uploadData = array();
        $_FILES['userFile']['name'] = $uplaod_files['name'];
        $_FILES['userFile']['type'] = $uplaod_files['type'];
        $_FILES['userFile']['tmp_name'] = $uplaod_files['tmp_name'];
        $_FILES['userFile']['error'] = $uplaod_files['error'];
        $_FILES['userFile']['size'] = $uplaod_files['size'];

		$this->upload->initialize($this->getInvoiceUploadConfig());
        if ($this->upload->do_upload('userFile')) {
            $data = $this->upload->data();            
            $file_url = base_url()."uploads/invoices/".$data['file_name'];
            return $file_url;
        } else {
            $error = $this->upload->display_errors();
			// print_r($uplaod_files);die;
            return null;
        }  
	}
	

    private function getInvoiceUploadConfig()
    {
        $config['upload_path'] = './uploads/invoices/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }

	public function uploadSanctionOrderFile($uplaod_files)
    {
        $this->load->library('upload');

        $files = $uplaod_files;        
        $uploadData = array();
        $_FILES['userFile']['name'] = $files['name'];
        $_FILES['userFile']['type'] = $files['type'];
        $_FILES['userFile']['tmp_name'] = $files['tmp_name'];
        $_FILES['userFile']['error'] = $files['error'];
        $_FILES['userFile']['size'] = $files['size'];
        // print_r($_FILES['userFile']);die;
        $this->upload->initialize($this->getUploadConfig());
        if ($this->upload->do_upload('userFile')) {
            $data = $this->upload->data();
            
            $file_url = base_url()."uploads/sanction_order/".$data['file_name'];
            return $file_url;
        } else {
            $error = $this->upload->display_errors();
            return false;
        }       
    }

    private function getUploadConfig()
    {
        $config['upload_path'] = './uploads/sanction_order/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }
	public function get_rate_amount($taxable_amount,$rate){
		if($rate && $taxable_amount){
			$amount=0;
			$amount=($taxable_amount*$rate)/100;
			return round($amount);
		}else{
			return 0;
		}
	}

	// public function invoices_get(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

    //     try {
	// 		$invoices_list=$this->sanctions_model->get_invoices_list();
	// 		$this->response([
	//             'status' => TRUE,
	//             'message' => 'invoices fetched successfully',
    //             'count'=>count($invoices_list),
	//             'list'=>$invoices_list,
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	} catch (Exception $e) {			
	// 		$this->response([
	//             'status' => false,
	//            	'message'=>'Something went wrong'
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	}
    // }

	public function invoices_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		if(in_array($data->user_type,array("L1","L2","L3"))){
			// try {
				$invoices_list=$this->sanctions_model->get_invoices_list(null,null,$data->user_type);
				$this->response([
					'status' => TRUE,
					'message' => 'invoices fetched successfully',
					'count'=>count($invoices_list),
					'list'=>$invoices_list,
				] 
				, REST_Controller::HTTP_OK
				);
			// } catch (Exception $e) {			
			// 	$this->response([
			// 		'status' => false,
			// 		   'message'=>'Something went wrong'
			// 	] 
			// 	, REST_Controller::HTTP_OK
			// 	);
			// }
		}else{
			$this->response([
				'status' => false,
				'message'=>'Invalid user type value.'
			] 
			, REST_Controller::HTTP_OK
			);
		}

    }

	// public function disbursment_invoice_get(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

    //     try {
	// 		$invoices_list=$this->sanctions_model->get_disbursment_invoices_list();
	// 		$this->response([
	//             'status' => TRUE,
	//             'message' => 'Disbursment invoices fetched successfully',
    //             'count'=>count($invoices_list),
	//             'list'=>$invoices_list,
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	} catch (Exception $e) {			
	// 		$this->response([
	//             'status' => false,
	//            	'message'=>'Something went wrong'
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	}
    // }

	public function disbursment_invoice_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        // try {
			$invoices_list=$this->sanctions_model->get_disbursment_invoices_list(null, $data->from, $data->to);
			$this->response([
	            'status' => TRUE,
	            'message' => 'Disbursment invoices fetched successfully',
                'count'=>count($invoices_list),
	            'list'=>$invoices_list,
	        ] 
			, REST_Controller::HTTP_OK
			);
		// } catch (Exception $e) {			
		// 	$this->response([
	    //         'status' => false,
	    //        	'message'=>'Something went wrong',
		// 		'error' => $e
	    //     ] 
		// 	, REST_Controller::HTTP_OK
		// 	);
		// }
    }


	// public function update_invoice_approval_status_post(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

    //     try {
	// 		$approval_status=$data->approval_status;
	// 		$l3_approval_status=$data->l3_approval_status;
	// 		if($data->invoice_id && ($approval_status|| $l3_approval_status)){
	// 			$post_data=[
	// 				'id'=>$data->invoice_id,
	// 				'remarks'=>$data->remarks,
	// 			];

	// 			if($approval_status){
	// 				$post_data['approval_status']=$approval_status;
	// 			}

	// 			if($l3_approval_status){
	// 				$post_data['l3_approval_status']=$l3_approval_status;
	// 			}
				
	// 			$invoices_list=$this->sanctions_model->update_invoice_approval_status($post_data);
	// 			$this->response([
	// 				'status' => TRUE,
	// 				'message' => 'invoices updated successfully',
	// 				'list'=>$invoices_list
	// 			] 
	// 			, REST_Controller::HTTP_OK
	// 			);
				
	// 		}else{
	// 			$this->response([
	// 				'status' => TRUE,
	// 				'message' => 'Invalid parameters.',
	// 			] 
	// 			, REST_Controller::HTTP_OK
	// 			);
	// 		}
			
	// 	} catch (Exception $e) {			
	// 		$this->response([
	//             'status' => false,
	//            	'message'=>'Something went wrong'
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	}
    // }

	public function update_invoice_approval_status_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$approval_status=$data->approval_status;
			$l3_approval_status=$data->l3_approval_status;
			if($data->invoice_id && ($approval_status|| $l3_approval_status)){
				$post_data=[
					'id'=>$data->invoice_id,
					'remarks'=>$data->remarks,
					'l2remarks'=>$data->l2remarks,
					'l3remarks'=>$data->l3remarks,
				];

				if($approval_status){
					$post_data['approval_status']=$approval_status;
				}

				if($l3_approval_status){
					$post_data['l3_approval_status']=$l3_approval_status;
				}

				$invoices_list=$this->sanctions_model->update_invoice_approval_status($post_data);
				if($invoices_list){
					if($approval_status == 2 || $l3_approval_status == 2){
						$this->sanctions_model->delete_invoice_schemes($data->invoice_id);
					}
					// if($approval_status == 2 || $l3_approval_status == 2){
					// 	$get_invoice_details = $this->sanctions_model->get_invoice_details($data->invoice_id);
					// 	if($get_invoice_details){
					// 		$restore_subhead_amount_update_arr = array();
					// 		foreach($get_invoice_details as $Key => $val){
					// 			$get_subhead_details = $this->sanctions_model->get_subhead_details($val['subhead_id']);
					// 			if($get_subhead_details){
					// 				$restore_subhead_amount_update_arr[] = array(
					// 					'id'=>$val['subhead_id'],
					// 					'budget'=>($get_subhead_details['budget'] + $val['subhead_amount']),
					// 				);
					// 			}
					// 		}

					// 		if(is_array($restore_subhead_amount_update_arr) && count($restore_subhead_amount_update_arr) > 0){
					// 			$this->sanctions_model->update_subhead($restore_subhead_amount_update_arr);
					// 		}
					// 	}
					// }

					$this->response([
						'status' => TRUE,
						'message' => 'invoices updated successfully',
						'list'=>$invoices_list
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => TRUE,
						'message' => 'Request failed!!.',
					] 
					, REST_Controller::HTTP_OK
					);
				}
				
			}else{
				$this->response([
					'status' => TRUE,
					'message' => 'Invalid parameters.',
				] 
				, REST_Controller::HTTP_OK
				);
			}
			
		} catch (Exception $e) {			
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	// public function payment_invoices_get(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

	// 	// if(in_array($data->user_type,array("L1","L2","L3"))){
	// 		try {
	// 			$invoices_list=$this->sanctions_model->get_payment_invoices_list();
	// 			$this->response([
	// 				'status' => TRUE,
	// 				'message' => 'invoices fetched successfully',
	// 				'count'=>count($invoices_list),
	// 				'list'=>$invoices_list,
	// 			] 
	// 			, REST_Controller::HTTP_OK
	// 			);
	// 		} catch (Exception $e) {			
	// 			$this->response([
	// 				'status' => false,
	// 				   'message'=>'Something went wrong'
	// 			] 
	// 			, REST_Controller::HTTP_OK
	// 			);
	// 		}
	// 	// }else{
	// 	// 	$this->response([
	// 	// 		'status' => false,
	// 	// 		'message'=>'Invalid user type value.'
	// 	// 	] 
	// 	// 	, REST_Controller::HTTP_OK
	// 	// 	);
	// 	// }

        
    // }

	public function payment_invoices_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		if(in_array($data->user_type,array("L1","L2","L3"))){
			try {
				$invoices_list=$this->sanctions_model->get_payment_invoices_list(null);
				$this->response([
					'status' => TRUE,
					'message' => 'invoices fetched successfully',
					'count'=>count($invoices_list),
					'list'=>$invoices_list,
				] 
				, REST_Controller::HTTP_OK
				);
			} catch (Exception $e) {			
				$this->response([
					'status' => false,
					   'message'=>'Something went wrong',
					   'error' => $e
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message'=>'Invalid user type value.'
			] 
			, REST_Controller::HTTP_OK
			);
		}

        
    }

	public function update_invoice_payment_status_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$payment_status=$data->payment_status;
			$l3_payment_status=$data->l3_payment_status;
			if($data->invoice_ids && ($payment_status || $l3_payment_status)){
				$post_data=[
					'id'=>$data->invoice_ids,
					'payment_from'=>$data->payment_from,
					'added_by'=>$data->added_by
				];				
				if($payment_status){
					$post_data['payment_status']=$payment_status;
				}
				if($l3_payment_status){
					$post_data['l3_payment_status']=$l3_payment_status;
				}
				$invoices_list=$this->sanctions_model->update_invoice_payment_status($post_data);
				$this->response([
					'status' => TRUE,
					'message' => 'invoices payments status updated successfully',
				] 
				, REST_Controller::HTTP_OK
				);
				
			}else{
				$this->response([
					'status' => TRUE,
					'message' => 'Invalid parameters.',
				] 
				, REST_Controller::HTTP_OK
				);
			}
		} catch (Exception $e) {			
			$this->response([
	            'status' => false,
	           	'message'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function invoice_details_get($invoice_id=null){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		if($invoice_id){
			$invoices_details=$this->sanctions_model->get_invoices_details($invoice_id);
			$this->response([
	            'status' => TRUE,
	            'message' => 'Invoice details fetched successfully',
	            'details'=>$invoices_details,
	        ] 
			, REST_Controller::HTTP_OK
			);
		}else{
			$this->response([
	            'status' => false,
	           	'message'=>'Invalid parameters'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
	}        
}

?>