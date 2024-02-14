<?php 
    if ( ! defined('BASEPATH')) exit('No direct script access allowed');

    if(!function_exists("check_null_value")){
        function check_null_value($val){
            return $val == 'null' || is_null($val) ? NULL : $val;
        }
    }
?>