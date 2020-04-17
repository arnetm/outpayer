<?php 

## Start includes

require_once __DIR__ . "/vendor/autoload.php";
require_once __DIR__ . "/src/database/database.php";
require_once __DIR__ . "/src/settlement/settlement.php";
require_once __DIR__ . "/src/account/account.php";
require_once __DIR__ . "/src/transaction/transaction.php";
require_once __DIR__ . "/src/outbound/outbound.php";
require_once __DIR__ . "/src/run/run.php";
require_once __DIR__ . "/src/log/log.php";

## End includes

## Create Function Class

$transactionClass = new Transaction;
$accountClass = new Account;
$settlementClass = new Settlement;
$outboundClass = new Outbound;
$runClass = new Run;
$logClass = new Log;

## End Function Class


## 0. Create Run

$result = $runClass->createRun();
$runId = $result[0];

## 1. List all settlements from all known accounts

$accountList = $accountClass->listAccounts();

foreach ($accountList as $account){

    echo "Getting settlements for account <b> " . $account['accountId'] . "</b><br>";


    $settlements = $settlementClass->listSettlement($account['organisationKey']);

    foreach ($settlements as $settlement){
        $settlementClass->checkAndInstertSettlement($settlement->reference);
    }

    $accountClass->setLastCheck($account['accountId']);
}

## 2. Retrieve unsplitted settlements 
$unsplitted = $settlementClass->listUnsplitted();


if(!empty($unsplitted)){
    echo "<br> Found new settlements! <br><br>";
}else{
    echo "<br> No new settlements found <br><br>";
}

$count = 0;

foreach($unsplitted as $settlement){

    $newSettlement = $settlement['settlementReference'];
    $totalVolumeSettlement = 0;
    $totalVolumeSplit = 0;
    $totalTransactionsSplit = 0;

    echo "<h3>Splitting settlement " . $newSettlement . "</h3>";

    $adminId = explode(".",$newSettlement);
    $adminId = $adminId[0];
    $settlementAccount = $accountClass->checkAccount($adminId);

    $settlementDetail = $settlementClass->retrieveSettlement($newSettlement, $settlementAccount[0]['organisationKey']);

    $destinationName = $settlementAccount[0]['name'];
    $destinationIban = $settlementAccount[0]['bankAccount'];

    $totalVolumeSettlement = $settlementDetail->amount->value;
    $totalSettledCurrency = $settlementDetail->amount->currency;
    
    echo "Total of Settlement: " . $totalVolumeSettlement . " " . $totalSettledCurrency . "<br>";

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
                
        $outboundTransactions[$count]['id'] = $id;
        $outboundTransactions[$count]['description'] = $description;
        $outboundTransactions[$count]['amount'] = $amount;
        $outboundTransactions[$count]['currency'] = $currency;
        $outboundTransactions[$count]['destinationName'] = $destinationName;
        $outboundTransactions[$count]['destinationIban'] = $destinationIban;
        
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
            
            $outboundTransactions[$count]['id'] = $id;
            $outboundTransactions[$count]['description'] = $description;
            $outboundTransactions[$count]['amount'] = $amount;
            $outboundTransactions[$count]['currency'] = $currency;
            $outboundTransactions[$count]['destinationName'] = $destinationName;
            $outboundTransactions[$count]['destinationIban'] = $destinationIban;
            
            $count+= 1;
            
            }
            
        }

    }

    echo "<u>Total # transactions in Settlement: " . $totalTransactionsSplit . "</u><br>";
    echo "<u>Total volume in split: " . $totalVolumeSplit . "</u><br>";

    $settlementClass->setSplitted($newSettlement,$totalVolumeSettlement,$totalTransactionsSplit);

}


## 4. Create XML file with queued transactions

if(!empty($outboundTransactions)){

    $countOutbound = count($outboundTransactions);

    //Create outbound in DB and return ID of created outbound
    $result = $outboundClass->createOutbound($countOutbound, $runId);
    
    $outboundClass->generateOutbound($outboundTransactions, $result[0]);

    foreach($unsplitted as $settlement){
        $settlementClass->setOutboundId($result[0],$settlement['id']);
    }

    $outboundClass->setCompleted($result[0]);

    
}

## 5. End Run

$runClass->endRun($runId);

// To-Do: (feature) Add logging 
// To-Do: Not shifted fee clients!

?>