<?php
require 'vendor/autoload.php';
use Aws\Ec2\Ec2Client;
class EC2_model {
    function __construct(){
        echo 'constructior ec2 called';
      
   }

   public function switch_ec2_on_server(){
      
       echo $_ENV["version"];
       
        $ec2 = new  Aws\Ec2\Ec2Client([
            'version' => $_ENV["version"],
            'region'  => 'eu-west-1',
            'credentials' => [
            'key'    => $_ENV["key_admin"],
            'secret' => $_ENV["secret_admin"]
        ]
        ]);
        $instanceIds = array($_ENV["ec2_instance_id"]);
        $result = $ec2->startInstances(array(
            'InstanceIds' => $instanceIds,
        ));
        
    }
    public function switch_ec2_off_server(){
        echo $_ENV["version"];
         $ec2 = new  Aws\Ec2\Ec2Client([
             'version' => $_ENV["version"],
             'region'  => 'eu-west-1',
             'credentials' => [
             'key'    => $_ENV["key_admin"],
             'secret' => $_ENV["secret_admin"]
         ]
         ]);
         $instanceIds = array($_ENV["ec2_instance_id"]);
         $result = $ec2->stopInstances(array(
             'InstanceIds' => $instanceIds,
         ));
     }
}
?>