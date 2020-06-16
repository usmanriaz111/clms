<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Instructor extends CI_Controller {
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


    public function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('user_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(site_url('instructor/message/message_read/' . $message_thread_code), 'refresh');
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(site_url('instructor/message/message_read/' . $param2), 'refresh');
        }

        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }

        $page_data['message_inner_page_name'] = $param1;
        $page_data['page_name'] = 'message';
        $page_data['page_title'] = get_phrase('private_messaging');
        $this->load->view('backend/index', $page_data);
    }



    public function index() {
         $role_id = $this->session->userdata('role_id');
        if ($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'instructor') {
            $this->courses();
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'user'){
            redirect(site_url('home'), 'refresh');
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'institute'){
            $this->courses();
        }
        else {
            redirect(site_url('login'), 'refresh');
        }
    }

 /******MANAGE OWN PROFILE AND CHANGE PASSWORD***/
 function profile($param1 = '', $param2 = '', $param3 = '')
 {
 
   if ($this->session->userdata('user_login') != 1)
   redirect(site_url('login'), 'refresh');
   if ($param1 == 'update_profile_info') {
     $this->user_model->edit_user($param2);
   }
   if ($param1 == 'change_password') {
     $this->user_model->change_password($param2);
   }
   $page_data['page_name']  = 'manage_profile';
   $page_data['page_title'] = get_phrase('manage_profile');
   $page_data['edit_data']  = $this->db->get_where('users', array(
     'id' => $this->session->userdata('user_id')
   ))->result_array();
   $this->load->view('backend/index', $page_data);
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

    // This function is responsible for loading the course data from server side for datatable SILENTLY
    public function get_courses() {
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $courses = array();
      // Filter portion
      $filter_data['selected_category_id']   = $this->input->post('selected_category_id');
      $filter_data['selected_instructor_id'] = $this->input->post('selected_instructor_id');
      $filter_data['selected_price']         = $this->input->post('selected_price');
      $filter_data['selected_status']        = $this->input->post('selected_status');

      // Server side processing portion
      $columns = array(
        0 => '#',
        1 => 'title',
        2 => 'category',
        3 => 'lesson_and_section',
        4 => 'enrolled_student',
        5 => 'status',
        6 => 'price',
        7 => 'actions',
        8 => 'course_id'
      );

      // Coming from databale itself. Limit is the visible number of data
      $limit = html_escape($this->input->post('length'));
      $start = html_escape($this->input->post('start'));
      $order = "";
      $dir   = $this->input->post('order')[0]['dir'];

      $totalData = $this->lazyload->count_all_courses($filter_data);
      $totalFiltered = $totalData;

      // This block of code is handling the search event of datatable
      if(empty($this->input->post('search')['value'])) {
        $courses = $this->lazyload->courses($limit, $start, $order, $dir, $filter_data);
      }
      else {
        $search = $this->input->post('search')['value'];
        $courses =  $this->lazyload->course_search($limit, $start, $search, $order, $dir, $filter_data);
        $totalFiltered = $this->lazyload->course_search_count($search);
      }

      // Fetch the data and make it as JSON format and return it.
      $data = array();
      if(!empty($courses)) {
        foreach ($courses as $key => $row) {
          $instructor_details = $this->user_model->get_all_user($row->user_id)->row_array();
          $category_details = $this->crud_model->get_category_details_by_id($row->sub_category_id)->row_array();
          $sections = $this->crud_model->get_section('course', $row->id);
          $lessons = $this->crud_model->get_lessons('course', $row->id);
          $enroll_history = $this->crud_model->enrol_history($row->id);

          $status_badge = "badge-success-lighten";
          if ($row->status == 'pending') {
              $status_badge = "badge-danger-lighten";
          }elseif ($row->status == 'draft') {
              $status_badge = "badge-dark-lighten";
          }

          $price_badge = "badge-dark-lighten";
          $price = 0;
          if ($row->is_free_course == null){
            if ($row->discount_flag == 1) {
              $price = currency($row->discounted_price);
            }else{
              $price = currency($row->price);
            }
          }elseif ($row->is_free_course == 1){
            $price_badge = "badge-success-lighten";
            $price = get_phrase('free');
          }

          $view_course_on_frontend_url = site_url('home/course/'.slugify($row->title).'/'.$row->id);
          $edit_this_course_url = site_url('instuctor/course_form/course_edit/'.$row->id);
          $section_and_lesson_url = site_url('instuctor/course_form/course_edit/'.$row->id);

          if ($row->status == 'active' || $row->status == 'pending') {
            $course_status_changing_action = "confirm_modal('".site_url('instuctor/course_actions/draft/'.$row->id)."')";
            $course_status_changing_message = get_phrase('mark_as_drafted');
          }else{
            $course_status_changing_action = "confirm_modal('".site_url('instuctor/course_actions/publish/'.$row->id)."')";
            $course_status_changing_message = get_phrase('publish_this_course');
          }

          $delete_course_url = "confirm_modal('".site_url('instuctor/course_actions/delete/'.$row->id)."')";

          $nestedData['#'] = $key+1;

          $nestedData['title'] = '<strong>'.$row->title.'</strong><br>
          <small class="text-muted">'.get_phrase('instructor').': <b>'.$instructor_details['first_name'].' '.$instructor_details['last_name'].'</b></small>';

          $nestedData['category'] = '<span class="badge badge-dark-lighten">'.$category_details['name'].'</span>';

          $nestedData['lesson_and_section'] = '
            <small class="text-muted"><b>'.get_phrase('total_section').'</b>: '.$sections->num_rows().'</small><br>
            <small class="text-muted"><b>'.get_phrase('total_lesson').'</b>: '.$lessons->num_rows().'</small><br>';

          $nestedData['enrolled_student'] = '<small class="text-muted"><b>'.get_phrase('total_enrolment').'</b>: '.$enroll_history->num_rows().'</small>';

          $nestedData['status'] = '<span class="badge '.$status_badge.'">'.get_phrase($row->status).'</span>';

          $nestedData['price'] = '<span class="badge '.$price_badge.'">'.get_phrase($price).'</span>';

          $nestedData['actions'] = $action;

          $nestedData['course_id'] = $row->id;

          $data[] = $nestedData;
        }
      }

      $json_data = array(
        "draw"            => intval($this->input->post('draw')),
        "recordsTotal"    => intval($totalData),
        "recordsFiltered" => intval($totalFiltered),
        "data"            => $data
      );

      echo json_encode($json_data);
    }

    public function classes($param1 = "", $param2 = "") {
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $instructor_id = $this->session->userdata('user_id');
      $page_data['page_name'] = 'classes';
      $page_data['page_title'] = get_phrase('class');
      $page_data['classes'] = $this->crud_model->curret_user_classes();
      $this->load->view('backend/index', $page_data);
    }
 
    // Ajax Portion
    public function ajax_get_video_details() {
        $video_details = $this->video_model->getVideoDetails($_POST['video_url']);
        echo $video_details['duration'];
    }

    // public function users($param1 = "", $param2 = "") {
    //     $page_data['page_name'] = 'users';
    //     $page_data['page_title'] = get_phrase('student');
    //     $page_data['users'] = $this->user_model->get_user($param2);
    //     $this->load->view('backend/index', $page_data);
    // }
    // this function is responsible for managing multiple choice question
    function manage_multiple_choices_options() {
        $page_data['number_of_options'] = $this->input->post('number_of_options');
        $this->load->view('backend/instuctor/manage_multiple_choices_options', $page_data);
    }

    // This function checks if this course belongs to current logged in instructor
    function is_the_course_belongs_to_current_instructor($course_id, $id = null, $type = null) {
        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
        if ($course_details['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
            redirect(site_url('instuctor/courses'), 'refresh');
        }

        if($type == 'section' && $this->db->get_where('section', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_section'));
          redirect(site_url('instuctor/courses'), 'refresh');
        }
        if($type == 'lesson' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_lesson'));
          redirect(site_url('instuctor/courses'), 'refresh');
        }
        if($type == 'quize' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quize'));
          redirect(site_url('instructor/courses'), 'refresh');
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

    public function sessions()
    {
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
        $page_data['page_name'] = 'sessions';
        $page_data['page_title'] = get_phrase('live_sessions');
        $this->load->view('backend/index', $page_data);
    }

}
