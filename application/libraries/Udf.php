<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Udf {
	protected $CI;
	public function __construct(){
		$this->CI = & get_instance();
		$this->CI->load->database();
	}
	
	public function insert_data($table, $data) {
		$this->CI->db->insert($table, $data);
		return $this->CI->db->insert_id();
    }
	
	public function update_data($table, $data, $id) {
		$this->CI->db->where('id', $id);
		$this->CI->db->update($table, $data);
		
		
		return true;
    }
	
	public function delete_data($table, $value, $field = 'id') {
		$this->CI->db->where($field, $value);
		$this->CI->db->delete($table);
		
		return true;
    }
	
	public function select_data($table, $conditions = array(), $limit = null, $start = null){
		
		$this->CI->db->select('*');
		$this->CI->db->from($table);	
		
		$is_id = false;
		if(count($conditions)){
			foreach($conditions as $field => $value){
				if(is_numeric($value)){
					$this->CI->db->where($field, $value);
					
					if($field == 'id'){
						$is_id = true;
					}
				}else{
					$this->CI->db->like($field, $value);
				}
				
			}
		} 
		
		if($limit){
			$this->CI->db->limit($limit, $start);
		}
		$query = $this->CI->db->get();
		
		/* echo $this->CI->db->last_query();
		die; */
	
			return $query->result_array();
		
	}
	
	public function select_data_total($table, $conditions = array()){
		
		$this->CI->db->select('count(id) as total');
		$this->CI->db->from($table);	
		
		if(count($conditions)){
			foreach($conditions as $field => $value){
				if(is_numeric($value)){
					$this->CI->db->where($field, $value);					
				}else{
					$this->CI->db->like($field, $value);
				}				
			}
		} 
		
		$query = $this->CI->db->get();
		$result = $query->row();
		return $result->total;
	}
	
	public function generate_slug($table, $value){
		$slug = url_title($value, 'dash', true);
		$this->CI->db->select('id');
		$this->CI->db->from($table);
		$this->CI->db->where('slug', $slug);
		$query = $this->CI->db->get();

		if($query->num_rows() > 0){
			$slug = $slug.'-'.rand(1,50);
		}

		return $slug;
	}
	
	public function rrmdir($dir) {
		$dir = '/var/www/api.basix/10pointer/application/cache/'.$dir;
		if (is_dir($dir)) { 
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object))
						rmdir($dir."/".$object);
					else
					unlink($dir."/".$object); 
				} 
			}
			rmdir($dir); 
		}
	}
}
