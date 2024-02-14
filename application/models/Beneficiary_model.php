<?php

class Beneficiary_model extends CI_Model{
    public function __construct() {		
		parent::__construct();
		$this->load->database();		
		date_default_timezone_set('Asia/Kolkata');
	}

    public function insert_data($table,$data){
        if($data && $table){
            $this->db->insert($table,$data);
            return $this->db->insert_id();
        }else{
            return false;
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
            $this->db->select('*');
            $this->db->from('beneficiary_other_details');
            $this->db->where('beneficiary_id',$beneficiary_id);
            $row=$this->db->get()->row();
            return $row;
        }else{
            return false;
        }
    }

    public function get_beneficiaries($beneficiary_id=null){
        $this->db->select('*, (select title from beneficiary_status where id=beneficiary.status) as beneficiar_status');
        $this->db->from('beneficiary'); 
        if($beneficiary_id){
            $this->db->where('id',$beneficiary_id);
        }
        $this->db->order_by('id','desc');
        $result=$this->db->get()->result();        
        foreach ($result as $key => $res) {
            if($res->id){
                $res->beneficiary_other_details=$this->get_beneficiaries_other_details_by_id($res->id);
                $res->beneficiary_partner_details=$this->get_beneficiaries_partner_details_by_id($res->id);
            }
        }
        return $result;
    }

    public function beneficiary_status_update($post_data){
		if($post_data){
			$this->db->where('id',$post_data['id']);
			$this->db->update('beneficiary',$post_data);
			// echo $this->db->last_query();die;
			return true;
		}else{
			return false;
		}
	}

    public function add_beneficiary($basic_details,$partner_details,$other_details){
        if($basic_details && $partner_details && $other_details){
            $beneficiary_id=$this->insert_data('beneficiary',$basic_details);
            if($beneficiary_id){
                $partner_details['beneficiary_id']=$beneficiary_id;
                $other_details['beneficiary_id']=$beneficiary_id;
                // print_r($partner_details);die;
                $this->db->insert('beneficiary_partner_details',$partner_details);
                $partner_id=$this->db->insert_id();
                $this->db->insert('beneficiary_other_details',$other_details);
                $other_id=$this->db->insert_id();
                // echo $this->db->last_query();die;
                if($beneficiary_id){
                    $details=$this->get_beneficiaries($beneficiary_id);
                    return $details;
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

    public function update_beneficiary($basic_details,$partner_details,$other_details,$beneficiary_id){
        if($beneficiary_id && $basic_details && $partner_details && $other_details){
            $this->db->where('id',$beneficiary_id);
            $this->db->update('beneficiary',$basic_details);
            if($beneficiary_id){
                $this->db->where('beneficiary_id',$beneficiary_id);
                $this->db->update('beneficiary_partner_details',$partner_details);

                $this->db->where('beneficiary_id',$beneficiary_id);
                $this->db->update('beneficiary_other_details',$other_details);
                // echo $this->db->last_query();die;
                if($beneficiary_id){
                    $details=$this->get_beneficiaries($beneficiary_id);
                    return $details;
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


    public function delete_beneficiary($id){
		if($id>0){
			$this->db->where('id',$id);
			$this->db->delete('beneficiary');
            
            $this->db->where('beneficiary_id',$id);
			$this->db->delete('beneficiary_partner_details');

            $this->db->where('beneficiary_id',$id);
			$this->db->delete('beneficiary_other_details');
			return true;
		}else{
			return false;
		}
	}

    function check_fields_already_exist($clm, $val,$condition = array()){
        if(is_array($condition) && count($condition) > 0){
            $this->db->where($condition);
        }
        $query = $this->db->get_where("beneficiary",array($clm=>$val));
        $result = $query->row_array();
        
        if($result){
            return $result;
        }
    }

    
}
?>