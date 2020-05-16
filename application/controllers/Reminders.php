<?php class Reminders extends CI_Controller { public function __construct() { parent::__construct(); 
    // $this->load->library('email');
    $this->load->model('Appointment_model');
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
           
            foreach($live_sessions as $live_session)
            {
                $class_students = $this->user_model->get_class_enrolled_students($live_session['class_id'])->result_array();
                $index = 0;
                foreach($class_students as $class_student){
                 
                    $data= $this->crud_model->create_live_session($class_student);
                    if ($data == -1 || empty($class_students)){
                        $admin_email = 'fixyourcell.ca@gmail.com';
                        $mail_subject = 'somthing went wrong with live session or their no student in class please check server';
                        $mail_body = 'Something went wrong Contact Admin';
                        $this->email_model->send_mail_for_live_session_confirmation($admin_email,$class_student['email'],$mail_subject, $mail_body);
                    }
                    else{ 
                        if($index == 0){
                         
                            $this->db->where('id =', $live_session['class_id']);
                            $class = $this->db->get('classes')->result_array();
                          
                            $this->db->where('id =', $class[0]['course_id']);
                            $course = $this->db->get('course')->result_array();
                           
                            $this->db->where('id =', $course[0]['instructor_id']);
                            $instructor = $this->db->get('users')->result_array();
                          
                            $mail_body = 'your live session is going to start in '.gmdate("Y-m-d\TH:i:s\Z",$live_session['start_time']).'and end at '.gmdate("Y-m-d\TH:i:s\Z",$live_session['end_time']);
                            $mail_subject = 'live sessions start links \n Teacher Url: '.$data['admin_url'];
                            $this->email_model->send_mail_for_live_session_confirmation($class_student['email'], $mail_body,$mail_subject);
                            $this->Appointment_model->mark_reminded($live_session->id);
                        }
                        $index++;                              
                        $mail_body = 'your live session is going to start in '.gmdate("Y-m-d\TH:i:s\Z",$live_session['start_time']).'and end at '.gmdate("Y-m-d\TH:i:s\Z",$live_session['end_time']);
                        $mail_subject = 'live sessions start links <br />  \n Url: '.$data['student_url'];
                        $this->email_model->send_mail_for_live_session_confirmation($class_student['email'], $mail_body,$mail_subject);
                        $this->Appointment_model->mark_reminded($live_session->id);
                    }   
                    sleep(30);   
                }
            }
          

        }
        else{
            echo 'no record of live session found';
            // stop ec2 server
            $ec2_server->switch_ec2_on_server();
        }
   }
}