<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
    class Import_model extends CI_Model {
 
        public function __construct()
        {
            $this->load->database();
        }
        
        public function insert($data) {
  
            $res = $this->db->insert_batch('users',$data);
            if($res){
                return TRUE;
            }else{
                return FALSE;
            }
      
        }
    }
?>