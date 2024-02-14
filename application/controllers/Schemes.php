<?php
require APPPATH . '/libraries/REST_Controller.php';

class Schemes extends REST_Controller{
    function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('schemes_model');
		$this->load->model('dashboard_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET,PUT");
    }

	function formatNumberWithLeadingZeros($number, $width) {
		return sprintf("%0{$width}d", $number);
	}

	public function get_latest_ifms_challan(){
		$this->db->select('*');
		$this->db->from('ifms_challans');
		$this->db->where('l2_status',1);
		$this->db->where('l3_status',1);
		$this->db->order_by('challan_date','desc');
		$row=$this->db->get()->row();
		return $row;
	}

	public function generate_xml($scheme_id,$date,$scheme_count=null){
		$latest_challan=$this->get_latest_ifms_challan();
		$scheme_details=$this->schemes_model->get_schemes($scheme_id);
		$scheme_payment_list=$this->schemes_model->get_scheme_payment_list($scheme_id,$date);
		$bank_details=$scheme_details[0]->bank_details;
		// print_r($scheme_payment_list);die;
		$balance_interest=$this->dashboard_model->get_balance_interest();
		$balance_date=$balance_interest->balance_date;

		$paid_invoices=$this->dashboard_model->get_paid_invoices($balance_date);   
		$account_balance= $balance_interest->account_balance;
		$total_payable_amount=$paid_invoices->total_payable_amount;
		$balance=$balance_interest->account_balance-($total_payable_amount>0?$total_payable_amount:0);
		// echo $balance;die;
		$xml_data = array(
			'Expenditure' => array(
				'FIN_YEAR' => $scheme_details[0]->start_year.$scheme_details[0]->end_year,
				'PAYEEDETAILS' => 'Y',
				'SUBSIDIARY_ACCOUNTS' => 'N',
				'PAYEE_PARTY'=>[
					'PAYEE_HEADER'=>[
						'BANK_IFSC'=>$bank_details->ifsc_code,
						'SNA_ACCOUNT_NO'=>$bank_details->account_no,
						'BALANCE'=>$balance>0?$balance:0,
						'INTEREST'=>$latest_challan->amount,
						'INT_DEPOSIT_DATE'=>date('d/m/Y',strtotime($latest_challan->challan_date)),
						'INT_DEPOSIT_CHALLAN'=>$latest_challan->challan_no,
						'PAYEECOUNT'=>count($scheme_payment_list),
					],
					'PAYEE_DETAILS'=>[
						
					]
				],
				'SUBSIDIARY_PARTY'=>[
					'PAYEE_HEADER'=>[
						'BANK_IFSC'=>'',
						'SNA_ACCOUNT_NO'=>'',
						'BALANCE'=>'',
						'INTEREST'=>null,
						'PAYEECOUNT'=>0,
					],
					'PAYEE_DETAILS'=>[
						'PAYEE'=>[
							'PayeeUniqueId'=>'',
							'Payee_Name'=>'',
							'Payee_Mobile_No'=>'',
							'Email'=>'',
							'Address'=>'',
							'IFSC_CODE'=>'',
							'ACC_NO'=>'',
							'VoucherNumber'=>'',
							'Date'=>'',
							'GrossAmount'=>'',
							'DeductionAmount'=>'',
							'NetAmount'=>'',
						]
					]
				],
			)
		);
		$payee_arr=$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS'];
		$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS']=[
			'SCHEME_DETAILS'=>[
				'SCHEME_CODE'=>$scheme_details[0]->code,
				'BALANCE'=>0,
			]	
		];		
		$total_net_amount=0.00;
		$total_subheads=0;
		$pay_index=0;
		foreach ($scheme_payment_list as $key => $scheme_payment) {
			// print_r($scheme_payment);die;
			$subheads=$scheme_payment->subheads;
			$payee_arr['OBJ_CODE']=[];
			foreach ($subheads as $key => $subhead) {
				$payee_arr['PayeeUniqueId']=$scheme_payment->beneficiary_id;
				$payee_arr['Payee_Name']=$scheme_payment->beneficiary_name;
				$payee_arr['Payee_Mobile_No']=$scheme_payment->b_mobile;
				$payee_arr['Email']=$scheme_payment->b_email;
				$payee_arr['Address']=$scheme_payment->b_address_1;
				$payee_arr['IFSC_CODE']=$scheme_payment->b_ifsc_code;
				$payee_arr['ACC_NO']=$scheme_payment->b_account_no;
				$payee_arr['VoucherNumber']=$scheme_payment->voucher_no;
				$payee_arr['Date']=date('d/m/Y',strtotime($scheme_payment->date));
				$payee_arr['OBJ_CODE']=$subhead->code;
				$payee_arr['GrossAmount']=$subhead->subhead_amount;
				$payee_arr['DeductionAmount']=0;
				$payee_arr['NetAmount']=$subhead->subhead_amount;
				// $payee_arr['OBJ_CODE'][]=[
				// 	'OBJ_CODE'=>$subhead->code,
				// 	'GrossAmount'=>$subhead->subhead_amount,
				// 	'NetAmount'=>$subhead->subhead_amount,
				// 	'DeductionAmount'=>0,
				// ];
				$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS'][$pay_index]=$payee_arr;
				$total_subheads++;
				$pay_index++;
			}
			// $payee_arr['GrossAmount']=$scheme_payment->sanction_amount;
			// $payee_arr['DeductionAmount']=$scheme_payment->total_deduction;
			// $payee_arr['NetAmount']=$scheme_payment->amount;
			
			$total_net_amount+=$scheme_payment->amount;
		}
		$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_HEADER']['PAYEECOUNT']=$total_subheads;
		// print_r($scheme_details);die;
		if($scheme_details[0]->balance>0){
			$opening_balance=$total_net_amount+$scheme_details[0]->balance;
		}else{
			$opening_balance=$total_net_amount;
		} 

		$budget_balance=$scheme_details[0]->total_budget-$scheme_details[0]->utilised_budget;
		$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS']['SCHEME_DETAILS']['BALANCE']=$budget_balance;
		
		$scheme_count=$scheme_count+1;
		$schemecountWithZeros=$this->formatNumberWithLeadingZeros($scheme_count,3);
		$messageId="HDFCEXP".date('dmY',strtotime($date)).$schemecountWithZeros;
		if(count($scheme_payment_list)>0){
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><BankExp MsgDtTm="'.date('Y-m-d',strtotime($date))."T".date('H:i:s').'" MessageId="'.$messageId.'" Source="HDFC" Destination="IFMS" StateName="Uttarakhand" RecordsCount="'.($total_subheads).'" NetAmountSum="'.$total_net_amount.'" ></BankExp>');
			$this->array_to_xml($xml_data, $xml);
		}else{
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><BankExp MsgDtTm="'.date('Y-m-d',strtotime($date))."T".date('H:i:s').'" MessageId="'.$messageId.'" Source="HDFC" Destination="IFMS" StateName="Uttarakhand" RecordsCount="0" NetAmountSum="0.00" ></BankExp>');
		}
		// echo $xml->asXML();die;
		return [
			'name'=>"".$messageId.".xml",
			'content'=>$xml->asXML()
		];
	}

	private function array_to_xml($array, &$xml) {
		
        foreach ($array as $key => $value) {
			
            // if (is_array($value)) {
            //     if (!is_numeric($key)) {
			// 		echo $key.'\n';
			// 		$subnode = $xml->addChild($key);
			// 		$this->array_to_xml($value, $subnode);
            //     } else {	
					
            //         $subnode = $xml->addChild('PAYEE');
            //         $this->array_to_xml($value, $subnode);
            //     }				
            // } else {				
            //     $xml->addChild($key, htmlspecialchars($value));
            // }

			if (is_array($value)) {
				if (!is_numeric($key)) {
					$subnode = $xml->addChild($key);
					$this->array_to_xml($value, $subnode);
				} else {
					if($value['PayeeUniqueId']){
						$subnode = $xml->addChild('PAYEE');
						$this->array_to_xml($value, $subnode);	
						
					}else{
						$this->array_to_xml($value, $xml);
						
					}
				}
			} else {
				$xml->addChild($key, htmlspecialchars($value));
			}
        }
		
    }

	public function download_xml_as_zip_get(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			// $data = json_decode(json_encode($_POST), FALSE);
		}
		$date=date("Y-m-d");
		// $date="2023-12-05";
		// $schemes=$this->schemes_model->get_invoice_schemes_list($date);
		$schemes=$this->schemes_model->get_schemes();
		// print_r($schemes);die;
		$xmlFiles = [
			// Add more files as needed
			];	
		foreach ($schemes as $key => $scheme) {
			$xml_data=$this->generate_xml($scheme->id,$date,$key);
			
			$xmlFiles[]=[
				"name"=>$xml_data['name'],
				"content"=>$xml_data['content'],
			];
			
		}	  
		if(count($xmlFiles)==0){
			echo "No Transection in Current Day";die;
		}
		// print_r($xmlFiles);die;
		$zip = new ZipArchive();
		$zipName = 'xmlFiles'.date('Y-m-d H:i:s').'.zip';		  
		if ($zip->open($zipName, ZipArchive::CREATE) === true) {
			foreach ($xmlFiles as $file) {
				$zip->addFromString($file['name'], $file['content']);
				
			}
			$zip->close();	
			if (file_exists($zipName)) {
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="' . $zipName . '"');
				readfile($zipName);		
				unlink($zipName); // Delete the temporary ZIP file
			}else{
				echo 'Failed to create ZIP file';
			}
			
		} else {
			echo 'Failed to create ZIP file';
		}
	}

    public function scheme_get($scheme_id=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$scheme_list=$this->schemes_model->get_schemes($scheme_id);

			$this->response([
	            'status' => TRUE,
	            'message' => 'Scheme fetched successfully',
                'count'=>count($scheme_list),
	            'schemes'=>$scheme_list,

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


	public function scheme_details_by_fy_get($scheme_id=null,$financial_year=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$scheme_list=$this->schemes_model->scheme_details_by_fy($scheme_id,$financial_year);

			$this->response([
	            'status' => TRUE,
	            'message' => 'Scheme fetched successfully',
                'count'=>count($scheme_list),
	            'schemes'=>$scheme_list,

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
	//added also pending amount
	public function scheme_details_by_fy_added_pending_get($scheme_id=null,$financial_year=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$scheme_list=$this->schemes_model->scheme_details_by_fy_added_pending($scheme_id,$financial_year);

			$this->response([
	            'status' => TRUE,
	            'message' => 'Scheme fetched successfully',
                'count'=>count($scheme_list),
	            'schemes'=>$scheme_list,

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


    public function scheme_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
            $name=$data->name;
            $type=$data->type;
            $code=$data->code;
            $grant_code=$data->grant_code;
            $department=$data->department;
            $financial_year=$data->financial_year;
            $carry_forwarded=$data->carry_forwarded;
            $sub_heads=json_decode($data->sub_heads);
            $bank=json_decode($data->bank);
            $added_by=$data->added_by;
            $post_data_arr=[
            	'name'=>$name,
            	'type'=>$type,
            	'code'=>$code,
            	'grant_code'=>$grant_code,
            	'department'=>$department,
            	'financial_year'=>$financial_year,
            	'carry_forwarded'=>$carry_forwarded,
            	'added_by'=>$added_by,
            	'added_on'=>date('Y-m-d H:i:s'),
            	'total_budget'=>0,
            ];     
			// print_r($data);die;
            
            if($name && $type && $code && $grant_code && $department && $financial_year && $added_by){
            	$subheads_fields=['name','code','budget_date'];
            	$sub_head_data_arr=[];
            	$i=0;
            	foreach ($sub_heads as $key => $sub_head) {					
            		$sub_head_arr = json_decode(json_encode($sub_head),true);
            		$sub_head_arr['added_by']=$added_by;
            		$sub_head_arr['financial_year']=$financial_year;
            		$sub_head_arr['added_on']=date('Y-m-d H:i:s');
            		// print_r($sub_head);die;
            		foreach($subheads_fields as $key1=>$each){
            			if(!$sub_head->$each){ 
            				$this->response([
			                    'status' => false,
			                    'message' => 'all subhead fields required.!',                    
			                ] 
			                , REST_Controller::HTTP_OK
			                );
            			}
            		}
            		// print_r($sub_head_arr['budget']);die;
            		$post_data_arr['total_budget']+=$sub_head_arr['budget']>0?$sub_head_arr['budget']:0;
            		$sub_head_data_arr[]=$sub_head_arr;
            	}
            	// print_r($post_data_arr);die;

            	$bank_fields=['account_name','bank_name','branch_name','account_no','account_type','ifsc_code'];
            	foreach ($bank as $key => $each_bank) {            		
            		if(!$bank->$key){
        				$this->response([
		                    'status' => false,
		                    'message' => 'all bank fields required.!',                    
		                ] 
		                , REST_Controller::HTTP_OK
		                );
        			}
            	}
            	$bank_arr = json_decode(json_encode($bank),true);
            	$bank_arr['added_by']=$added_by;
            	$bank_arr['added_on']=date('Y-m-d H:i:s');
            	$post_data_arr['sub_heads']=$sub_head_data_arr;
            	$post_data_arr['bank']=$bank_arr;
            	// print_r($post_data_arr);die;
            	try {
					$scheme_response=$this->schemes_model->add_schemes($post_data_arr);
	                if($scheme_response){
						if($_FILES['attachment']){
							$uploaded_files=$this->uploadFiles($_FILES['attachment']);
							$uploaded_files_post_data=[];
							
							foreach ($uploaded_files as $key => $uploaded_file) {
								$uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
								$uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
								$uploaded_files_post_data[$key]['scheme_id']=$scheme_response;
								$uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
								$uploaded_files_post_data[$key]['added_by']=$added_by;
							}
							$scheme_attachments=$this->schemes_model->add_scheme_attachments($uploaded_files_post_data);
						}
						$scheme_list=$this->schemes_model->get_schemes($scheme_response);   
	                	$this->response([
		                    'status' => TRUE,
							"list"=>$scheme_list,
		                    'message' => 'Scheme added successfully'
		                ] 
		                , REST_Controller::HTTP_OK
		                );
	                }else{
						$this->response([
			                    'status' => false,
			                    'message' => 'Scheme code should be unique.'
			                ] 
			                , REST_Controller::HTTP_OK
		                );
	                }
            	} catch (Exception $e) {
        			$this->response([
		                    'status' => false,
		                    'message' => 'Something went wrong.'
		                ] 
		                , REST_Controller::HTTP_OK
	                );
            	}                                
            }else{
                
                $this->response([
                    'status' => false,
                    'message' => 'invalid parameters',                    
                ] 
                , REST_Controller::HTTP_OK
                );
            }
			
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function update_scheme_post($id=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
	
        try {
            $name=$data->name;
            $type=$data->type;
            $code=$data->code;
            $grant_code=$data->grant_code;
            $department=$data->department;
            $financial_year=$data->financial_year;
            $carry_forwarded=$data->carry_forwarded;
            $sub_heads=json_decode($data->sub_heads);
            $bank=json_decode($data->bank);
            $added_by=$data->added_by;
            $post_data_arr=[
            	'name'=>$name,
            	'type'=>$type,
            	'code'=>$code,
            	'grant_code'=>$grant_code,
            	'department'=>$department,
            	'financial_year'=>$financial_year,
            	'carry_forwarded'=>$carry_forwarded,
            	'added_by'=>$added_by,
            	'added_on'=>date('Y-m-d H:i:s'),
            	'total_budget'=>0,
            ];     
			
            
            if($id>0 && $name && $type && $code && $grant_code && $department && $financial_year && $added_by){
            	$subheads_fields=['name','code','budget_date'];
            	$sub_head_data_arr=[];
            	$i=0;
            	foreach ($sub_heads as $key => $sub_head) {					
            		$sub_head_arr = json_decode(json_encode($sub_head),true);
            		$sub_head_arr['added_by']=$added_by;
            		$sub_head_arr['financial_year']=$financial_year;
            		$sub_head_arr['added_on']=date('Y-m-d H:i:s');
            		// print_r($sub_head);die;
            		foreach($subheads_fields as $key1=>$each){
            			if(!$sub_head->$each){ 
            				$this->response([
			                    'status' => false,
			                    'message' => 'all subhead fields required.!',                    
			                ] 
			                , REST_Controller::HTTP_OK
			                );
            			}
            		}
            		// print_r($sub_head_arr['budget']);die;
            		$post_data_arr['total_budget']+=$sub_head_arr['budget']>0?$sub_head_arr['budget']:0;
            		$sub_head_data_arr[]=$sub_head_arr;
            	}
            	// print_r($post_data_arr);die;

            	$bank_fields=['account_name','bank_name','branch_name','account_no','account_type','ifsc_code'];
            	foreach ($bank as $key => $each_bank) {            		
            		if(!$bank->$key){
        				$this->response([
		                    'status' => false,
		                    'message' => 'all bank fields required.!',                    
		                ] 
		                , REST_Controller::HTTP_OK
		                );
        			}
            	}
            	$bank_arr = json_decode(json_encode($bank),true);
            	$bank_arr['added_by']=$added_by;
            	$bank_arr['added_on']=date('Y-m-d H:i:s');
            	$post_data_arr['sub_heads']=$sub_head_data_arr;
            	$post_data_arr['bank']=$bank_arr;
            	// print_r($post_data_arr);die;
            	try {
					$scheme_response=$this->schemes_model->update_schemes($post_data_arr,$id);
	                if($scheme_response){
						// print_r($_FILES);die;
						if($_FILES['attachment']){
							$uploaded_files=$this->uploadFiles($_FILES['attachment']);
							$uploaded_files_post_data=[];
							
							foreach ($uploaded_files as $key => $uploaded_file) {
								$uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
								$uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
								$uploaded_files_post_data[$key]['scheme_id']=$scheme_response;
								$uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
								$uploaded_files_post_data[$key]['added_by']=$added_by;
							}
							$scheme_attachments=$this->schemes_model->add_scheme_attachments($uploaded_files_post_data);
						}
						$scheme_list=$this->schemes_model->get_schemes($scheme_response);   
	                	$this->response([
		                    'status' => TRUE,
							"list"=>$scheme_list,
		                    'message' => 'Scheme updated successfully'
		                ] 
		                , REST_Controller::HTTP_OK
		                );
	                }else{
						$this->response([
			                    'status' => false,
			                    'message' => 'Scheme code should be unique.'
			                ] 
			                , REST_Controller::HTTP_OK
		                );
	                }
            	} catch (Exception $e) {
        			$this->response([
		                    'status' => false,
		                    'message' => 'Something went wrong.'
		                ] 
		                , REST_Controller::HTTP_OK
	                );
            	}                                
            }else{
                
                $this->response([
                    'status' => false,
                    'message' => 'invalid parameters',                    
                ] 
                , REST_Controller::HTTP_OK
                );
            }
			
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong'
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

	public function delete_scheme_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$scheme_id=$data->scheme_id;
		if($scheme_id>0){
			try {
				$response=$this->schemes_model->delete_scheme($scheme_id);
				if($response){
					$this->response([
						'status' => true,
						'message' => 'Scheme deleted successfully.',                    
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => true,
						'message' => 'Something went wrong.',                    
					] 
					, REST_Controller::HTTP_OK
					);
				}				
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					'message' => 'Something went wrong.',                    
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}

	public function scheme_status_update_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$scheme_id=$data->scheme_id;
		$l2_status=$data->l2_status;
		$l3_status=$data->l3_status;
		if($scheme_id>0 && ($l2_status || $l3_status)){
			$post_data=[
				'id'=>(int)$scheme_id,
			];
			if($l2_status){
				$post_data['l2_status']=$l2_status;
				$post_data['l2remarks']=$data->l2remarks;
			}
			if($l3_status){
				$post_data['l3_status']=$l3_status;
				$post_data['l3remarks']=$data->l3remarks;

			}
			$msg='';
			if($l2_status==1 || $l3_status==1){
				$msg="Scheme approved successfully.";
			}
			if($l2_status==2 || $l3_status==2){
				$msg="Scheme rejected successfully.";
			}
			try {
				$response=$this->schemes_model->scheme_status_update($post_data);
				if($response){
					$scheme_list=$this->schemes_model->get_schemes($scheme_id);
					$this->response([
						'status' => true,
						'list'=>$scheme_list,
						'message'=>$msg
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => false,
						'message'=>'Something went wrong'
					] 
					, REST_Controller::HTTP_OK
					);
				}
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					   'message'=>'Something went wrong'
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}


    public function uploadFiles($uplaod_files)
    {
        $this->load->library('upload');

        $files = $uplaod_files;
        $fileCount = count($files['name']);
        
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
                $uploadData[$i]['file_url'] = base_url()."uploads/schemes/".$data['file_name'];

            } else {
                $error = $this->upload->display_errors();
                // echo $error;
            }
        }
        return $uploadData;
    }

    private function getUploadConfig()
    {
        $config['upload_path'] = './uploads/schemes/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }

	public function attachment_post(){
		// just for checking
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		// $uploaded_files=$this->uploadFiles($_FILES['attachment']);
		$this->response([
			'status' => true,
			'files'=>$_FILES,
			'subheads'=>json_decode($data->sub_heads),
			'data'=>$data,
			// "uploaded_files"=>$uploaded_files
		] 
		, REST_Controller::HTTP_OK
		);
	}


	public function add_budget_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$scheme_id=trim($data->scheme_id);
		$sub_heads=json_decode($data->sub_heads);
		$newSub_heads=json_decode($data->newSub_heads);
		$added_by=trim($data->added_by);
		$financial_year=$data->financial_year;
		
		if($scheme_id && $sub_heads && $added_by){
			$sub_head_data_arr=[];
			foreach ($sub_heads as $key => $sub_head) {
				$sub_head_arr = json_decode(json_encode($sub_head),true);
				
				// if($sub_head->provisional_budget<0 && $sub_head->budget<0){ 
				// 	$this->response([
				// 		'status' => false,
				// 		'message' => 'budget or provisisonal budget fields required.!',                    
				// 	] 
				// 	, REST_Controller::HTTP_OK
				// 	);die;
				// }
				$subHeadData=[];
				$subHeadData['added_by']=$added_by;
				$subHeadData['added_on']=date('Y-m-d H:i:s');				
				$subHeadData['scheme_id']=$scheme_id;				
				$subHeadData['subhead_id']=$sub_head_arr['id'];
				$subHeadData['budget']=$sub_head_arr['budget']>0?$sub_head_arr['budget']:0;
				$subHeadData['provisional_budget']=$sub_head_arr['provisional_budget']>0?$sub_head_arr['provisional_budget']:0;
				$subHeadData['budget_date']=$sub_head_arr['budget_date'];
				$subHeadData['financial_year']=$financial_year;				
				$sub_head_data_arr[]=$subHeadData;
			}
			
			$new_subhead_data_arr=[];
			
			foreach ($newSub_heads as $key => $sub_head) {
				$newsub_head_arr = json_decode(json_encode($sub_head),true);	
					
				$newsub_head_arr['scheme_id']=$scheme_id;				
				$newsub_head_arr['name']=$newsub_head_arr['name'];	
				$newsub_head_arr['code']=$newsub_head_arr['code'];	
				$newsub_head_arr['financial_year']=$financial_year;	
				$newsub_head_arr['budget']=0;	
				$newsub_head_arr['provisional_budget']=0;	
				$newsub_head_arr['budget_date']=NULL;	
				$newsub_head_arr['added_from']=2;	
				$newsub_head_arr['added_by']=$added_by;
				$newsub_head_arr['added_on']=date('Y-m-d H:i:s');			
				$subhead_id=$this->schemes_model->add_new_subhead_from_budget($newsub_head_arr);	
				$subHeadData=[];
				$subHeadData['added_by']=$added_by;
				$subHeadData['added_on']=date('Y-m-d H:i:s');				
				$subHeadData['scheme_id']=$scheme_id;				
				$subHeadData['subhead_id']=$subhead_id;
				$subHeadData['budget']=$sub_head->budget>0?$sub_head->budget:0;
				$subHeadData['provisional_budget']=$sub_head->provisional_budget>0?$sub_head->provisional_budget:0;
				$subHeadData['budget_date']=$sub_head->budget_date;
				$subHeadData['financial_year']=$financial_year;				
				$sub_head_data_arr[]=$subHeadData;
				
			}
			
			try {				
				$response=$this->schemes_model->add_budget($sub_head_data_arr);
				
				if($response){
					if($_FILES['attachment']){
						$uploaded_files=$this->uploadFiles($_FILES['attachment']);
						$uploaded_files_post_data=[];					
						foreach ($uploaded_files as $key => $uploaded_file) {
							$uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
							$uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
							$uploaded_files_post_data[$key]['scheme_id']=$scheme_id;
							$uploaded_files_post_data[$key]['budget_id']=$response;
							$uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
							$uploaded_files_post_data[$key]['added_by']=$added_by;
							$uploaded_files_post_data[$key]['type']=2;
						}
						$scheme_attachments=$this->schemes_model->add_scheme_attachments($uploaded_files_post_data);
					}


					$this->response([
						'status' => true,
						'message'=>'Budget added successfully',
						'budget'=>$response,
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => false,
						'message'=>'Something went wrong.'
					] 
					, REST_Controller::HTTP_OK
					);
				}
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					'message'=>'Something went wrong.Code Error.!'
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}

	public function update_budget_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$scheme_id=trim($data->scheme_id);
		$subhead_id=trim($data->subhead_id);
		$financial_year=trim($data->financial_year);
		$budget=trim($data->budget);
		$provisional_budget=trim($data->provisional_budget);
		$budget_date=trim($data->budget_date);
		$added_by=trim($data->added_by);
		if($scheme_id && $subhead_id && $financial_year && $budget_date && $added_by){
			$data_arr = json_decode(json_encode($data),true);
			// print_r($data_arr);die;
			try {				
				$response=$this->schemes_model->update_budget($data_arr);
				// 
				if($response){
					if($_FILES['attachment']){
						
						$uploaded_files=$this->uploadFiles($_FILES['attachment']);
						$uploaded_files_post_data=[];					
						foreach ($uploaded_files as $key => $uploaded_file) {
							$uploaded_files_post_data[$key]['file_url']=$uploaded_file['file_url'];
							$uploaded_files_post_data[$key]['file_type']=$uploaded_file['file_ext'];
							$uploaded_files_post_data[$key]['scheme_id']=$scheme_id;
							$uploaded_files_post_data[$key]['budget_id']=$data_arr['id'];
							$uploaded_files_post_data[$key]['added_on']=date('Y-m-d H:i:s');
							$uploaded_files_post_data[$key]['added_by']=$added_by;
							$uploaded_files_post_data[$key]['type']=2;
						}
						$scheme_attachments=$this->schemes_model->add_scheme_attachments($uploaded_files_post_data);
					}

					$this->response([
						'status' => true,
						'message'=>'Budget updated successfully',
						'budget'=>$response,
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => false,
						'message'=>'Something went wrong.'
					] 
					, REST_Controller::HTTP_OK
					);
				}
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					'message'=>'Something went wrong.Code Error.!'
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}
	public function delete_budget_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$budget_id=$data->budget_id;
		if($budget_id>0){
			try {
				$response=$this->schemes_model->delete_budget($budget_id);
				if($response){
					$this->response([
						'status' => true,
						'message' => 'Budget deleted successfully.',                    
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => true,
						'message' => 'Something went wrong.',                    
					] 
					, REST_Controller::HTTP_OK
					);
				}				
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					'message' => 'Something went wrong.',                    
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}

	public function get_budget_list_post($id=null){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
			$scheme_id=trim($data->scheme_id);
			$subhead_id=trim($data->subhead_id);
			try {
				$budgets=$this->schemes_model->get_budgets_by_scheme_id_and_subhead_id($scheme_id,$subhead_id,$id);
				$this->response([
					'status' => true,
					'message'=>'budget fetched successfully',
					'count'=>count($budgets),
					'budgets'=>$budgets,
				] 
				, REST_Controller::HTTP_OK
				);
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					'message'=>'Something went wrong.Code Error.!'
				] 
				, REST_Controller::HTTP_OK
				);
			}
	}


	public function budget_status_update_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$budget_id=$data->budget_id;
		$l2_status=$data->l2_status;
		$l3_status=$data->l3_status;
		if($budget_id>0 && ($l2_status || $l3_status)){
			$post_data=[
				'id'=>(int)$budget_id,
			];
			if($l2_status){
				$post_data['l2_status']=$l2_status;
				$post_data['l2remarks']=$data->l2remarks;
				
			}
			if($l3_status){
				$post_data['l3_status']=$l3_status;
				$post_data['l3remarks']=$data->l3remarks;
			}
			$msg='';
			if($l2_status==1 || $l3_status==1){
				$msg="Budget approved successfully.";
			}
			if($l2_status==2 || $l3_status==2){
				$msg="Budget rejected successfully.";
			}
			try {
				$response=$this->schemes_model->budget_status_update($post_data);
				if($response){
					$budget_list=$this->schemes_model->get_budgets_by_scheme_id_and_subhead_id($budget_id);
					$this->response([
						'status' => true,
						'list'=>$budget_list,
						'message'=>$msg
					] 
					, REST_Controller::HTTP_OK
					);
				}else{
					$this->response([
						'status' => false,
						'message'=>'Something went wrong'
					] 
					, REST_Controller::HTTP_OK
					);
				}
			} catch (\Throwable $th) {
				$this->response([
					'status' => false,
					   'message'=>'Something went wrong'
				] 
				, REST_Controller::HTTP_OK
				);
			}
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
	}

	public function generate_xml_of_scheme_payment_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$scheme_id=$data->scheme_id;
		$date=$data->date;
		if($scheme_id){
			$scheme_details=$this->schemes_model->get_schemes($scheme_id);
			$scheme_payment_list=$this->schemes_model->get_scheme_payment_list($scheme_id,$date);
			$bank_details=$scheme_details[0]->bank_details;
			// print_r($scheme_details);
			// print_r($scheme_payment_list);
			$xml_data = array(
				'Expenditure' => array(
					'FIN_YEAR' => $scheme_details[0]->start_year.$scheme_details[0]->end_year,
					'PAYEEDETAILS' => 'Y',
					'SUBSIDIARY_ACCOUNTS' => 'Y/N',
					'PAYEE_PARTY'=>[
						'PAYEE_HEADER'=>[
							'BANK_IFSC'=>$bank_details->ifsc_code,
							'SNA_ACCOUNT_NO'=>$bank_details->account_no,
							'BALANCE'=>$scheme_details[0]->balance,
							'INTEREST'=>0,
							'PAYEECOUNT'=>count($scheme_payment_list),
						],
						'PAYEE_DETAILS'=>[
							
						]
					],
					'SUBSIDIARY_PARTY'=>[
						'PAYEE_HEADER'=>[
							'BANK_IFSC'=>'',
							'SNA_ACCOUNT_NO'=>'',
							'BALANCE'=>'',
							'INTEREST'=>null,
							'PAYEECOUNT'=>0,
						],
						'PAYEE_DETAILS'=>[
							'PAYEE'=>[
								'PayeeUniqueId'=>'',
								'Payee_Name'=>'',
								'Payee_Mobile_No'=>'',
								'Email'=>'',
								'Address'=>'',
								'IFSC_CODE'=>'',
								'ACC_NO'=>'',
								'VoucherNumber'=>'',
								'Date'=>'',
								'GrossAmount'=>'',
								'DeductionAmount'=>'',
								'NetAmount'=>'',

							]
						]
					],
				)
			);
			$payee_arr=$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS'];
			$total_net_amount=0.00;

			foreach ($scheme_payment_list as $key => $scheme_payment) {
				$other_deducting_amount=0;
				$other_deducting_amount+=$scheme_payment->tds_it_amount>0?$scheme_payment->tds_it_amount:0;
				$other_deducting_amount+=$scheme_payment->s_gst_amount>0?$scheme_payment->s_gst_amount:0;
				$other_deducting_amount+=$scheme_payment->c_gst_amount>0?$scheme_payment->c_gst_amount:0;
				$other_deducting_amount+=$scheme_payment->i_gst_amount>0?$scheme_payment->i_gst_amount:0;
				$other_deducting_amount+=$scheme_payment->other_deduction>0?$scheme_payment->other_deduction:0;
				
				$payee_arr['PayeeUniqueId']=$scheme_payment->beneficiary_id;
				$payee_arr['Payee_Name']=$scheme_payment->beneficiary_name;
				$payee_arr['Payee_Mobile_No']=$scheme_payment->b_mobile;
				$payee_arr['Email']=$scheme_payment->b_email;
				$payee_arr['Address']=$scheme_payment->b_address_1;
				$payee_arr['IFSC_CODE']=$scheme_payment->b_ifsc_code;
				$payee_arr['ACC_NO']=$scheme_payment->b_account_no;
				$payee_arr['VoucherNumber']=$scheme_payment->voucher_no;
				$payee_arr['Date']=date('d/m/Y',strtotime($scheme_payment->date));
				$payee_arr['GrossAmount']=$scheme_payment->sanction_amount;
				$payee_arr['DeductionAmount']=$scheme_payment->total_deduction;
				$payee_arr['NetAmount']=$scheme_payment->payable_amount;
				$xml_data['Expenditure']['PAYEE_PARTY']['PAYEE_DETAILS'][$key]=$payee_arr;
				$total_net_amount+=$scheme_payment->payable_amount;
			}

			// print_r($xml_data);die;
	
			// Create a new SimpleXMLElement
			$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><BankExp MsgDtTm="2023-07-01T12:56:34.869" MessageId="HDFCEXP'.date('dmY').$scheme_id.'" Source="HDFC" Destination="IFMS" StateName="Uttarakhand" RecordsCount="'.count($scheme_payment_list).'" NetAmountSum="'.$total_net_amount.'" ></BankExp>');
	
			$this->array_to_xml($xml_data, $xml);
			$xml_file_path = '/var/www/api.uatesting.in/uploads/file'.time().'.xml';
			$xml->asXML($xml_file_path);
			
	
			header('Content-Type: application/xml');
			header('Content-Disposition: attachment; filename="downloaded_file.xml"');
			header('Content-Length: ' . filesize($xml_file_path));
			readfile($xml_file_path);
			// Clean up: delete the generated file
			unlink($xml_file_path);
			// Stop the script to prevent further output
			exit();
		}else{
			$this->response([
				'status' => false,
				'message' => 'invalid parameters',                    
			] 
			, REST_Controller::HTTP_OK
			);
		}
		
		
	}

	function array_to_xml2($array, &$xml) {
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				if ($key === '_attributes') {
					foreach ($value as $attr_key => $attr_value) {
						$xml->addAttribute($attr_key, $attr_value);
					}
				} else {
					$subnode = $xml->addChild($key);
					$this->array_to_xml2($value, $subnode);
				}
			} else {
				$xml->addChild($key, htmlspecialchars($value));
			}
		}
	}
    

	public function get_payment_list_of_schemes_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		
		$scheme_id=$data->scheme_id;
		if($scheme_id>0){
			try {
				$list=$this->schemes_model->get_payment_list_of_scheme($scheme_id);
				$this->response([
					'status' => TRUE,
					'message' => 'payment list fetched successfully',
					'count'=>count($list),
					'list'=>$list,
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