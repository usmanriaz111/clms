<?php
defined('BASEPATH') or exit('No direct script access allowed');
    class Appointment_model extends CI_Model{
        function __construct()
	    {   $this->load->database();
		    parent::__construct();
    	}
        public function get_days_appointments()
        {  
            // date_default_timezone_set("Asia/Calcutta");
            $start_time=strtotime("now");
            $end_time=$start_time+950;    
            $this->db->where('start_time >=', $start_time);
            $this->db->where('start_time <=', $end_time);
            $this->db->where('status =', 0);
            $result = $this->db->get('live_sessions')->result_array();
            return $result; 
        }
        public function get_continued_appointments()
        {  
            // date_default_timezone_set("Asia/Calcutta")        
            $this->db->where('status =', 1);
            $result = $this->db->get('live_sessions')->result_array();
            return $result; 
        }
        public function get_ended_appointments()
        {  
            // date_default_timezone_set("Asia/Calcutta")        
            $this->db->where('status =', 2);
            $result = $this->db->get('live_sessions')->result_array();
            return $result; 
        }
        public function mark_end($appointment_id)
        {
            $data = [
                'status' => 2,
            ];
            $this->db->where('id', $appointment_id);
            $this->db->update('live_sessions', $data);
        //   return $this->db->where('id', $appointment_id)->update('live_sessions', array('status' => 2));
        }
        public function mark_continue($appointment_id)
        {
            $data = [
                'status' => 1,
            ];
            $this->db->where('id', $appointment_id);
            $this->db->update('live_sessions', $data);
            
        //   return $this->db->where('id', $appointment_id)->update('live_sessions', array('status' => 1));
        }
    }
?>