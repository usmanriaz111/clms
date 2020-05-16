<?php
defined('BASEPATH') OR exit('No direct script access allowed');
 
    class Import_model extends CI_Model {
 
        public function __construct()
        {
            $this->load->database();
        }
        
        public function insert($data, $institute_id) {
            $user_role = $this->session->userdata('role_name');
            $institute_data = $this->db->get_where('users', array('id' => $institute_id))->row_array();
            if ($institute_data['plan_id'] > 0){
                $plan_id = $institute_data['plan_id'];
                // $plan = $this->db->get_where('plans', array('id' => $plan_id))->row_array();
                $plan = $this->crud_model->check_plan($institute_data['id'])->row_array();
                if ($plan){
                foreach ($data as $user){
                    $student_count = $this->user_model->check_students_limit($user['class_id']);
                        if($student_count >= $plan['students'] ){
                            $this->session->set_flashdata('error_message', get_phrase($plan['students'].' students imported successfully'.' no_more_students_added'));
                            redirect(site_url($user_role.'/classes'), 'refresh');
                        }else{
                            $this->db->insert('users',$user);
                        }
                }
            }else{
                $this->session->set_flashdata('error_message', get_phrase('Choose a plan'));
                redirect(site_url($user_role.'/classes'), 'refresh');
            }
            }else{
                $this->session->set_flashdata('error_message', get_phrase('You have not purchaes plan'));
                redirect(site_url($user_role.'/classes'), 'refresh');
            }
        }
    }
?>