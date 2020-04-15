<?php

$creationDate = date("Y-m-d\Th:i:s");
$executionDate = date("Y-m-d");
$messageID = "trf_gjrkefk";
$paymentID = "pmt_dkfjldk";
$numberOfTransactions = 1;

$writer = new XMLWriter();
$writer->openURI('php://output');
$writer->startDocument('1.0','UTF-8');    
    $writer->startElement('Document');
        $writer->writeAttribute('xmlns', 'urn:iso:std:iso:20022:tech:xsd:pain.001.001.03');
        $writer->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');

        $writer->startElement("CstmrCdtTrfInitn");

            //Header

            $writer->startElement("GrpHdr");

                $writer->writeElement('MsgId', $messageID);
                $writer->writeElement('CreDtTm', $creationDate);
                $writer->writeElement('NbOfTxs', $numberOfTransactions);

                $writer->startElement("InitgPty");
                    $writer->writeElement('Nm', "Mollie"); 
                $writer->endElement();

            $writer->endElement();

            //For each payment

            $writer->startElement("PmtInf");

                $writer->writeElement('PmtInfId', $paymentID);
                $writer->writeElement('PmtMtd', "TRF"); 
                $writer->writeElement('BtchBookg', "false"); 

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

            $writer->startElement("CdtTrfTxInf");
                $writer->startElement("PmtId");
                    $writer->writeElement('EndToEndId', "ABC/4562/2010-12-18");
                $writer->endElement();

                $writer->startElement("Amt");
                    $writer->startElement("InstdAmt");
                        $writer->writeAttribute('Ccy', "EUR");
                        $writer->text("535.25");    
                    $writer->endElement();
                $writer->endElement();

                $writer->startElement("Cdtr");
                    $writer->writeElement('Nm', "SocMetal");
                $writer->endElement();

                $writer->startElement("CdtrAcct");
                    $writer->startElement("Id");
                        $writer->writeElement('IBAN', "BE43187123456701");
                    $writer->endElement();
                $writer->endElement();

                $writer->startElement("RmtInf");
                    $writer->writeElement('Ustrd', "Invoice 378265");
                $writer->endElement();

            $writer->endElement();



        $writer->endElement();
    $writer->endElement();
$writer->endDocument();

$writer->flush();

header('Content-type: text/xml');

echo $writer->outputMemory();
?>