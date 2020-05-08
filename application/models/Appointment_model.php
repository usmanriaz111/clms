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
            $end_time=$start_time+3600;    
            $this->db->where('start_time >=', $start_time);
            $this->db->where('start_time <=', $end_time);
            $this->db->where('status !=', 2);
            $result = $this->db->get('live_sessions')->result_array();
            return $result; 
        }
     
        public function mark_reminded($appointment_id)
        {
          return $this->db->where('id', $appointment_id)->update('live_sessions', array('status' => 2));
        }
    }
?>