<?php
defined('BASEPATH') OR exit('No direct script access allowed');

    class Import_model extends CI_Model {

        public function __construct()
        {
            $this->load->database();
        }

        public function insert($data) {
          $validity = $this->check_students_limit($this->session->userdata('user_id'));
            if($validity == false){
              $this->session->set_flashdata('error_message', get_phrase('you_inscrease_students_limit'));
              redirect(site_url('institute/users'), 'refresh');
            }else{
              // echo '<pre>',print_r($data),'</pre>';
              // die;
              $res = $this->db->insert_batch('users',$data);
              if($res){
                  return TRUE;
              }else{
                  return FALSE;
              }
            }

        }

        public function check_students_limit($institute_id ='')
        {
          if($institute_id ==''){
              $institute_id = $this->session->userdata('user_id');
          }
          if($institute_id > 0){
            $institute_classes = $this->crud_model->get_institute_classes($institute_id);
            $plan = $this->user_model->get_plan_by_id($institute_id)->row_array();
            $class_ids = array();

            foreach ($institute_classes as $cls) {
                array_push($class_ids, $cls['id']);
            }
            if (sizeof($class_ids)) {
                $this->db->where_in('class_id', $class_ids);
            } else {
                return true;
            }
            $students = $this->db->get('users')->result_array();
            echo count($students);
            die;

            if(count($students) >= $plan['students']){
              return false;
            }
            else{
              return true;
            }
          }
        }

    }
?>
