<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;
class S3_model {
    function __construct(){
        echo 'constructior called';
      
   }

   public function create_s3_object(){
        $s3 = new S3Client([
            'version' => $_ENV["version"],
            'region'  => $_ENV["region"],
            'credentials' => [
            'key'    => $_ENV["key_admin"],
            'secret' => $_ENV["secret_admin"]
        ]
        ]);
        return $s3;
    }

// Upload a publicly accessible file. The file size and type are determined by the SDK.
    public function upload_data($s3, $key,$video_path, $ext_name,  $institute_name){
        $bucketName = 'clms-storage';
        // $file_Path = __DIR__ . '/bhai.jpg';   
        
        $video_extensions = ['FLV', 'MP4', 'WMV','AVI', 'MOV'];
        $ext_index = array_search($ext_name, $video_extensions);
        $tmp_path = '.'.strtolower($video_extensions[$ext_index]);  
        $key = basename($video_path) . $tmp_path; 
        try {
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    =>  $institute_name.'/'.$key,
                'Body'   => fopen($video_path, 'r'),
                'ACL'    => 'public-read',
            ]);
            // echo $result->get('ObjectURL');
        } catch (Aws\S3\Exception\S3Exception $e) {
            echo "There was an error uploading the file.\n";
            echo $e->getMessage();
     }
    return $result;
    }
}
?>