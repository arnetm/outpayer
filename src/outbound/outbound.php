<?php

use Google\Cloud\Storage\StorageClient;

class Outbound{

    public function createOutbound($countOutbound, $runId){

        $db = new Database();
        $db->connect();
        $db->insert('outbounds',array('transactions'=>$countOutbound,'runId'=>$runId,'date_created'=>date("Y-m-d H:i:s"))); 
        $res = $db->getResult();  

        return $res;

    }

    public function setCompleted($outboundId){

        $db = new Database();
        $db->connect();
        $db->update('outbounds',array('date_completed'=>date("Y-m-d H:i:s")),"id=" . $outboundId); // Table name, column names and values, WHERE conditions
        $res = $db->getResult(); 

    }

    public function generateOutbound($outboundTransactions, $outboundId){

        $creationDate = date("Y-m-d\TH:i:s");
        $executionDate = date("Y-m-d");
        $messageID = $outboundId;
        $paymentID = $outboundId;
        $numberOfTransactions = count($outboundTransactions);


        //header('Content-type: text/xml');
        //header('Content-Disposition: attachment; filename=' . $messageID . '.xml');

        $writer = new XMLWriter();
        //$writer->openURI('php://output');
        $writer->openMemory();
        $writer->startDocument('1.0','UTF-8');    
            $writer->startElement('Document');
                $writer->writeAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
                $writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

                $writer->startElement("CstmrCdtTrfInitn");

                    //Header

                    $writer->startElement("GrpHdr");

                        $writer->writeElement('MsgId', $messageID);
                        $writer->writeElement('CreDtTm', $creationDate);
                        $writer->writeElement('BtchBookg', "false");
                        $writer->writeElement('NbOfTxs', $numberOfTransactions);
                        $writer->writeElement('Grpg', "MIXD");

                        $writer->startElement("InitgPty");
                            $writer->writeElement('Nm', "Mollie"); 
                        $writer->endElement();

                    $writer->endElement();

                    //For each payment

                    $writer->startElement("PmtInf");

                        $writer->writeElement('PmtMtd', "TRF"); 

                        $writer->startElement("PmtTpInf");

                            $writer->writeElement('InstrPrty', "HIGH"); 
                            
                            $writer->startElement("SvcLvl");

                                $writer->writeElement('Cd', "SEPA");

                            $writer->endElement(); 


                    $writer->endElement();

                    $writer->writeElement('ReqdExctnDt', $executionDate);

                    $writer->startElement("Dbtr");

                        $writer->writeElement('Nm', "Mollie");

                    $writer->endElement();

                    $writer->startElement("DbtrAcct");
                        $writer->startElement("Id");
                            $writer->writeElement('IBAN', "BE68539007547034");
                        $writer->endElement();
                    $writer->endElement();

                    $writer->startElement("DbtrAgt");
                        $writer->startElement("FinInstnId");
                            $writer->writeElement('BIC', "GKCCBEBB");
                        $writer->endElement();
                    $writer->endElement();

                foreach ($outboundTransactions as $transaction){

                    $writer->startElement("CdtTrfTxInf");
                        $writer->startElement("PmtId");
                            $writer->writeElement('EndToEndId', $transaction['id']);
                        $writer->endElement();

                        $writer->startElement("Amt");
                            $writer->startElement("InstdAmt");
                                $writer->writeAttribute('Ccy', $transaction['currency']);
                                $writer->text(number_format($transaction['amount'],2));    
                            $writer->endElement();
                        $writer->endElement();

                        $writer->startElement("Cdtr");
                            $writer->writeElement('Nm', $transaction['destinationName']);
                        $writer->endElement();

                        $writer->startElement("CdtrAcct");
                            $writer->startElement("Id");
                                $writer->writeElement('IBAN', $transaction['destinationIban']);
                            $writer->endElement();
                        $writer->endElement();

                        $writer->startElement("RmtInf");
                            $writer->writeElement('Ustrd', $transaction['description']);
                        $writer->endElement();

                    $writer->endElement();

                }



                $writer->endElement();
            $writer->endElement();
        $writer->endDocument();

        $content = $writer->flush();
        
        $bucketName = "outpayer.appspot.com";
        $objectName = $outboundId . ".xml";

        $storage = new StorageClient();
        $bucket = $storage->bucket($bucketName);
        $object = $bucket->upload($content, [
            'name' => $objectName
        ]);

        $url = "https://storage.cloud.google.com/" . $bucketName . "/" . $objectName;

        return $url;
    

    }



}

?>