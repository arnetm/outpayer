<?php

class Settlement{

    public function retrieveSettlement($settlementId, $organisationKey){

        try {

            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setAccessToken($organisationKey);

            $settlements = $mollie->settlements->get($settlementId);
            return $settlements;

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            echo "API call failed: " . htmlspecialchars($e->getMessage());
        }

    }

    public function listSettlement($organisationKey){

        try {

            $mollie = new \Mollie\Api\MollieApiClient();
            $mollie->setAccessToken($organisationKey);

            $settlements = $mollie->settlements->page();
            return $settlements;

        } catch (\Mollie\Api\Exceptions\ApiException $e) {
            echo "API call failed: " . htmlspecialchars($e->getMessage());
        }

    }

    public function checkAndInstertSettlement($settlementReference){
        $db = new Database();
        $db->connect();
        $db->select('settlements', "id, settlementReference, splitted", null, "settlementReference = '" . $settlementReference . "'"); // Table name
        $res = $db->getResult();
        if (empty($res)){
            $db->insert('settlements',array('settlementReference'=>$settlementReference,'splitted'=>'0'));  // Table name, column names and respective values
        }
    }

    public function listUnsplitted(){
        $db = new Database();
        $db->connect();
        $db->select('settlements', "id, settlementReference, splitted", null, "splitted = 0"); // Table name
        $res = $db->getResult(); 
        return $res;
    }

    public function setSplitted($settlementReference, $totalSplit, $totalTransactions){
        $db = new Database();
        $db->connect();
        $db->update('settlements',array('splitted'=>"1", "totalSplit"=>$totalSplit,"totalTransactions"=>$totalTransactions),"settlementReference='" . $settlementReference . "'"); // Table name, column names and values, WHERE conditions
        $res = $db->getResult();  
    }

    public function setOutboundId($outboundId, $id){
        $db = new Database();
        $db->connect();
        $db->update('settlements',array('outboundId'=>$outboundId),"id=" . $id); // Table name, column names and values, WHERE conditions
        $res = $db->getResult();
    }

} 

?>