<?php
require 'vendor/autoload.php';
use Aws\S3\S3Client;
class S3_model {
    function __construct(){
        echo 'constructior called';
      
   }

   public function create_s3_object(){
        $s3 = new S3Client([
            'version' => 'latest',
            'region'  => 'eu-west-1',
            'credentials' => [
            'key'    => 'AKIA3KJKHLCDC3YNKUAQ',
            'secret' => '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'
        ]
        ]);
        return $s3;
    }

// Upload a publicly accessible file. The file size and type are determined by the SDK.
    public function upload_data($s3, $key,$video_path){
        $bucketName = 'clms-storage';
        // $file_Path = __DIR__ . '/bhai.jpg';     
        $key = basename($video_path) . '.mp4';    
        try {
            $result = $s3->putObject([
                'Bucket' => $bucketName,
                'Key'    => $key,
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