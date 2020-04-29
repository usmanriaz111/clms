<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {
    public function __construct()
    {
        parent::__construct();

        $this->load->database();
        $this->load->library('session');
        /*cache control*/
        $this->output->set_header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $this->output->set_header('Pragma: no-cache');


        // THIS FUNCTION DECIDES WHTHER THE ROUTE IS REQUIRES PUBLIC INSTRUCTOR.
        $this->get_protected_routes($this->router->method);
    }

    public function get_protected_routes($method) {
      // IF ANY FUNCTION DOES NOT REQUIRE PUBLIC INSTRUCTOR, PUT THE NAME HERE.
      $unprotected_routes = ['save_course_progress'];

      if (!in_array($method, $unprotected_routes)) {
        if (get_settings('allow_instructor') != 1){
            redirect(site_url('home'), 'refresh');
        }
      }
    }

    public function index() {
         $role_id = $this->session->userdata('role_id');
        if ($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'instructor') {
            $this->courses();
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'user'){
            redirect(site_url('home'), 'refresh');
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'institute'){
          $this->user_model->check_plan(true);
            $this->courses();
        }
        else {
            redirect(site_url('login'), 'refresh');
        }
    }

    public function courses() {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
        $page_data['selected_instructor_id'] = $this->session->userdata('user_id');
        $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
        $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";
        $page_data['courses']                = $this->crud_model->filter_course_for_backend($page_data['selected_category_id'], $page_data['selected_instructor_id'], $page_data['selected_price'], $page_data['selected_status']);
        $page_data['page_name']              = 'courses-server-side';
        $page_data['categories']             = $this->crud_model->get_categories();
        $page_data['page_title']             = get_phrase('active_courses');
        $this->load->view('backend/index', $page_data);
    }


    public function payment_settings($param1 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'paypal_settings') {
            $this->user_model->update_instructor_paypal_settings($this->session->userdata('user_id'));
            redirect(site_url('user/payment_settings'), 'refresh');
        }
        if ($param1 == 'stripe_settings') {
            $this->user_model->update_instructor_stripe_settings($this->session->userdata('user_id'));
            redirect(site_url('user/payment_settings'), 'refresh');
        }

        $page_data['page_name'] = 'payment_settings';
        $page_data['page_title'] = get_phrase('payment_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_revenue($param1 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        $page_data['payment_history'] = $this->crud_model->get_instructor_revenue();
        $page_data['page_name'] = 'instructor_revenue';
        $page_data['page_title'] = get_phrase('instructor_revenue');
        $this->load->view('backend/index', $page_data);
    }

    public function preview($course_id = '') {
        if ($this->session->userdata('user_login') != 1)
        redirect(site_url('login'), 'refresh');

        $this->is_the_course_belongs_to_current_instructor($course_id);
        if ($course_id > 0) {
            $courses = $this->crud_model->get_course_by_id($course_id);
            if ($courses->num_rows() > 0) {
                $course_details = $courses->row_array();
                redirect(site_url('home/lesson/'.slugify($course_details['title']).'/'.$course_details['id']), 'refresh');
            }
        }
        redirect(site_url('user/courses'), 'refresh');
    }

    public function sections($param1 = "", $param2 = "", $param3 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param2 == 'add') {
          $this->is_the_course_belongs_to_current_instructor($param1);
            $this->crud_model->add_section($param1);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_added_successfully'));
        }
        elseif ($param2 == 'edit') {
            $this->is_the_course_belongs_to_current_instructor($param1, $param3, 'section');
            $this->crud_model->edit_section($param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
        }
        elseif ($param2 == 'delete') {
            $this->is_the_course_belongs_to_current_instructor($param1, $param3, 'section');
            $this->crud_model->delete_section($param1, $param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
        }
        redirect(site_url('user/course_form/course_edit/'.$param1));
    }

    public function lessons($course_id = "", $param1 = "", $param2 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'add') {
            $this->is_the_course_belongs_to_current_instructor($course_id);
            $this->crud_model->add_lesson();
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_added_successfully'));
            redirect('user/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'edit') {
            $this->is_the_course_belongs_to_current_instructor($course_id, $param2, 'lesson');
            $this->crud_model->edit_lesson($param2);
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_updated_successfully'));
            redirect('user/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'delete') {
            $this->is_the_course_belongs_to_current_instructor($course_id, $param2, 'lesson');
            $this->crud_model->delete_lesson($param2);
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
            redirect('user/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'filter') {
            redirect('user/lessons/'.$this->input->post('course_id'));
        }
        $page_data['page_name'] = 'lessons';
        $page_data['lessons'] = $this->crud_model->get_lessons('course', $course_id);
        $page_data['course_id'] = $course_id;
        $page_data['page_title'] = get_phrase('lessons');
        $this->load->view('backend/index', $page_data);
    }

    // Manage Quizes
    public function quizes($course_id = "", $action = "", $quiz_id = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($action == 'add') {
            $this->is_the_course_belongs_to_current_instructor($course_id);
            $this->crud_model->add_quiz($course_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
        }
        elseif ($action == 'edit') {
            $this->is_the_course_belongs_to_current_instructor($course_id, $quiz_id, 'quize');
            $this->crud_model->edit_quiz($quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
        }
        elseif ($action == 'delete') {
            $this->is_the_course_belongs_to_current_instructor($course_id, $quiz_id, 'quize');
            $this->crud_model->delete_lesson($quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
        }
        redirect(site_url('user/course_form/course_edit/'.$course_id));
    }

    // Manage Quize Questions
    public function quiz_questions($quiz_id = "", $action = "", $question_id = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

        if ($action == 'add') {
            $this->is_the_course_belongs_to_current_instructor($quiz_details['course_id'], $quiz_id, 'quize');
            $response = $this->crud_model->add_quiz_questions($quiz_id);
            echo $response;
        }

        elseif ($action == 'edit') {
            if($this->db->get_where('question', array('id' => $question_id, 'quiz_id' => $quiz_id))->num_rows() <= 0){
              $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quiz_question'));
              redirect(site_url('user/courses'), 'refresh');
            }

            $response = $this->crud_model->update_quiz_questions($question_id);
            echo $response;
        }

        elseif ($action == 'delete') {
            if($this->db->get_where('question', array('id' => $question_id, 'quiz_id' => $quiz_id))->num_rows() <= 0){
              $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quiz_question'));
              redirect(site_url('user/courses'), 'refresh');
            }

            $response = $this->crud_model->delete_quiz_question($question_id);
            $this->session->set_flashdata('flash_message', get_phrase('question_has_been_deleted'));
            redirect(site_url('user/course_form/course_edit/'.$quiz_details['course_id']));
        }
    }

    function manage_profile() {
        redirect(site_url('home/profile/user_profile'), 'refresh');
    }

    function invoice($payment_id = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['page_name'] = 'invoice';
        $page_data['payment_details'] = $this->crud_model->get_payment_details_by_id($payment_id);
        $page_data['page_title'] = get_phrase('invoice');
        $this->load->view('backend/index', $page_data);
    }
    // Ajax Portion
    public function ajax_get_video_details() {
        $video_details = $this->video_model->getVideoDetails($_POST['video_url']);
        echo $video_details['duration'];
    }

    // this function is responsible for managing multiple choice question
    function manage_multiple_choices_options() {
        $page_data['number_of_options'] = $this->input->post('number_of_options');
        $this->load->view('backend/user/manage_multiple_choices_options', $page_data);
    }

    // This function checks if this course belongs to current logged in instructor
    function is_the_course_belongs_to_current_instructor($course_id, $id = null, $type = null) {
        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
        if ($course_details['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
            redirect(site_url('user/courses'), 'refresh');
        }

        if($type == 'section' && $this->db->get_where('section', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_section'));
          redirect(site_url('user/courses'), 'refresh');
        }
        if($type == 'lesson' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_lesson'));
          redirect(site_url('user/courses'), 'refresh');
        }
        if($type == 'quize' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quize'));
          redirect(site_url('user/courses'), 'refresh');
        }

    }

    public function ajax_sort_section() {
        $section_json = $this->input->post('itemJSON');
        $this->crud_model->sort_section($section_json);
    }
    public function ajax_sort_lesson() {
        $lesson_json = $this->input->post('itemJSON');
        $this->crud_model->sort_lesson($lesson_json);
    }
    public function ajax_sort_question() {
        $question_json = $this->input->post('itemJSON');
        $this->crud_model->sort_question($question_json);
    }

    // Mark this lesson as completed codes
    function save_course_progress() {
        $response = $this->crud_model->save_course_progress();
        echo $response;
    }
}
