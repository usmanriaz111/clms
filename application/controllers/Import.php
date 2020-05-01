<?php
 
defined('BASEPATH') OR exit('No direct script access allowed');
 
class Import extends CI_Controller {
    // construct
    public function __construct() {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
        // load model
        $this->load->model('Import_model', 'import');
        $this->load->helper(array('url','html','form'));
    }    
 
 
    public function importFile(){
        // if ($this->session->userdata('admin_login') != true) {
        //     $this->session->set_flashdata('error_message', get_phrase('user_are_not_authorized_to_perform_action'));
        //     redirect(site_url('admin/users'), 'refresh');
        //   }
      if ($this->input->post('submit')) {
                $path = 'uploads/';
                require_once APPPATH . "/third_party/PHPExcel.php";
                $config['upload_path'] = $path;
                $config['allowed_types'] = 'xlsx|xls|csv';
                $config['remove_spaces'] = TRUE;
                $this->load->library('upload', $config);
                $this->upload->initialize($config); 
                
                if (!$this->upload->do_upload('uploadFile')) {
                    $user_role = $this->session->userdata('role_name');
                    $error = array('error' => $this->upload->display_errors());
                    $this->session->set_flashdata('error_message', get_phrase('file_not_uploaded'));
                    redirect(site_url($user_role.'/classes'), 'refresh');
                } else {
                    $data = array('upload_data' => $this->upload->data());
                }
                if(empty($error)){
                  if (!empty($data['upload_data']['file_name'])) {
                    $this->session->set_flashdata('error_message', get_phrase('File not found!'));
                    $import_xls_file = $data['upload_data']['file_name'];
                } else {
                    $import_xls_file = 0;
                }
                $inputFileName = $path . $import_xls_file;
                 
                try {
                    $class_id = $this->input->post('class_id');
                    $cls = $this->db->get_where('classes', array('id' => $class_id))->row_array();
                    $course = $this->db->get_where('course', array('id' => $cls['course_id']))->row_array();
                    $user = $this->db->get_where('users', array('id' => $course['user_id']))->row_array();
                    $institute_id = $user['institute_id'];

                    $inputFileType = PHPExcel_IOFactory::identify($inputFileName);
                    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                    $objPHPExcel = $objReader->load($inputFileName);
                    $allDataInSheet = $objPHPExcel->getActiveSheet()->toArray(null, true, true, true);
                    $flag = true;
                    $i=0;
                    foreach ($allDataInSheet as $value) {
                      if($flag){
                        $flag =false;
                        continue;
                      }
                      $inserdata[$i]['first_name'] = $value['A'];
                      $inserdata[$i]['last_name'] = $value['B'];
                      $inserdata[$i]['email'] = $value['C'];
                      $inserdata[$i]['password'] = sha1(html_escape($value['D']));
                      $inserdata[$i]['role_id'] = 2;
                      $inserdata[$i]['class_id'] = $class_id;
                      $inserdata[$i]['date_added'] = strtotime(date('D, d-M-Y'));
                      $inserdata[$i]['status'] = 1;
                      $i++;
                    }     
                    $result = $this->import->insert($inserdata, $institute_id);   
                    if($result){
                        $this->session->set_flashdata('flash_message', get_phrase('user_imported_successfully'));
                        redirect(site_url('admin/users'), 'refresh');
                    }else{
                      echo "ERROR !";
                    }             
      
              } catch (Exception $e) {
                   die('Error loading file "' . pathinfo($inputFileName, PATHINFO_BASENAME)
                            . '": ' .$e->getMessage());
                }
              }else{
                  echo $error['error'];
                }
                 
                 
        }
        $this->load->view('import');
    }
     
}
?>