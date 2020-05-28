<?php class Reminders extends CI_Controller { public function __construct() { parent::__construct(); 
    // $this->load->library('email');
    $this->load->model('Appointment_model');
  }

  function get_instructor($class_id){
    $this->db->where('id =', $class_id);
    $class = $this->db->get('classes')->result_array();
  
    $this->db->where('id =', $class[0]['course_id']);
    $course = $this->db->get('course')->result_array();
   
    $this->db->where('id =', $course[0]['instructor_id']);
    $instructor = $this->db->get('users')->result_array();
    return $instructor;
  }
 
  public function index()
  {
           
        // if(!$this->input->is_cli_request())
        // {
        //     echo "This script can only be accessed via the command line" . PHP_EOL;
        //     return;
        // }
        $ec2_server = new EC2_model();
        $live_sessions = $this->Appointment_model->get_days_appointments();
        if(!empty($live_sessions))
        {
           
              // starting my ec2 server
            $ec2_server->switch_ec2_on_server();
           
            sleep(10);
            foreach($live_sessions as $live_session)
            {
                
                $class_students = $this->user_model->get_class_enrolled_students($live_session['class_id'])->result_array();
                $current_instructor=$this->get_instructor($live_session['class_id']);
                $data= $this->crud_model->create_live_session($current_instructor[0]['first_name'],$class_students, $live_session);
                
                if ($data == -1 || empty($class_students)){
                    // admin email
                    $admin_email = 'fixyourcell.ca@gmail.com';
                    $mail_subject = 'somthing went wrong with live session or their no student in class please check server';
                    $mail_body = 'Something went wrong Contact Admin';
                    $this->email_model->send_mail_for_live_session_confirmation($admin_email, $mail_body,$mail_subject);
                    break;
                }
                for ($i = 0; $i < count($class_students); $i++){
                // foreach($class_students as $class_student ){
               
                       $current_time = strtotime("now"); 
                       if ($live_session['end_time'] <=  $current_time){
                           $this->Appointment_model->mark_end($live_session['id']);
                           $currest_live_session = $this->db->get_where('live_sessions', array('id' => $live_session['id']))->row_array();
                          $url = 'https://dynamiclogicltd.info/bigbluebutton/api/end?meetingID='.$currest_live_session['meeting_id'].'&password=333444&checksum='.$currest_live_session['checksum'];

                           $ch = curl_init();
                            curl_setopt ( $ch, CURLOPT_URL, $url );
                            curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
                            curl_setopt ( $ch, CURLOPT_TIMEOUT, 10 );
                            $http_respond = curl_exec($ch);
                            $http_respond = trim( strip_tags( $http_respond ) );
                            $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
                            curl_close( $ch );

                           continue;
                           

                       }
                        if($i == 0){
                            
                            $this->db->where('id =', $live_session['class_id']);
                            $class = $this->db->get('classes')->result_array();
                            
                            $this->db->where('id =', $class[0]['course_id']);
                            $course = $this->db->get('course')->result_array();
                           
                            $this->db->where('id =', $course[0]['instructor_id']);
                            $instructor = $this->db->get('users')->result_array();
                          
                           
                            $mail_body = 'your live session is going to start in '.gmdate("Y-m-d\TH:i:s\Z",$live_session['start_time']).'and end at '.gmdate("Y-m-d\TH:i:s\Z",$live_session['end_time']);
                            $mail_subject = 'live sessions start links  Teacher Url: '.$data['admin_url'];
                            $this->email_model->send_mail_for_live_session_confirmation($instructor[0]['email'], $mail_body,$mail_subject);
                        }     
                                           
                        $mail_body = 'your live session is going to start in '.gmdate("Y-m-d\TH:i:s\Z",$live_session['start_time']).'and end at '.gmdate("Y-m-d\TH:i:s\Z",$live_session['end_time']);
                        $mail_subject = 'student live sessions start links <br />   Url: '.$data['student_urls'][$i];
                        $this->email_model->send_mail_for_live_session_confirmation($class_students[$i]['email'], $mail_body,$mail_subject);
                        $this->Appointment_model->mark_continue($live_session['id']);
                                        
                      
                }
                sleep(10); 
            }
          

        }
        else{
            echo 'no record of live session found';
            // stop ec2 server
            $ec2_server->switch_ec2_off_server();
        }
   }
}