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
        $live_sessions = $this->Appointment_model->get_days_appointments();
        if(!empty($live_sessions))
        {
            foreach($live_sessions as $live_session)
            {
              // send email to all the student of particular class
            
                if ($this->crud_model->create_live_session() == false){
                    $mail_subject = 'somthing went wrong with live session please check server';
                    $mail_body = 'Something went wrong Contact Admin';
                    $this->email_model->send_mail_for_live_session_confirmation($mail_subject, $mail_body);
                }
                else{
                    $mail_subject = 'your live session is going to start in '.$live_session->start_time.'and end at '.$live_session->end_time;
                    $mail_body = 'below are links of live sessions';
                    $this->email_model->send_mail_for_live_session_confirmation($mail_subject, $mail_body);
                    $this->Appointment_model->mark_reminded($live_session->id);
                }      
            }
            // starting my ec2 server
            // $ec2_server->switch_ec2_on_server();

        }
        else{
         
            // stop ec2 server
            $ec2_server->switch_ec2_on_server();
        }
   }
}