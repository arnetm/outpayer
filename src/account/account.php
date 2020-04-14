<?php 

class Account{

    public function checkAccount($accountId){       
        
        $where = "accountId='" . $accountId . "'";

        $db = new Database();
        $db->connect();
        $db->select('accounts', 'id, accountId, bankAccount, organisationKey', null , $where); 
        $res = $db->getResult();
        return $res;

    }

    public function listAccounts(){
        $db = new Database();
        $db->connect();
        $db->select('accounts'); // Table name
        $res = $db->getResult();
        return $res;
    }

    public function setLastCheck($accountId){
        $db = new Database();
        $db->connect();
        $db->update('accounts',array('lastCheck'=>date("Y-m-d H:i:s")),"accountId=" . $accountId); // Table name, column names and values, WHERE conditions
        $res = $db->getResult();
    }

}

?>