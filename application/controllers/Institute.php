<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Institute extends CI_Controller {
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

    public function message($param1 = 'message_home', $param2 = '', $param3 = '')
    {
        if ($this->session->userdata('user_login') != 1) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'send_new') {
            $message_thread_code = $this->crud_model->send_new_private_message();
        }

        if ($param1 == 'send_reply') {
            $this->crud_model->send_reply_message($param2); //$param2 = message_thread_code
            $this->session->set_flashdata('flash_message', get_phrase('message_sent!'));
            redirect(site_url('institute/message/message_read/' . $param2), 'refresh');
        }

        if ($param1 == 'message_read') {
            $page_data['current_message_thread_code'] = $param2; // $param2 = message_thread_code
            $this->crud_model->mark_thread_messages_read($param2);
        }
        $institute_id = $this->session->userdata('user_id');
        $page_data['instructors'] = $this->crud_model->sync_instructors($institute_id);

        $page_data['message_inner_page_name'] = $param1;
        $page_data['page_name'] = 'message';
        $page_data['page_title'] = get_phrase('private_messaging');
        $this->load->view('backend/index', $page_data);
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

    public function live_session($param1='', $param2=''){
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      
      if($param1 == "add"){
        $data = $this->crud_model->insert_live_session();
        $course_id = $this->input->post('course_id');
        // $page_data['admin_url'] = $data['admin_url'];
        // $page_data['student_url'] = $data['student_url'];
        // $page_data['page_name'] = $data['page_name'];
        // $this->session->set_flashdata('flash_message', get_phrase('live_session_successfully_created'));
        // $this->load->view('backend/index.php', $page_data);
        redirect(site_url('institute/course_form/course_edit/'.$course_id), 'refresh');
      }elseif ($param1 == "delete") {
        $live_session = $this->db->get_where('live_sessions', array('id' => $param2))->row_array();
        $this->crud_model->delete_live_session($param2);
        redirect(site_url('institute/course_form/course_edit/'.$live_session['course_id']), 'refresh');
    }
  }

    public function change_course_type($updated_type = "", $course_id)
    {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->crud_model->change_course_type($updated_type, $course_id);
        $this->session->set_flashdata('flash_message', get_phrase('course_'.$updated_type.'_updated'));
        redirect('institute/courses');
    }

    public function class_id($param1 = '', $param2 = ''){
      if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
      } else {
          if ($param2 == 'add_student'){
          $page_data['class_id'] = $param1;
          $page_data['page_name'] = 'user_add';
          $page_data['page_title'] = get_phrase('student_add');
          $this->load->view('backend/index', $page_data);
          }elseif($param2 == 'users'){
              $page_data['class_id'] = $param1;
              $page_data['page_name'] = 'users';
              $page_data['page_title'] = get_phrase('students');
              $page_data['users'] = $this->user_model->get_class_enrolled_students($param1);
              $this->load->view('backend/index', $page_data);
          }
      }
  }

  //   public function amazons3_setting($param1='', $param2=''){
  //     if ($this->session->userdata('user_login') != true) {
  //       redirect(site_url('login'), 'refresh');
  //   }
  //     if($param1 == "add"){
  //         $this->crud_model->add_s3_settings();
  //     }elseif($param1 == "edit"){
  //         $this->crud_model->edit_s3_settings($param2);
  //     }
  //     redirect(site_url('institute/amazons3_setting_form/add_form'), 'refresh');
      
  // }

  // public function amazons3_setting_form($param1='', $param2=''){
  //   if ($this->session->userdata('user_login') != true) {
  //     redirect(site_url('login'), 'refresh');
  // }
  //     if($param1 == "add_form"){
  //         $page_data['page_name'] = 'amazons3_setting';
  //         $page_data['page_title'] = get_phrase('s3_setting');
  //         $this->load->view('backend/index.php', $page_data);
  //     }
  // }

    public function purchase_plan(){
      if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
      }
      $page_data['plans'] = $this->crud_model->get_public_plans();
      $page_data['page_name'] = 'purchase_plan';
      $page_data['page_title'] = get_phrase('purchase_plan');
      $this->load->view('backend/index.php', $page_data);
    }

    public function plan_price() {
        $user_id = $this->session->userdata('user_id');
        $plan_id = $this->input->post('plan_id');
        $this->session->set_userdata('plan_id', $plan_id);
        $plan = $this->db->get_where('plans', array('id' => $plan_id))->row_array();
        if ($plan['price'] > 0 && $plan['type'] == 'paid') {
            $this->session->set_userdata('plan_price', $plan['price']);
            $page_data['page_title'] = get_phrase("payment_gateway");
            $page_data['plan_price'] = $plan['price'];
            $this->session->set_userdata('plan_price', $plan['price']);
            $this->load->view('backend/institute/payment/index.php', $page_data);
        }else{
          $plan_exist = $this->db->get_where('purchased_plans', array('user_id' => $user_id))->row_array();
          if (count($plan_exist) > 0) {
              $this->session->set_flashdata('error_message', get_phrase('please_upgrade_your_plan'));
              redirect('institute/purchase_plan', 'refresh');
          }else{
            $this->crud_model->plan_purchase($user_id, 'stripe', $plan['price']);
            $this->email_model->course_purchase_notification($user_id, 'free', $plan['price']);
            $this->session->set_flashdata('flash_message', get_phrase('free_plane_successfully_activated'));
            redirect('institute/courses', 'refresh');
          }
        }

    }

        // SHOW PAYPAL CHECKOUT PAGE
        public function paypal_checkout($payment_request = "only_for_mobile") {
            if ($this->session->userdata('user_login') != 1 && $payment_request != 'true')
            redirect(site_url('institute/purchase_plan'), 'refresh');

            //checking price
            if($this->session->userdata('plan_price') == $this->input->post('total_price_of_checking_out')):
                $total_price_of_checking_out = $this->input->post('total_price_of_checking_out');
            else:
                $total_price_of_checking_out = $this->input->post('total_price_of_checking_out');
            endif;
            $page_data['payment_request'] = $payment_request;
            $page_data['user_details']    = $this->user_model->get_single_institute($this->session->userdata('user_id'));
            $page_data['amount_to_pay']   = $total_price_of_checking_out;
            $this->load->view('backend/institute/paypal_checkout', $page_data);
        }

        // PAYPAL CHECKOUT ACTIONS
        public function paypal_payment($user_id = "", $amount_paid = "", $paymentID = "", $paymentToken = "", $payerID = "", $payment_request_mobile = "") {
            $paypal_keys = get_settings('paypal');
            $paypal = json_decode($paypal_keys);

            if ($paypal[0]->mode == 'sandbox') {
                $paypalClientID = $paypal[0]->sandbox_client_id;
                $paypalSecret   = $paypal[0]->sandbox_secret_key;
            }else{
                $paypalClientID = $paypal[0]->production_client_id;
                $paypalSecret   = $paypal[0]->production_secret_key;
            }

            //THIS IS HOW I CHECKED THE PAYPAL PAYMENT STATUS
            $status = $this->payment_model->paypal_payment($paymentID, $paymentToken, $payerID, $paypalClientID, $paypalSecret);
            if (!$status) {
                $this->session->set_flashdata('error_message', get_phrase('an_error_occurred_during_payment'));
                redirect('home', 'refresh');
            }
            $this->crud_model->plan_purchase($user_id, 'paypal', $amount_paid);
            $this->email_model->course_purchase_notification($user_id, 'paypal', $amount_paid);
            $this->session->set_flashdata('flash_message', get_phrase('payment_successfully_done'));
            if($payment_request_mobile == 'true'):
                $course_id = $this->session->userdata('cart_items');
                redirect('home/payment_success_mobile/'.$course_id[0].'/'.$user_id.'/paid', 'refresh');
            else:
              $this->session->set_userdata('plan_id', '');
              redirect('institute/courses', 'refresh');
            endif;

        }

        // SHOW STRIPE CHECKOUT PAGE
        public function stripe_checkout($payment_request = "only_for_mobile") {
            if ($this->session->userdata('user_login') != 1 && $payment_request != 'true')
            redirect('home', 'refresh');

            //checking price
            if($this->session->userdata('plan_price') == $this->input->post('total_price_of_checking_out')):
                $total_price_of_checking_out = $this->input->post('total_price_of_checking_out');
            else:
                $total_price_of_checking_out = $this->session->userdata('plan_price');
            endif;
            $page_data['payment_request'] = $payment_request;
            $page_data['user_details']    = $this->user_model->get_institute($this->session->userdata('user_id'));
            $page_data['amount_to_pay']   = $total_price_of_checking_out;
            $this->load->view('backend/institute/stripe_checkout', $page_data);
        }

        // STRIPE CHECKOUT ACTIONS
        public function stripe_payment($user_id = "", $amount_paid = "", $payment_request_mobile = "") {

          
            $token_id = $this->input->post('stripeToken');
            $stripe_keys = get_settings('stripe_keys');
            $values = json_decode($stripe_keys);
            if ($values[0]->testmode == 'on') {
                $public_key = $values[0]->public_key;
                $secret_key = $values[0]->secret_key;
            } else {
                $public_key = $values[0]->public_live_key;
                $secret_key = $values[0]->secret_live_key;
            }

            //THIS IS HOW I CHECKED THE STRIPE PAYMENT STATUS
            // echo $token_id;
            // die;
            $status = $this->payment_model->stripe_payment($token_id, $user_id, $amount_paid, $secret_key);

            if (!$status) {
                $this->session->set_flashdata('error_message', get_phrase('an_error_occurred_during_payment'));
                redirect('home', 'refresh');
            }

            $this->crud_model->plan_purchase($user_id, 'stripe', $amount_paid);
            $this->email_model->course_purchase_notification($user_id, 'stripe', $amount_paid);
            $this->session->set_flashdata('flash_message', get_phrase('payment_successfully_done'));
            if($payment_request_mobile == 'true'):
                $course_id = $this->session->userdata('cart_items');
                redirect('home/payment_success_mobile/'.$course_id[0].'/'.$user_id.'/paid', 'refresh');
            else:
                $this->session->set_userdata('plan_id', '');
                redirect('institute/courses', 'refresh');
            endif;
        }

    public function index() {
         $role_id = $this->session->userdata('role_id');
        if ($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'instructor') {
            $this->courses();
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'user'){
            redirect(site_url('home'), 'refresh');
        }elseif($this->session->userdata('user_login') == true && $this->session->userdata('role_name') == 'institute'){
          $this->user_model->check_plan(ture);
            $this->courses();
        }
        else {
            redirect(site_url('login'), 'refresh');
        }
    }

    public function import_students($param1 = '', $param2 = ''){
      $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $page_data['class_id'] = $param2;
        $page_data['page_name'] = 'import_students';
        $page_data['page_title'] = get_phrase('import_students');
        $this->load->view('backend/index', $page_data);
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
    $page_data['user_id'] = $this->session->userdata('user_id');
    $page_data['edit_data']  = $this->db->get_where('users', array(
      'id' => $this->session->userdata('user_id')
    ))->result_array();
    $this->load->view('backend/index', $page_data);
  }

    public function courses() {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);
        $page_data['selected_category_id']   = isset($_GET['category_id']) ? $_GET['category_id'] : "all";
        // $page_data['selected_instructor_id'] = $this->session->userdata('user_id');
        $page_data['selected_price']         = isset($_GET['price']) ? $_GET['price'] : "all";
        $page_data['selected_status']        = isset($_GET['status']) ? $_GET['status'] : "all";
        $page_data['courses']                = $this->crud_model->get_institute_courses($page_data['selected_category_id'], $page_data['selected_price'], $page_data['selected_status']);
        $page_data['page_name']              = 'courses';
        $page_data['categories']             = $this->crud_model->get_categories();
        $page_data['page_title']             = get_phrase('active_courses');
        $this->load->view('backend/index', $page_data);
    }

    // This function is responsible for loading the course data from server side for datatable SILENTLY
    public function get_courses() {
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $this->user_model->check_plan(true);
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
        $courses = $this->lazyload->institute_courses($limit, $start, $order, $dir, $filter_data);
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
          $edit_this_course_url = site_url('institute/course_form/course_edit/'.$row->id);
          $section_and_lesson_url = site_url('institute/course_form/course_edit/'.$row->id);

          if ($row->status == 'active' || $row->status == 'pending') {
            $course_status_changing_action = "confirm_modal('".site_url('institute/course_actions/draft/'.$row->id)."')";
            $course_status_changing_message = get_phrase('mark_as_drafted');
          }else{
            $course_status_changing_action = "confirm_modal('".site_url('institute/course_actions/publish/'.$row->id)."')";
            $course_status_changing_message = get_phrase('publish_this_course');
          }

          $delete_course_url = "confirm_modal('".site_url('institute/course_actions/delete/'.$row->id)."')";

          $action = '
          <div class="dropright dropright">
            <button type="button" class="btn btn-sm btn-outline-primary btn-rounded btn-icon" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="mdi mdi-dots-vertical"></i>
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="'.$view_course_on_frontend_url.'" target="_blank">'.get_phrase("view_course_on_frontend").'</a></li>
                <li><a class="dropdown-item" href="'.$edit_this_course_url.'">'.get_phrase("edit_this_course").'</a></li>
                <li><a class="dropdown-item" href="'.$section_and_lesson_url.'">'.get_phrase("section_and_lesson").'</a></li>
                <li><a class="dropdown-item" href="javascript::" onclick="'.$course_status_changing_action.'">'.$course_status_changing_message.'</a></li>
                <li><a class="dropdown-item" href="javascript::" onclick="'.$delete_course_url.'">'.get_phrase("delete").'</a></li>
            </ul>
        </div>
        ';

          $nestedData['#'] = $key+1;

          $nestedData['title'] = '<strong><a href="'.site_url('institute/course_form/course_edit/'.$row->id).'" title="'.$row->title.'">'.substr($row->title,0,25).'</a></strong><br>
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
    public function course_actions($param1 = "", $param2 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);
        if ($param1 == "add") {
            $instructor_id = $this->input->post('instructors');
            $course_id = $this->crud_model->add_course("", $instructor_id );
            redirect(site_url('institute/course_form/course_edit/'.$course_id), 'refresh');

        }
        elseif ($param1 == "edit") {
            // $this->is_the_course_belongs_to_current_instructor($param2);
            $this->crud_model->update_course($param2);
            redirect(site_url('institute/courses'), 'refresh');

        }
        elseif ($param1 == 'delete') {
            // $this->is_the_course_belongs_to_current_instructor($param2);
            $this->crud_model->delete_course($param2);
            redirect(site_url('institute/courses'), 'refresh');
        }
        elseif ($param1 == 'draft') {
            // $this->is_the_course_belongs_to_current_instructor($param2);
            $this->crud_model->change_course_status('draft', $param2);
            redirect(site_url('institute/courses'), 'refresh');
        }
        elseif ($param1 == 'publish') {
            // $this->is_the_course_belongs_to_current_instructor($param2);
            $this->crud_model->change_course_status('pending', $param2);
            redirect(site_url('institute/courses'), 'refresh');
        }
    }

    public function course_form($param1 = "", $param2 = "") {

        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);

        if ($param1 == 'add_course') {
            $page_data['languages'] = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $page_data['page_name'] = 'course_add';
            $page_data['page_title'] = get_phrase('add_course');
            $institute_id = $this->session->userdata('user_id');
            $page_data['instructors'] = $this->crud_model->sync_instructors($institute_id);
            $this->load->view('backend/index', $page_data);

        }elseif ($param1 == 'course_edit') {
          $course_details = $this->crud_model->get_course_by_id($param2)->row_array();
          if($course_details['status'] == 'block'){
            $this->session->set_flashdata('error_message', get_phrase('Access_denied'));
            redirect(site_url('institute/courses'), 'refresh');
          }
            // $this->is_the_course_belongs_to_current_instructor($param2);
            $page_data['page_name'] = 'course_edit';
            $page_data['course_id'] =  $param2;
            $page_data['page_title'] = get_phrase('edit_course');
            $page_data['languages'] = $this->crud_model->get_all_languages();
            $page_data['categories'] = $this->crud_model->get_categories();
            $this->load->view('backend/index', $page_data);
        }
    }

    public function classes($param1 = "", $param2 = "") {
      $this->user_model->check_plan(true);
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      elseif ($param1 == "add") {
        $this->crud_model->add_class();
        redirect(site_url('institute/classes'), 'refresh');
      }
      elseif ($param1 == "edit") {
        $this->crud_model->edit_class($param2);
        redirect(site_url('institute/classes'), 'refresh');
      }
      elseif ($param1 == "delete") {
        $this->crud_model->delete_class($param2);
        redirect(site_url('institute/classes'), 'refresh');
      }
      $instructor_id = $this->session->userdata('user_id');
      $page_data['page_name'] = 'classes';
      $page_data['page_title'] = get_phrase('class');
      $page_data['classes'] = $this->crud_model->get_institute_classes();
      $this->load->view('backend/index', $page_data);
    }
    public function class_form($param1 = "", $param2 = "") {
      $this->user_model->check_plan(true);
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      elseif ($param1 == 'add_class_form') {
        $institute_id = $this->session->userdata('user_id');
        $page_data['page_name'] = 'class_add';
        $page_data['instructors'] = $this->crud_model->sync_instructors($institute_id);
        $page_data['page_title'] = get_phrase('class_add');
        $this->load->view('backend/index', $page_data);
      }
      elseif ($param1 == 'edit_class_form') {
        $institute_id = $this->session->userdata('user_id');
        $page_data['page_name'] = 'class_edit';
        $page_data['class_id'] = $param2;
        $page_data['instructors'] = $this->crud_model->sync_instructors($institute_id);
        $page_data['page_title'] = get_phrase('class_edit');
        $this->load->view('backend/index', $page_data);
      }
    }

    public function ajax_sync_course(){
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $this->user_model->check_plan(true);
      $instructor_id = $this->input->post('instructor_id');
        $data = $this->crud_model->sync_courses($instructor_id);
        echo json_encode($data);
    }

    public function ajax_sync_classes(){
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $course_id = $this->input->post('course_id');
      $data = $this->crud_model->single_instructor_cls($course_id);
        echo json_encode($data);
    }

    public function ajax_sync_students(){
      if ($this->session->userdata('user_login') != true) {
        redirect(site_url('login'), 'refresh');
      }
      $course_id = $this->input->post('course_id');
      $data = $this->crud_model->get_course_students($course_id);
        echo json_encode($data);
    }

    public function payment_settings($param1 = "") {
      $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'paypal_settings') {
            $this->user_model->update_instructor_paypal_settings($this->session->userdata('user_id'));
            redirect(site_url('institute/payment_settings'), 'refresh');
        }
        if ($param1 == 'stripe_settings') {
            $this->user_model->update_instructor_stripe_settings($this->session->userdata('user_id'));
            redirect(site_url('institute/payment_settings'), 'refresh');
        }

        $page_data['page_name'] = 'payment_settings';
        $page_data['page_title'] = get_phrase('payment_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_revenue($param1 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);
        $page_data['payment_history'] = $this->crud_model->get_instructor_revenue();
        $page_data['page_name'] = 'instructor_revenue';
        $page_data['page_title'] = get_phrase('instructor_revenue');
        $this->load->view('backend/index', $page_data);
    }

    public function preview($course_id = '') {
        if ($this->session->userdata('user_login') != 1)
        redirect(site_url('login'), 'refresh');
        $this->user_model->check_plan(true);

        // $this->is_the_course_belongs_to_current_instructor($course_id);
        if ($course_id > 0) {
            $courses = $this->crud_model->get_course_by_id($course_id);
            if ($courses->num_rows() > 0) {
                $course_details = $courses->row_array();
                redirect(site_url('home/lesson/'.slugify($course_details['title']).'/'.$course_details['id']), 'refresh');
            }
        }
        redirect(site_url('instituter/courses'), 'refresh');
    }

    public function sections($param1 = "", $param2 = "", $param3 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);

        if ($param2 == 'add') {
          // $this->is_the_course_belongs_to_current_instructor($param1);
            $this->crud_model->add_section($param1);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_added_successfully'));
        }
        elseif ($param2 == 'edit') {
            // $this->is_the_course_belongs_to_current_instructor($param1, $param3, 'section');
            $this->crud_model->edit_section($param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_updated_successfully'));
        }
        elseif ($param2 == 'delete') {
            // $this->is_the_course_belongs_to_current_instructor($param1, $param3, 'section');
            $this->crud_model->delete_section($param1, $param3);
            $this->session->set_flashdata('flash_message', get_phrase('section_has_been_deleted_successfully'));
        }
        redirect(site_url('institute/course_form/course_edit/'.$param1));
    }

    public function lessons($course_id = "", $param1 = "", $param2 = "") {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $this->user_model->check_plan(true);
        if ($param1 == 'add') {
          $this->db->where('id =', $course_id);
          $course = $this->db->get_where('course', array('id' => $course_id))->row_array();
          $institute = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
          $institute_name = $institute['first_name'] . '_'.$institute['last_name']; 
         
            // $this->is_the_course_belongs_to_current_instructor($course_id);
            $this->crud_model->add_lesson($institute_name);
            redirect('institute/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'edit') {
            // $this->is_the_course_belongs_to_current_instructor($course_id, $param2, 'lesson');
            $this->crud_model->edit_lesson($param2);
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_updated_successfully'));
            redirect('institute/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'delete') {
            // $this->is_the_course_belongs_to_current_instructor($course_id, $param2, 'lesson');
            $this->crud_model->delete_lesson($param2);
            $this->session->set_flashdata('flash_message', get_phrase('lesson_has_been_deleted_successfully'));
            redirect('institute/course_form/course_edit/'.$course_id);
        }
        elseif ($param1 == 'filter') {
            redirect('institute/lessons/'.$this->input->post('course_id'));
        }
        $page_data['page_name'] = 'lessons';
        $page_data['lessons'] = $this->crud_model->get_lessons('course', $course_id);
        $page_data['course_id'] = $course_id;
        $page_data['page_title'] = get_phrase('lessons');
        $this->load->view('backend/index', $page_data);
    }

    // Manage Quizes
    public function quizes($course_id = "", $action = "", $quiz_id = "") {
        $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }

        if ($action == 'add') {
            // $this->is_the_course_belongs_to_current_instructor($course_id);
            $this->crud_model->add_quiz($course_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_added_successfully'));
        }
        elseif ($action == 'edit') {
            // $this->is_the_course_belongs_to_current_instructor($course_id, $quiz_id, 'quize');
            $this->crud_model->edit_quiz($quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_updated_successfully'));
        }
        elseif ($action == 'delete') {
            // $this->is_the_course_belongs_to_current_instructor($course_id, $quiz_id, 'quize');
            $this->crud_model->delete_lesson($quiz_id);
            $this->session->set_flashdata('flash_message', get_phrase('quiz_has_been_deleted_successfully'));
        }
        redirect(site_url('institute/course_form/course_edit/'.$course_id));
    }

    // Manage Quize Questions
    public function quiz_questions($quiz_id = "", $action = "", $question_id = "") {
        $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        $quiz_details = $this->crud_model->get_lessons('lesson', $quiz_id)->row_array();

        if ($action == 'add') {
            // $this->is_the_course_belongs_to_current_instructor($quiz_details['course_id'], $quiz_id, 'quize');
            $response = $this->crud_model->add_quiz_questions($quiz_id);
            echo $response;
        }

        elseif ($action == 'edit') {
            if($this->db->get_where('question', array('id' => $question_id, 'quiz_id' => $quiz_id))->num_rows() <= 0){
              $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quiz_question'));
              redirect(site_url('institute/courses'), 'refresh');
            }

            $response = $this->crud_model->update_quiz_questions($question_id);
            echo $response;
        }

        elseif ($action == 'delete') {
            if($this->db->get_where('question', array('id' => $question_id, 'quiz_id' => $quiz_id))->num_rows() <= 0){
              $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quiz_question'));
              redirect(site_url('institute/courses'), 'refresh');
            }

            $response = $this->crud_model->delete_quiz_question($question_id);
            $this->session->set_flashdata('flash_message', get_phrase('question_has_been_deleted'));
            redirect(site_url('institute/course_form/course_edit/'.$quiz_details['course_id']));
        }
    }

    function manage_profile() {
        redirect(site_url('home/profile/user_profile'), 'refresh');
    }

    function invoice($payment_id = "") {
      $this->user_model->check_plan(true);
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

    public function instructor_form($param1 = "", $param2 = "") {
      $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
        }
        elseif ($param1 == 'add_instructor_form') {
          $page_data['page_name'] = 'instructor_add';
          $page_data['page_title'] = get_phrase('instructor_add');
          $page_data['institutes'] = $this->user_model->get_institute();
          $this->load->view('backend/index', $page_data);
        }
        elseif ($param1 == 'edit_instructor_form') {
          $page_data['page_name'] = 'instructor_edit';
          $page_data['user_id'] = $param2;
          $page_data['institutes'] = $this->user_model->get_institute();
          $page_data['page_title'] = get_phrase('instructor_edit');
          $this->load->view('backend/index', $page_data);
        }
    }

    public function instructors($param1 = "", $param2 = "") {
      $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
        }
        elseif ($param1 == "add") {
          $this->user_model->add_user(4);
          redirect(site_url('institute/instructors'), 'refresh');
        }
        elseif ($param1 == "edit") {
          $this->user_model->edit_user($param2);
          redirect(site_url('institute/instructors'), 'refresh');
        }
        elseif ($param1 == "delete") {
          $this->user_model->delete_user($param2);
          redirect(site_url('institute/instructors'), 'refresh');
        }

        $page_data['page_name'] = 'instructors';
        $page_data['page_title'] = get_phrase('instructor');
        $institute_id = $this->session->userdata('user_id');
        $page_data['instructors'] = $this->crud_model->sync_instructors($institute_id);
        // $page_data['instructors'] = $this->user_model->get_instructors();
        $this->load->view('backend/index', $page_data);
    }

    public function instructor_settings($param1 = "") {
      $this->user_model->check_plan(true);
        if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
        }
        if ($param1 == 'update') {
          $this->crud_model->update_instructor_settings();
          $this->session->set_flashdata('flash_message', get_phrase('instructor_settings_updated'));
          redirect(site_url('institute/instructor_settings'), 'refresh');
        }

        $page_data['page_name'] = 'instructor_settings';
        $page_data['page_title'] = get_phrase('instructor_settings');
        $this->load->view('backend/index', $page_data);
    }

    public function user_form($param1 = "", $param2 = "") {
        if ($this->session->userdata('user_login') != true) {
          redirect(site_url('login'), 'refresh');
        }

        if ($param1 == 'add_user_form') {
          $page_data['page_name'] = 'user_add';
          $page_data['page_title'] = get_phrase('student_add');
          $this->load->view('backend/index', $page_data);
        }
        elseif ($param1 == 'edit_user_form') {
          $page_data['page_name'] = 'user_edit';
          $page_data['user_id'] = $param2;
          $page_data['page_title'] = get_phrase('student_edit');
          $this->load->view('backend/index', $page_data);
        }
    }
    
    public function users($param1 = "", $param2 = "")
    {
        if ($this->session->userdata('user_login') != true) {
            redirect(site_url('login'), 'refresh');
        }
        if ($param1 == "add") {
            $this->user_model->add_user(2, $param2);
            redirect(site_url('institute/classes'), 'refresh');
        }
        if ($param1 == "edit") {
            $this->user_model->edit_user($param2);
            redirect(site_url('institute/classes'), 'refresh');
        } elseif ($param1 == "delete") {
            $this->user_model->delete_user($param2);
            redirect(site_url('institute/classes'), 'refresh');
        }

        $page_data['page_name'] = 'users';
        $page_data['page_title'] = get_phrase('student');
        $page_data['users'] = $this->user_model->get_user($param2);
        $this->load->view('backend/index', $page_data);
    }

    // this function is responsible for managing multiple choice question
    function manage_multiple_choices_options() {
        $page_data['number_of_options'] = $this->input->post('number_of_options');
        $this->load->view('backend/institute/manage_multiple_choices_options', $page_data);
    }

    // This function checks if this course belongs to current logged in instructor
    function is_the_course_belongs_to_current_instructor($course_id, $id = null, $type = null) {
        $course_details = $this->crud_model->get_course_by_id($course_id)->row_array();
        if ($course_details['user_id'] != $this->session->userdata('user_id')) {
            $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_course'));
            redirect(site_url('institute/courses'), 'refresh');
        }

        if($type == 'section' && $this->db->get_where('section', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_section'));
          redirect(site_url('institute/courses'), 'refresh');
        }
        if($type == 'lesson' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_lesson'));
          redirect(site_url('institute/courses'), 'refresh');
        }
        if($type == 'quize' && $this->db->get_where('lesson', array('id' => $id, 'course_id' => $course_id))->num_rows() <= 0){
          $this->session->set_flashdata('error_message', get_phrase('you_do_not_have_right_to_access_this_quize'));
          redirect(site_url('institute/courses'), 'refresh');
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
