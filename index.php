<?php 

## Start includes

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/database/database.php";
require_once __DIR__ . "/src/settlement/settlement.php";
require_once __DIR__ . "/src/account/account.php";
require_once __DIR__ . "/src/transaction/transaction.php";

## End includes

## 1. List all settlements from all known accounts
$accountClass = new Account();
$accountList = $accountClass->listAccounts();

foreach ($accountList as $account){

    echo "Getting settlements for account <b> " . $account['accountId'] . "</b><br>";

    $settlementClass = new Settlement;
    $settlements = $settlementClass->listSettlement($account['organisationKey']);

    foreach ($settlements as $settlement){
        $settlementClass->checkAndInstertSettlement($settlement->reference);
    }

    $accountClass->setLastCheck($account['accountId']);
}

## 2. Retrieve unsplitted settlements 
$settlementClass = new Settlement;
$unsplitted = $settlementClass->listUnsplitted();

if(!empty($unsplitted)){
    echo "<br> Found new settlements! <br><br>";
}else{
    echo "<br> No new settlements found <br><br>";
}

foreach($unsplitted as $settlement){

    $newSettlement = $settlement['settlementReference'];
    $totalVolumeSettlement = 0;
    $totalVolumeSplit = 0;
    $totalTransactionsSplit = 0;

    $count = 1;

    echo "<b>Splitting settlement " . $newSettlement . "</b><br>";

    $adminId = explode(".",$newSettlement);
    $adminId = $adminId[0];
    $settlementAccount = $accountClass->checkAccount($adminId);

    $settlementDetail = $settlementClass->retrieveSettlement($newSettlement, $settlementAccount[0]['organisationKey']);

    $totalVolumeSettlement = $settlementDetail->amount->value;
    $totalSettledCurrency = $settlementDetail->amount->currency;
    
    echo "Total of Settlement: " . $totalVolumeSettlement . " " . $totalSettledCurrency . "<br><br>";

## 3. Create individual transactions

    ## 3.1 Get first page
    $payments = $settlementDetail->payments();
    $totalTransactionsSplit+= $payments->count();

    foreach ($payments as $payment){

        if(isset($payment->settlementAmount->value)){
        
        $id = $payment->id;
        $amount = $payment->settlementAmount->value;
        $currency = $payment->settlementAmount->currency;
        $description = $payment->description;
        
        $totalVolumeSplit += $amount;
        
        echo $count . ". " . $id . " - " . $description . " - " . $amount . " " . $currency . "<br>";
        
        $transaction = new Transaction;
        $transaction->createTransaction($id, $newSettlement, $description, $amount, $currency);
        
        $count+= 1;
        
        }
        
    }

    ## 3.2 Get next settlement 

    while(null !== $payments->next()){
    
        $payments = $payments->next();
        $totalTransactionsSplit+= $payments->count();

        foreach ($payments as $payment){

            if(isset($payment->settlementAmount->value)){
            
            $id = $payment->id;
            $amount = $payment->settlementAmount->value;
            $currency = $payment->settlementAmount->currency;
            $description = $payment->description;
            
            $totalVolumeSplit += $amount;
            
            echo $count . ". " . $id . " - " . $description . " - " . $amount . " " . $currency . "<br>";
            
            $transaction = new Transaction;
            $transaction->createTransaction($id, $newSettlement, $description, $amount, $currency);
            
            $count+= 1;
            
            }
            
        }

    }

    echo "<br><u>Total # transactions in Settlement: " . $totalTransactionsSplit . "</u><br>";
    echo "<u>Total volume in split: " . $totalVolumeSplit . "</u><br>";

    $settlementClass->setSplitted($newSettlement,$totalVolumeSettlement);

}


## 4. Create XML file with queued transactions


?>