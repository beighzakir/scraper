<?php

echo $getcwd = getcwd();
require_once 'amazon-mws-master/vendor/autoload.php';
include_once __DIR__ . '/includes/init.php';
$date = date('Y-m-d H:i:s');
logMessage("Reqestreportstats.php Starts----> ".$date);
class GetReportRequestStatus {

    protected $reportRequestId;
    protected $reportId;
    protected $client;
    protected $statusReport;

    function __construct() {
    }

    public function startOfProgram() {
        $date = date('Y-m-d H:i:s');
        $config = getAmazonAPIConfiguration();
        $this->client = new MCS\MWSClient([
            'Marketplace_Id' => $config['MARKETPLACE_ID'],
            'Seller_Id' => $config['MERCHANT_ID'],
            'Access_Key_ID' => $config['AWS_ACCESS_KEY_ID'],
            'Secret_Access_Key' => $config['AWS_SECRET_ACCESS_KEY'],
        ]);
        //$this->fetchReport();exit();
        $statusReport = array();
        $this->getReportRequestId();
        $reportStatus = $this->client->GetReportRequestStatus($this->reportRequestId);
        if (isset($reportStatus['ReportProcessingStatus']) && $reportStatus['ReportProcessingStatus'] == '_DONE_') {
            $this->reportId = $reportStatus['GeneratedReportId'];
            
            $this->fetchReport();
            print_r($this->statusReport);
            if($this->statusReport == 'success')
            {
                $this->updateReportStatus();
                echo $message = "\nStatus Report Updated Successfully\n";
                logMessage($message);
            }
        } else {
            $statusReport['ReportProcessingStatus'] = $reportStatus['ReportProcessingStatus'];
            dbg($statusReport, 0);
        }
    }

    private function getReportRequestId() {
        $SELECT_QRY = "SELECT requestId,status FROM mwsReports WHERE status != '_DONE_' ORDER BY createdAt DESC LIMIT 1";
        $result = query_db($SELECT_QRY);
        $result = mysqli_fetch_assoc($result['returned']);
        dbg($result, 0);
        // check here if requestid is not empty
        if ($result['requestId'] != '') {
            $this->reportRequestId = $result['requestId'];
            dbg($this->reportRequestId, 0);
            // we should report here if report is not _DONE_
        } else {
            $reportRequest = $result['status'];
            dbg($reportRequest, 0);
        }
    }

    private function updateReportStatus() {
        $UPDATE_QRY = "UPDATE mwsReports SET  status = '_DONE_' ,reportId = '$this->reportId' WHERE requestId = '$this->reportRequestId'";
        $result = query_db($UPDATE_QRY);
        if ($result['status'] == 'success') {
            echo "Status update ReportRequestId : $this->reportRequestId \n";
        } else {
            echo "\nERROR:- \n";
        }
    }

    private function fetchReport() {
        $reponse = $this->client->GetReport($this->reportRequestId);
        $insert_update = array();
        $reportResponse = array();
        if (count($reponse) > 0) {
            $reportdata = [];
            foreach ($reponse as $key => $data) {
            $Reportasin = $data['asin1'];
            $description = $data['item-description'];
            $description = mysql_escape($description);
            $status = $data['status'];

            $SELECT_QRY = "SELECT asin FROM productDescription where asin = '$Reportasin' and scraped = '0'";
            $result = query_db($SELECT_QRY);
            $row=mysqli_fetch_assoc($result['returned']);
            $Org_asin =  $row['asin'];
            if($Reportasin == $Org_asin)
            {
                $QURY = "UPDATE `productDescription` SET `productDescription`='$description',`status`='$status',`scraped`='1' WHERE `asin` = '$Org_asin'" ;
                    $response_result = query_db($QURY);
                    $this->statusReport = $response_result['status'];
            }
            
            
            
                    
                
            }   
        }
    }


}


$auFeed = new GetReportRequestStatus();
$auFeed->startOfProgram();
logMessage("Reqestreportstats.php Ends----> ".$date);


