<?php

class Transaction{

    public function createTransaction($transactionId, $settlementId, $description, $amount, $currency){

        $db = new Database();
        $db->connect();
        $data = $db->escapeString("name5@email.com"); // Escape any input before insert
        $db->insert('transactions',array('transactionId'=>$transactionId,'settlementId'=>$settlementId,'description'=>$description, 'amount'=>$amount, 'currency'=>$currency, 'settled'=>'0')); 
        $res = $db->getResult();  

    }

}