<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (file_exists("application/aws-module/aws-autoloader.php")) {
    include APPPATH . 'aws-module/aws-autoloader.php';
}
require 'upload-to-aws.php';
require 'aws-ec2-client.php';
class Crud_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');
    }

    public function fetch_all_event(){
        
        $this->db->order_by('id');
        $event_data = $this->db->get('live_sessions')->result_array();
      
        foreach($event_data as $row)
        {
            
            $start_date = $row['start_time'] + $row["timezone"][0] + $row["timezone"]*60*60;
            $start_time = gmdate('h:i a', $start_date);
            $start_date = gmdate('Y-m-d h:i:s', $start_date);
            $end_date = $row['end_time'] + $row["timezone"][0] + $row["timezone"]*60*60;
            $end_date = gmdate('Y-m-d h:i:s', $end_date);
            $cls = $this->db->get_where('classes', array('id' => $row['class_id']))->row_array();
            $course = $this->db->get_where('course', array('id' => $cls['course_id']))->row_array();
            $instructor = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
            $institute = $this->db->get_where('users', array('id' => $instructor['institute_id']))->row_array();

                $data[] = array(
                'id' => $row['id'],
                'title' => $row['name'],
                'description' => $start_time.'-Duration ('.ucfirst($institute['first_name']).' '.ucfirst($institute['last_name']).', '.ucfirst($course['title']).', '. ucfirst($cls['name']),
                'start' => $start_date,
                'end' => $end_date
                );
        }
          return json_encode($data);
       }

       public function fetch_instructor_events(){
        $instructor_classes = $this->curret_user_classes();
        // echo '<pre>',print_r($instructor_classes),'</pre>';
        //     die;
        foreach($instructor_classes as $cls)
        {
            $this->db->order_by('id');
            $event_data = $this->db->get_where('live_sessions', array('class_id' => $cls['id']))->row_array();
            $start_date = $event_data['start_time'] + $event_data["timezone"][0] + $event_data["timezone"]*60*60;
            $start_time = gmdate('h:i a', $start_date);
            $start_date = gmdate('Y-m-d h:i:s', $start_date);
            $end_date = $event_data['end_time'] + $event_data["timezone"][0] + $event_data["timezone"]*60*60;
            $end_date = gmdate('Y-m-d h:i:s', $end_date);
            $course = $this->db->get_where('course', array('id' => $cls['course_id']))->row_array();
            $instructor = $this->db->get_where('users', array('id' => $this->session->userdata('user_id')))->row_array();
            $institute = $this->db->get_where('users', array('id' => $instructor['institute_id']))->row_array();

                $data[] = array(
                'id' => $course['id'],
                'title' => $course['title'],
                'description' => $start_time.'-Duration ('.ucfirst($institute['first_name']).' '.ucfirst($institute['last_name']).', '.ucfirst($course['title']).', '. ucfirst($cls['name']),
                'start' => $start_date,
                'end' => $end_date
                );
        }
          return json_encode($data);
       }

    public function add_s3_settings(){
       $user_id = $this->session->userdata('user_id');
       $data['access_key'] = html_escape($this->input->post('aws_access_key'));
       $data['secret_key'] = html_escape($this->input->post('aws_secret_key'));
       $data['region'] = html_escape($this->input->post('region'));
       $data['url'] = html_escape($this->input->post('aws_url'));
       $data['bucket_name'] = html_escape($this->input->post('bucket_name'));
       $data['user_id'] = $user_id;
       $data['date_added'] = strtotime(date('D, d-M-Y'));
       return $this->db->insert('s3_settings', $data);
       $this->session->set_flashdata('flash_message', get_phrase('s3_keys_successfully_added'));
    }

    public function edit_s3_settings($id = ''){
        
        $data['access_key'] = html_escape($this->input->post('aws_access_key'));
        $data['secret_key'] = html_escape($this->input->post('aws_secret_key'));
        $data['region'] = html_escape($this->input->post('region'));
        $data['url'] = html_escape($this->input->post('aws_url'));
        $data['bucket_name'] = html_escape($this->input->post('bucket_name'));
        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $this->db->where('id', $id);
        $this->db->update('s3_settings', $data);
        $this->session->set_flashdata('flash_message', get_phrase('s3_keys_update_successfully'));
     }

     public function get_s3_settings(){
        return $this->db->get_where('s3_settings', array('user_id' => $this->session->userdata('user_id')));
     }

    public function curret_user_classes()
    {
        $course_ids = array();
        $courses = array();

        $this->db->where('user_id', $this->session->userdata('user_id'));
        $this->db->select('id');
        $courses = $this->db->get('course')->result_array();
        foreach ($courses as $course) {
            if (!in_array($course['id'], $course_ids)) {
                array_push($course_ids, $course['id']);
            }
        }
        if (sizeof($course_ids)) {
            $this->db->where_in('course_id', $course_ids);
        } else {
            return array();
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('classes')->result_array();
    }

    public function get_classes_course()
    {
      $courses = $this->get_classes('all','all','all');
      $course_ids = array();

      foreach ($courses as $key => $course) {
        array_push($course_ids, $course['course_id']);
      }

      $this->db->where_in('id', $course_ids);
      return $this->db->get('course')->result_array();
    }

    public function get_institute_classes()
    {
        $course_ids = array();
        $courses = array();
        $instructor_ids = array();

        $this->db->where('institute_id', $this->session->userdata('user_id'));
        $instructors = $this->db->get('users')->result_array();
        foreach ($instructors as $instructor) {
            array_push($instructor_ids, $instructor['id']);
        }
        if (sizeof($instructor_ids)) {
            $this->db->where_in('user_id', $instructor_ids);
        } else {
            return array();
        }

        $courses = $this->db->get('course')->result_array();

        foreach ($courses as $course) {
            if (!in_array($course['id'], $course_ids)) {
                array_push($course_ids, $course['id']);
            }
        }
        if (sizeof($course_ids)) {
            $this->db->where_in('course_id', $course_ids);
        } else {
            return array();
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('classes')->result_array();
    }

    public function get_institute_courses($category_id, $price, $status)
    {
        // $course_ids = array();
        // $courses = array();
        $instructor_ids = array();

        $this->db->where('institute_id', $this->session->userdata('user_id'));
        $instructors = $this->db->get('users')->result_array();
        foreach ($instructors as $instructor) {
            array_push($instructor_ids, $instructor['id']);
        }

        if (sizeof($instructor_ids)) {
            $this->db->where_in('user_id', $instructor_ids);
        } else {
            return array();
        }

        if ($category_id != "all") {
            $this->db->where('sub_category_id', $category_id);
        }

        if ($price != "all") {
            if ($price == "paid") {
                $this->db->where('is_free_course', null);
            } elseif ($price == "free") {
                $this->db->where('is_free_course', 1);
            }
        }
        if ($status != "all") {
            $this->db->where('status', $status);
        }

        return $this->db->get('course')->result_array();
    }

    public function count_institute_courses($institute_id)
    {
        $instructor_ids = array();

        $this->db->where('institute_id', $institute_id);
        $instructors = $this->db->get('users')->result_array();
        foreach ($instructors as $instructor) {
            array_push($instructor_ids, $instructor['id']);
        }

        if (sizeof($instructor_ids)) {
            $this->db->where_in('user_id', $instructor_ids);
        } else {
            return array();
        }

        return $this->db->get('course')->result_array();
    }

    public function add_class()
    {
        $validity_name = $this->check_name_duplication($this->input->post('name'));
        if ($validity_name == false) {
            $this->session->set_flashdata('error_message', get_phrase('class_name_duplication'));
        } else {

            $institute_id = $this->input->post('institutes');

            if ($institute_id == ''){
                $institute_id = $this->session->userdata('user_id');
            }
            if ($institute_id > 0){
                $institute = $this->user_model->get_single_institute($institute_id);
                $plan = $this->check_plan($institute['id'])->row_array();

                $institute_courses_count = $this->count_institute_courses($institute_id);
                $course_ids = array();

                foreach($institute_courses_count as $row)
                {
                    array_push($course_ids, $row['id']);
                }

                if (sizeof($course_ids)) {
                    $this->db->where_in('course_id', $course_ids);
                } else {
                    return array();
                }

                 $course_classes = $this->db->get('classes')->result_array();
                 if (count($course_classes) >= $plan['classes']){
                    $this->session->set_flashdata('error_message', get_phrase('you_inscrease_class_limit'));
                 }else{
                    $data_class['name'] = html_escape($this->input->post('name'));
                    $data_class['course_id'] = html_escape($this->input->post('courses'));
                    $data_class['date_added'] = strtotime(date('D, d-M-Y'));
                    $this->db->insert('classes', $data_class);
                    $this->session->set_flashdata('flash_message', get_phrase('class_added_successfully'));
                 }
            }else{
                $this->session->set_flashdata('error_message', get_phrase('class_not_created'));
            }

        }
    }

    public function edit_class($class_id = "")
    {
        // $validity_name = $this->check_name_duplication($this->input->post('name'));
        // if ($validity_name){
        $data_class['name'] = html_escape($this->input->post('name'));
        $data_class['course_id'] = html_escape($this->input->post('courses'));
        $data_class['last_modified'] = strtotime(date('D, d-M-Y'));
        $this->db->where('id', $class_id);
        $this->db->update('classes', $data_class);
        $this->session->set_flashdata('flash_message', get_phrase('class_update_successfully'));
        // }else{
        // $this->session->set_flashdata('error_message', get_phrase('class_name_duplication'));
        // }
    }

    public function delete_live_session($ls_id = "")
    {
        $this->db->where('id', $ls_id);
        $this->db->delete('live_sessiions');
        $this->session->set_flashdata('flash_message', get_phrase('live_session_deleted_successfully'));
    }

    public function get_classes()
    {
        $classes = $this->db->get('classes')->result_array();
        return $classes;
    }

    public function get_class_by_id($class_id){
        $this->db->where('id', $class_id);
        return $this->db->get('classes')->row_array();
    }

    public function check_name_duplication($name)
    {
        $duplicate_name_check = $this->db->get_where('classes', array('name' => $name));
        if ($duplicate_name_check->num_rows() > 0) {
            return false;
        } else {
            return true;
        }
    }

    //Get plans
    public function get_plans()
    {
        $query = $this->db->get('plans')->result_array();
        return $query;
    }

    public function get_public_plans()
    {
        $this->db->where('private', 'no');
        $query = $this->db->get('plans')->result_array();
        return $query;
    }

    public function get_all_plans()
    {
        $query = $this->db->get('plans')->result_array();
        return $query;
    }

    // Adding plan functionalities
    public function add_plan()
    {
        $data_plan['name'] = html_escape($this->input->post('name'));
        $data_plan['classes'] = html_escape($this->input->post('classes'));
        $data_plan['courses'] = html_escape($this->input->post('courses'));
        $data_plan['course_minutes'] = html_escape($this->input->post('course_minutes'));
        $data_plan['students'] = html_escape($this->input->post('students'));
        $data_plan['cloud_space'] = html_escape($this->input->post('cloud_space'));
        $data_plan['price'] = html_escape($this->input->post('price'));
        // $data_plan['institute_id'] = html_escape($this->input->post('institutes'));
        $data_plan['date_added'] = strtotime(date('D, d-M-Y'));
        $data_plan['private'] = html_escape($this->input->post('is_private'));
        $this->db->insert('plans', $data_plan);
        $this->session->set_flashdata('flash_message', get_phrase('plan_added_successfully'));
    }

    public function edit_plan($plan_id = "")
    {
        $data_plan['name'] = html_escape($this->input->post('name'));
        $data_plan['classes'] = html_escape($this->input->post('classes'));
        $data_plan['courses'] = html_escape($this->input->post('courses'));
        $data_plan['course_minutes'] = html_escape($this->input->post('course_minutes'));
        $data_plan['students'] = html_escape($this->input->post('students'));
        $data_plan['cloud_space'] = html_escape($this->input->post('cloud_space'));
        $data_plan['price'] = html_escape($this->input->post('price'));
        // $data_plan['institute_id'] = html_escape($this->input->post('institutes'));
        $data_plan['last_modified'] = strtotime(date('D, d-M-Y'));
        $data_plan['private'] = html_escape($this->input->post('is_private'));
        $this->db->where('id', $plan_id);
        $this->db->update('plans', $data_plan);
        $this->session->set_flashdata('flash_message', get_phrase('plan_update_successfully'));
    }

    public function delete_plan($plan_id = "")
    {
        $this->db->where('id', $plan_id);
        $this->db->delete('plans');
        $this->session->set_flashdata('flash_message', get_phrase('plan_deleted_successfully'));
    }

    public function check_duplication($action = "", $name = "", $institute_id = "")
    {
        $duplicate_name_check = $this->db->get_where('institutes', array('name' => $name));
        if ($action == 'on_create') {
            if ($duplicate_name_check->num_rows() > 0) {
                return false;
            } else {
                return true;
            }
        } elseif ($action == 'on_update') {
            if ($duplicate_name_check->num_rows() > 0) {
                if ($duplicate_name_check->row()->id == $institute_id) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        }
    }

    public function check_class_duplication($name = "")
    {
        $duplicate_name_check = $this->db->get_where('classes', array('name' => $name));
        if ($duplicate_name_check->num_rows() > 0) {
            return false;
        } else {
            return true;
        }
    }

    public function get_categories($param1 = "")
    {
        if ($param1 != "") {
            $this->db->where('id', $param1);
        }
        $this->db->where('parent', 0);
        return $this->db->get('category');
    }

    public function get_category_details_by_id($id)
    {
        return $this->db->get_where('category', array('id' => $id));
    }

    public function get_category_id($slug = "")
    {
        $category_details = $this->db->get_where('category', array('slug' => $slug))->row_array();
        return $category_details['id'];
    }

    public function add_category()
    {
        $data['code'] = html_escape($this->input->post('code'));
        $data['name'] = html_escape($this->input->post('name'));
        $data['parent'] = html_escape($this->input->post('parent'));
        $data['slug'] = slugify(html_escape($this->input->post('name')));
        if ($this->input->post('parent') == 0) {
            // Font awesome class adding
            if ($_POST['font_awesome_class'] != "") {
                $data['font_awesome_class'] = html_escape($this->input->post('font_awesome_class'));
            } else {
                $data['font_awesome_class'] = 'fas fa-chess';
            }

            // category thumbnail adding
            if (!file_exists('uploads/thumbnails/category_thumbnails')) {
                mkdir('uploads/thumbnails/category_thumbnails', 0777, true);
            }
            if ($_FILES['category_thumbnail']['name'] == "") {
                $data['thumbnail'] = 'category-thumbnail.png';
            } else {
                $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/thumbnails/category_thumbnails/' . $data['thumbnail']);
            }
        }
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $this->db->insert('category', $data);
    }

    public function edit_category($param1)
    {
        $data['name'] = html_escape($this->input->post('name'));
        $data['parent'] = html_escape($this->input->post('parent'));
        $data['slug'] = slugify(html_escape($this->input->post('name')));
        if ($this->input->post('parent') == 0) {
            // Font awesome class adding
            if ($_POST['font_awesome_class'] != "") {
                $data['font_awesome_class'] = html_escape($this->input->post('font_awesome_class'));
            } else {
                $data['font_awesome_class'] = 'fas fa-chess';
            }
            // category thumbnail adding
            if (!file_exists('uploads/thumbnails/category_thumbnails')) {
                mkdir('uploads/thumbnails/category_thumbnails', 0777, true);
            }
            if ($_FILES['category_thumbnail']['name'] != "") {
                $data['thumbnail'] = md5(rand(10000000, 20000000)) . '.jpg';
                move_uploaded_file($_FILES['category_thumbnail']['tmp_name'], 'uploads/thumbnails/category_thumbnails/' . $data['thumbnail']);
            }
        }
        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $this->db->where('id', $param1);
        $this->db->update('category', $data);
    }

    public function delete_category($category_id)
    {
        $this->db->where('id', $category_id);
        $this->db->delete('category');
    }

    public function get_sub_categories($parent_id = "")
    {
        return $this->db->get_where('category', array('parent' => $parent_id))->result_array();
    }

    public function enrol_history($course_id = "")
    {
        if ($course_id > 0) {
            return $this->db->get_where('enrol', array('course_id' => $course_id));
        } else {
            return $this->db->get('enrol');
        }
    }

    public function enrol_history_by_user_id($user_id = "")
    {
        return $this->db->get_where('enrol', array('user_id' => $user_id));
    }

    public function all_enrolled_student()
    {
        $this->db->select('user_id');
        $this->db->distinct('user_id');
        return $this->db->get('enrol');
    }

    public function enrol_history_by_date_range($timestamp_start = "", $timestamp_end = "")
    {
        $this->db->order_by('date_added', 'desc');
        $this->db->where('date_added >=', $timestamp_start);
        $this->db->where('date_added <=', $timestamp_end);
        return $this->db->get('enrol');
    }

    public function get_revenue_by_user_type($timestamp_start = "", $timestamp_end = "", $revenue_type = "")
    {
        $course_ids = array();
        $courses = array();
        $admin_details = $this->user_model->get_admin_details()->row_array();
        if ($revenue_type == 'admin_revenue') {
            $this->db->where('date_added >=', $timestamp_start);
            $this->db->where('date_added <=', $timestamp_end);
        } elseif ($revenue_type == 'instructor_revenue') {
            $this->db->where('user_id !=', $admin_details['id']);
            $this->db->select('id');
            $courses = $this->db->get('course')->result_array();
            foreach ($courses as $course) {
                if (!in_array($course['id'], $course_ids)) {
                    array_push($course_ids, $course['id']);
                }
            }
            if (sizeof($course_ids)) {
                $this->db->where_in('course_id', $course_ids);
            } else {
                return array();
            }
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('payment')->result_array();
    }

    public function get_instructor_revenue()
    {
        $course_ids = array();
        $courses = array();

        $this->db->where('user_id', $this->session->userdata('user_id'));
        $this->db->select('id');
        $courses = $this->db->get('course')->result_array();
        foreach ($courses as $course) {
            if (!in_array($course['id'], $course_ids)) {
                array_push($course_ids, $course['id']);
            }
        }
        if (sizeof($course_ids)) {
            $this->db->where_in('course_id', $course_ids);
        } else {
            return array();
        }

        $this->db->order_by('date_added', 'desc');
        return $this->db->get('payment')->result_array();
    }

    public function delete_payment_history($param1)
    {
        $this->db->where('id', $param1);
        $this->db->delete('payment');
    }
    public function delete_enrol_history($param1)
    {
        $this->db->where('id', $param1);
        $this->db->delete('enrol');
    }

    public function purchase_history($user_id)
    {
        if ($user_id > 0) {
            return $this->db->get_where('payment', array('user_id' => $user_id));
        } else {
            return $this->db->get('payment');
        }
    }

    public function get_payment_details_by_id($payment_id = "")
    {
        return $this->db->get_where('payment', array('id' => $payment_id))->row_array();
    }

    public function update_instructor_payment_status($payment_id = "")
    {
        $updater = array(
            'instructor_payment_status' => 1,
        );
        $this->db->where('id', $payment_id);
        $this->db->update('payment', $updater);
    }

    public function update_system_settings()
    {
        $data['value'] = html_escape($this->input->post('system_name'));
        $this->db->where('key', 'system_name');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('system_title'));
        $this->db->where('key', 'system_title');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('author'));
        $this->db->where('key', 'author');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('slogan'));
        $this->db->where('key', 'slogan');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('language'));
        $this->db->where('key', 'language');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('text_align'));
        $this->db->where('key', 'text_align');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('system_email'));
        $this->db->where('key', 'system_email');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('address'));
        $this->db->where('key', 'address');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('phone'));
        $this->db->where('key', 'phone');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('youtube_api_key'));
        $this->db->where('key', 'youtube_api_key');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('vimeo_api_key'));
        $this->db->where('key', 'vimeo_api_key');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('purchase_code'));
        $this->db->where('key', 'purchase_code');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('footer_text'));
        $this->db->where('key', 'footer_text');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('footer_link'));
        $this->db->where('key', 'footer_link');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('website_keywords'));
        $this->db->where('key', 'website_keywords');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('website_description'));
        $this->db->where('key', 'website_description');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('student_email_verification'));
        $this->db->where('key', 'student_email_verification');
        $this->db->update('settings', $data);
    }

    public function update_smtp_settings()
    {
        $data['value'] = html_escape($this->input->post('protocol'));
        $this->db->where('key', 'protocol');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_host'));
        $this->db->where('key', 'smtp_host');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_port'));
        $this->db->where('key', 'smtp_port');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_user'));
        $this->db->where('key', 'smtp_user');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('smtp_pass'));
        $this->db->where('key', 'smtp_pass');
        $this->db->update('settings', $data);
    }

    public function update_paypal_settings()
    {
        // update paypal keys
        $paypal_info = array();
        $paypal['active'] = $this->input->post('paypal_active');
        $paypal['mode'] = $this->input->post('paypal_mode');
        $paypal['sandbox_client_id'] = $this->input->post('sandbox_client_id');
        $paypal['sandbox_secret_key'] = $this->input->post('sandbox_secret_key');

        $paypal['production_client_id'] = $this->input->post('production_client_id');
        $paypal['production_secret_key'] = $this->input->post('production_secret_key');

        array_push($paypal_info, $paypal);

        $data['value'] = json_encode($paypal_info);
        $this->db->where('key', 'paypal');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('paypal_currency'));
        $this->db->where('key', 'paypal_currency');
        $this->db->update('settings', $data);
    }

    public function update_stripe_settings()
    {
        // update stripe keys
        $stripe_info = array();

        $stripe['active'] = $this->input->post('stripe_active');
        $stripe['testmode'] = $this->input->post('testmode');
        $stripe['public_key'] = $this->input->post('public_key');
        $stripe['secret_key'] = $this->input->post('secret_key');
        $stripe['public_live_key'] = $this->input->post('public_live_key');
        $stripe['secret_live_key'] = $this->input->post('secret_live_key');

        array_push($stripe_info, $stripe);

        $data['value'] = json_encode($stripe_info);
        $this->db->where('key', 'stripe_keys');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('stripe_currency'));
        $this->db->where('key', 'stripe_currency');
        $this->db->update('settings', $data);
    }

    public function update_system_currency()
    {
        $data['value'] = html_escape($this->input->post('system_currency'));
        $this->db->where('key', 'system_currency');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('currency_position'));
        $this->db->where('key', 'currency_position');
        $this->db->update('settings', $data);
    }

    public function update_instructor_settings()
    {
        $data['value'] = html_escape($this->input->post('allow_instructor'));
        $this->db->where('key', 'allow_instructor');
        $this->db->update('settings', $data);

        $data['value'] = html_escape($this->input->post('instructor_revenue'));
        $this->db->where('key', 'instructor_revenue');
        $this->db->update('settings', $data);
    }

    public function get_lessons($type = "", $id = "")
    {
        $this->db->order_by("order", "asc");
        if ($type == "course") {
            return $this->db->get_where('lesson', array('course_id' => $id));
        } elseif ($type == "section") {
            return $this->db->get_where('lesson', array('section_id' => $id));
        } elseif ($type == "lesson") {
            return $this->db->get_where('lesson', array('id' => $id));
        } else {
            return $this->db->get('lesson');
        }
    }

    public function insert_live_session(){
        $offset = 5 * 60 * 60;
        $course_id = $this->input->post('course_id');
        $timezone = $this->input->post('timezone_offset');
        $session_start_time = $this->input->post('start_session');
        $session_end_time = $this->input->post('end_session');
        $session_start_time =  strtotime($session_start_time .$timezone."hours");
        $session_end_time =  strtotime($session_end_time .$timezone."hours");
       $class_id = $this->input->post('live_session_class');
       $minutes = $this->input->post('time');
       $current_class = $this->db->get_where('classes', array('id' => $class_id))->row_array();
       $course = $this->db->get_where('course', array('id' => $current_class['course_id']))->row_array();
       if ($course['status'] == 'active') {
           $course_instructor = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
           $institute = $this->user_model->get_single_institute($course_instructor['institute_id']);
           $plan = $this->check_plan($institute['id'])->row_array();
           $remaining_minutes = $plan['remaining_minutes'];
           if ($remaining_minutes > 0 && $remaining_minutes >= $minutes) {
               $data['course_id'] = $course_id;
               $data['class_id'] = html_escape($class_id);
               $data['name'] = html_escape($this->input->post('session_name'));
               $data['mints'] = html_escape($minutes);
               $data['date_added'] = strtotime(date('D, d-M-Y'));
               $data['start_time'] = $session_start_time;
               $data['end_time'] = $session_end_time;
               $data['status'] = 1;
               $data['timezone'] = $timezone;
               $this->db->insert('live_sessions', $data);
               $this->update_plan_minutes($plan['id'], $remaining_minutes, $minutes);
               $this->session->set_flashdata('flash_message', get_phrase('live_session_successfully_created'));
           } else {
               $this->session->set_flashdata('error_message', get_phrase('you_have_only '.$remaining_minutes.' remaining_minutes'));
           }
       }
       else{
        $this->session->set_flashdata('error_message', get_phrase('course_is_not_active_please_contact_with_adminstration'));
       }
       
    }

    public function update_plan_minutes($plan_id, $remaining_minutes, $minutes){
        $plan['remaining_minutes'] = $remaining_minutes - $minutes;
        $this->db->where('id', $plan_id);
        $this->db->update('purchased_plans', $plan);
    }
    
    function get_create_url($meeting_id, $name, $mins){
        $query_str = 'name='.$name.'&meetingID='.$meeting_id.'&attendeePW=111222&moderatorPW=333444&allowStartStopRecording=true&autoStartRecording=false&duration='.$mins;
        $query_secret = 'createname='.$name.'&meetingID='.$meeting_id.'&attendeePW=111222&moderatorPW=333444&allowStartStopRecording=true&autoStartRecording=false&duration='.$mins.$_ENV["shared_secret"];
        $sh1_checksum = sha1($query_secret);
        $name_str = 'https://dynamiclogicltd.info/bigbluebutton/api/create?'.$query_str.'&checksum='.$sh1_checksum;
        return $name_str;
        // $name='Test+Meeting&meetingID='.$meeting_id.'&attendeePW=111222&moderatorPW=333444';
    } 
    function get_moderator_url($meeting_id,$current_instructor_name, $live_session_id){
        $name = 'fullName='.$current_instructor_name.'&meetingID='.$meeting_id.'&password=333444&redirect=true';
        $query_secret = 'joinfullName='.$current_instructor_name.'&meetingID='.$meeting_id.'&password=333444&redirect=true'.$_ENV["shared_secret"];
        $sh1_checksum = sha1($query_secret);
        $name = 'https://dynamiclogicltd.info/bigbluebutton/api/join?'.$name.'&checksum='.$sh1_checksum;

        $data['checksum'] = $sh1_checksum;
        $this->db->where('id', $live_session_id);
        $this->db->update('live_sessions', $data);

        return $name;
    } 
    function get_student_url($meeting_id,$current_instructor_name){
        $name = 'fullName='.$current_instructor_name.'&meetingID='.$meeting_id.'&password=111222&redirect=true';
        $query_secret = 'joinfullName='.$current_instructor_name.'&meetingID='.$meeting_id.'&password=111222&redirect=true'.$_ENV["shared_secret"];
        // $query_secret = 'joinfullName='.$current_instructor_name.'&meetingID='.$meeting_id.'&password=111222&redirect=true'.$_ENV["shared_secret"];
        $sh1_checksum = sha1($query_secret);
        $name = 'https://dynamiclogicltd.info/bigbluebutton/api/join?'.$name.'&checksum='.$sh1_checksum;
        return $name;
    }
    public function create_live_session($current_instructor_name, $student_list, $live_session){
       
       $meeting_id = (rand(100,100000));
       $live_session_id = $live_session['id'];
       $data['meeting_id'] = $meeting_id;
       $url = $this->get_create_url($meeting_id,'DynamicLogic', $live_session['mins']+15);
       $institute_url =$this->get_moderator_url($meeting_id, $current_instructor_name,  $live_session_id);
       $student_urls = [];
       foreach ($student_list as $student) 
       {
           
            $student_url =$this->get_student_url($meeting_id, $student['first_name']);  
            array_push($student_urls, $student_url);
       }

    
    //    $url = 'https://dynamiclogicltd.info/bigbluebutton/api/create?allowStartStopRecording=true&attendeePW=ap&autoStartRecording=false&meetingID=meeting-room-2256245&moderatorPW=mp&name=meeting-room-2256245&record=false&voiceBridge=73424&welcome=%3Cbr%3EWelcome+to+%3Cb%3E%25%25CONFNAME%25%25%3C%2Fb%3E%21&checksum=eb8582046c4c0575d04380b58fe42bf63e38f600';
    //    $institute_url = 'https://dynamiclogicltd.info/bigbluebutton/api/join?fullName=User+4576832&meetingID=meeting-room-2256245&password=mp&redirect=true&checksum=3dd5db03cd89407e4206357ab811c55d55e0dc1a';
    //    $student_url = 'https://dynamiclogicltd.info/bigbluebutton/api/join?fullName=User+4576832&meetingID=meeting-room-2256245&password=ap&redirect=true&checksum=38a15d8d41739cc42c3ceedb85345a54ce4d826c';
       $timeout = 10;
       $ch = curl_init();
       curl_setopt ( $ch, CURLOPT_URL, $url );
       curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
       curl_setopt ( $ch, CURLOPT_TIMEOUT, $timeout );
       $http_respond = curl_exec($ch);
       $http_respond = trim( strip_tags( $http_respond ) );
       $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
       curl_close( $ch ); 
       if ( ( $http_code == "200" ) || ( $http_code == "302" ) ) {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);

        $this->db->where('id', $live_session_id);
        $live_session=$this->db->update('live_sessions', $data);

         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $response = curl_exec($ch);
         $xml = simplexml_load_string($response);
         if($xml->returncode == 'SUCCESS'){
            // $this->db->insert('live_sessions', $data);
            $page_data['page_name'] = 'live_session_url_popup';
            $page_data['admin_url'] = $institute_url;
            $page_data['student_urls'] = $student_urls;
            // $page_data['role'] = $logged_in_user_role;
            return $page_data;
         }else{
            return -1;
         }
         curl_close($ch);
       } else {
         $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL, $url);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         $response = curl_exec($ch);
         $xml = simplexml_load_string($response);
         echo $xml->internalMeetingID;
         curl_close($ch);
         return -1;
       }
    }
    public function add_course($param1 = "", $user_param = 0)
    {
        $institute_id = $this->input->post('institutes');
        $course_validity = $this->check_institute_course_limit($institute_id);
        if ($course_validity == false){
            $this->session->set_flashdata('error_message', get_phrase('you_inscrease_course_limit'));
        }else{
            $outcomes = $this->trim_and_return_json($this->input->post('outcomes'));
            $requirements = $this->trim_and_return_json($this->input->post('requirements'));

            $data['title'] = html_escape($this->input->post('title'));
            $data['short_description'] = $this->input->post('short_description');
            $data['description'] = $this->input->post('description');
            $data['outcomes'] = $outcomes;
            $data['language'] = $this->input->post('language_made_in');
            $data['sub_category_id'] = $this->input->post('sub_category_id');
            $category_details = $this->get_category_details_by_id($this->input->post('sub_category_id'))->row_array();
            $data['category_id'] = $category_details['parent'];
            $data['requirements'] = $requirements;
            $data['price'] = $this->input->post('price');
            $data['type'] = $this->input->post('type');
            $data['discount_flag'] = $this->input->post('discount_flag');
            $data['discounted_price'] = $this->input->post('discounted_price');
            $data['level'] = $this->input->post('level');
            $data['is_free_course'] = $this->input->post('is_free_course');
            $data['video_url'] = html_escape($this->input->post('course_overview_url'));
            $type = $this->input->post('type');
            if ($this->input->post('course_overview_url') != "") {
                $data['course_overview_provider'] = html_escape($this->input->post('course_overview_provider'));
            } else {
                $data['course_overview_provider'] = "";
            }

            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $data['section'] = json_encode(array());
            $data['is_top_course'] = $this->input->post('is_top_course');
            if ($user_param > 0) {
                if ($user_param != '') {
                    $data['user_id'] = $user_param;
                }
            } else {
                $data['user_id'] = $this->session->userdata('user_id');
            }
            $data['meta_description'] = $this->input->post('meta_description');
            $data['meta_keywords'] = $this->input->post('meta_keywords');
            $data['instructor_id'] = $this->input->post('instructors');
            $data['institute_id'] = $institute_id;
            $admin_details = $this->user_model->get_admin_details()->row_array();
            if ($admin_details['id'] == $this->session->userdata('user_id')) {
                $data['is_admin'] = 1;
            } else {
                $data['is_admin'] = 0;
            }
            if ($param1 == "save_to_draft") {
                $data['status'] = 'draft';
            } else {
                if ($this->session->userdata('admin_login')) {
                    $data['status'] = 'active';
                } else {
                    if ($user_param > 0) {
                        
                        if ($type == 'private') {
                            $data['status'] = 'active';
                        } else {
                            $data['status'] = 'pending';
                        }
                    }
                }
            }
            $this->db->insert('course', $data);

            $course_id = $this->db->insert_id();
            // Create folder if does not exist
            if (!file_exists('uploads/thumbnails/course_thumbnails')) {
                mkdir('uploads/thumbnails/course_thumbnails', 0777, true);
            }

            // Upload different number of images according to activated theme. Data is taking from the config.json file
            $course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
            foreach ($course_media_files as $course_media => $size) {
                if ($_FILES[$course_media]['name'] != "") {
                    move_uploaded_file($_FILES[$course_media]['tmp_name'], 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg');
                }
            }

            if ($data['status'] == 'approved') {
                $this->session->set_flashdata('flash_message', get_phrase('course_added_successfully'));
            } elseif ($data['status'] == 'pending') {
                $this->session->set_flashdata('flash_message', get_phrase('course_added_successfully') . '. ' . get_phrase('please_wait_untill_Admin_approves_it'));
            } elseif ($data['status'] == 'draft') {
                $this->session->set_flashdata('flash_message', get_phrase('your_course_has_been_added_to_draft'));
            }

            $this->session->set_flashdata('flash_message', get_phrase('course_has_been_added_successfully'));
            return $course_id;
        }
    }

    public function check_plan($user_id){
        return $this->db->get_where('purchased_plans', array('user_id' => $user_id));
    }

    public function check_institute_course_limit($institute_id=''){
         if ($this->session->userdata('role_name') == 'admin' && $institute_id ==''){
           $this->session->set_flashdata('error_message', get_phrase('please_choose_the_institute'));
           redirect(site_url('admin/course_form/add_course'), 'refresh');
         }
           if ($institute_id == ''){
               $institute_id = $this->session->userdata('user_id');
           }
           $institute = $this->user_model->get_single_institute($institute_id);
           $plan = $this->check_plan($institute['id'])->row_array();
           if($plan){
             $institute_id = $institute['id'];
             if($institute_id > 0){
                 $institute_courses_count = $this->count_institute_courses($institute_id);
                 if ($plan['courses'] > 0){
                   if (count($institute_courses_count) >= $plan['courses']){
                   return false;
                   }else{
                       return true;
                   }
                 }else {
                   $this->session->set_flashdata('error_message', get_phrase('please_choose_a_plan'));
                   redirect(site_url('admin/course_form/add_course'), 'refresh');
                 }
             }else{
               $this->session->set_flashdata('error_message', get_phrase('institute_not_found'));
             }
         }else{
           $this->session->set_flashdata('error_message', get_phrase('please_choose_a_plan'));
           redirect(site_url('admin/course_form/add_course'), 'refresh');
         }

       }

       public function check_institute_membory_limit($institute_id=''){
        if ($this->session->userdata('role_name') == 'admin' && $institute_id ==''){
          $this->session->set_flashdata('error_message', get_phrase('please_choose_the_institute'));
          redirect(site_url('admin/course_form/add_course'), 'refresh');
        }
          if ($institute_id == ''){
              $institute_id = $this->session->userdata('user_id');
          }
          $institute = $this->user_model->get_single_institute($institute_id);
          $plan = $this->user_model->get_plan_by_id($institute['plan_id'])->row_array();
          if($institute['plan_id'] == $plan['id']){
            $institute_id = $institute['id'];
            if($institute_id > 0){
                $count_sizes = 0.0;
                $institute_courses_count = $this->count_institute_courses($institute_id);
                foreach ($institute_courses_count as $row) {
                    $count_sizes += $row['video_size'];
                }
                if ($plan['courses'] > 0){
                    $plan_space = $plan['cloud_space'] ** 1024;
                  if ($count_sizes >= $plan_space){
                  return false;
                  }else{
                      return true;
                  }
                }else {
                  $this->session->set_flashdata('error_message', get_phrase('You_donot_have_more_space'));
                  redirect(site_url('admin/course_form/add_course'), 'refresh');
                }
            }else{
              $this->session->set_flashdata('error_message', get_phrase('institute_not_found'));
            }
        }else{
          $this->session->set_flashdata('error_message', get_phrase('you_can not_purchased_any_plan'));
          redirect(site_url('admin/course_form/add_course'), 'refresh');
        }

      }


    public function trim_and_return_json($untrimmed_array)
    {
        $trimmed_array = array();
        if (sizeof($untrimmed_array) > 0) {
            foreach ($untrimmed_array as $row) {
                if ($row != "") {
                    array_push($trimmed_array, $row);
                }
            }
        }
        return json_encode($trimmed_array);
    }

    public function update_course($course_id, $type = "")
    {
        $course_details = $this->get_course_by_id($course_id)->row_array();

        $outcomes = $this->trim_and_return_json($this->input->post('outcomes'));
        $requirements = $this->trim_and_return_json($this->input->post('requirements'));
        $data['title'] = $this->input->post('title');
        $data['short_description'] = $this->input->post('short_description');
        $data['description'] = $this->input->post('description');
        $data['outcomes'] = $outcomes;
        $data['language'] = $this->input->post('language_made_in');
        $data['sub_category_id'] = $this->input->post('sub_category_id');
        $category_details = $this->get_category_details_by_id($this->input->post('sub_category_id'))->row_array();
        $data['category_id'] = $category_details['parent'];
        $data['requirements'] = $requirements;
        $data['is_free_course'] = $this->input->post('is_free_course');
        $type = $this->input->post('type');
        $data['price'] = $this->input->post('price');
        $data['discount_flag'] = $this->input->post('discount_flag');
        $data['discounted_price'] = $this->input->post('discounted_price');
        $data['level'] = $this->input->post('level');
        $data['video_url'] = $this->input->post('course_overview_url');

        if ($this->input->post('course_overview_url') != "") {
            $data['course_overview_provider'] = html_escape($this->input->post('course_overview_provider'));
        } else {
            $data['course_overview_provider'] = "";
        }

        $data['meta_description'] = $this->input->post('meta_description');
        $data['meta_keywords'] = $this->input->post('meta_keywords');
        $data['instructor_id'] = $this->input->post('instructors');
        $data['institute_id'] = $this->input->post('institutes');
        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $data['type'] = $type;

        if ($this->input->post('is_top_course') != 1) {
            $data['is_top_course'] = 0;
        } else {
            $data['is_top_course'] = 1;
        }

        if ($type == "save_to_draft") {
            $data['status'] = 'draft';
        } else {
            // if ($this->session->userdata('admin_login')) {
            //     $data['status'] = 'active';
            // } else {
            //     $data['status'] = $course_details['status'];
            // }
            if($type == 'public'){
                $data['status'] = 'pending';
            }else{
                $data['status'] = $course_details['status'];
            }
        }
        $this->db->where('id', $course_id);
        $this->db->update('course', $data);

        // Upload different number of images according to activated theme. Data is taking from the config.json file
        $course_media_files = themeConfiguration(get_frontend_settings('theme'), 'course_media_files');
        foreach ($course_media_files as $course_media => $size) {
            if ($_FILES[$course_media]['name'] != "") {
                move_uploaded_file($_FILES[$course_media]['tmp_name'], 'uploads/thumbnails/course_thumbnails/' . $course_media . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg');
            }
        }

        if ($data['status'] == 'active') {
            $this->session->set_flashdata('flash_message', get_phrase('course_updated_successfully'));
        } elseif ($data['status'] == 'pending') {
            $this->session->set_flashdata('flash_message', get_phrase('course_updated_successfully') . '. ' . get_phrase('please_wait_untill_Admin_approves_it'));
        } elseif ($data['status'] == 'draft') {
            $this->session->set_flashdata('flash_message', get_phrase('your_course_has_been_added_to_draft'));
        }
    }

    public function change_course_status($status = "", $course_id = "")
    {
        if ($status == 'active') {
            if ($this->session->userdata('admin_login') != true) {
                redirect(site_url('login'), 'refresh');
            }
        }
        $updater = array(
            'status' => $status,
        );
        $this->db->where('id', $course_id);
        $this->db->update('course', $updater);
    }

    public function change_course_type($type = "", $course_id = "")
    {
        if ($type == 'public') {
            $updater = array(
                'type' => $type,
                'status' => 'pending',
            );
        }else{
            $updater = array(
                'type' => $type,
            );
        }
        $this->db->where('id', $course_id);
        $this->db->update('course', $updater);
    }

    public function get_course_thumbnail_url($course_id, $type = 'course_thumbnail')
    {
        // Course media placeholder is coming from the theme config file. Which has all the placehoder for different images. Choose like course type.
        $course_media_placeholders = themeConfiguration(get_frontend_settings('theme'), 'course_media_placeholders');
        // if (file_exists('uploads/thumbnails/course_thumbnails/'.$type.'_'.get_frontend_settings('theme').'_'.$course_id.'.jpg')){
        //     return base_url().'uploads/thumbnails/course_thumbnails/'.$type.'_'.get_frontend_settings('theme').'_'.$course_id.'.jpg';
        // } elseif(file_exists('uploads/thumbnails/course_thumbnails/'.$course_id.'.jpg')){
        //     return base_url().'uploads/thumbnails/course_thumbnails/'.$course_id.'.jpg';
        // } else{
        //     return $course_media_placeholders[$type.'_placeholder'];
        // }
        if (file_exists('uploads/thumbnails/course_thumbnails/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg')) {
            return base_url() . 'uploads/thumbnails/course_thumbnails/' . $type . '_' . get_frontend_settings('theme') . '_' . $course_id . '.jpg';
        } else {
            return base_url() . $course_media_placeholders[$type . '_placeholder'];
        }
    }
    public function get_lesson_thumbnail_url($lesson_id)
    {

        if (file_exists('uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg')) {
            return base_url() . 'uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg';
        } else {
            return base_url() . 'uploads/thumbnails/thumbnail.png';
        }

    }

    public function get_my_courses_by_category_id($category_id)
    {
        $this->db->select('course_id');
        $course_lists_by_enrol = $this->db->get_where('enrol', array('user_id' => $this->session->userdata('user_id')))->result_array();
        $course_ids = array();
        foreach ($course_lists_by_enrol as $row) {
            if (!in_array($row['course_id'], $course_ids)) {
                array_push($course_ids, $row['course_id']);
            }
        }
        $this->db->where_in('id', $course_ids);
        $this->db->where('category_id', $category_id);
        return $this->db->get('course');
    }

    public function get_my_courses_by_search_string($search_string)
    {
        $this->db->select('course_id');
        $course_lists_by_enrol = $this->db->get_where('enrol', array('user_id' => $this->session->userdata('user_id')))->result_array();
        $course_ids = array();
        foreach ($course_lists_by_enrol as $row) {
            if (!in_array($row['course_id'], $course_ids)) {
                array_push($course_ids, $row['course_id']);
            }
        }
        $this->db->where_in('id', $course_ids);
        $this->db->like('title', $search_string);
        return $this->db->get('course');
    }

    public function get_courses_by_search_string($search_string)
    {
        $this->db->like('title', $search_string);
        $this->db->where('status', 'active');
        return $this->db->get('course');
    }

    public function get_course_by_id($course_id = "")
    {
        return $this->db->get_where('course', array('id' => $course_id));
    }
    public function get_plan_by_id($plan_id = "")
    {
        return $this->db->get_where('plans', array('id' => $plan_id))->row_array();
    }

    public function delete_course($course_id)
    {
        $this->db->where('id', $course_id);
        $this->db->delete('course');
    }

    public function get_top_courses()
    {
        return $this->db->get_where('course', array('is_top_course' => 1, 'status' => 'active', 'type', 'public'));
    }

    public function get_default_category_id()
    {
        $categories = $this->get_categories()->result_array();
        foreach ($categories as $category) {
            return $category['id'];
        }
    }

    public function get_courses_by_user_id($param1 = "")
    {
        $courses['draft'] = $this->db->get_where('course', array('user_id' => $param1, 'status' => 'draft'));
        $courses['pending'] = $this->db->get_where('course', array('user_id' => $param1, 'status' => 'pending'));
        $courses['active'] = $this->db->get_where('course', array('user_id' => $param1, 'status' => 'active'));
        return $courses;
    }

    public function get_status_wise_courses($status = "")
    {
        if ($status != "") {
            $courses = $this->db->get_where('course', array('status' => $status));
        } else {
            $courses['draft'] = $this->db->get_where('course', array('status' => 'draft'));
            $courses['pending'] = $this->db->get_where('course', array('status' => 'pending'));
            $courses['active'] = $this->db->get_where('course', array('status' => 'active'));
        }
        return $courses;
    }

    public function get_status_wise_courses_for_instructor($status = "")
    {
        if ($status != "") {
            $this->db->where('status', $status);
            $this->db->where('user_id', $this->session->userdata('user_id'));
            $courses = $this->db->get('course');
        } else {
            $this->db->where('status', 'draft');
            $this->db->where('user_id', $this->session->userdata('user_id'));
            $courses['draft'] = $this->db->get('course');

            $this->db->where('user_id', $this->session->userdata('user_id'));
            $this->db->where('status', 'draft');
            $courses['pending'] = $this->db->get('course');

            $this->db->where('status', 'draft');
            $this->db->where('user_id', $this->session->userdata('user_id'));
            $courses['active'] = $this->db->get_where('course');
        }
        return $courses;
    }

    public function get_default_sub_category_id($default_cateegory_id)
    {
        $sub_categories = $this->get_sub_categories($default_cateegory_id);
        foreach ($sub_categories as $sub_category) {
            return $sub_category['id'];
        }
    }

    public function get_instructor_wise_courses($instructor_id = "", $return_as = "")
    {
        $courses = $this->db->get_where('course', array('user_id' => $instructor_id));
        if ($return_as == 'simple_array') {
            $array = array();
            foreach ($courses->result_array() as $course) {
                if (!in_array($course['id'], $array)) {
                    array_push($array, $course['id']);
                }
            }
            return $array;
        } else {
            return $courses;
        }
    }

    public function get_instructor_wise_payment_history($instructor_id = "")
    {
        $courses = $this->get_instructor_wise_courses($instructor_id, 'simple_array');
        if (sizeof($courses) > 0) {
            $this->db->where_in('course_id', $courses);
            return $this->db->get('payment')->result_array();
        } else {
            return array();
        }
    }

    public function add_section($course_id)
    {
        $data['title'] = html_escape($this->input->post('title'));
        $data['course_id'] = $course_id;
        $this->db->insert('section', $data);
        $section_id = $this->db->insert_id();

        $course_details = $this->get_course_by_id($course_id)->row_array();
        $previous_sections = json_decode($course_details['section']);

        if (sizeof($previous_sections) > 0) {
            array_push($previous_sections, $section_id);
            $updater['section'] = json_encode($previous_sections);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        } else {
            $previous_sections = array();
            array_push($previous_sections, $section_id);
            $updater['section'] = json_encode($previous_sections);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        }
    }

    public function edit_section($section_id)
    {
        $data['title'] = $this->input->post('title');
        $this->db->where('id', $section_id);
        $this->db->update('section', $data);
    }

    public function delete_section($course_id, $section_id)
    {
        $this->db->where('id', $section_id);
        $this->db->delete('section');

        $course_details = $this->get_course_by_id($course_id)->row_array();
        $previous_sections = json_decode($course_details['section']);

        if (sizeof($previous_sections) > 0) {
            $new_section = array();
            for ($i = 0; $i < sizeof($previous_sections); $i++) {
                if ($previous_sections[$i] != $section_id) {
                    array_push($new_section, $previous_sections[$i]);
                }
            }
            $updater['section'] = json_encode($new_section);
            $this->db->where('id', $course_id);
            $this->db->update('course', $updater);
        }
    }

    public function get_section($type_by, $id)
    {
        $this->db->order_by("order", "asc");
        if ($type_by == 'course') {
            return $this->db->get_where('section', array('course_id' => $id));
        } elseif ($type_by == 'section') {
            return $this->db->get_where('section', array('id' => $id));
        }
    }

    public function serialize_section($course_id, $serialization)
    {
        $updater = array(
            'section' => $serialization,
        );
        $this->db->where('id', $course_id);
        $this->db->update('course', $updater);
    }

    public function add_lesson( $institute_name = '')
    {
        $course_id = html_escape($this->input->post('course_id'));
        $data['course_id'] = $course_id;
        $course = $this->db->get_where('course', array('id' => $course_id))->row_array();
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $lesson_type_array = explode('-', $this->input->post('lesson_type'));
        $lesson_type = $lesson_type_array[0];
    
        $data['attachment_type'] = $lesson_type_array[1];
        $data['lesson_type'] = $lesson_type;

        if ($lesson_type == 'video') {
            // This portion is for web application's video lesson
            $lesson_provider = $this->input->post('lesson_provider');
            if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
                if ($this->input->post('video_url') == "" || $this->input->post('duration') == "") {
                    $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->input->post('video_url'));

                $duration_formatter = explode(':', $this->input->post('duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;

                $video_details = $this->video_model->getVideoDetails($data['video_url']);
                $data['video_type'] = $video_details['provider'];
            } elseif ($lesson_provider == 'html5') {
                if ($this->input->post('html5_video_url') == "" || $this->input->post('html5_duration') == "") {
                    $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->input->post('html5_video_url'));
                $duration_formatter = explode(':', $this->input->post('html5_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'html5';
            }elseif($lesson_provider == 's3'){
                if($course['title']){
                    $institute_name =$institute_name .'/'. $course['title'];
                }
                $space_validity = $this->check_institute_membory_limit($course['institute_id']);
                if ($space_validity == false){
                    $this->session->set_flashdata('error_message', get_phrase('You do not have more storage'));
                }else{
                    $video_size = $_FILES['video_file_for_amazon_s3']['size'];
                    
                    if ($video_size > 1024){
                    $video_kb_size = round($video_size / 1024, 4);
                    }

                    $course_instructor = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
                    $institute = $this->user_model->get_single_institute($course_instructor['institute_id']);
                    $plan = $this->check_plan($institute['id'])->row_array();
                    $cloud_space = $plan['remaining_cloud_space'];
                    if ($cloud_space >= $video_kb_size){

                          // SET MAXIMUM EXECUTION TIME 600
                            ini_set('max_execution_time', '600');
                
                            $fileName = $_FILES['video_file_for_amazon_s3']['name'];
                            $tmp = explode('.', $fileName);
                            $fileExtension = strtoupper(end($tmp));
                            $video_extensions = ['FLV', 'MP4', 'WMV','AVI', 'MOV'];
                            if (!in_array($fileExtension, $video_extensions)) {
                                $this->session->set_flashdata('error_message', get_phrase('please_select_valid_video_file.'));
                                redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                            }
                
                            if ($this->input->post('amazon_s3_duration') == "") {
                                $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_duration'));
                                redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                            }
                
                            $tmpfile = $_FILES['video_file_for_amazon_s3'];
                            $tmppath = $_FILES['video_file_for_amazon_s3']['tmp_name'];
                            $s3_model = new S3_model();
                            $s3= $s3_model->create_s3_object();
                            $key = str_replace(".", "-" . rand(1, 9999) . ".", $tmpfile['name']);
                            $result = $s3_model->upload_data($s3,$key ,$tmppath, $fileExtension,  $institute_name);
                            $data['video_url'] = $result['ObjectURL'];
                            $data['video_type'] = 'amazon';
                            $data['video_size'] = $video_kb_size;
                            $data['lesson_type'] = 'video';
                            $data['attachment_type'] = 'url';
                
                            $duration_formatter = explode(':', $this->input->post('amazon_s3_duration'));
                            $hour = sprintf('%02d', $duration_formatter[0]);
                            $min = sprintf('%02d', $duration_formatter[1]);
                            $sec = sprintf('%02d', $duration_formatter[2]);
                            $data['duration'] = $hour . ':' . $min . ':' . $sec;
                
                            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
                            $data['video_type_for_mobile_application'] = "html5";
                            $data['video_url_for_mobile_application'] = $result['ObjectURL'];
                            $this->update_plan_space($plan['id'], $cloud_space, $video_kb_size);

                    }else{
                        $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_more_cloud_space'));
                        return false;
                    }

                }
            }
            
            else {
                $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_provider'));
                redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
            }

            // This portion is for mobile application video lessons
            if ($this->input->post('html5_video_url_for_mobile_application') == "" || $this->input->post('html5_duration_for_mobile_application') == "") {
                $mobile_app_lesson_url = "https://www.html5rocks.com/en/tutorials/video/basics/devstories.webm";
                $mobile_app_lesson_duration = "00:01:10";
            } else {
                $mobile_app_lesson_url = $this->input->post('html5_video_url_for_mobile_application');
                $mobile_app_lesson_duration = $this->input->post('html5_duration_for_mobile_application');
            }
            $duration_for_mobile_application_formatter = explode(':', $mobile_app_lesson_duration);
            $hour = sprintf('%02d', $duration_for_mobile_application_formatter[0]);
            $min = sprintf('%02d', $duration_for_mobile_application_formatter[1]);
            $sec = sprintf('%02d', $duration_for_mobile_application_formatter[2]);
            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = 'html5';
            $data['video_url_for_mobile_application'] = $mobile_app_lesson_url;
        } else {
            if ($_FILES['attachment']['name'] == "") {
                $this->session->set_flashdata('error_message', get_phrase('invalid_attachment'));
                redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
            } else {
                $fileName = $_FILES['attachment']['name'];
                $tmp = explode('.', $fileName);
                $fileExtension = end($tmp);
                $uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;
                $data['attachment'] = $uploadable_file;

                if (!file_exists('uploads/lesson_files')) {
                    mkdir('uploads/lesson_files', 0777, true);
                }
                move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
            }
        }

        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = $this->input->post('summary');

        $this->db->insert('lesson', $data);
        $inserted_id = $this->db->insert_id();

        if ($_FILES['thumbnail']['name'] != "") {
            if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
                mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
            }
            move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $inserted_id . '.jpg');
        }
        $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_added_successfully'));
    }

    public function update_plan_space($plan_id, $remaining_space, $reduce_space){
        $plan['remaining_cloud_space'] = $remaining_space - $reduce_space;
        $this->db->where('id', $plan_id);
        $this->db->update('purchased_plans', $plan);
    }

    public function edit_lesson($lesson_id,  $institute_name = '')
    {

        $previous_data = $this->db->get_where('lesson', array('id' => $lesson_id))->row_array();
        $course = $this->db->get_where('course', array('id' => $previous_data['course_id']))->row_array();
        $data['course_id'] = html_escape($this->input->post('course_id'));
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $lesson_type_array = explode('-', $this->input->post('lesson_type'));
        $lesson_type = $lesson_type_array[0];

        $data['attachment_type'] = $lesson_type_array[1];
        $data['lesson_type'] = $lesson_type;
        if ($lesson_type == 'video') {
            $lesson_provider = $this->input->post('lesson_provider');
            if ($lesson_provider == 'youtube' || $lesson_provider == 'vimeo') {
                if ($this->input->post('video_url') == "" || $this->input->post('duration') == "") {
                    $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->input->post('video_url'));

                $duration_formatter = explode(':', $this->input->post('duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;

                $video_details = $this->video_model->getVideoDetails($data['video_url']);
                $data['video_type'] = $video_details['provider'];
            } elseif ($lesson_provider == 'html5') {
                if ($this->input->post('html5_video_url') == "" || $this->input->post('html5_duration') == "") {
                    $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_url_and_duration'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
                $data['video_url'] = html_escape($this->input->post('html5_video_url'));

                $duration_formatter = explode(':', $this->input->post('html5_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type'] = 'html5';

                if ($_FILES['thumbnail']['name'] != "") {
                    if (!file_exists('uploads/thumbnails/lesson_thumbnails')) {
                        mkdir('uploads/thumbnails/lesson_thumbnails', 0777, true);
                    }
                    move_uploaded_file($_FILES['thumbnail']['tmp_name'], 'uploads/thumbnails/lesson_thumbnails/' . $lesson_id . '.jpg');
                }
            }elseif($lesson_provider == 's3'){
               
                $space_validity = $this->check_institute_membory_limit($course['institute_id']);
                if ($space_validity == false){
                    $this->session->set_flashdata('error_message', get_phrase('You do not have more storage'));
                }else{
                    $video_size = $_FILES['video_file_for_amazon_s3']['size'];
                    
                    if ($video_size > 1024){
                    $video_kb_size = round($video_size / 1024, 4);
                    }
                
                // SET MAXIMUM EXECUTION TIME 600
                ini_set('max_execution_time', '600');
    
                $fileName = $_FILES['video_file_for_amazon_s3']['name'];
                $tmp = explode('.', $fileName);
                $fileExtension = strtoupper(end($tmp));
                $video_extensions = ['FLV', 'MP4', 'WMV','AVI', 'MOV'];
                if (!in_array($fileExtension, $video_extensions)) {
                    $this->session->set_flashdata('error_message', get_phrase('please_select_valid_video_file.'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
    
                if ($this->input->post('amazon_s3_duration') == "") {
                    $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_duration'));
                    redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
                }
    
                $tmpfile = $_FILES['video_file_for_amazon_s3'];
                $tmppath = $_FILES['video_file_for_amazon_s3']['tmp_name'];
                $s3_model = new S3_model();
                $s3= $s3_model->create_s3_object();
                $key = str_replace(".", "-" . rand(1, 9999) . ".", $tmpfile['name']);
                $result = $s3_model->upload_data($s3,$key ,$tmppath, $fileExtension);
                $data['video_url'] = $result['ObjectURL'];
                $data['video_type'] = 'amazon';
                $data['video_size'] = $video_kb_size;
                $data['lesson_type'] = 'video';
                $data['attachment_type'] = 'url';
    
                $duration_formatter = explode(':', $this->input->post('amazon_s3_duration'));
                $hour = sprintf('%02d', $duration_formatter[0]);
                $min = sprintf('%02d', $duration_formatter[1]);
                $sec = sprintf('%02d', $duration_formatter[2]);
                $data['duration'] = $hour . ':' . $min . ':' . $sec;
    
                $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
                $data['video_type_for_mobile_application'] = "html5";
                $data['video_url_for_mobile_application'] = $result['ObjectURL'];
    
                }
            }
             else {
                $this->session->set_flashdata('error_message', get_phrase('invalid_lesson_provider'));
                redirect(site_url(strtolower($this->session->userdata('role')) . '/course_form/course_edit/' . $data['course_id']), 'refresh');
            }
            $data['attachment'] = "";

            // This portion is for mobile application video lessons
            if ($this->input->post('html5_video_url_for_mobile_application') == "" || $this->input->post('html5_duration_for_mobile_application') == "") {
                $mobile_app_lesson_url = "https://www.html5rocks.com/en/tutorials/video/basics/devstories.webm";
                $mobile_app_lesson_duration = "00:01:10";
            } else {
                $mobile_app_lesson_url = $this->input->post('html5_video_url_for_mobile_application');
                $mobile_app_lesson_duration = $this->input->post('html5_duration_for_mobile_application');
            }
            $duration_for_mobile_application_formatter = explode(':', $mobile_app_lesson_duration);
            $hour = sprintf('%02d', $duration_for_mobile_application_formatter[0]);
            $min = sprintf('%02d', $duration_for_mobile_application_formatter[1]);
            $sec = sprintf('%02d', $duration_for_mobile_application_formatter[2]);
            $data['duration_for_mobile_application'] = $hour . ':' . $min . ':' . $sec;
            $data['video_type_for_mobile_application'] = 'html5';
            $data['video_url_for_mobile_application'] = $mobile_app_lesson_url;
        }else {
            if ($_FILES['attachment']['name'] != "") {
                // unlinking previous attachments
                if ($previous_data['attachment'] != "") {
                    unlink('uploads/lesson_files/' . $previous_data['attachment']);
                }

                $fileName = $_FILES['attachment']['name'];
                $tmp = explode('.', $fileName);
                $fileExtension = end($tmp);
                $uploadable_file = md5(uniqid(rand(), true)) . '.' . $fileExtension;
                $data['attachment'] = $uploadable_file;
                $data['video_type'] = "";
                $data['duration'] = "";
                $data['video_url'] = "";
                $data['duration_for_mobile_application'] = "";
                $data['video_type_for_mobile_application'] = '';
                $data['video_url_for_mobile_application'] = "";
                if (!file_exists('uploads/lesson_files')) {
                    mkdir('uploads/lesson_files', 0777, true);
                }
                move_uploaded_file($_FILES['attachment']['tmp_name'], 'uploads/lesson_files/' . $uploadable_file);
            }
        }

        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = $this->input->post('summary');

        $this->db->where('id', $lesson_id);
        $this->db->update('lesson', $data);
    }
    public function delete_lesson($lesson_id)
    {
        $this->db->where('id', $lesson_id);
        $this->db->delete('lesson');
    }

    public function update_frontend_settings()
    {
        $data['value'] = html_escape($this->input->post('banner_title'));
        $this->db->where('key', 'banner_title');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('banner_sub_title'));
        $this->db->where('key', 'banner_sub_title');
        $this->db->update('frontend_settings', $data);

        $data['value'] = html_escape($this->input->post('cookie_status'));
        $this->db->where('key', 'cookie_status');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('cookie_note');
        $this->db->where('key', 'cookie_note');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('cookie_policy');
        $this->db->where('key', 'cookie_policy');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('about_us');
        $this->db->where('key', 'about_us');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('terms_and_condition');
        $this->db->where('key', 'terms_and_condition');
        $this->db->update('frontend_settings', $data);

        $data['value'] = $this->input->post('privacy_policy');
        $this->db->where('key', 'privacy_policy');
        $this->db->update('frontend_settings', $data);
    }

    public function update_frontend_banner()
    {
        move_uploaded_file($_FILES['banner_image']['tmp_name'], 'uploads/system/home-banner.jpg');
    }

    public function update_light_logo()
    {
        move_uploaded_file($_FILES['light_logo']['tmp_name'], 'uploads/system/logo-light.png');
    }

    public function update_dark_logo()
    {
        move_uploaded_file($_FILES['dark_logo']['tmp_name'], 'uploads/system/logo-dark.png');
    }

    public function update_small_logo()
    {
        move_uploaded_file($_FILES['small_logo']['tmp_name'], 'uploads/system/logo-light-sm.png');
    }

    public function update_favicon()
    {
        move_uploaded_file($_FILES['favicon']['tmp_name'], 'uploads/system/favicon.png');
    }

    public function handleWishList($course_id)
    {
        $wishlists = array();
        $user_details = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
        if ($user_details['wishlist'] == "") {
            array_push($wishlists, $course_id);
        } else {
            $wishlists = json_decode($user_details['wishlist']);
            if (in_array($course_id, $wishlists)) {
                $container = array();
                foreach ($wishlists as $key) {
                    if ($key != $course_id) {
                        array_push($container, $key);
                    }
                }
                $wishlists = $container;
                // $key = array_search($course_id, $wishlists);
                // unset($wishlists[$key]);
            } else {
                array_push($wishlists, $course_id);
            }
        }

        $updater['wishlist'] = json_encode($wishlists);
        $this->db->where('id', $this->session->userdata('user_id'));
        $this->db->update('users', $updater);
    }

    public function is_added_to_wishlist($course_id = "")
    {
        if ($this->session->userdata('user_login') == 1) {
            $wishlists = array();
            $user_details = $this->user_model->get_user($this->session->userdata('user_id'))->row_array();
            $wishlists = json_decode($user_details['wishlist']);
            if (in_array($course_id, $wishlists)) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function getWishLists($user_id = "")
    {
        if ($user_id == "") {
            $user_id = $this->session->userdata('user_id');
        }
        $user_details = $this->user_model->get_user($user_id)->row_array();
        return json_decode($user_details['wishlist']);
    }

    public function get_latest_10_course()
    {
        $this->db->order_by("id", "desc");
        $this->db->limit('10');
        $this->db->where('status', 'active');
        $this->db->where('type', 'public');
        return $this->db->get('course')->result_array();
    }

    public function enrol_student($user_id)
    {
        $purchased_courses = $this->session->userdata('cart_items');
        foreach ($purchased_courses as $purchased_course) {
            $data['user_id'] = $user_id;
            $data['course_id'] = $purchased_course;
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('enrol', $data);
        }
    }
    public function enrol_a_student_manually()
    {
        $data['course_id'] = $this->input->post('course_id');
        $data['user_id'] = $this->input->post('user_id');
        if ($this->db->get_where('enrol', $data)->num_rows() > 0) {
            $this->session->set_flashdata('error_message', get_phrase('student_has_already_been_enrolled_to_this_course'));
        } else {
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('enrol', $data);
            $this->session->set_flashdata('flash_message', get_phrase('student_has_been_enrolled_to_that_course'));
        }
    }

    public function enrol_a_student($course_id = '', $user_id = '')
    {
        $data['course_id'] = $course_id;
        $data['user_id'] = $user_id;
        if ($this->db->get_where('enrol', $data)->num_rows() > 0) {
            $this->session->set_flashdata('error_message', get_phrase('student_has_already_been_enrolled_to_this_class_course'));
        } else {
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('enrol', $data);
            $this->session->set_flashdata('flash_message', get_phrase('student_has_been_enrolled_to_that_class_course'));
        }
    }

    public function enrol_to_free_course($course_id = "", $user_id = "")
    {
        $course_details = $this->get_course_by_id($course_id)->row_array();
        if ($course_details['is_free_course'] == 1) {
            $data['course_id'] = $course_id;
            $data['user_id'] = $user_id;
            if ($this->db->get_where('enrol', $data)->num_rows() > 0) {
                $this->session->set_flashdata('error_message', get_phrase('student_has_already_been_enrolled_to_this_course'));
            } else {
                $data['date_added'] = strtotime(date('D, d-M-Y'));
                $this->db->insert('enrol', $data);
                $this->session->set_flashdata('flash_message', get_phrase('successfully_enrolled'));
            }
        } else {
            $this->session->set_flashdata('error_message', get_phrase('this_course_is_not_free_at_all'));
            redirect(site_url('home/course/' . slugify($course_details['title']) . '/' . $course_id), 'refresh');
        }
    }
    public function course_purchase($user_id, $method, $amount_paid)
    {
        $purchased_courses = $this->session->userdata('cart_items');
        foreach ($purchased_courses as $purchased_course) {
            $data['user_id'] = $user_id;
            $data['payment_type'] = $method;
            $data['course_id'] = $purchased_course;
            $course_details = $this->get_course_by_id($purchased_course)->row_array();
            if ($course_details['discount_flag'] == 1) {
                $data['amount'] = $course_details['discounted_price'];
            } else {
                $data['amount'] = $course_details['price'];
            }
            if (get_user_role('role_id', $course_details['user_id']) == 1) {
                $data['admin_revenue'] = $data['amount'];
                $data['instructor_revenue'] = 0;
                $data['instructor_payment_status'] = 1;
            } else {
                if (get_settings('allow_instructor') == 1) {
                    $instructor_revenue_percentage = get_settings('instructor_revenue');
                    $data['instructor_revenue'] = ceil(($data['amount'] * $instructor_revenue_percentage) / 100);
                    $data['admin_revenue'] = $data['amount'] - $data['instructor_revenue'];
                } else {
                    $data['instructor_revenue'] = 0;
                    $data['admin_revenue'] = $data['amount'];
                }
                $data['instructor_payment_status'] = 0;
            }
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('payment', $data);
        }
    }

    public function plan_purchase($user_id, $method, $amount_paid)
    {
        $plan_id = $this->session->userdata('plan_id');
        if ($plan_id > 0){
            $data['user_id'] = $user_id;
            $data['payment_type'] = $method;
            $data['plan_id'] = $plan_id;
            // $course_details = $this->get_course_by_id($purchased_course)->row_array();
            $data['amount'] = $amount_paid;
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('payment', $data);
            $this->user_model->update_user_plan($user_id, $plan_id);
            $this->purchased_plan($plan_id, $user_id);
          }
    }

    public function purchased_plan($plan_id, $user_id){
        $plan_exist = $this->db->get_where('purchased_plans', array('user_id' => $user_id))->row_array();
        if(count($plan_exist) > 0){
            $current_plan = $this->db->get_where('plans', array('id' => $plan_id))->row_array();
            $current_cloud_space = $current_plan['cloud_space'] * 1024 * 1024;
            $existing_cloud_space = $plan_exist['cloud_space'];
            $remaining_cloud_space = $plan_exist['remaining_cloud_space'];
            $data['plan_id'] = $current_plan['id'];
            $data['courses'] = $current_plan['courses'];
            $data['classes'] = $current_plan['classes'];
            $data['course_minutes'] = $current_plan['course_minutes'] + $plan_exist['course_minutes'];
            $data['remaining_minutes'] = $current_plan['remaining_minutes'] + $plan_exist['course_minutes'];
            $data['students'] = $current_plan['students'];
            $data['cloud_space'] = $current_cloud_space + $existing_cloud_space;
            $data['remaining_cloud_space'] = $remaining_cloud_space + $current_cloud_space;
            $data['last_modified'] = strtotime(date('D, d-M-Y'));
            $this->db->where('id', $plan_exist['id']);
            $this->db->update('purchased_plans', $data);

        }else{
            $current_plan = $this->db->get_where('plans', array('id' => $plan_id))->row_array();
            $cloud_space = ($current_plan['cloud_space'] * 1024 * 1024);
            $data['user_id'] = $user_id;
            $data['plan_id'] = $current_plan['id'];
            $data['name'] = $current_plan['name'];
            $data['courses'] = $current_plan['courses'];
            $data['classes'] = $current_plan['classes'];
            $data['course_minutes'] = $current_plan['course_minutes'];
            $data['remaining_minutes'] = $current_plan['course_minutes'];
            $data['students'] = $current_plan['students'];
            $data['cloud_space'] = $cloud_space;
            $data['remaining_cloud_space'] = $cloud_space;
            $data['date_added'] = strtotime(date('D, d-M-Y'));
            $this->db->insert('purchased_plans', $data);
        }
    }

    public function get_default_lesson($section_id)
    {
        $this->db->order_by('order', "asc");
        $this->db->limit(1);
        $this->db->where('section_id', $section_id);
        return $this->db->get('lesson');
    }

    public function get_courses_by_wishlists()
    {
        $wishlists = $this->getWishLists();
        if (sizeof($wishlists) > 0) {
            $this->db->where_in('id', $wishlists);
            return $this->db->get('course')->result_array();
        } else {
            return array();
        }
    }

    public function get_courses_of_wishlists_by_search_string($search_string)
    {
        $wishlists = $this->getWishLists();
        if (sizeof($wishlists) > 0) {
            $this->db->where_in('id', $wishlists);
            $this->db->like('title', $search_string);
            return $this->db->get('course')->result_array();
        } else {
            return array();
        }
    }

    public function get_total_duration_of_lesson_by_course_id($course_id)
    {
        $total_duration = 0;
        $lessons = $this->crud_model->get_lessons('course', $course_id)->result_array();
        foreach ($lessons as $lesson) {
            if ($lesson['lesson_type'] != "other") {
                $time_array = explode(':', $lesson['duration']);
                $hour_to_seconds = $time_array[0] * 60 * 60;
                $minute_to_seconds = $time_array[1] * 60;
                $seconds = $time_array[2];
                $total_duration += $hour_to_seconds + $minute_to_seconds + $seconds;
            }
        }
        // return gmdate("H:i:s", $total_duration).' '.get_phrase('hours');
        $hours = floor($total_duration / 3600);
        $minutes = floor(($total_duration % 3600) / 60);
        $seconds = $total_duration % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) . ' ' . get_phrase('hours');
    }

    public function get_total_duration_of_lesson_by_section_id($section_id)
    {
        $total_duration = 0;
        $lessons = $this->crud_model->get_lessons('section', $section_id)->result_array();
        foreach ($lessons as $lesson) {
            if ($lesson['lesson_type'] != 'other') {
                $time_array = explode(':', $lesson['duration']);
                $hour_to_seconds = $time_array[0] * 60 * 60;
                $minute_to_seconds = $time_array[1] * 60;
                $seconds = $time_array[2];
                $total_duration += $hour_to_seconds + $minute_to_seconds + $seconds;
            }
        }
        //return gmdate("H:i:s", $total_duration).' '.get_phrase('hours');
        $hours = floor($total_duration / 3600);
        $minutes = floor(($total_duration % 3600) / 60);
        $seconds = $total_duration % 60;
        return sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds) . ' ' . get_phrase('hours');
    }

    public function rate($data)
    {
        if ($this->db->get_where('rating', array('user_id' => $data['user_id'], 'ratable_id' => $data['ratable_id'], 'ratable_type' => $data['ratable_type']))->num_rows() == 0) {
            $this->db->insert('rating', $data);
        } else {
            $checker = array('user_id' => $data['user_id'], 'ratable_id' => $data['ratable_id'], 'ratable_type' => $data['ratable_type']);
            $this->db->where($checker);
            $this->db->update('rating', $data);
        }
    }

    public function get_user_specific_rating($ratable_type = "", $ratable_id = "")
    {
        return $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'user_id' => $this->session->userdata('user_id'), 'ratable_id' => $ratable_id))->row_array();
    }

    public function get_ratings($ratable_type = "", $ratable_id = "", $is_sum = false)
    {
        if ($is_sum) {
            $this->db->select_sum('rating');
            return $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'ratable_id' => $ratable_id));
        } else {
            return $this->db->get_where('rating', array('ratable_type' => $ratable_type, 'ratable_id' => $ratable_id));
        }
    }
    public function get_instructor_wise_course_ratings($instructor_id = "", $ratable_type = "", $is_sum = false)
    {
        $course_ids = $this->get_instructor_wise_courses($instructor_id, 'simple_array');
        if ($is_sum) {
            $this->db->where('ratable_type', $ratable_type);
            $this->db->where_in('ratable_id', $course_ids);
            $this->db->select_sum('rating');
            return $this->db->get('rating');
        } else {
            $this->db->where('ratable_type', $ratable_type);
            $this->db->where_in('ratable_id', $course_ids);
            return $this->db->get('rating');
        }
    }
    public function get_percentage_of_specific_rating($rating = "", $ratable_type = "", $ratable_id = "")
    {
        $number_of_user_rated = $this->db->get_where('rating', array(
            'ratable_type' => $ratable_type,
            'ratable_id' => $ratable_id,
        ))->num_rows();

        $number_of_user_rated_the_specific_rating = $this->db->get_where('rating', array(
            'ratable_type' => $ratable_type,
            'ratable_id' => $ratable_id,
            'rating' => $rating,
        ))->num_rows();

        //return $number_of_user_rated.' '.$number_of_user_rated_the_specific_rating;
        if ($number_of_user_rated_the_specific_rating > 0) {
            $percentage = ($number_of_user_rated_the_specific_rating / $number_of_user_rated) * 100;
        } else {
            $percentage = 0;
        }
        return floor($percentage);
    }

    ////////private message//////
    public function send_new_private_message()
    {
        $message = $this->input->post('message');
        $timestamp = strtotime(date("Y-m-d H:i:s"));

        $receiver = $this->input->post('receiver');
        $sender = $this->session->userdata('user_id');

        //check if the thread between those 2 users exists, if not create new thread
        $num1 = $this->db->get_where('message_thread', array('sender' => $sender, 'receiver' => $receiver))->num_rows();
        $num2 = $this->db->get_where('message_thread', array('sender' => $receiver, 'receiver' => $sender))->num_rows();
        if ($num1 == 0 && $num2 == 0) {
            $message_thread_code = substr(md5(rand(100000000, 20000000000)), 0, 15);
            $data_message_thread['message_thread_code'] = $message_thread_code;
            $data_message_thread['sender'] = $sender;
            $data_message_thread['receiver'] = $receiver;
            $this->db->insert('message_thread', $data_message_thread);
        }
        if ($num1 > 0) {
            $message_thread_code = $this->db->get_where('message_thread', array('sender' => $sender, 'receiver' => $receiver))->row()->message_thread_code;
        }

        if ($num2 > 0) {
            $message_thread_code = $this->db->get_where('message_thread', array('sender' => $receiver, 'receiver' => $sender))->row()->message_thread_code;
        }

        $data_message['message_thread_code'] = $message_thread_code;
        $data_message['message'] = $message;
        $data_message['sender'] = $sender;
        $data_message['timestamp'] = $timestamp;
        $this->db->insert('message', $data_message);

        return $message_thread_code;
    }

    public function send_reply_message($message_thread_code)
    {
        $message = html_escape($this->input->post('message'));
        $timestamp = strtotime(date("Y-m-d H:i:s"));
        $sender = $this->session->userdata('user_id');

        $data_message['message_thread_code'] = $message_thread_code;
        $data_message['message'] = $message;
        $data_message['sender'] = $sender;
        $data_message['timestamp'] = $timestamp;
        $this->db->insert('message', $data_message);
    }

    public function mark_thread_messages_read($message_thread_code)
    {
        // mark read only the oponnent messages of this thread, not currently logged in user's sent messages
        $current_user = $this->session->userdata('user_id');
        $this->db->where('sender !=', $current_user);
        $this->db->where('message_thread_code', $message_thread_code);
        $this->db->update('message', array('read_status' => 1));
    }

    public function count_unread_message_of_thread($message_thread_code)
    {
        $unread_message_counter = 0;
        $current_user = $this->session->userdata('user_id');
        $messages = $this->db->get_where('message', array('message_thread_code' => $message_thread_code))->result_array();
        foreach ($messages as $row) {
            if ($row['sender'] != $current_user && $row['read_status'] == '0') {
                $unread_message_counter++;
            }

        }
        return $unread_message_counter;
    }

    public function get_last_message_by_message_thread_code($message_thread_code)
    {
        $this->db->order_by('message_id', 'desc');
        $this->db->limit(1);
        $this->db->where(array('message_thread_code' => $message_thread_code));
        return $this->db->get('message');
    }

    public function curl_request($code = '')
    {

        $product_code = $code;

        $personal_token = "FkA9UyDiQT0YiKwYLK3ghyFNRVV9SeUn";
        $url = "https://api.envato.com/v3/market/author/sale?code=" . $product_code;
        $curl = curl_init($url);

        //setting the header for the rest of the api
        $bearer = 'bearer ' . $personal_token;
        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:' . $product_code . '.json';
        $ch_verify = curl_init($verify_url . '?code=' . $product_code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        $response = json_decode($cinit_verify_data, true);

        if (count($response['verify-purchase']) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // version 1.3
    public function get_currencies()
    {
        return $this->db->get('currency')->result_array();
    }

    public function get_paypal_supported_currencies()
    {
        $this->db->where('paypal_supported', 1);
        return $this->db->get('currency')->result_array();
    }

    public function get_stripe_supported_currencies()
    {
        $this->db->where('stripe_supported', 1);
        return $this->db->get('currency')->result_array();
    }

    // version 1.4
    public function filter_course($selected_category_id = "", $selected_price = "", $selected_level = "", $selected_language = "", $selected_rating = "")
    {
        //echo $selected_category_id.' '.$selected_price.' '.$selected_level.' '.$selected_language.' '.$selected_rating;

        $course_ids = array();
        if ($selected_category_id != "all") {
            $category_details = $this->get_category_details_by_id($selected_category_id)->row_array();

            if ($category_details['parent'] > 0) {
                $this->db->where('sub_category_id', $selected_category_id);
            } else {
                $this->db->where('category_id', $selected_category_id);
            }
        }

        if ($selected_price != "all") {
            if ($selected_price == "paid") {
                $this->db->where('is_free_course', null);
            } elseif ($selected_price == "free") {
                $this->db->where('is_free_course', 1);
            }
        }

        if ($selected_level != "all") {
            $this->db->where('level', $selected_level);
        }

        if ($selected_language != "all") {
            $this->db->where('language', $selected_language);
        }
        $this->db->where('status', 'active');
        $courses = $this->db->get('course')->result_array();

        foreach ($courses as $course) {
            if ($selected_rating != "all") {
                $total_rating = $this->get_ratings('course', $course['id'], true)->row()->rating;
                $number_of_ratings = $this->get_ratings('course', $course['id'])->num_rows();
                if ($number_of_ratings > 0) {
                    $average_ceil_rating = ceil($total_rating / $number_of_ratings);
                    if ($average_ceil_rating == $selected_rating) {
                        array_push($course_ids, $course['id']);
                    }
                }
            } else {
                array_push($course_ids, $course['id']);
            }
        }

        if (count($course_ids) > 0) {
            $this->db->where_in('id', $course_ids);
            return $this->db->get('course')->result_array();
        } else {
            return array();
        }
    }

    public function get_courses($category_id = "", $sub_category_id = "", $instructor_id = 0)
    {
        if ($category_id > 0 && $sub_category_id > 0 && $instructor_id > 0) {
            return $this->db->get_where('course', array('category_id' => $category_id, 'sub_category_id' => $sub_category_id, 'user_id' => $instructor_id));
        } elseif ($category_id > 0 && $sub_category_id > 0 && $instructor_id == 0) {
            return $this->db->get_where('course', array('category_id' => $category_id, 'sub_category_id' => $sub_category_id));
        } else {
            return $this->db->get('course');
        }
    }

    public function filter_course_for_backend($category_id, $user_id, $price, $status)
    {
        if ($category_id != "all") {
            $this->db->where('sub_category_id', $category_id);
        }

        if ($price != "all") {
            if ($price == "paid") {
                $this->db->where('is_free_course', null);
            } elseif ($price == "free") {
                $this->db->where('is_free_course', 1);
            }
        }

        if ($user_id != "all") {
            $user_role = $this->session->userdata('user_id');
            if($user_role == 'admin'){
                $this->db->where('institute_id', $user_id);
            }else{
                $this->db->where('user_id', $user_id);
            }
            
        }

        if ($status != "all") {
            $this->db->where('status', $status);
        }
        return $this->db->get('course')->result_array();
    }

    public function sort_section($section_json)
    {
        $sections = json_decode($section_json);
        foreach ($sections as $key => $value) {
            $updater = array(
                'order' => $key + 1,
            );
            $this->db->where('id', $value);
            $this->db->update('section', $updater);
        }
    }

    public function sync_courses($id = 0)
    {

        if ($id > 0) {
            $this->db->where('user_id', $id);
            return $this->db->get('course')->result_array();
        }
    }

    public function sync_instructors($id = 0)
    {

        if ($id > 0) {
            $this->db->where('institute_id', $id);
            return $this->db->get('users')->result_array();
        }
    }

    public function sync_instructor_id($id = 0)
    {
        $this->db->select('id');
        $this->db->where('user_id', $id);
        return $this->db->get('course')->row_array();
    }

    public function sort_lesson($lesson_json)
    {
        $lessons = json_decode($lesson_json);
        foreach ($lessons as $key => $value) {
            $updater = array(
                'order' => $key + 1,
            );
            $this->db->where('id', $value);
            $this->db->update('lesson', $updater);
        }
    }
    public function sort_question($question_json)
    {
        $questions = json_decode($question_json);
        foreach ($questions as $key => $value) {
            $updater = array(
                'order' => $key + 1,
            );
            $this->db->where('id', $value);
            $this->db->update('question', $updater);
        }
    }

    public function get_free_and_paid_courses($price_status = "", $instructor_id = "")
    {
        $this->db->where('status', 'active');
        if ($price_status == 'free') {
            $this->db->where('is_free_course', 1);
        } else {
            $this->db->where('is_free_course', null);
        }

        if ($instructor_id > 0) {
            $this->db->where('user_id', $instructor_id);
        }
        return $this->db->get('course');
    }

    // Adding quiz functionalities
    public function add_quiz($course_id = "")
    {
        $data['course_id'] = $course_id;
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));

        $data['lesson_type'] = 'quiz';
        $data['duration'] = '00:00:00';
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = html_escape($this->input->post('summary'));
        $this->db->insert('lesson', $data);
    }

    // updating quiz functionalities
    public function edit_quiz($lesson_id = "")
    {
        $data['title'] = html_escape($this->input->post('title'));
        $data['section_id'] = html_escape($this->input->post('section_id'));
        $data['last_modified'] = strtotime(date('D, d-M-Y'));
        $data['summary'] = html_escape($this->input->post('summary'));
        $this->db->where('id', $lesson_id);
        $this->db->update('lesson', $data);
    }

    // Get quiz questions
    public function get_quiz_questions($quiz_id)
    {
        $this->db->order_by("order", "asc");
        $this->db->where('quiz_id', $quiz_id);
        return $this->db->get('question');
    }

    public function get_quiz_question_by_id($question_id)
    {
        $this->db->order_by("order", "asc");
        $this->db->where('id', $question_id);
        return $this->db->get('question');
    }

    // Add Quiz Questions
    public function add_quiz_questions($quiz_id)
    {
        $question_type = $this->input->post('question_type');
        if ($question_type == 'mcq') {
            $response = $this->add_multiple_choice_question($quiz_id);
            return $response;
        }
    }

    public function update_quiz_questions($question_id)
    {
        $question_type = $this->input->post('question_type');
        if ($question_type == 'mcq') {
            $response = $this->update_multiple_choice_question($question_id);
            return $response;
        }
    }
    // multiple_choice_question crud functions
    public function add_multiple_choice_question($quiz_id)
    {
        if (sizeof($this->input->post('options')) != $this->input->post('number_of_options')) {
            return false;
        }
        foreach ($this->input->post('options') as $option) {
            if ($option == "") {
                return false;
            }
        }
        if (sizeof($this->input->post('correct_answers')) == 0) {
            $correct_answers = [""];
        } else {
            $correct_answers = $this->input->post('correct_answers');
        }
        $data['quiz_id'] = $quiz_id;
        $data['title'] = html_escape($this->input->post('title'));
        $data['number_of_options'] = html_escape($this->input->post('number_of_options'));
        $data['type'] = 'multiple_choice';
        $data['options'] = json_encode($this->input->post('options'));
        $data['correct_answers'] = json_encode($correct_answers);
        $this->db->insert('question', $data);
        return true;
    }
    // update multiple choice question
    public function update_multiple_choice_question($question_id)
    {
        if (sizeof($this->input->post('options')) != $this->input->post('number_of_options')) {
            return false;
        }
        foreach ($this->input->post('options') as $option) {
            if ($option == "") {
                return false;
            }
        }

        if (sizeof($this->input->post('correct_answers')) == 0) {
            $correct_answers = [""];
        } else {
            $correct_answers = $this->input->post('correct_answers');
        }

        $data['title'] = html_escape($this->input->post('title'));
        $data['number_of_options'] = html_escape($this->input->post('number_of_options'));
        $data['type'] = 'multiple_choice';
        $data['options'] = json_encode($this->input->post('options'));
        $data['correct_answers'] = json_encode($correct_answers);
        $this->db->where('id', $question_id);
        $this->db->update('question', $data);
        return true;
    }

    public function delete_quiz_question($question_id)
    {
        $this->db->where('id', $question_id);
        $this->db->delete('question');
        return true;
    }

    public function get_application_details()
    {
        $purchase_code = get_settings('purchase_code');
        $returnable_array = array(
            'purchase_code_status' => get_phrase('not_found'),
            'support_expiry_date' => get_phrase('not_found'),
            'customer_name' => get_phrase('not_found'),
        );

        $personal_token = "gC0J1ZpY53kRpynNe4g2rWT5s4MW56Zg";
        $url = "https://api.envato.com/v3/market/author/sale?code=" . $purchase_code;
        $curl = curl_init($url);

        //setting the header for the rest of the api
        $bearer = 'bearer ' . $personal_token;
        $header = array();
        $header[] = 'Content-length: 0';
        $header[] = 'Content-type: application/json; charset=utf-8';
        $header[] = 'Authorization: ' . $bearer;

        $verify_url = 'https://api.envato.com/v1/market/private/user/verify-purchase:' . $purchase_code . '.json';
        $ch_verify = curl_init($verify_url . '?code=' . $purchase_code);

        curl_setopt($ch_verify, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch_verify, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch_verify, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch_verify, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch_verify, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');

        $cinit_verify_data = curl_exec($ch_verify);
        curl_close($ch_verify);

        $response = json_decode($cinit_verify_data, true);

        if (count($response['verify-purchase']) > 0) {

            //print_r($response);
            $item_name = $response['verify-purchase']['item_name'];
            $purchase_time = $response['verify-purchase']['created_at'];
            $customer = $response['verify-purchase']['buyer'];
            $licence_type = $response['verify-purchase']['licence'];
            $support_until = $response['verify-purchase']['supported_until'];
            $customer = $response['verify-purchase']['buyer'];

            $purchase_date = date("d M, Y", strtotime($purchase_time));

            $todays_timestamp = strtotime(date("d M, Y"));
            $support_expiry_timestamp = strtotime($support_until);

            $support_expiry_date = date("d M, Y", $support_expiry_timestamp);

            if ($todays_timestamp > $support_expiry_timestamp) {
                $support_status = get_phrase('expired');
            } else {
                $support_status = get_phrase('valid');
            }

            $returnable_array = array(
                'purchase_code_status' => $support_status,
                'support_expiry_date' => $support_expiry_date,
                'customer_name' => $customer,
            );
        } else {
            $returnable_array = array(
                'purchase_code_status' => 'invalid',
                'support_expiry_date' => 'invalid',
                'customer_name' => 'invalid',
            );
        }

        return $returnable_array;
    }

    // Version 2.2 codes

    // This function is responsible for retreving all the language file from language folder
    public function get_all_languages()
    {
        $language_files = array();
        $all_files = $this->get_list_of_language_files();
        foreach ($all_files as $file) {
            $info = pathinfo($file);
            if (isset($info['extension']) && strtolower($info['extension']) == 'json') {
                $file_name = explode('.json', $info['basename']);
                array_push($language_files, $file_name[0]);
            }
        }
        return $language_files;
    }

    // This function is responsible for showing all the installed themes
    public function get_installed_themes($dir = APPPATH . '/views/frontend')
    {
        $result = array();
        $cdir = $files = preg_grep('/^([^.])/', scandir($dir));
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value)) {
                    array_push($result, $value);
                }
            }
        }
        return $result;
    }
    // This function is responsible for showing all the uninstalled themes inside themes folder
    public function get_uninstalled_themes($dir = 'themes')
    {
        $result = array();
        $cdir = $files = preg_grep('/^([^.])/', scandir($dir));
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", "..", ".DS_Store"))) {
                array_push($result, $value);
            }
        }
        return $result;
    }
    // This function is responsible for retreving all the language file from language folder
    public function get_list_of_language_files($dir = APPPATH . '/language', &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->get_list_of_directories_and_files($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }

    // This function is responsible for retreving all the files and folder
    public function get_list_of_directories_and_files($dir = APPPATH, &$results = array())
    {
        $files = scandir($dir);
        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if (!is_dir($path)) {
                $results[] = $path;
            } else if ($value != "." && $value != "..") {
                $this->get_list_of_directories_and_files($path, $results);
                $results[] = $path;
            }
        }
        return $results;
    }

    public function remove_files_and_folders($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        $this->remove_files_and_folders($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }

                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public function get_category_wise_courses($category_id = "")
    {
        $category_details = $this->get_category_details_by_id($category_id)->row_array();

        if ($category_details['parent'] > 0) {
            $this->db->where('sub_category_id', $category_id);
        } else {
            $this->db->where('category_id', $category_id);
        }
        $this->db->where('status', 'active');
        return $this->db->get('course');
    }

    public function activate_theme($theme_to_active)
    {
        $data['value'] = $theme_to_active;
        $this->db->where('key', 'theme');
        $this->db->update('frontend_settings', $data);
    }

    // code of mark this lesson as completed
    public function save_course_progress()
    {
        $lesson_id = $this->input->post('lesson_id');
        $progress = $this->input->post('progress');
        $user_id = $this->session->userdata('user_id');
        $user_details = $this->user_model->get_all_user($user_id)->row_array();
        $watch_history = $user_details['watch_history'];
        $watch_history_array = array();
        if ($watch_history == '') {
            array_push($watch_history_array, array('lesson_id' => $lesson_id, 'progress' => $progress));
        } else {
            $founder = false;
            $watch_history_array = json_decode($watch_history, true);
            for ($i = 0; $i < count($watch_history_array); $i++) {
                $watch_history_for_each_lesson = $watch_history_array[$i];
                if ($watch_history_for_each_lesson['lesson_id'] == $lesson_id) {
                    $watch_history_for_each_lesson['progress'] = $progress;
                    $watch_history_array[$i]['progress'] = $progress;
                    $founder = true;
                }
            }
            if (!$founder) {
                array_push($watch_history_array, array('lesson_id' => $lesson_id, 'progress' => $progress));
            }
        }
        $data['watch_history'] = json_encode($watch_history_array);
        $this->db->where('id', $user_id);
        $this->db->update('users', $data);

        // CHECK IF THE USER IS ELIGIBLE FOR CERTIFICATE
        if (addon_status('certificate')) {
            $this->load->model('addons/Certificate_model', 'certificate_model');
            $this->certificate_model->check_certificate_eligibility("lesson", $lesson_id, $user_id);
        }

        return $progress;
    }

    //FOR MOBILE
    public function enrol_to_free_course_mobile($course_id = "", $user_id = "")
    {
        $data['course_id'] = $course_id;
        $data['user_id'] = $user_id;
        $data['date_added'] = strtotime(date('D, d-M-Y'));
        if ($this->db->get_where('course', array('id' => $course_id))->row('is_free_course') == 1):
            $this->db->insert('enrol', $data);
        endif;
    }

    public function check_course_enrolled($course_id = "", $user_id = "")
    {
        return $this->db->get_where('enrol', array('course_id' => $course_id, 'user_id' => $user_id))->num_rows();
    }
}
