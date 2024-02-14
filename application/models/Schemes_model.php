<?php

class Schemes_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
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

	public function get_extra_provisional_budget($subhead_id,$financial_year=null){
		if($subhead_id){
			$this->db->select('sum(provisional_budget) as provisional_budget');
			$this->db->from('subhead_budgets');
			$this->db->where('subhead_id',$subhead_id);
			if($financial_year){
				$this->db->where('financial_year',$financial_year);
			}
			$this->db->where('l2_status',1);
			$this->db->where('l3_status',1);
			$result=$this->db->get()->row();
			if($result->provisional_budget){
				return $result->provisional_budget;
			}else{
				return 0;
			}
		}else{
			return 0;
		}
	}

	public function get_approve_invoice_by_scheme_id_and_subhead_id_and_fy($scheme_id=null,$subhead_id,$paid=false,$financial_year=null){
		
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
			if($financial_year){
				$this->db->where('invoice_scheme_subheads.financial_year', $financial_year);
			}
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

	public function get_approve_and_pending_invoice_by_scheme_id_and_subhead_id_and_fy($scheme_id=null,$subhead_id,$paid=false,$financial_year=null){
		
		if($scheme_id && $subhead_id){
			$this->db->select('invoice_scheme_subheads.id as id, invoice_scheme_subheads.scheme_id as scheme_id, invoice_scheme_subheads.subhead_amount as amount');
			$this->db->from('invoice_scheme_subheads');
			$this->db->join('invoices', 'invoices.id = invoice_scheme_subheads.invoice_id', 'RIGHT'); // Use left join here
			$this->db->where('invoices.approval_status',1);
			$this->db->where('invoices.l3_approval_status',1);
			if($paid){
				$this->db->where('invoices.payment_status', 1);
				$this->db->where('invoices.l3_payment_status', 1);
			}
			$this->db->where('invoice_scheme_subheads.scheme_id', $scheme_id);
			$this->db->where('invoice_scheme_subheads.subhead_id', $subhead_id);
			if($financial_year){
				$this->db->where('invoice_scheme_subheads.financial_year', $financial_year);
			}
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

	public function get_pending_invoice_by_scheme_id_and_subhead_id_and_fy($scheme_id=null,$subhead_id,$financial_year=null){		
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
			if($financial_year){
				$this->db->where('invoice_scheme_subheads.financial_year', $financial_year);
			}
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

	public function scheme_details_by_fy($id=null,$financial_year=null){
		$this->db->select('schemes.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year,scheme_type.title as type,financial_year.start_year,financial_year.end_year');
		$this->db->from('schemes');
		$this->db->join('financial_year','financial_year.id=schemes.financial_year','left');
		$this->db->join('scheme_type','scheme_type.id=schemes.type','left');
		if($id){
			$this->db->where('schemes.id',$id);
		}
		$this->db->where('schemes.l2_status',1);
		$this->db->where('schemes.l3_status',1);
		
		$this->db->order_by('id','desc');
		$schemes_list	=	$this->db->get()->result();
		$total_subhead_budget=0;
		if($schemes_list){
			foreach ($schemes_list as $key => $scheme) {
				
				$this->db->select('scheme_sub_head.*');
				$this->db->from('scheme_sub_head');
				$this->db->where('scheme_sub_head.scheme_id',$scheme->id);
				$sub_heads_list	=	$this->db->get()->result();
				$scheme_budget=0;
				$scheme_provisional_budget=0;
				$scheme_balance=0;
				$scheme_payable_expanses=0;
				$scheme_utilised_budget=0;
				$scheme_total_payment=0;
				foreach ($sub_heads_list as $key => $subhead) {
					$subhead_budget=0;
					$subhead_provisional_budget=0;
					if($financial_year>0){
						if($subhead->financial_year==$financial_year){
							$subhead_budget+=$subhead->budget;
							$subhead_provisional_budget+=$subhead->provisional_budget;
						}
					}else{
						$subhead_budget+=$subhead->budget;
						$subhead_provisional_budget+=$subhead->provisional_budget;
					}	  				
					$subhead_extra_budget=$this->get_extra_budget($subhead->id,$financial_year);
					$subhead_extra_provisional_budget=$this->get_extra_provisional_budget($subhead->id,$financial_year);
					if($subhead_extra_budget>0){
						$subhead_budget+=$subhead_extra_budget;
					}
					if($subhead_extra_provisional_budget>0){
						$subhead_provisional_budget+=$subhead_extra_provisional_budget;
					}

					$subhead->budget=$subhead_budget;
					$subhead->provisional_budget=$subhead_provisional_budget;
					$scheme_budget+=$subhead_budget;
					$scheme_provisional_budget+=$subhead_provisional_budget;
					$approve_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id_and_fy($scheme->id,$subhead->id,false,$financial_year);
					$paid_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id_and_fy($scheme->id,$subhead->id,true,$financial_year);
					$subhead->balance=$subhead->budget-$approve_invoices_amount;
					$subhead->payable_expanses=$approve_invoices_amount-$paid_invoices_amount;
					$subhead->utilised_budget=$approve_invoices_amount;
					$subhead->total_payment=$paid_invoices_amount;

					$scheme_balance+=$subhead->balance;
					$scheme_payable_expanses+=$subhead->payable_expanses;
					$scheme_utilised_budget+=$subhead->utilised_budget;
					$scheme_total_payment+=$subhead->total_payment;

				}	
				$scheme->total_budget=$scheme_budget;
				$scheme->provisional_budget=$scheme_provisional_budget;
				$scheme->balance=$scheme_balance;
				$scheme->payable_expanses=$scheme_payable_expanses;
				$scheme->utilised_budget=$scheme_utilised_budget;
				$scheme->total_payment=$scheme_total_payment;
				$scheme->sub_heads_list=$sub_heads_list;
				// print_r($sub_heads_list);die;

			}
			

			return $schemes_list;
		}else{
			return [];
		}
	}

	public function scheme_details_by_fy_added_pending($id=null,$financial_year=null){
		$this->db->select('schemes.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year,scheme_type.title as type,financial_year.start_year,financial_year.end_year');
		$this->db->from('schemes');
		$this->db->join('financial_year','financial_year.id=schemes.financial_year','left');
		$this->db->join('scheme_type','scheme_type.id=schemes.type','left');
		if($id){
			$this->db->where('schemes.id',$id);
		}
		$this->db->where('schemes.l2_status',1);
		$this->db->where('schemes.l3_status',1);
		
		$this->db->order_by('id','desc');
		$schemes_list	=	$this->db->get()->result();
		$total_subhead_budget=0;
		if($schemes_list){
			foreach ($schemes_list as $key => $scheme) {
				
				$this->db->select('scheme_sub_head.*');
				$this->db->from('scheme_sub_head');
				$this->db->where('scheme_sub_head.scheme_id',$scheme->id);
				$sub_heads_list	=	$this->db->get()->result();
				$scheme_budget=0;
				$scheme_provisional_budget=0;
				$scheme_balance=0;
				$scheme_payable_expanses=0;
				$scheme_utilised_budget=0;
				$scheme_total_payment=0;
				foreach ($sub_heads_list as $key => $subhead) {
					$subhead_budget=0;
					$subhead_provisional_budget=0;
					if($financial_year>0){
						if($subhead->financial_year==$financial_year){
							$subhead_budget+=$subhead->budget;
							$subhead_provisional_budget+=$subhead->provisional_budget;
						}
					}else{
						$subhead_budget+=$subhead->budget;
						$subhead_provisional_budget+=$subhead->provisional_budget;
					}	  				
					$subhead_extra_budget=$this->get_extra_budget($subhead->id,$financial_year);
					$subhead_extra_provisional_budget=$this->get_extra_provisional_budget($subhead->id,$financial_year);
					if($subhead_extra_budget>0){
						$subhead_budget+=$subhead_extra_budget;
					}
					if($subhead_extra_provisional_budget>0){
						$subhead_provisional_budget+=$subhead_extra_provisional_budget;
					}

					$subhead->budget=$subhead_budget;
					$subhead->provisional_budget=$subhead_provisional_budget;
					$scheme_budget+=$subhead_budget;
					$scheme_provisional_budget+=$subhead_provisional_budget;
					$approve_invoices_amount=$this->get_approve_and_pending_invoice_by_scheme_id_and_subhead_id_and_fy($scheme->id,$subhead->id,false,$financial_year);
					$paid_invoices_amount=$this->get_approve_and_pending_invoice_by_scheme_id_and_subhead_id_and_fy($scheme->id,$subhead->id,true,$financial_year);
					$pending_invoices_amount=$this->get_pending_invoice_by_scheme_id_and_subhead_id_and_fy($scheme->id,$subhead->id,$financial_year);
					
					$subhead->balance = $subhead->budget - $approve_invoices_amount - $pending_invoices_amount;
					$subhead->payable_expanses=$approve_invoices_amount-$paid_invoices_amount;
					$subhead->utilised_budget=$approve_invoices_amount;
					$subhead->total_payment=$paid_invoices_amount;
					$scheme_balance+=$subhead->balance;
					$scheme_payable_expanses+=$subhead->payable_expanses;
					$scheme_utilised_budget+=$subhead->utilised_budget;
					$scheme_total_payment+=$subhead->total_payment;
				}	
				$scheme->total_budget=$scheme_budget;
				$scheme->provisional_budget=$scheme_provisional_budget;
				$scheme->balance=$scheme_balance;
				$scheme->payable_expanses=$scheme_payable_expanses;
				$scheme->utilised_budget=$scheme_utilised_budget;
				$scheme->total_payment=$scheme_total_payment;
				$scheme->sub_heads_list=$sub_heads_list;
				// print_r($sub_heads_list);die;

			}
			

			return $schemes_list;
		}else{
			return [];
		}
	}

	public function get_sub_head_list_by_scheme_id($scheme_id){
		if($scheme_id){
			$this->db->select('scheme_sub_head.*,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year');
			$this->db->from('scheme_sub_head');
			$this->db->join('financial_year','financial_year.id=scheme_sub_head.financial_year','left');
			$this->db->where('scheme_sub_head.scheme_id',$scheme_id);
			$sub_heads_list	=	$this->db->get()->result();
			$total_subhead_budget=0;
			$total_subhead_provisional_budget=0;
			foreach ($sub_heads_list as $key => $subhead) {				
				$subhead_extra_budget=$this->get_extra_budget($subhead->id);
				$subhead_extra_provisional_budget=$this->get_extra_provisional_budget($subhead->id);
				$approve_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id,false);
				$pending_invoices_amount=$this->get_pending_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id);
				$subhead->budget=$subhead->budget+$subhead_extra_budget;

				$subhead->provisional_budget=$subhead->provisional_budget+$subhead_extra_provisional_budget;
				$total_subhead_budget+=$subhead->budget;
				$total_subhead_provisional_budget+=$subhead->provisional_budget;
				$paid_invoices_amount=$this->get_approve_invoice_by_scheme_id_and_subhead_id($scheme_id,$subhead->id,true);
				$subhead->balance=$subhead->budget-$approve_invoices_amount-$pending_invoices_amount;
				$subhead->payable_expanses=$approve_invoices_amount-$paid_invoices_amount;
				$subhead->utilised_budget=$approve_invoices_amount;
				$subhead->pending_budget=$pending_invoices_amount;
				$subhead->total_payment=$paid_invoices_amount;
			}
			$sub_heads_list['total_subhead_budget']=$total_subhead_budget;
			$sub_heads_list['total_subhead_provisional_budget']=$total_subhead_provisional_budget;			
			return $sub_heads_list;
		}else{
			return [];
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
			// $this->db->where('invoice_scheme_subheads.subhead_id', $subhead_id);
			$res = $this->db->get()->result();
			// print_r($this->db->last_query());  
			// die;
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}			
			return $amount;
		}else{
			return 0;
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

	public function get_attachments_by_scheme_id($scheme_id,$budget_id=null){
		if($scheme_id){
			$this->db->select('*');
			$this->db->from('scheme_attachments');
			$this->db->where('scheme_id',$scheme_id);
			if($budget_id){
				$this->db->where('budget_id',$budget_id);
			}
			$attachements_list	=	$this->db->get()->result();
			return $attachements_list;
		}else{
			return [];
		}
	}

    public function get_schemes($id=null){
        $this->db->select('schemes.*,financial_year.id as financial_year_id,CONCAT(financial_year.start_year,"-",financial_year.end_year) as financial_year,scheme_type.title as type,scheme_type.id as type_id,financial_year.start_year,financial_year.end_year');
		$this->db->from('schemes');
		$this->db->join('financial_year','financial_year.id=schemes.financial_year','left');
		$this->db->join('scheme_type','scheme_type.id=schemes.type','left');
		if($id){
			$this->db->where('schemes.id',$id);
		}

		$this->db->order_by('id','desc');
		$schemes_list	=	$this->db->get()->result();
		$total_subhead_budget=0;
		if($schemes_list){
			foreach ($schemes_list as $key => $scheme) {
				$sub_heads_list=$this->get_sub_head_list_by_scheme_id($scheme->id);
				$total_subhead_budget=$sub_heads_list['total_subhead_budget'];
				$total_subhead_provisional_budget=$sub_heads_list['total_subhead_provisional_budget'];
				unset($sub_heads_list['total_subhead_budget']);
				unset($sub_heads_list['total_subhead_provisional_budget']);
				$scheme->total_budget=$total_subhead_budget;
				$scheme->total_provisional_budget=$total_subhead_provisional_budget;
				$approve_invoices_amount=$this->get_approve_invoice_by_scheme_id($scheme->id);
				$pending_invoices_amount=$this->get_pending_invoice_by_scheme_id($scheme->id);
				$paid_invoices_amount=$this->get_paid_invoice_by_scheme_id($scheme->id);
				$scheme->balance=$scheme->total_budget-$approve_invoices_amount-$pending_invoices_amount;
				$scheme->payable_expanses=$approve_invoices_amount-$paid_invoices_amount;
				$scheme->utilised_budget=$approve_invoices_amount;
				$scheme->pending_budget=$pending_invoices_amount;
				$scheme->total_payment=$paid_invoices_amount;
				$scheme->total_interest=72;//remain default for now.
				
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

	public function get_pending_invoice_by_scheme_id($scheme_id=null){
		
		if($scheme_id){
			$this->db->select('invoice_schemes.id as id, invoice_schemes.scheme_id as scheme_id, invoice_schemes.amount as amount');
			$this->db->from('invoice_schemes');
			$this->db->join('invoices', 'invoices.id = invoice_schemes.invoice_id', 'RIGHT'); // Use left join here
			$this->db->group_start();
			$this->db->where("(invoices.approval_status=0 and invoices.l3_approval_status=0) OR (invoices.approval_status=1 and invoices.l3_approval_status=0) OR (invoices.approval_status=0 and invoices.l3_approval_status=1)");
			// $this->db->where('invoices.l3_approval_status',0);
			$this->db->group_end();
			$this->db->where('invoice_schemes.scheme_id', $scheme_id);
			$res = $this->db->get()->result();
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}
			return $amount;
		}
	}

	public function get_paid_invoice_by_scheme_id($scheme_id=null){
		
		if($scheme_id){
			$this->db->select('invoice_schemes.id as id, invoice_schemes.scheme_id as scheme_id, invoice_schemes.amount as amount');
			$this->db->from('invoice_schemes');
			$this->db->join('invoices', 'invoices.id = invoice_schemes.invoice_id', 'RIGHT'); // Use left join here
			$this->db->where('invoices.approval_status', 1);
			$this->db->where('invoices.l3_approval_status', 1);
			$this->db->where('invoices.payment_status', 1);
			$this->db->where('invoices.l3_payment_status', 1);
			$this->db->where('invoice_schemes.scheme_id', $scheme_id);
			$res = $this->db->get()->result();
			$amount=0;
			foreach ($res as $key => $r) {
				$amount+=$r->amount;
			}
			return $amount;
		}
	}
	
    public function add_schemes($data){
    	// print_r($data);die;
    	if($data){
    		$subheads=$data['sub_heads'];
    		$bank=$data['bank'];
    		unset($data['sub_heads']);
    		unset($data['bank']);
	 		
	 		$this->db->select('*');
			$this->db->from('schemes');
			$this->db->where('code',$data['code']);
			$this->db->group_start();
			$this->db->where('l2_status < ',2);
			$this->db->or_where('l3_status < ',2);
			$this->db->group_end();
			// $this->db->or_where('grant_code',$data['grant_code']);
			$result	=	$this->db->get()->result();
			$count=count($result);
			
			if($count<1){
				$this->db->insert('schemes',$data);
	    		$scheme_id=$this->db->insert_id();
	    		
	    		if($scheme_id){
	    			foreach ($subheads as $key => $subhead) {
	    				$subheads[$key]['scheme_id']=$scheme_id;	    				
	    			}
	    			// print_r($subheads);die;
	    			$this->db->insert_batch('scheme_sub_head',$subheads);
	    			$subhead_id=$this->db->insert_id();
	    			$bank['scheme_id']=$scheme_id;
	    			$this->db->insert('scheme_bank_account',$bank);
	    			$bank_id=$this->db->insert_id();

	    			if($bank_id && $subhead_id){
	    				return $scheme_id;
	    			}else{
	    				return false;
	    			}
	    		}else{
	    			return false;
	    		}	
			}else{
				return false;
			}    		
    	}else{
    		return false;
    	}
    }

	public function update_schemes($data,$id){
    	// print_r($data);die;
    	if($data){
    		$subheads=$data['sub_heads'];
    		$bank=$data['bank'];
    		unset($data['sub_heads']);
    		unset($data['bank']);
	 		
	 		$this->db->select('*');
			$this->db->from('schemes');
			$this->db->where('code',$data['code']);
			$this->db->group_start();
			$this->db->where('l2_status < ',2);
			$this->db->or_where('l3_status < ',2);
			$this->db->group_end();
			$this->db->where('id!=',$id);
			// $this->db->or_where('grant_code',$data['grant_code']);
			$result	=	$this->db->get()->result();
			$count=count($result);
			
			if($count<1){
				$this->db->where('id',$id);
				$this->db->update('schemes',$data);
	    		$scheme_id=$id;
	    		if($scheme_id){
	    			foreach ($subheads as $key => $subhead) {
						unset($subhead['balance']);
						unset($subhead['payable_expanses']);
						unset($subhead['utilised_budget']);
						unset($subhead['total_payment']);
	    				if($subhead['id']>0){
							$this->db->where('id',$subhead['id']);
							$this->db->update('scheme_sub_head',$subhead);
						}    				
	    			}
	    			// print_r($bank);die;
	    			$this->db->where('id',$bank['id']);
					$this->db->update('scheme_bank_account',$bank);

	    			return $scheme_id;
	    		}else{
	    			return false;
	    		}	
			}else{
				return false;
			}    		
    	}else{
    		return false;
    	}
    }


    public function add_scheme_attachments($attachments){
    	if($attachments){
			// print_r($attachments);die;
			$this->db->where('scheme_id',$attachments[0]['scheme_id']);		
			if($attachments[0]['budget_id']>0){
				$this->db->where('type',2);
				$this->db->where('budget_id',$attachments[0]['budget_id']);
			}else{
				$this->db->where('type',1);
			}
			$this->db->delete('scheme_attachments');
			// echo $this->db->last_query();die;

			$this->db->insert_batch('scheme_attachments',$attachments);
	    	$attachment_id=$this->db->insert_id();
			// print_r($attachment_id);die;
	    	return $attachments;
    	}else{
    		return false;
    	}
    }

	public function delete_scheme($id){
		if($id){
			$this->db->where('id',$id);
			$this->db->delete('schemes');
			return true;
		}else{
			return false;
		}
	}

	public function scheme_status_update($post_data){
		if($post_data){
			$this->db->where('id',$post_data['id']);
			$this->db->update('schemes',$post_data);
			// echo $this->db->last_query();die;
			return true;
		}else{
			return false;
		}
	}

	public function budget_status_update($post_data){
		if($post_data){
			$this->db->where('id',$post_data['id']);
			$this->db->update('subhead_budgets',$post_data);
			// echo $this->db->last_query();die;
			return true;
		}else{
			return false;
		}
	}

	public function get_scheme_details($id){
		if($id){
			$this->db->select('*');
			$this->db->from('schemes');
			$this->db->where('id',$id);
			$row=$this->db->get()->row();
			return $row;
		}else{
			return false;
		}
	}

	public function add_budget($post_data){
		if($post_data){
			$this->db->insert_batch('subhead_budgets',$post_data);
	    	$budget_id=$this->db->insert_id();
			if($budget_id){
				$total_budget=0;
				foreach ($post_data as $key => $post) {
					$total_budget=$total_budget+$post['budget'];
				}

				$scheme=$this->get_scheme_details($post_data[0]['scheme_id']);
				if($scheme->id){
					$update_arr=[
						'total_budget'=>$total_budget+$scheme->total_budget,
					];
					$this->db->where('id',$scheme->id);
					$this->db->update('schemes',$update_arr);
					
					$this->db->set("account_balance","account_balance+".$total_budget, FALSE);
					$this->db->where(array('id'=>1));
                    $this->db->update('balance_interest');
				}
				// $budget=$this->get_budget_by_id($scheme_id);
				return $budget_id;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	public function update_budget($data){
		if($data['id']>0){
			$this->db->where('id',$data['id']);
			$this->db->update('subhead_budgets',$data);
			return true;
		}else{
			return false;
		}
	}

	public function delete_budget($id){
		if($id){
			$this->db->where('id',$id);
			$this->db->delete('subhead_budgets');
			return true;
		}else{
			return false;
		}
	}

	public function add_new_subhead_from_budget($data){
		if($data){
			$this->db->insert('scheme_sub_head',$data);
			return $this->db->insert_id();
		}else{
			return false;
		}
	}

	public function get_budget_by_id($id){
		$this->db->select('*');
		$this->db->from('subhead_budgets');
		$this->db->where('id',$id);
		$row=$this->db->get()->row();
		if($row){
			return $row;
		}else{
			return false;
		}
	}

	public function get_budgets_by_scheme_id_and_subhead_id($scheme_id=null,$subhead_id=null,$id=null){
		$this->db->select('subhead_budgets.*,scheme_sub_head.code as subhead_code,scheme_sub_head.name as subhead_name,schemes.code as scheme_code,schemes.name as scheme_name,financial_year.start_year,financial_year.end_year');
		$this->db->from('subhead_budgets');
		$this->db->join('financial_year','financial_year.id=subhead_budgets.financial_year','left');
		$this->db->join('schemes','schemes.id=subhead_budgets.scheme_id','left');
		$this->db->join('scheme_sub_head','scheme_sub_head.id=subhead_budgets.subhead_id','left');
		if($id){
			$this->db->where('subhead_budgets.id',$id);
		}
		if($scheme_id){
			$this->db->where('subhead_budgets.scheme_id',$scheme_id);
		}
		if($subhead_id){
			$this->db->where('subhead_budgets.subhead_id',$subhead_id);
		}
		$this->db->order_by('subhead_budgets.id','desc');
		$result=$this->db->get()->result();
		foreach ($result as $key => $res) {
			$attachments=$this->get_attachments_by_scheme_id($res->scheme_id,$res->id);
			$res->attachments=$attachments;
		}
		if($result){
			return $result;
		}else{
			return [];
		}		
	}

	public function get_invoice_schemes_list($date){
		$this->db->select('invoice_schemes.*');
		$this->db->from('invoice_schemes'); 
		$this->db->join('invoices','invoice_schemes.invoice_id=invoices.id','left');        
		$this->db->join('invoice_payment','invoice_payment.invoice_id=invoices.id','right');        
		$this->db->where('invoices.approval_status',1);
		$this->db->where('invoices.l3_approval_status',1);
		$this->db->where('invoices.payment_status',1);
		$this->db->where('invoices.l3_payment_status',1);
		
		if($date){
			$this->db->where("DATE_FORMAT(invoice_payment.added_on,'%Y-%m-%d')",$date);     
		} 
		$this->db->group_by('invoice_schemes.scheme_id');
		$this->db->order_by('invoice_schemes.id','desc');
		$result=$this->db->get()->result(); 
		return $result;
	}

	public function get_scheme_payment_list($scheme_id,$date=null){
		if($scheme_id){
			
			$this->db->select('beneficiary.id as beneficiary_id,beneficiary.company_name,beneficiary.email as b_email,beneficiary.mobile as b_mobile,beneficiary.address_1 as b_address_1,vouchers.voucher_no,beneficiary.beneficiary_name as beneficiary_name,beneficiary.bank_name as b_bank_name,beneficiary.branch_name as b_branch_name,beneficiary.account_no as b_account_no,beneficiary.ifsc_code as b_ifsc_code,invoice_schemes.added_on as date,invoice_schemes.amount as amount,invoices.payable_amount as payable_amount,invoices.total_deduction as total_deduction,invoices.invoice_value as invoice_value,invoices.sanction_amount as sanction_amount,invoice_schemes.scheme_id as scheme_id,invoices.id as invoice_id');
			$this->db->from('invoice_schemes'); 
			$this->db->join('invoices','invoice_schemes.invoice_id=invoices.id','left');        
			$this->db->join('invoice_payment','invoice_payment.invoice_id=invoices.id','right');        
			$this->db->join('vouchers','vouchers.id=invoices.voucher_id','left');        
			$this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','left');
			$this->db->where('invoices.approval_status',1);
			$this->db->where('invoices.l3_approval_status',1);
			$this->db->where('invoices.payment_status',1);
			$this->db->where('invoices.l3_payment_status',1);
			$this->db->where('invoice_schemes.scheme_id',$scheme_id);    
			if($date){
				$this->db->where("DATE_FORMAT(invoice_payment.added_on,'%Y-%m-%d')",$date);     
			} 
			$this->db->order_by('invoice_schemes.id','desc');
			$result=$this->db->get()->result(); 
			
			// echo $this->db->last_query();die;
			foreach ($result as $key => $res) {
				if($res->scheme_id && $res->invoice_id){
					$this->db->select('invoice_scheme_subheads.*,scheme_sub_head.code as code');
					$this->db->from('invoice_scheme_subheads');
					$this->db->join('scheme_sub_head','scheme_sub_head.id=invoice_scheme_subheads.subhead_id');
					$this->db->where('invoice_scheme_subheads.scheme_id',$res->scheme_id);
					$this->db->where('invoice_scheme_subheads.invoice_id',$res->invoice_id);
					$subheads=$this->db->get()->result();
					$res->subheads=$subheads;
				}
			}
			
			return $result;
		}else{
			return [];
		}
	}

	public function get_payment_list_of_scheme($scheme_id=null){
        if($scheme_id){
			$this->db->select('invoices.id as invoice_id,beneficiary.id as beneficiary_id,beneficiary.company_name,vouchers.voucher_no,invoice_schemes.amount as i_scheme_amount');
			$this->db->from('invoice_schemes'); 
			$this->db->join('invoices','invoice_schemes.invoice_id=invoices.id','right');        
			$this->db->join('vouchers','vouchers.id=invoices.voucher_id','right');        
			$this->db->join('beneficiary','beneficiary.id=invoices.vendor_id','right');
			// $this->db->join('invoice_scheme_subheads','invoice_scheme_subheads.scheme_id=invoice_schemes.scheme_id','right');
			$this->db->where('invoices.approval_status',1);
			$this->db->where('invoices.payment_status',1);
			$this->db->where('invoice_schemes.scheme_id',$scheme_id);     
			$this->db->order_by('invoice_schemes.id','desc');
			$result=$this->db->get()->result(); 
			foreach ($result as $key => $res) {
				
			}
			return $result;
		}else{
			return [];
		}
    }

	
}
?>