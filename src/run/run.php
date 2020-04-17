<?php

class Run{

    public function createRun()
    {
        $db = new Database();
        $db->connect();
        $db->insert('runs',array('date_started'=>date("Y-m-d H:i:s"))); 
        $res = $db->getResult();  
        return $res;
    }

    public function endRun($id){
        $db = new Database();
        $db->connect();
        $db->update('runs',array('date_ended'=>date("Y-m-d H:i:s")),"id=" . $id); // Table name, column names and values, WHERE conditions
        $res = $db->getResult();  
        return $res;
    }

}