<?php

class Transaction{

    public function createTransaction($transactionId, $settlementId, $description, $amount, $currency, $accountId, $destinationName, $destinationIban){

        $db = new Database();
        $db->connect();
        $db->insert('transactions',array('transactionId'=>$transactionId,'settlementId'=>$settlementId,'description'=>$description, 'amount'=>$amount, 'currency'=>$currency, 'accountId'=>$accountId,'destinationName'=>$destinationName,'destinationIban'=>$destinationIban,'date_created'=>date("Y-m-d H:i:s"))); 
        $res = $db->getResult();  

    }

    public function selectUnsplitted(){
        $db = new Database();
        $db->connect();
        $db->select('transactions', 'id, transactionId, description, amount, currency, accountId, destinationName, destinationIban', null , "outboundId is null");
        $res = $db->getResult();
        return $res;
    }

    public function setOutboundId($outboundId, $id){
        $db = new Database();
        $db->connect();
        $db->update('transactions',array('outboundId'=>$outboundId),"id=" . $id); // Table name, column names and values, WHERE conditions
        $res = $db->getResult();
    }

}