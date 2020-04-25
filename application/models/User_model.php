<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function get_admin_details() {
        return $this->db->get_where('users', array('role_id' => 1));
    }

    public function get_user($user_id = 0) {
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        $this->db->where('role_id', 2);
        return $this->db->get('users');
    }

    public function get_class_enrolled_students($class_id){
        if ($class_id > 0 && $class_id !='') {
            $this->db->where('class_id', $class_id);
            return $this->db->get('users');
        }

    }

    public function get_all_user($user_id = 0) {
        if ($user_id > 0) {
            $this->db->where('id', $user_id);
        }
        return $this->db->get('users');
    }

    public function get_all_instructor($institute_id = 0) {
        if ($institute_id > 0) {
            $this->db->where('institute_id', $institute_id);
            return $this->db->get('users')->result_array();
        }
    }

    public function get_plan_by_id($user_id = ''){
        return $this->db->get_where('plans', array('institute_id' => $user_id));
    }


    public function add_user($role_id = 2, $class_id = '') {
        $validity = $this->check_duplication('on_create', $this->input->post('email'));
        if ($validity == false) {
            $this->session->set_flashdata('error_message', get_phrase('email_duplication'));
        }else {

            $data['first_name'] = html_escape($this->input->post('first_name'));
            $data['last_name'] = html_escape($this->input->post('last_name'));
            $data['email'] = html_escape($this->input->post('email'));
            $data['password'] = sha1(html_escape($this->input->post('password')));
            if (isset($_POST['type'])){
                $user_type = html_escape($this->input->post('type'));
            }
            $instructor_logged_in = html_escape($this->input->post('current_instructor'));
            if ($user_type == "institute"){
                $data['type'] = $user_type;
                if ($instructor_logged_in == "present"){
                    $data['institute_id'] = $this->session->userdata('user_id');
                }
                else {
                    $data['institute_id'] = html_escape($this->input->post('institutes'));
                }

            }elseif($user_type == "freelancer"){
                $data['type'] = $user_type;
                $data['institute_id'] = NULL;
            }
            else{
                $data['type'] = NULL;
                $data['institute_id'] = NULL;
            }

            if ($class_id > 0 && $class_id != ''){
                $data['class_id'] = $class_id;
            }

            $social_link['facebook'] = html_escape($this->input->post('facebook_link'));
            $social_link['twitter'] = html_escape($this->input->post('twitter_link'));
            $social_link['linkedin'] = html_escape($this->input->post('linkedin_link'));
            $data['social_links'] = json_encode($social_link);
            $data['biography'] = $this->input->post('biography');
            if($role_id == 4){
                $data['role_id'] = 4;
            }elseif($role_id == 3){
                $data['role_id'] = 3;
            }
            else{
                $data['role_id'] = 2;
            }
            $data['date_added'] = strtotime(date("Y-m-d H:i:s"));
            $data['wishlist'] = json_encode(array());
            $data['watch_history'] = json_encode(array());
            $data['status'] = 1;

            // Add paypal keys
            $paypal_info = array();
            $paypal['production_client_id']  = html_escape($this->input->post('paypal_client_id'));
            $paypal['production_secret_key'] = html_escape($this->input->post('paypal_secret_key'));
            array_push($paypal_info, $paypal);
            $data['paypal_keys'] = json_encode($paypal_info);

            // Add Stripe keys
            $stripe_info = array();
            $stripe_keys = array(
                'public_live_key' => html_escape($this->input->post('stripe_public_key')),
                'secret_live_key' => html_escape($this->input->post('stripe_secret_key'))
            );
            array_push($stripe_info, $stripe_keys);
            $data['stripe_keys'] = json_encode($stripe_info);

            $this->db->insert('users', $data);
            $user_id = $this->db->insert_id();
            $this->upload_user_image($user_id);
            $this->session->set_flashdata('flash_message', get_phrase('user_added_successfully'));
        }
    }


    public function check_institute($institute){
        $duplicate_email_check = $this->db->get_where('users', array('institute_id' => $institute));
        if ($duplicate_email_check->num_rows() > 0) {
            return false;
        }else {
            return true;
        }
    }

    public function check_duplication($action = "", $email = "", $user_id = "") {
        $duplicate_email_check = $this->db->get_where('users', array('email' => $email));

        if ($action == 'on_create') {
            if ($duplicate_email_check->num_rows() > 0) {
                return false;
            }else {
                return true;
            }
        }elseif ($action == 'on_update') {
            if ($duplicate_email_check->num_rows() > 0) {
                if ($duplicate_email_check->row()->id == $user_id) {
                    return true;
                }else {
                    return false;
                }
            }else {
                return true;
            }
        }
    }

    public function edit_user($user_id = "") { // Admin does this editing
        $validity = $this->check_duplication('on_update', $this->input->post('email'), $user_id);
        if ($validity) {
            $data['first_name'] = html_escape($this->input->post('first_name'));
            $data['last_name'] = html_escape($this->input->post('last_name'));
            $user_type = html_escape($this->input->post('type'));

            //Association multiple instructors to institute
            // $instructor_list = $this->input->post('instructors');
            // foreach ($instructor_list as $instructor_id) {
            //     $data_user['institute_id'] = $instructor_id;
            //     $this->db->where('id', $instructor_id);
            //     $this->db->update('users', $data);
            // }
            // $this->session->set_flashdata('flash_message', get_phrase('instructor_add_successfully'));
            //end

            if ($user_type == "institute"){
                $data['type'] = $user_type;
                $data['institute_id'] = html_escape($this->input->post('institutes'));
            }elseif($user_type == "freelancer"){
                $data['type'] = $user_type;
                $data['institute_id'] = NULL;
            }
            else{
                $data['type'] = NULL;
                $data['institute_id'] = NULL;
            }

            if (isset($_POST['email'])) {
                $data['email'] = html_escape($this->input->post('email'));
            }
            $social_link['facebook'] = html_escape($this->input->post('facebook_link'));
            $social_link['twitter'] = html_escape($this->input->post('twitter_link'));
            $social_link['linkedin'] = html_escape($this->input->post('linkedin_link'));
            $data['social_links'] = json_encode($social_link);
            $data['biography'] = $this->input->post('biography');
            $data['title'] = html_escape($this->input->post('title'));
            $data['last_modified'] = strtotime(date("Y-m-d H:i:s"));

            // Update paypal keys
            $paypal_info = array();
            $paypal['production_client_id']  = html_escape($this->input->post('paypal_client_id'));
            $paypal['production_secret_key'] = html_escape($this->input->post('paypal_secret_key'));
            array_push($paypal_info, $paypal);
            $data['paypal_keys'] = json_encode($paypal_info);
            // Update Stripe keys
            $stripe_info = array();
            $stripe_keys = array(
                'public_live_key' => html_escape($this->input->post('stripe_public_key')),
                'secret_live_key' => html_escape($this->input->post('stripe_secret_key'))
            );
            array_push($stripe_info, $stripe_keys);
            $data['stripe_keys'] = json_encode($stripe_info);

            $this->db->where('id', $user_id);
            $this->db->update('users', $data);
            $this->upload_user_image($user_id);
            $this->session->set_flashdata('flash_message', get_phrase('user_update_successfully'));
        }else {
            $this->session->set_flashdata('error_message', get_phrase('email_duplication'));
        }

        $this->upload_user_image($user_id);
    }


public function update_user_plan($user_id, $plan_id){
  if ($user_id > 0){
    $count_user = $this->db->get_where('users', array('id' => $user_id))->num_rows();
    if ($count_user == 1){
      $data['plan_id'] = $plan_id;
      $this->db->where('id', $user_id);
      $this->db->update('users', $data);
    }

  }

}

    public function delete_user($user_id = "") {
        $this->db->where('id', $user_id);
        $this->db->delete('users');
        $this->session->set_flashdata('flash_message', get_phrase('user_deleted_successfully'));
    }

    public function unlock_screen_by_password($password = "") {
        $password = sha1($password);
        return $this->db->get_where('users', array('id' => $this->session->userdata('user_id'), 'password' => $password))->num_rows();
    }

    public function register_user($data) {
        $this->db->insert('users', $data);
        return $this->db->insert_id();
    }

    public function my_courses($user_id = "") {
        if ($user_id == "") {
            $user_id = $this->session->userdata('user_id');
        }
        return $this->db->get_where('enrol', array('user_id' => $user_id));
    }

    public function upload_user_image($user_id) {
        if (isset($_FILES['user_image']) && $_FILES['user_image']['name'] != "") {
            move_uploaded_file($_FILES['user_image']['tmp_name'], 'uploads/user_image/'.$user_id.'.png');
            $this->session->set_flashdata('flash_message', get_phrase('user_update_successfully'));
        }
    }

    public function update_account_settings($user_id) {
        $validity = $this->check_duplication('on_update', $this->input->post('email'), $user_id);
        if ($validity) {
            if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                $user_details = $this->get_user($user_id)->row_array();
                $current_password = $this->input->post('current_password');
                $new_password = $this->input->post('new_password');
                $confirm_password = $this->input->post('confirm_password');
                if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
                    $data['password'] = sha1($new_password);
                }else {
                    $this->session->set_flashdata('error_message', get_phrase('mismatch_password'));
                    return;
                }
            }
            $data['email'] = html_escape($this->input->post('email'));
            $this->db->where('id', $user_id);
            $this->db->update('users', $data);
            $this->session->set_flashdata('flash_message', get_phrase('updated_successfully'));
        }else {
            $this->session->set_flashdata('error_message', get_phrase('email_duplication'));
        }
    }

    public function change_password($user_id) {
        $data = array();
        if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
            $user_details = $this->get_all_user($user_id)->row_array();
            $current_password = $this->input->post('current_password');
            $new_password = $this->input->post('new_password');
            $confirm_password = $this->input->post('confirm_password');

            if ($user_details['password'] == sha1($current_password) && $new_password == $confirm_password) {
                $data['password'] = sha1($new_password);
            }else {
                $this->session->set_flashdata('error_message', get_phrase('mismatch_password'));
                return;
            }
        }

        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
        $this->session->set_flashdata('flash_message', get_phrase('password_updated'));
    }


    public function get_institute($id = 0){
        if ($id > 0){
            $this->db->where('id', $id);
            return $this->db->get('users')->result_array();
        }else{
            $this->db->where('role_id', 3);
            return $this->db->get('users')->result_array();
        }
    }

    public function get_single_institute($id = 0){
        if ($id > 0){
            $this->db->where('id', $id);
            return $this->db->get('users')->result_array();
        }else{
          $this->session->set_flashdata('error_message', get_phrase('institute_not_found'));
        }
    }

    public function get_instructors(){
        $this->db->where('role_id', 4);
            return $this->db->get('users')->result_array();
    }

    // public function get_unassigned_instructors(){
    //     $checker = array(
    //         'role_id' => 4,
    //         'institute_id'  => NULL,
    //         'type' => NULL
    //       );

    //       $result = $this->db->get_where('users', $checker)->result_array();
    //       return $result;
    // }

    public function get_instructor($id = 0) {
        if ($id > 0) {
            return $this->db->get_all_user($id);
        }else {
            if ($this->check_if_instructor_exists()) {
                $this->db->select('user_id');
                $this->db->distinct('user_id');
                $query_result =  $this->db->get('course');
                $ids = array();
                foreach ($query_result->result_array() as $query) {
                    if ($query['user_id']) {
                        array_push($ids, $query['user_id']);
                    }
                }

                $this->db->where_in('id', $ids);
                return $this->db->get('users')->result_array();
            }
            else {
                return array();
            }
        }
    }

    public function sync_institute_id($id = 0){
        $this->db->select('id');
        $this->db->where('institute_id', $id[0]);
        return $this->db->get('users')->row_array();
    }

    public function check_if_instructor_exists() {
        $this->db->where('user_id >', 0);
        $result = $this->db->get('course')->num_rows();
        if ($result > 0) {
            return true;
        }else {
            return false;
        }
    }

    public function get_number_of_active_courses_of_instructor($instructor_id) {
        $checker = array(
          'user_id' => $instructor_id,
          'status'  => 'active'
        );
        $result = $this->db->get_where('course', $checker)->num_rows();
        return $result;
    }

    public function get_number_of_instructor($institute_id) {
        $checker = array(
          'institute_id' => $institute_id,
          'status'  => '1'
        );
        $result = $this->db->get_where('users', $checker)->num_rows();
        return $result;
    }

    public function get_institute_name($institute_id){
        $this->db->where('id', $institute_id);
        return $this->db->get('users')->result();
    }

    public function get_user_image_url($user_id) {

         if (file_exists('uploads/user_image/'.$user_id.'.jpg'))
             return base_url().'uploads/user_image/'.$user_id.'.jpg';
        else
            return base_url().'uploads/user_image/placeholder.png';
    }
    public function get_instructor_list() {
        $query1 = $this->db->get_where('course', array('status' => 'active'))->result_array();
        $instructor_ids = array();
        $query_result = array();
        foreach ($query1 as $row1) {
            if (!in_array($row1['user_id'], $instructor_ids) && $row1['user_id'] != "") {
                array_push($instructor_ids, $row1['user_id']);
            }
        }
        if (count($instructor_ids) > 0) {
            $this->db->where_in('id', $instructor_ids);
            $query_result = $this->db->get('users');
        }else {
            $query_result = $this->get_admin_details();
        }

        return $query_result;
    }

    public function update_instructor_paypal_settings($user_id = '') {
        // Update paypal keys
        $paypal_info = array();
        $paypal['production_client_id'] = html_escape($this->input->post('paypal_client_id'));
        $paypal['production_secret_key'] = html_escape($this->input->post('paypal_secret_key'));
        array_push($paypal_info, $paypal);
        $data['paypal_keys'] = json_encode($paypal_info);
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
    }
    public function update_instructor_stripe_settings($user_id = '') {
        // Update Stripe keys
        $stripe_info = array();
        $stripe_keys = array(
            'public_live_key' => html_escape($this->input->post('stripe_public_key')),
            'secret_live_key' => html_escape($this->input->post('stripe_secret_key'))
        );
        array_push($stripe_info, $stripe_keys);
        $data['stripe_keys'] = json_encode($stripe_info);
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);
    }
}
