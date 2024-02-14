<?php

class Sanctions_order_model extends CI_Model
{
    function __construct(){
        parent::__construct();
        $this->load->database();
        date_default_timezone_set('Asia/Kolkata');
    }    

    public function get_sanction_orders($id=null){
        $this->db->select('*');
        $this->db->from('sanction_orders'); 
        if($id){
            $this->db->where('id',$id);
        }
        $this->db->order_by('id','desc');
        $result=$this->db->get()->result(); 
        return $result;
    }
    
    public function add_sanction_order($sanction_orders_data,$voucher_data){
        
        if($sanction_orders_data && $voucher_data){
            $sanction_order_no=$sanction_orders_data['sanction_order_no'];
            $this->db->insert('sanction_orders',$sanction_orders_data);
            $sanction_order_id=$this->db->insert_id();
            if($sanction_order_id){
                foreach ($voucher_data as $key => $voucher) {
                    $invoices=$voucher['invoices'];
                    unset($voucher['invoices']);
                    $voucher['sanction_order_id']=$sanction_order_id;                    
                    $voucher['financial_year']=$sanction_orders_data['financial_year'];
                    $this->db->insert('vouchers',$voucher);
                    $voucher_no=$voucher['voucher_no'];
                    $voucher_id=$this->db->insert_id();
                    if($voucher_id){
                        foreach ($invoices as $key => $invoice) {
                            $schemes=$invoice['schemes'];
                            unset($invoice['schemes']);
                            $invoice['sanction_order_id']=$sanction_order_id;
                            $invoice['voucher_id']=$voucher_id;
                            // $scheme_financial_year=$invoice['financial_year'];
                            // $invoice['financial_year']=$scheme_financial_year;
                            // print_r($invoice);die;
                            $this->db->insert('invoices',$invoice);
                            $invoice_id=$this->db->insert_id();
                            if($invoice_id){
                                foreach ($schemes as $key => $scheme) {
                                    $subheads=$scheme['subheads'];
                                    unset($scheme['subheads']);
                                    $scheme['invoice_id']=$invoice_id;
                                    $scheme['added_on']=date('Y-m-d H:i:s');
                                    $scheme['added_by']=$invoice['added_by'];
                                    $scheme['voucher_no']=$voucher_no;
                                    // $scheme['financial_year']=$scheme_financial_year;
                                    $scheme['sanction_order_no']=$sanction_order_no;
                                    $scheme_financial_year=$scheme['financial_year'];
                                    
                                    $this->db->insert('invoice_schemes',$scheme);
                                    $invoice_scheme_id=$this->db->insert_id();
                                    if($invoice_scheme_id){
                                        foreach ($subheads as $key => $subhead) {     
                                            if($subhead['sub_heads_id'] && $subhead['sub_head_amount']){
                                                $subhead_data=[];                                    
                                                $subhead_data['added_on']=date('Y-m-d H:i:s');
                                                $subhead_data['added_by']=$invoice['added_by'];
                                                $subhead_data['invoice_id']=$invoice_id;
                                                $subhead_data['scheme_id']=$scheme['scheme_id'];
                                                $subhead_data['subhead_id']=$subhead['sub_heads_id'];
                                                $subhead_data['subhead_amount']=$subhead['sub_head_amount'];
                                                $subhead_data['financial_year']=$scheme_financial_year;
                                                $this->db->insert('invoice_scheme_subheads',$subhead_data);
                                                // echo $this->db->last_query();die;
                                                $sub_id=$this->db->insert_id();
                                            }
                                        }
                                    }else{
                                        return false;
                                    }
                                }
                            }else{
                                return false;
                            }
                        }
                    }else{
                        return false;
                    }
                }
                return $sanction_order_id;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    public function update_invoice_data($invoice,$invoice_id){   
        // print_r($invoice);die;
         $sanction_order_no=$invoice['sanction_order_no'];   
         $voucher_no=$invoice['voucher_no'];   
         unset($invoice['sanction_order_no']);
         unset($invoice['voucher_no']);
        if($invoice){
            $schemes=$invoice['schemes'];
            unset($invoice['schemes']);      
            if($invoice_id){
                $this->db->where('id',$invoice_id);
                // $this->db->delete('invoices');
                $this->db->update('invoices',$invoice);
                $this->db->where('invoice_id',$invoice_id);
                $this->db->delete('invoice_schemes');
                $this->db->where('invoice_id',$invoice_id);
                $this->db->delete('invoice_scheme_subheads');
            }      
            
            // $invoice_id=$this->db->insert_id();
            if($invoice_id){
                foreach ($schemes as $key => $scheme) {
                    $subheads=$scheme['subheads'];
                    unset($scheme['subheads']);
                    $scheme['invoice_id']=$invoice_id;
                    $scheme['added_on']=date('Y-m-d H:i:s');
                    $scheme['added_by']=$invoice['added_by'];
                    $scheme['voucher_no']=$voucher_no;
                    // $scheme['financial_year']=$scheme_financial_year;
                    $scheme['sanction_order_no']=$sanction_order_no;
                    $scheme_financial_year=$scheme['financial_year'];
                    
                    $this->db->insert('invoice_schemes',$scheme);
                    $invoice_scheme_id=$this->db->insert_id();
                    if($invoice_scheme_id){
                        foreach ($subheads as $key => $subhead) {     
                            if($subhead['sub_heads_id'] && $subhead['sub_head_amount']){
                                $subhead_data=[];                                    
                                $subhead_data['added_on']=date('Y-m-d H:i:s');
                                $subhead_data['added_by']=$invoice['added_by'];
                                $subhead_data['invoice_id']=$invoice_id;
                                $subhead_data['scheme_id']=$scheme['scheme_id'];
                                $subhead_data['subhead_id']=$subhead['sub_heads_id'];
                                $subhead_data['subhead_amount']=$subhead['sub_head_amount'];
                                $subhead_data['financial_year']=$scheme_financial_year;
                                $this->db->insert('invoice_scheme_subheads',$subhead_data);
                                // echo $this->db->last_query();die;
                                $sub_id=$this->db->insert_id();
                            }
                        }
                    }else{
                        return false;
                    }
                }
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    public function add_vouchers($data){
        if($data){
            $this->db->insert('vouchers',$data);
            $id=$this->db->insert_id();
            if($id){
                return $id;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function add_invoices($data){
        if($data){
            $this->db->insert('invoices',$data);
            $id=$this->db->insert_id();
            if($id){
                return $id;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    public function check_sanction_order_exist($order_no){
        if($order_no){
            $this->db->select('id');
            $this->db->from('sanction_orders'); 
            $this->db->where('sanction_order_no',$order_no);
            // $this->db->where('financial_year',$f_year);
            $this->db->order_by('id','desc');
            $row=$this->db->get()->row(); 
            return $row;
        }else{
            return false;
        }
    }

    public function check_voucher_exist($voucher_no,$financial_year){
        if($voucher_no && $financial_year){
            $this->db->select('id');
            $this->db->from('vouchers'); 
            $this->db->where('voucher_no',$voucher_no);
            $this->db->where('financial_year',$financial_year);
            $this->db->order_by('id','desc');
            $row=$this->db->get()->row(); 
            return $row;
        }else{
            return false;
        }
    }

    public function check_invoice_exist($vendor_id,$invoice_date,$invoice_no,$payment_type,$invoice_id=null){
        
        if($vendor_id && $invoice_date && $invoice_no && $payment_type){
            $this->db->select('invoices.*');
            $this->db->from('invoices'); 
            // $this->db->join('invoice_schemes','invoice_schemes.invoice_id = invoices.id');
            $this->db->where('invoices.vendor_id',$vendor_id);
            // $this->db->where('invoice_schemes.financial_year',$financial_year);
            $this->db->where('invoices.invoice_no',$invoice_no);
            $this->db->where('invoices.invoice_date',$invoice_date);
            $this->db->where('invoices.payment_type',$payment_type);
            if($invoice_id){
                $this->db->where('invoices.id!=',$invoice_id);
            }
            $this->db->order_by('invoices.id','desc');
            $row=$this->db->get()->row(); 
            
            return $row;
        }else{
            return false;
        }
    }

    public function get_disbursment_invoices_list($id=null,$from = '', $to = ''){
        $this->db->select('invoices.*,beneficiary.company_name,sanction_orders.sanction_order_no,DATE_FORMAT(sanction_orders.sanction_order_date,"%Y-%m-%d") as sanction_order_date,vouchers.voucher_no,DATE_FORMAT(vouchers.voucher_date,"%Y-%m-%d") as voucher_date,vouchers.voucher as voucher,vouchers.supporting_docs as supporting_docs,sanction_orders.sanction_order as sanction_order,beneficiary.beneficiary_name as beneficiary_name,beneficiary.bank_name as b_bank_name,beneficiary.branch_name as b_branch_name,beneficiary.account_no as b_account_no,beneficiary.ifsc_code as b_ifsc_code,invoice_payment.added_on as transaction_date');
        $this->db->from('invoices'); 
        $this->db->join('sanction_orders','sanction_orders.id=invoices.sanction_order_id','right');        
        $this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');        
        $this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','right');
        $this->db->join('invoice_payment','invoice_payment.invoice_id=invoices.id','left');     
        $this->db->where('invoices.payment_status',1);
        $this->db->where('invoices.l3_payment_status',1);
        if($id){
            $this->db->where('invoices.id',$id);
        }        

        if($from != ""){
            $this->db->where('invoices.invoice_date >=',$from);
        }

        if($to != ""){
            $this->db->where('invoices.invoice_date <=',$to);
        }

        $this->db->order_by('invoices.id','desc');
        $result=$this->db->get()->result(); 
        
        foreach ($result as $key => $res) {
            if($res->id){
                $scheme_details=$this->get_scheme_details_by_invoice_id($res->id);
                $res->scheme_list=$scheme_details;
                $res->beneficiary_other_details=$this->get_beneficiaries_other_details_by_id($res->vendor_id);
            }
        }        
        return $result;
    }

    function get_invoices_details($invoice_id=null){
        if($invoice_id){
            $this->db->select('invoices.*,sanction_orders.sanction_order_no,DATE_FORMAT(sanction_orders.sanction_order_date,"%Y-%m-%d") as sanction_order_date,sanction_orders.sanction_order_value,vouchers.total_voucher_value,vouchers.voucher_no,DATE_FORMAT(vouchers.voucher_date,"%Y-%m-%d") as voucher_date,vouchers.voucher as voucher,vouchers.supporting_docs as supporting_docs,sanction_orders.sanction_order as sanction_order,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year_name');
            $this->db->from('invoices');
            $this->db->join('sanction_orders','sanction_orders.id=invoices.sanction_order_id','right');        
            $this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');      
            $this->db->join('financial_year','financial_year.id=invoices.financial_year','left');
            $this->db->where('invoices.id',$invoice_id);      
            $this->db->order_by('invoices.id','desc');
            $row=$this->db->get()->row(); 
            // print_r($row);die;
            // echo $this->db->last_query();die;
            $scheme_details=$this->get_scheme_details_by_invoice_id($row->id);
            $row->scheme_list=$scheme_details;
            $row->beneficiary=$this->get_beneficiary_details($row->vendor_id);
            return $row;
        }else{
            return false;
        }
    }

    // public function get_invoices_list($id=null,$type=null){
    //     $this->db->select('invoices.*,beneficiary.company_name,sanction_orders.sanction_order_no,DATE_FORMAT(sanction_orders.sanction_order_date,"%Y-%m-%d") as sanction_order_date,vouchers.voucher_no,DATE_FORMAT(vouchers.voucher_date,"%Y-%m-%d") as voucher_date,vouchers.voucher as voucher,vouchers.supporting_docs as supporting_docs,sanction_orders.sanction_order as sanction_order');
    //     $this->db->from('invoices');
    //     $this->db->join('sanction_orders','sanction_orders.id=invoices.sanction_order_id','right');        
    //     $this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');        
    //     $this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','right');
    //     $this->db->where('invoices.id >',0);
    //     if($type){

    //     }else{
    //         $this->db->group_start();
    //         $this->db->where('invoices.approval_status !=',1);
    //         $this->db->or_where('invoices.l3_approval_status !=',1);
    //         $this->db->group_end();
    //     }
    //     if($id){
    //         $this->db->where('invoices.id',$id);
    //     }        
    //     $this->db->order_by('invoices.id','desc');
    //     $result=$this->db->get()->result(); 
    //     // print_r($result);die;
    //     // echo $this->db->last_query();die;
    //     foreach ($result as $key => $res) {
            
    //         if($res->id){
    //             $scheme_details=$this->get_scheme_details_by_invoice_id($res->id);
    //             $res->scheme_list=$scheme_details;
    //             $res->beneficiary_other_details=$this->get_beneficiaries_other_details_by_id($res->vendor_id);
    //         }
    //     }
        
    //     return $result;
    // }


    public function get_approve_invoice_by_scheme_id($scheme_id=null){
		
		if($scheme_id){
			$this->db->select('invoice_schemes.id as id, invoice_schemes.scheme_id as scheme_id, invoice_schemes.amount as amount');
			$this->db->from('invoice_schemes');
			$this->db->join('invoices', 'invoices.id = invoice_schemes.invoice_id', 'RIGHT'); // Use left join here
			$this->db->where('invoices.approval_status', 1);
			$this->db->where('invoices.l3_approval_status', 1);
			$this->db->where('invoice_schemes.scheme_id', $scheme_id);
			$res = $this->db->get()->result();
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}
			return $amount;
		}
	}

// , (SELECT SUM(ssh.budget) FROM scheme_sub_head as ssh JOIN invoice_scheme_subheads as iss ON iss. subhead_id = ssh.id WHERE iss.invoice_id = invoices.id GROUP BY iss.invoice_id) as utilized_budget
    public function get_invoices_list($id=null,$type=null,$user_type = ''){
        $this->db->select('invoices.*,beneficiary.company_name,sanction_orders.sanction_order_no,sanction_orders.sanction_order_value,DATE_FORMAT(sanction_orders.sanction_order_date,"%Y-%m-%d") as sanction_order_date,vouchers.voucher_no,DATE_FORMAT(vouchers.voucher_date,"%Y-%m-%d") as voucher_date,vouchers.voucher as voucher,vouchers.total_voucher_value as voucher_amount,vouchers.supporting_docs as supporting_docs,sanction_orders.sanction_order as sanction_order, (SELECT SUM(ssh.budget) FROM scheme_sub_head as ssh JOIN invoice_scheme_subheads as iss ON iss. subhead_id = ssh.id WHERE iss.invoice_id = invoices.id GROUP BY iss.invoice_id) as utilized_budget');
        $this->db->from('invoices');
        $this->db->join('sanction_orders','sanction_orders.id=invoices.sanction_order_id','right');        
        $this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');        
        $this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','right');
        $this->db->where('invoices.id >',0);
        if($type){

        }else{
            $this->db->group_start();
            $this->db->where('invoices.approval_status !=',1);
            $this->db->or_where('invoices.l3_approval_status !=',1);
            $this->db->group_end();
        }
        if($id){
            $this->db->where('invoices.id',$id);
        }     
        
        
        if($user_type == 'L2'){
            $this->db->where("approval_status=0 and l3_approval_status!=2");
        }
        if($user_type == 'L3'){
            $this->db->where("l3_approval_status=0 and approval_status!=2");
        }
        $this->db->order_by('invoices.id','desc');
        $result=$this->db->get()->result(); 
        // print_r($result);die;
        // echo $this->db->last_query();die;
        foreach ($result as $key => $res) {
            
            if($res->id){
                $scheme_details = $this->get_scheme_details_by_invoice_id($res->id);
                foreach($scheme_details as $key => $r) {
                    $r->utilized_budget = $this->get_approve_invoice_by_scheme_id($r->id);
                    $r->subheads = $this->get_sub_head_list_by_scheme_id($r->id, $res->id);
                }
                $res->scheme_list=$scheme_details;
                $res->beneficiary_other_details=$this->get_beneficiaries_other_details_by_id($res->vendor_id);
                // $res->utilized_budget = $this->get_approve_invoice_by_scheme_id($res->)
            }
        }
        
        return $result;
    }

    public function get_extra_budget($subhead_id,$financial_year=null){
		if($subhead_id){
			$this->db->select('sum(budget) as budget');
			$this->db->from('subhead_budgets');
			$this->db->where('subhead_id',$subhead_id);
			if($financial_year){
				$this->db->where('financial_year',$financial_year);
			}
			$this->db->where('l2_status',1);
			$this->db->where('l3_status',1);
			$result=$this->db->get()->row();
			if($result->budget){
				return $result->budget;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}

    public function get_approve_invoice_by_scheme_id_and_subhead_id($scheme_id=null,$subhead_id,$paid=false){
		
		if($scheme_id && $subhead_id){
			$this->db->select('invoice_scheme_subheads.id as id, invoice_scheme_subheads.scheme_id as scheme_id, invoice_scheme_subheads.subhead_amount as amount');
			$this->db->from('invoice_scheme_subheads');
			$this->db->join('invoices', 'invoices.id = invoice_scheme_subheads.invoice_id', 'RIGHT'); // Use left join here
			$this->db->where('invoices.approval_status', 1);
			$this->db->where('invoices.l3_approval_status', 1);
			if($paid){
				$this->db->where('invoices.payment_status', 1);
				$this->db->where('invoices.l3_payment_status', 1);
			}
			$this->db->where('invoice_scheme_subheads.scheme_id', $scheme_id);
			$this->db->where('invoice_scheme_subheads.subhead_id', $subhead_id);
			$res = $this->db->get()->result();
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}
			
			return $amount;
		}else{
			return 0;
		}
	}

	public function get_pending_invoice_by_scheme_id_and_subhead_id($scheme_id=null,$subhead_id){		
		if($scheme_id && $subhead_id){
			$this->db->select('invoice_scheme_subheads.id as id, invoice_scheme_subheads.scheme_id as scheme_id, invoice_scheme_subheads.subhead_amount as amount');
			$this->db->from('invoice_scheme_subheads');
			$this->db->join('invoices', 'invoices.id = invoice_scheme_subheads.invoice_id', 'RIGHT');
			$this->db->group_start();
			$this->db->where("(invoices.approval_status=0 AND invoices.l3_approval_status=0) OR (invoices.approval_status=1 AND invoices.l3_approval_status=0) OR (invoices.approval_status=0 AND invoices.l3_approval_status=1)");
			// $this->db->where('invoices.approval_status',0);
			// $this->db->or_where('invoices.l3_approval_status',0);
			$this->db->group_end();		
			$this->db->where('invoice_scheme_subheads.scheme_id', $scheme_id);
			$this->db->where('invoice_scheme_subheads.subhead_id', $subhead_id);
			$res = $this->db->get()->result();
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}			
			return $amount;
		}else{
			return 0;
		}
	}

    public function get_sub_head_list_by_scheme_id($scheme_id, $invoiceid=null){
		if($scheme_id){
            if($invoiceid) {
                $this->db->select('scheme_sub_head.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year, invoice_scheme_subheads.subhead_amount');
            }else{
                $this->db->select('scheme_sub_head.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year');
            }
			
			$this->db->from('scheme_sub_head');
			$this->db->join('financial_year','financial_year.id=scheme_sub_head.financial_year','left');
            if($invoiceid) {
                $this->db->join('invoice_scheme_subheads','invoice_scheme_subheads.subhead_id=scheme_sub_head.id','inner');
                $this->db->where('invoice_scheme_subheads.invoice_id',$invoiceid);
            }
			$this->db->where('scheme_sub_head.scheme_id',$scheme_id);
			$sub_heads_list	= $this->db->get()->result();
			$total_subhead_budget=0;
			$total_subhead_provisional_budget=0;
			foreach ($sub_heads_list as $key => $subhead) {				
				$subhead_extra_budget=$this->get_extra_budget($subhead->id);
				// $subhead_extra_provisional_budget=$this->get_extra_provisional_budget($subhead->id);
				$approve_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id,false);
				$pending_invoices_amount=$this->get_pending_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id);
                $subhead->subhead_extra_budget = $subhead_extra_budget;
                
				$total_subhead_budget+=$subhead->budget+$subhead_extra_budget;
                $subhead->total_subhead_budget = $subhead->budget+$subhead_extra_budget;

				// $subhead->provisional_budget=$subhead->provisional_budget+$subhead_extra_provisional_budget;
				// $total_subhead_budget+=$subhead->budget;
				// $total_subhead_provisional_budget+=$subhead->provisional_budget;
				// $paid_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id,true);
                $subhead->budget=$subhead->budget+$subhead_extra_budget;
				$subhead->balance=$subhead->budget-$approve_invoices_amount-$pending_invoices_amount;
				// $subhead->payable_expanses=$approve_invoices_amount-$paid_invoices_amount;
				// $subhead->utilised_budget=$approve_invoices_amount;
				// $subhead->pending_budget=$pending_invoices_amount;
				// $subhead->total_payment=$paid_invoices_amount;
			}
			// $sub_heads_list['total_subhead_budget']=$total_subhead_budget;
			// $sub_heads_list['total_subhead_provisional_budget']=$total_subhead_provisional_budget;			
			return $sub_heads_list;
		}else{
			return [];
		}
	}

    public function get_scheme_details_by_invoice_id($invoice_id){
        // echo $invoice_id;die;
        $this->db->select('schemes.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year,scheme_type.title as type,invoice_schemes.amount as invoice_scheme_amount');
		$this->db->from('invoice_schemes');
        $this->db->join('schemes','schemes.id=invoice_schemes.scheme_id','right');
		$this->db->join('financial_year','financial_year.id=schemes.financial_year','left');
		$this->db->join('scheme_type','scheme_type.id=schemes.type','left');
		$this->db->where('invoice_schemes.invoice_id',$invoice_id);
		$this->db->order_by('invoice_schemes.id','desc');
		$schemes_list	=	$this->db->get()->result();
        // echo $this->db->last_query();

        // print_r($schemes_list);die;
        if($schemes_list){
			foreach ($schemes_list as $key => $scheme) {
				$bank_details=$this->get_bank_details_by_scheme_id($scheme->id);
				$attachments=$this->get_attachments_by_scheme_id($scheme->id);
                $scheme_subheads = $this->get_sub_head_list_by_scheme_id($scheme->id);
                $total_subhead_budget=$scheme_subheads['total_subhead_budget'];
                $scheme->total_budget = $total_subhead_budget;
				$scheme->bank_details=$bank_details;
                $scheme->attachments=$attachments;                
				
			}
			return $schemes_list;
		}else{
			return [];
		}
    }

    public function get_attachments_by_scheme_id($scheme_id){
		if($scheme_id){
			$this->db->select('*');
			$this->db->from('scheme_attachments');
			$this->db->where('scheme_id',$scheme_id);
			$attachements_list	=	$this->db->get()->result();
			return $attachements_list;
		}else{
			return false;
		}
	}

    public function get_bank_details_by_scheme_id($scheme_id){
		if($scheme_id){
			$this->db->select('*');
			$this->db->from('scheme_bank_account');
			$this->db->where('scheme_id',$scheme_id);
			$sub_heads_list	=	$this->db->get()->row();
			return $sub_heads_list;
		}else{
			return false;
		}
	}

    public function get_schemes($id=null){
        $this->db->select('schemes.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year,scheme_type.title as type');
		$this->db->from('schemes');
		$this->db->join('financial_year','financial_year.id=schemes.financial_year','left');
		$this->db->join('scheme_type','scheme_type.id=schemes.type','left');
		if($id){
			$this->db->where('schemes.id',$id);
		}
		$this->db->order_by('id','desc');
		$schemes_list	=	$this->db->get()->result();
		if($schemes_list){
			foreach ($schemes_list as $key => $scheme) {
				$sub_heads_list=$this->get_sub_head_list_by_scheme_id($scheme->id);
				$bank_details=$this->get_bank_details_by_scheme_id($scheme->id);
				$attachments=$this->get_attachments_by_scheme_id($scheme->id);
				$scheme->sub_head_list=$sub_heads_list;
				$scheme->bank_details=$bank_details;
				$scheme->attachments=$attachments;
			}
			return $schemes_list;
		}else{
			return [];
		}
    }

    public function get_beneficiaries_partner_details_by_id($beneficiary_id){
        if($beneficiary_id){
            $this->db->select('*');
            $this->db->from('beneficiary_partner_details');
            $this->db->where('beneficiary_id',$beneficiary_id);
            $row=$this->db->get()->row();
            return $row;
        }else{
            return false;
        }
    }

    public function get_beneficiaries_other_details_by_id($beneficiary_id){
        if($beneficiary_id){
            $this->db->select('*, (select company_name from beneficiary where id=beneficiary_other_details.beneficiary_id) as beneficiary_company_name');
            $this->db->from('beneficiary_other_details');
            $this->db->where('beneficiary_id',$beneficiary_id);
            $row=$this->db->get()->row();
            return $row;
        }else{
            return false;
        }
    }

    function get_beneficiary_details($beneficiary_id){
        if($beneficiary_id){
            $this->db->select('beneficiary.*,states.name as state_name,cities.city as district_name');
            $this->db->from('beneficiary'); 
            $this->db->join('states','states.id=beneficiary.state_id','left');
            $this->db->join('cities','cities.id=beneficiary.district','left');
            $this->db->where('beneficiary.id',$beneficiary_id);
            $row=$this->db->get()->row();
            $row->other_details=$this->get_beneficiaries_other_details_by_id($row->id);
            $row->partner_details=$this->get_beneficiaries_partner_details_by_id($row->id);
            return $row;
        }else{
            return false;
        }

    }


    public function update_invoice_approval_status($data){
        if($data['id']){
            $this->db->where('id',$data['id']);
            $this->db->update('invoices',$data);
            // print_r($data);die;
            // echo $this->db->last_query();die;
            $details=$this->get_invoices_list($data['id'],true);
            
            return $details;
        }else{
            return false;
        }
    }



    public function get_payment_invoices_list($id=null, $user_type = ''){
        $this->db->select('invoices.*,beneficiary.company_name,sanction_orders.sanction_order_no,sanction_orders.sanction_order_value,DATE_FORMAT(sanction_orders.sanction_order_date,"%Y-%m-%d") as sanction_order_date,vouchers.voucher_no,DATE_FORMAT(vouchers.voucher_date,"%Y-%m-%d") as voucher_date,vouchers.voucher as voucher,vouchers.total_voucher_value as voucher_amount,vouchers.supporting_docs as supporting_docs,sanction_orders.sanction_order as sanction_order,beneficiary.beneficiary_name as beneficiary_name,beneficiary.bank_name as b_bank_name,beneficiary.branch_name as b_branch_name,beneficiary.account_no as b_account_no,beneficiary.ifsc_code as b_ifsc_code,invoice_payment.added_on as transaction_date');
        $this->db->from('invoices'); 
        $this->db->join('sanction_orders','sanction_orders.id=invoices.sanction_order_id','right');        
        $this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');        
        $this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','right');
        $this->db->join('invoice_payment','invoice_payment.invoice_id=invoices.id','left');     
        $this->db->where('invoices.approval_status',1);
        $this->db->where('invoices.l3_approval_status',1);
        // $this->db->group_start();
        // $this->db->where('invoices.payment_status !=',1);
        // $this->db->or_where('invoices.l3_payment_status !=',1);
        // $this->db->group_end();

        if($id){
            $this->db->where('invoices.id',$id);
        }
        if($user_type == 'L2'){
            $this->db->where("payment_status",0);
        }
        if($user_type == 'L3'){
            $this->db->where("l3_payment_status",0);
        }        
        $this->db->order_by('invoices.id','desc');
        $result=$this->db->get()->result(); 
        
        foreach ($result as $key => $res) {
            if($res->id){
                $scheme_details=$this->get_scheme_details_by_invoice_id($res->id);
                foreach($scheme_details as $key => $r) {
                    $r->utilized_budget = $this->get_approve_invoice_by_scheme_id($r->id);
                    $r->subheads = $this->get_sub_head_list_by_scheme_id($r->id, $res->id);
                }
                $res->scheme_list=$scheme_details;
                $res->beneficiary_other_details=$this->get_beneficiaries_other_details_by_id($res->vendor_id);
            }
        }        
        return $result;
    }
    
    public function update_invoice_payment_status($data){
        if($data['id']){           
            foreach ($data['id'] as $key => $id) {
                
                if($data['payment_status']){
                    $update_data=[
                        'payment_status'=>$data['payment_status']
                    ];
                }
                if($data['l3_payment_status']){
                    $update_data=[
                        'l3_payment_status'=>$data['l3_payment_status']
                    ];
                }
               
                $this->db->where('id',$id);
                $this->db->update('invoices',$update_data);

                $this->db->select('id');
                $this->db->from('invoice_payment');
                $this->db->where('invoice_id',$id);
                $row=$this->db->get()->row();
                $payment_data=[
                    'payment_from'=>$data['payment_from'],
                    'invoice_id'=>$id,
                    'added_on'=>date('Y-m-d H:i:s'),
                    'added_by'=>$data['added_by']
                ];
                // print_r($payment_data);die;
                if($row){                    
                    $this->db->where('invoice_id',$id);
                    $this->db->update('invoice_payment',$payment_data);
                    // echo $this->db->last_query();die;
                }else{
                    $this->db->insert('invoice_payment',$payment_data);
                }                
            }    
            return true;        
        }else{
            return false;
        }
    }

    public function invoice_list_by_invoice_no($search){
        $this->db->select('invoices.*');
        $this->db->from('invoices');
        
        if($search['invoice_no']){
            $this->db->where('invoices.invoice_no',$search['invoice_no']);
        }
        if($search['invoice_date']){
            $this->db->where('invoices.invoice_date',$search['invoice_date']);
        }
        if($search['vendor_id']){
            $this->db->where('invoices.vendor_id',$search['vendor_id']);
        }
        if($search['payment_type']){
            $this->db->where('invoices.payment_type',$search['payment_type']);
        }
               
        $this->db->order_by('invoices.id','desc');
        $result=$this->db->get()->result(); 
       
        
        return $result;
    }

    function get_invoice_details($id){
        $query = $this->db->get_where("invoice_scheme_subheads",array("invoice_id"=>$id));
        $result = $query->result_array();
        if($result){
            return $result;
        }
    }

    function update_subhead($array){
        return $this->db->update_batch("scheme_sub_head",$array,'id');
    }

    function get_subhead_details($id){
        $query = $this->db->get_where("scheme_sub_head",array("id"=>$id));
        $result = $query->result_array();
        if($result){
            return $result;
        }
    }

    function delete_invoice_schemes($id) {
        $this->db->where('invoice_id', $id);
        $this->db->delete('invoice_schemes');

        $this->db->where('invoice_id', $id);
        $this->db->delete('invoice_scheme_subheads');
        return true;
    }

}


?>