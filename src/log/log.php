<?php

class Log{

    //type 1 = error
    //type 2 = notice

    public function createLog($type, $run, $message){
        $db = new Database();
        $db->connect();
        $db->insert('logs',array('type'=>$type,'message'=>$message,'run'=>$run)); 
        $res = $db->getResult();  
    }

}