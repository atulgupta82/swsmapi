<?php
require APPPATH . '/libraries/REST_Controller.php';

class Beneficiary extends REST_Controller{
    function __construct()
    {
        parent::__construct();
        $this->load->model('users_model');
        $this->load->model('beneficiary_model');
		header('Access-Control-Allow-Origin: *');
		header("Access-Control-Allow-Methods: POST, GET");
    }


    public function beneficiary_get($id=null){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
			$beneficiary_list=$this->beneficiary_model->get_beneficiaries($id);

			$this->response([
	            'status' => TRUE,
	            'message' => 'Beneficiary fetched successfully',
                'count'=>count($beneficiary_list),
	            'list'=>$beneficiary_list,

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


    // public function beneficiary_post(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

    //     try {
           
	// 		// $beneficiary_list=$this->beneficiary_model->get_beneficiaries();
    //         $required_fields=['company_name','address_1','state_id','district','pin_code','contact_person','email','mobile','beneficiary_name','bank_name','branch_name','account_no','ifsc_code','pan_no','pan_holder_name','status','added_by'];
    //         // print_r($data);die;
    //         foreach ($required_fields as $key => $required_field) {  
               
    //             if($data->$required_field=="null" || $data->$required_field==null || !$data->$required_field){                    
    //                 $this->response([
    //                     'status' => false,
    //                     'message' => "$required_field field required."
    //                 ] 
    //                 , REST_Controller::HTTP_OK
    //                 );die;
    //             }               
    //         }
          

    //         // $files_fields=['contract_copy','reg_cert','pan_card','authority_letter','letter_head','cancel_cheque'];
    //         // foreach ($files_fields as $key => $required_field) {
    //         //     if(!$_FILES[$required_field]){
    //         //         $this->response([
    //         //             'status' => false,
    //         //             'message' => "Invalid parameters. $required_field field required."
    //         //         ] 
    //         //         , REST_Controller::HTTP_OK
    //         //         );
    //         //     }
    //         // }

    //         // $basic_details=[
    //         //     'company_name'=>$data->company_name,
    //         //     'address_1'=>$data->address_1,
    //         //     'address_2'=>$data->address_2,
    //         //     'state_id'=>$data->state_id,
    //         //     'district'=>$data->district,
    //         //     'pin_code'=>$data->pin_code,
    //         //     'contact_person'=>$data->contact_person,
    //         //     'email'=>$data->email,
    //         //     'mobile'=>$data->mobile,
    //         //     'landline_no'=>$data->landline_no,
    //         //     'beneficiary_name'=>$data->beneficiary_name,
    //         //     'bank_name'=>$data->bank_name,
    //         //     'branch_name'=>$data->branch_name,
    //         //     'account_no'=>$data->account_no,
    //         //     'ifsc_code'=>$data->ifsc_code,
    //         //     'pan_no'=>$data->pan_no,
    //         //     'pan_holder_name'=>$data->pan_holder_name,
    //         //     'status'=>$data->status,
    //         //     'gst_no'=>$data->gst_no,
    //         //     'reg_no'=>$data->reg_no,
    //         //     'added_by'=>$data->added_by,
    //         //     'added_on'=>date('Y-m-d H:i:s'),
    //         // ];

    //         // $partner_details=[
    //         //     'p_name'=>$data->p_name,
    //         //     'p_pan_no'=>$data->p_pan_no,
    //         //     'p_dob'=>date('Y-m-d',strtotime($data->p_dob)),
    //         //     'p_mobile'=>$data->p_mobile,
    //         //     'p_email'=>$data->p_email,
    //         //     'p_address_1'=>$data->p_address_1,
    //         //     'p_address_2'=>$data->p_address_2,
    //         //     'p_district'=>$data->p_district,
    //         //     'p_pincode'=>$data->p_pincode,
    //         //     'added_by'=>$data->added_by,
    //         //     'added_on'=>date('Y-m-d H:i:s'),
    //         // ];
    //         // $other_details=[
    //         //     'is_agrement_available'=>$data->is_agrement_available,
    //         //     'payment_terms'=>$data->payment_terms,
                
    //         //     'added_by'=>$data->added_by,
    //         //     'added_on'=>date('Y-m-d H:i:s'),
    //         // ];
    //         $basic_details=[
    //             'company_name'=>check_null_value($data->company_name),
    //             'address_1'=>check_null_value($data->address_1),
    //             'address_2'=>check_null_value($data->address_2),
    //             'state_id'=>$data->state_id,
    //             'district'=>$data->district,
    //             'pin_code'=>check_null_value($data->pin_code),
    //             'contact_person'=>check_null_value($data->contact_person),
    //             'email'=>check_null_value($data->email),
    //             'mobile'=>check_null_value($data->mobile),
    //             'landline_no'=>check_null_value($data->landline_no),
    //             'beneficiary_name'=>check_null_value($data->beneficiary_name),
    //             'bank_name'=>check_null_value($data->bank_name),
    //             'branch_name'=>check_null_value($data->branch_name),
    //             'account_no'=>check_null_value($data->account_no),
    //             'ifsc_code'=>check_null_value($data->ifsc_code),
    //             'pan_no'=>check_null_value($data->pan_no),
    //             'pan_holder_name'=>check_null_value($data->pan_holder_name),
    //             'status'=>$data->status,
    //             'gst_no'=>check_null_value($data->gst_no),
    //             'reg_no'=>check_null_value($data->reg_no),
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];

    //         $partner_details=[
    //             'p_name'=>check_null_value($data->p_name),
    //             'p_pan_no'=>check_null_value($data->p_pan_no),
    //             'p_dob'=>date('Y-m-d',strtotime($data->p_dob)),
    //             'p_mobile'=>check_null_value($data->p_mobile),
    //             'p_email'=>check_null_value($data->p_email),
    //             'p_address_1'=>check_null_value($data->p_address_1),
    //             'p_address_2'=>check_null_value($data->p_address_2),
    //             'p_district'=>$data->p_district,
    //             'p_pincode'=>$data->p_pincode,
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];
    //         $other_details=[
    //             'is_agrement_available'=>check_null_value($data->is_agrement_available),
    //             'payment_terms'=>check_null_value($data->payment_terms),
                
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];
    //         // print_r($_FILES);die;
    //         if($_FILES['contract_copy']){
    //             $contract_copy=$this->uploadFiles($_FILES['contract_copy']);
    //             $other_details['contract_copy']=$contract_copy;
    //         }
    //         if($_FILES['reg_cert']){
    //             $reg_cert=$this->uploadFiles($_FILES['reg_cert']);
    //             $other_details['reg_cert']=$reg_cert;
    //         }
    //         if($_FILES['pan_card']){
    //             $pan_card=$this->uploadFiles($_FILES['pan_card']);
    //             $other_details['pan_card']=$pan_card;
    //         }
    //         if($_FILES['authority_letter']){
    //             $authority_letter=$this->uploadFiles($_FILES['authority_letter']);
    //             $other_details['authority_letter']=$authority_letter;
    //         }
    //         if($_FILES['letter_head']){
    //             $letter_head=$this->uploadFiles($_FILES['letter_head']);
    //             $other_details['letter_head']=$letter_head;
    //         }
    //         if($_FILES['cancel_cheque']){
    //             $cancel_cheque=$this->uploadFiles($_FILES['authority_letter']);
    //             $other_details['cancel_cheque']=$cancel_cheque;
    //         }
            
	// 		$beneficiary_response=$this->beneficiary_model->add_beneficiary($basic_details,$partner_details,$other_details);
    //         if($beneficiary_response){
    //             $this->response([
    //                 'status' => TRUE,
    //                 'message' => 'Beneficiary added successfully',
    //                 'list'=>$beneficiary_response,
    //             ] 
    //             , REST_Controller::HTTP_OK
    //             );
    //         }else{
    //             $this->response([
    //                 'status' => false,
    //                 'error'=>'Something went wrong',
    //                 'message'=>$e
    //             ] 
    //             , REST_Controller::HTTP_OK
    //             );
    //         }
			
	// 	} catch (Exception $e) {
			
	// 		$this->response([
	//             'status' => false,
	//            	'error'=>'Something went wrong',
    //             'message'=>$e
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	}
    // }

    public function beneficiary_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {
           
			// $beneficiary_list=$this->beneficiary_model->get_beneficiaries();
            $required_fields=['company_name','address_1','state_id','district','pin_code','contact_person','email','mobile','beneficiary_name','bank_name','branch_name','account_no','ifsc_code','pan_no','pan_holder_name','status','added_by'];
            // print_r($data);die;
            foreach ($required_fields as $key => $required_field) {  
               
                if($data->$required_field=="null" || $data->$required_field==null || !$data->$required_field){                    
                    $this->response([
                        'status' => false,
                        'message' => "$required_field field required."
                    ] 
                    , REST_Controller::HTTP_OK
                    );die;
                }               
            }
          

            // $files_fields=['contract_copy','reg_cert','pan_card','authority_letter','letter_head','cancel_cheque'];
            // foreach ($files_fields as $key => $required_field) {
            //     if(!$_FILES[$required_field]){
            //         $this->response([
            //             'status' => false,
            //             'message' => "Invalid parameters. $required_field field required."
            //         ] 
            //         , REST_Controller::HTTP_OK
            //         );
            //     }
            // }

            $check_pan_no_already_exist = $this->beneficiary_model->check_fields_already_exist('pan_no',$data->pan_no);

            if(!$check_pan_no_already_exist){
                $check_gst_no_already_exist = $this->beneficiary_model->check_fields_already_exist('gst_no',$data->gst_no);

                if(!$check_gst_no_already_exist){
    
                    $basic_details=[
                        'company_name'=>check_null_value($data->company_name),
                        'address_1'=>check_null_value($data->address_1),
                        'address_2'=>check_null_value($data->address_2),
                        'state_id'=>$data->state_id,
                        'district'=>$data->district,
                        'pin_code'=>check_null_value($data->pin_code),
                        'contact_person'=>check_null_value($data->contact_person),
                        'email'=>check_null_value($data->email),
                        'mobile'=>check_null_value($data->mobile),
                        'landline_no'=>check_null_value($data->landline_no),
                        'beneficiary_name'=>check_null_value($data->beneficiary_name),
                        'bank_name'=>check_null_value($data->bank_name),
                        'branch_name'=>check_null_value($data->branch_name),
                        'account_no'=>check_null_value($data->account_no),
                        'ifsc_code'=>check_null_value($data->ifsc_code),
                        'pan_no'=>check_null_value($data->pan_no),
                        'pan_holder_name'=>check_null_value($data->pan_holder_name),
                        'status'=>$data->status,
                        'gst_no'=>check_null_value($data->gst_no),
                        'reg_no'=>check_null_value($data->reg_no),
                        'added_by'=>$data->added_by,
                        'added_on'=>date('Y-m-d H:i:s'),
                    ];
        
                    $partner_details=[
                        'p_name'=>check_null_value($data->p_name),
                        'p_pan_no'=>check_null_value($data->p_pan_no),
                        'p_dob'=>date('Y-m-d',strtotime($data->p_dob)),
                        'p_mobile'=>check_null_value($data->p_mobile),
                        'p_email'=>check_null_value($data->p_email),
                        'p_address_1'=>check_null_value($data->p_address_1),
                        'p_address_2'=>check_null_value($data->p_address_2),
                        'p_district'=>$data->p_district,
                        'p_pincode'=>$data->p_pincode,
                        'added_by'=>$data->added_by,
                        'added_on'=>date('Y-m-d H:i:s'),
                    ];
                    $other_details=[
                        'is_agrement_available'=>check_null_value($data->is_agrement_available),
                        'payment_terms'=>check_null_value($data->payment_terms),
                        
                        'added_by'=>$data->added_by,
                        'added_on'=>date('Y-m-d H:i:s'),
                    ];
                    // print_r($_FILES);die;
                    if($_FILES['contract_copy']){
                        $contract_copy=$this->uploadFiles($_FILES['contract_copy']);
                        $other_details['contract_copy']=$contract_copy;
                    }
                    if($_FILES['reg_cert']){
                        $reg_cert=$this->uploadFiles($_FILES['reg_cert']);
                        $other_details['reg_cert']=$reg_cert;
                    }
                    if($_FILES['pan_card']){
                        $pan_card=$this->uploadFiles($_FILES['pan_card']);
                        $other_details['pan_card']=$pan_card;
                    }
                    if($_FILES['authority_letter']){
                        $authority_letter=$this->uploadFiles($_FILES['authority_letter']);
                        $other_details['authority_letter']=$authority_letter;
                    }
                    if($_FILES['letter_head']){
                        $letter_head=$this->uploadFiles($_FILES['letter_head']);
                        $other_details['letter_head']=$letter_head;
                    }
                    if($_FILES['cancel_cheque']){
                        $cancel_cheque=$this->uploadFiles($_FILES['authority_letter']);
                        $other_details['cancel_cheque']=$cancel_cheque;
                    }
                    
                    $beneficiary_response=$this->beneficiary_model->add_beneficiary($basic_details,$partner_details,$other_details);
                    if($beneficiary_response){
                        $this->response([
                            'status' => TRUE,
                            'message' => 'Beneficiary added successfully',
                            'list'=>$beneficiary_response,
                        ] 
                        , REST_Controller::HTTP_OK
                        );
                    }else{
                        $this->response([
                            'status' => false,
                            'error'=>'Something went wrong',
                            'message'=>$e
                        ] 
                        , REST_Controller::HTTP_OK
                        );
                    }
                }else{
                    $this->response([
                        'status' => false,
                           'error'=>'GST Number `'.$data->gst_no.'` already exists.',
                        'message'=>$e
                    ] 
                    , REST_Controller::HTTP_OK
                    );
                }
            }else{
                $this->response([
                    'status' => false,
                       'error'=>'Pan Number `'.$data->pan_no.'` already exists.',
                    'message'=>$e
                ] 
                , REST_Controller::HTTP_OK
                );
            }
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong',
                'message'=>$e
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }

    // public function update_beneficiary_post(){
    //     $data	=	json_decode(file_get_contents('php://input'));		
	// 	if(!$data){
	// 		$data = json_decode(json_encode($_POST), FALSE);
	// 	}

    //     try {

           
	// 		// $beneficiary_list=$this->beneficiary_model->get_beneficiaries();
    //         $required_fields=['id','company_name','address_1','state_id','district','pin_code','contact_person','email','mobile','beneficiary_name','bank_name','branch_name','account_no','ifsc_code','pan_no','pan_holder_name','status','added_by'];
    //         // print_r($data);die;
    //         foreach ($required_fields as $key => $required_field) {  
               
    //             if($data->$required_field=="null" || $data->$required_field==null || !$data->$required_field){                    
    //                 $this->response([
    //                     'status' => false,
    //                     'message' => "$required_field field required."
    //                 ] 
    //                 , REST_Controller::HTTP_OK
    //                 );die;
    //             }               
    //         }

    //         $beneficiary_id=$data->id;

    //         $basic_details=[
    //             'company_name'=>$data->company_name,
    //             'address_1'=>$data->address_1,
    //             'address_2'=>$data->address_2,
    //             'state_id'=>$data->state_id,
    //             'district'=>$data->district,
    //             'pin_code'=>$data->pin_code,
    //             'contact_person'=>$data->contact_person,
    //             'email'=>$data->email,
    //             'mobile'=>$data->mobile,
    //             'landline_no'=>$data->landline_no,
    //             'beneficiary_name'=>$data->beneficiary_name,
    //             'bank_name'=>$data->bank_name,
    //             'branch_name'=>$data->branch_name,
    //             'account_no'=>$data->account_no,
    //             'ifsc_code'=>$data->ifsc_code,
    //             'pan_no'=>$data->pan_no,
    //             'pan_holder_name'=>$data->pan_holder_name,
    //             'status'=>$data->status,
    //             'gst_no'=>$data->gst_no,
    //             'reg_no'=>$data->reg_no,
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];

    //         $partner_details=[
    //             'p_name'=>$data->p_name,
    //             'p_pan_no'=>$data->p_pan_no,
    //             'p_dob'=>date('Y-m-d',strtotime($data->p_dob)),
    //             'p_mobile'=>$data->p_mobile,
    //             'p_email'=>$data->p_email,
    //             'p_address_1'=>$data->p_address_1,
    //             'p_address_2'=>$data->p_address_2,
    //             'p_district'=>$data->p_district,
    //             'p_pincode'=>$data->p_pincode,
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];
    //         $other_details=[
    //             'is_agrement_available'=>$data->is_agrement_available,
    //             'payment_terms'=>$data->payment_terms,
                
    //             'added_by'=>$data->added_by,
    //             'added_on'=>date('Y-m-d H:i:s'),
    //         ];
    //         // print_r($_FILES);die;
    //         if($_FILES['contract_copy']){
    //             $contract_copy=$this->uploadFiles($_FILES['contract_copy']);
    //             $other_details['contract_copy']=$contract_copy;
    //         }
    //         if($_FILES['reg_cert']){
    //             $reg_cert=$this->uploadFiles($_FILES['reg_cert']);
    //             $other_details['reg_cert']=$reg_cert;
    //         }
    //         if($_FILES['pan_card']){
    //             $pan_card=$this->uploadFiles($_FILES['pan_card']);
    //             $other_details['pan_card']=$pan_card;
    //         }
    //         if($_FILES['authority_letter']){
    //             $authority_letter=$this->uploadFiles($_FILES['authority_letter']);
    //             $other_details['authority_letter']=$authority_letter;
    //         }
    //         if($_FILES['letter_head']){
    //             $letter_head=$this->uploadFiles($_FILES['letter_head']);
    //             $other_details['letter_head']=$letter_head;
    //         }
    //         if($_FILES['cancel_cheque']){
    //             $cancel_cheque=$this->uploadFiles($_FILES['authority_letter']);
    //             $other_details['cancel_cheque']=$cancel_cheque;
    //         }
            
	// 		$beneficiary_response=$this->beneficiary_model->update_beneficiary($basic_details,$partner_details,$other_details,$beneficiary_id);
    //         if($beneficiary_response){
    //             $this->response([
    //                 'status' => TRUE,
    //                 'message' => 'Beneficiary updated successfully',
    //                 'list'=>$beneficiary_response,
    //             ] 
    //             , REST_Controller::HTTP_OK
    //             );
    //         }else{
    //             $this->response([
    //                 'status' => false,
    //                 'message'=>'Something went wrong',
                    
    //             ] 
    //             , REST_Controller::HTTP_OK
    //             );
    //         }
			
	// 	} catch (Exception $e) {
			
	// 		$this->response([
	//             'status' => false,
	//            	'error'=>'Something went wrong',
    //             'message'=>$e
	//         ] 
	// 		, REST_Controller::HTTP_OK
	// 		);
	// 	}
    // }

    public function update_beneficiary_post(){
        $data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

        try {

           
			// $beneficiary_list=$this->beneficiary_model->get_beneficiaries();
            $required_fields=['id','company_name','address_1','state_id','district','pin_code','contact_person','email','mobile','beneficiary_name','bank_name','branch_name','account_no','ifsc_code','pan_no','pan_holder_name','status','added_by'];
            // print_r($data);die;
            foreach ($required_fields as $key => $required_field) {  
               
                if($data->$required_field=="null" || $data->$required_field==null || !$data->$required_field){                    
                    $this->response([
                        'status' => false,
                        'message' => "$required_field field required."
                    ] 
                    , REST_Controller::HTTP_OK
                    );die;
                }               
            }

            $check_pan_no_already_exist = $this->beneficiary_model->check_fields_already_exist('pan_no',$data->pan_no,array("id!="=>$beneficiary_id));

            if(!$check_pan_no_already_exist){
                $check_gst_no_already_exist = $this->beneficiary_model->check_fields_already_exist('gst_no',$data->gst_no,array("id!="=>$beneficiary_id));

                if(!$check_gst_no_already_exist){

            $beneficiary_id=$data->id;

            $basic_details=[
                'company_name'=>check_null_value($data->company_name),
                'address_1'=>check_null_value($data->address_1),
                'address_2'=>check_null_value($data->address_2),
                'state_id'=>$data->state_id,
                'district'=>$data->district,
                'pin_code'=>check_null_value($data->pin_code),
                'contact_person'=>check_null_value($data->contact_person),
                'email'=>check_null_value($data->email),
                'mobile'=>check_null_value($data->mobile),
                'landline_no'=>check_null_value($data->landline_no),
                'beneficiary_name'=>check_null_value($data->beneficiary_name),
                'bank_name'=>check_null_value($data->bank_name),
                'branch_name'=>check_null_value($data->branch_name),
                'account_no'=>check_null_value($data->account_no),
                'ifsc_code'=>check_null_value($data->ifsc_code),
                'pan_no'=>check_null_value($data->pan_no),
                'pan_holder_name'=>check_null_value($data->pan_holder_name),
                'status'=>$data->status,
                'gst_no'=>check_null_value($data->gst_no),
                'reg_no'=>check_null_value($data->reg_no),
                'added_by'=>$data->added_by,
                'added_on'=>date('Y-m-d H:i:s'),
            ];

            $partner_details=[
                'p_name'=>check_null_value($data->p_name),
                'p_pan_no'=>check_null_value($data->p_pan_no),
                'p_dob'=>date('Y-m-d',strtotime($data->p_dob)),
                'p_mobile'=>check_null_value($data->p_mobile),
                'p_email'=>check_null_value($data->p_email),
                'p_address_1'=>check_null_value($data->p_address_1),
                'p_address_2'=>check_null_value($data->p_address_2),
                'p_district'=>$data->p_district,
                'p_pincode'=>$data->p_pincode,
                'added_by'=>$data->added_by,
                'added_on'=>date('Y-m-d H:i:s'),
            ];
            $other_details=[
                'is_agrement_available'=>check_null_value($data->is_agrement_available),
                'payment_terms'=>check_null_value($data->payment_terms),
                
                'added_by'=>$data->added_by,
                'added_on'=>date('Y-m-d H:i:s'),
            ];
            // print_r($_FILES);die;
            if($_FILES['contract_copy']){
                $contract_copy=$this->uploadFiles($_FILES['contract_copy']);
                $other_details['contract_copy']=$contract_copy;
            }
            if($_FILES['reg_cert']){
                $reg_cert=$this->uploadFiles($_FILES['reg_cert']);
                $other_details['reg_cert']=$reg_cert;
            }
            if($_FILES['pan_card']){
                $pan_card=$this->uploadFiles($_FILES['pan_card']);
                $other_details['pan_card']=$pan_card;
            }
            if($_FILES['authority_letter']){
                $authority_letter=$this->uploadFiles($_FILES['authority_letter']);
                $other_details['authority_letter']=$authority_letter;
            }
            if($_FILES['letter_head']){
                $letter_head=$this->uploadFiles($_FILES['letter_head']);
                $other_details['letter_head']=$letter_head;
            }
            if($_FILES['cancel_cheque']){
                $cancel_cheque=$this->uploadFiles($_FILES['authority_letter']);
                $other_details['cancel_cheque']=$cancel_cheque;
            }
            
			$beneficiary_response=$this->beneficiary_model->update_beneficiary($basic_details,$partner_details,$other_details,$beneficiary_id);
            if($beneficiary_response){
                $this->response([
                    'status' => TRUE,
                    'message' => 'Beneficiary updated successfully',
                    'list'=>$beneficiary_response,
                ] 
                , REST_Controller::HTTP_OK
                );
            }else{
                $this->response([
                    'status' => false,
                    'message'=>'Something went wrong',
                    
                ] 
                , REST_Controller::HTTP_OK
                );
            }
			
        }else{
            $this->response([
                'status' => false,
                   'error'=>'GST Number `'.$data->gst_no.'` already exists.',
                'message'=>$e
            ] 
            , REST_Controller::HTTP_OK
            );
        }
    }else{
        $this->response([
            'status' => false,
               'error'=>'Pan Number `'.$data->pan_no.'` already exists.',
            'message'=>$e
        ] 
        , REST_Controller::HTTP_OK
        );
    }
		} catch (Exception $e) {
			
			$this->response([
	            'status' => false,
	           	'error'=>'Something went wrong',
                'message'=>$e
	        ] 
			, REST_Controller::HTTP_OK
			);
		}
    }


    public function delete_beneficiary_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}
		$beneficiary_id=$data->beneficiary_id;
		if($beneficiary_id>0){
			try {
				$response=$this->beneficiary_model->delete_beneficiary($beneficiary_id);
				if($response){
					$this->response([
						'status' => true,
						'message' => 'Beneficiary deleted successfully.',                    
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

    public function beneficiary_status_update_post(){
		$data	=	json_decode(file_get_contents('php://input'));		
		if(!$data){
			$data = json_decode(json_encode($_POST), FALSE);
		}

		$beneficiary_id=$data->beneficiary_id;
		$l2_status=$data->l2_status;
		$l3_status=$data->l3_status;
		if($beneficiary_id>0 && ($l2_status || $l3_status)){
			$post_data=[
				'id'=>(int)$beneficiary_id,
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
				$msg="Beneficiary approved successfully.";
			}
			if($l2_status==2 || $l3_status==2){
				$msg="Beneficiary rejected successfully.";
			}
			try {
				$response=$this->beneficiary_model->beneficiary_status_update($post_data);
				if($response){
					$beneficiary_list=$this->beneficiary_model->get_beneficiaries($beneficiary_id);
					$this->response([
						'status' => true,
						'list'=>$beneficiary_list,
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
            
            $file_url = base_url()."uploads/beneficiary/".$data['file_name'];
            return $file_url;
        } else {
            $error = $this->upload->display_errors();
            return false;
        }
       
    }

    private function getUploadConfig()
    {
        $config['upload_path'] = './uploads/beneficiary/';
        $config['allowed_types'] = 'jpg|jpeg|png|pdf';
        $config['max_size'] = 10000;
        return $config;
    }
}
?>