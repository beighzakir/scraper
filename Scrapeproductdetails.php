<?php
error_reporting(E_ALL);
require_once 'amazon-mws-master/vendor/autoload.php';
include_once __DIR__. '/includes/init.php';
$date = date('Y-m-d H:i:s');
logMessage("Scrapeproductdetails.php Starts----> ".$date);

class ScrapeData {


	protected $client;
	protected $newasinArr;
	protected $scrapedasinArr;
	protected $stock;
	protected $asin;
	protected $updateString;
	protected $arrayChunk;
	protected $scrapedarrayChunk;
	protected $ScrapedProductDetailsArr;
	protected $ProductDetailsArr;
	protected $arrayChunkOffers;
	protected $scrapedarrayChunkOffers;
	protected $ProductPriceDetailsArr;
	protected $ScrapedProductPriceDetailsArr;
	protected $reportRequestId;
    protected $reportType;
	function __construct(){
		$this->reportType = '_GET_MERCHANT_LISTINGS_ALL_DATA_';
	}

	public function startOfProgram(){
		$config = getAmazonAPIConfiguration();
        $this->client = new MCS\MWSClient([
            'Marketplace_Id' => $config['MARKETPLACE_ID'],
            'Seller_Id' => $config['MERCHANT_ID'],
            'Access_Key_ID' => $config['AWS_ACCESS_KEY_ID'],
            'Secret_Access_Key' => $config['AWS_SECRET_ACCESS_KEY'],
        ]);
        
		$this->getReportRequestId();
		
	}

	private function getReportRequestId(){
		// non scraped asins to scrap all details
		$SELECT_QRY = "SELECT `asin` FROM productDescription where scraped = '0'";
		$result = query_db($SELECT_QRY);
        while($row=mysqli_fetch_assoc($result['returned'])){
        	
        		$this->newasinArr[] = $row['asin'];
        	
        }
        // scraped asins to scrap updated price and quantity
		$SELECT_QRY_SCRAPED = "SELECT `asin` FROM productDescription where scraped = '1'";
		$scrapedresult = query_db($SELECT_QRY_SCRAPED);
        while($scrapedrow=mysqli_fetch_assoc($scrapedresult['returned'])){
        	
        		$this->scrapedasinArr[] = $scrapedrow['asin'];
        	
        }

// scraped products to update only

        	$this->scrapedarrayChunk=array_chunk($this->scrapedasinArr, '5');
        	$this->scrapedarrayChunkOffers=array_chunk($this->scrapedasinArr, '20');
        	foreach ($this->scrapedarrayChunk as $scrapedkey => $scrapedarraychk) {
        		$scrapedresponse=$this->client->GetMatchingProductForId($scrapedarraychk);
        		$this->parseApiResponsescraped($scrapedresponse);  

        	}
        	foreach ($this->scrapedarrayChunkOffers as $scrapedOfferkey => $scrapedOffervalue) {
        		$this->getmypriceASINResponseScraped(
    				$this->client->GetMyPriceForASIN($scrapedOffervalue)
    			);
        	}

// scrap all new products 

        	$this->arrayChunk=array_chunk($this->newasinArr, '5');
        	$this->arrayChunkOffers=array_chunk($this->newasinArr, '20');
        	foreach ($this->arrayChunk as $key => $arraychk) {
        		$response=$this->client->GetMatchingProductForId($arraychk);
        		$this->parseApiResponse($response);  

        	}
        	foreach ($this->arrayChunkOffers as $Offerkey => $Offervalue) {
        		$this->getmypriceASINResponse(
    				$this->client->GetMyPriceForASIN($Offervalue)
    			);
        	}
        		
			$this->reportRequestId = $this->client->RequestReport($this->reportType);

			print_r($this->reportRequestId);
			if ($this->reportRequestId) {
				$reportRequest['status'] = "success";
				$reportRequest['RequestId'] = $this->reportRequestId;
			}

			print_r($reportRequest);
			if ($reportRequest['status'] == 'success') {
				$this->saveReportRequestId();
			}	

			$this->insertUpdateQueryMached();  
        	$this->insertUpdateQueryMyprice(); 
        	$this->insertUpdateQueryscraped();  
        	$this->insertUpdateQueryMypricescraped();  
	}

private function parseApiResponsescraped($apiResponse){
		if(isset($apiResponse['found']))
		{

			foreach ($apiResponse as $key => $arrRes) {
				
				foreach ($apiResponse[$key] as $arrkey => $arrvalue) {
					foreach ($arrvalue as $key => $value) {
						// print_r $value);
					$asin=$value['ASIN'];
					if(isset($value['PackageQuantity']))
						$PackageQuantity=$value['PackageQuantity'];
					else
						$PackageQuantity=0;
					$Title = mysql_escape($Title);
					$scrapedproductStr = "('$asin','$PackageQuantity'),";
	
					if(strlen(trim($scrapedproductStr))>0){
                        $this->ScrapedProductDetailsArr['ScrapedProductDetails'][] = $scrapedproductStr ;
                    }
					
					
					}

				}
			}
		}
		else
		{
			echo "asin not found ------->". $apiResponse['not_found'][0];
		}

	}


	private function parseApiResponse($apiResponse){
		if(isset($apiResponse['found']))
		{

			foreach ($apiResponse as $key => $arrRes) {
				
				foreach ($apiResponse[$key] as $arrkey => $arrvalue) {
					foreach ($arrvalue as $key => $value) {
						// print_r $value);
					$asin=$value['ASIN'];

					if(isset($value['Brand']))
						$brand=$value['Brand'];
					else
						$brand='';

					if(isset($value['Title']))
						$Title=$value['Title'];
					else
						$Title='';
					if(isset($value['ListPrice']['Amount']))
						$Amount=$value['ListPrice']['Amount'];
					else
						$Amount=0.00;
					if(isset($value['PackageQuantity']))
						$PackageQuantity=$value['PackageQuantity'];
					else
						$PackageQuantity=0;
					if(isset($value['ListPrice']['CurrencyCode']))
						$CurrencyCode=$value['ListPrice']['CurrencyCode'];
					else
						$CurrencyCode='';

					if(isset($value['medium_image']))
						$medium_image=$value['medium_image'];
					else
						$medium_image='';
					if(isset($value['small_image']))
							$small_image=$value['small_image'];
						else
						$small_image='';
					if(isset($value['large_image']))
							$large_image=$value['large_image'];
						else
						$large_image='';
					$Title = mysql_escape($Title);
					$productStr = "('$asin','$PackageQuantity','$brand','$small_image','$medium_image','$large_image','$Title','$CurrencyCode'),";
					

					if(strlen(trim($productStr))>0){
                        $this->ProductDetailsArr['ProductDetails'][] = $productStr ;
                    }
					
					
					}

				}
			}
		}
		else
		{
			echo "asin not found ------->". $apiResponse['not_found'][0];
		}

	}
private function getmypriceASINResponse($response){
		foreach ($response as $key => $value) {
			$asin=$key;
			// print_r($value);
			if(isset($value['FulfillmentChannel']))
				$FulfillmentChannel=$value['FulfillmentChannel'];
			else
				$FulfillmentChannel='';
			if(isset($value['BuyingPrice']['LandedPrice']['Amount']))
				$LandedPrice = $value['BuyingPrice']['LandedPrice']['Amount'];
			else
				$LandedPrice = 0.00;
			if(isset($value['BuyingPrice']['LandedPrice']['CurrencyCode']))
				$CurrencyCode = $value['BuyingPrice']['LandedPrice']['CurrencyCode'];
			else
				$CurrencyCode = '';
			if(isset($value['ItemCondition']))
				$ItemCondition=$value['ItemCondition'];
			else
				$ItemCondition='';
			if(isset($value['SellerId']))
				$SellerId=$value['SellerId'];
			else
				$SellerId='';
			if(isset($value['SellerSKU']))
				$SellerSKU=$value['SellerSKU'];
			else
				$SellerSKU='';
			
			$productpriceStr = "('$asin','$SellerSKU','$FulfillmentChannel','$ItemCondition','$SellerId','$LandedPrice','$CurrencyCode'),";
					

			if(strlen(trim($productpriceStr))>0){
                $this->ProductPriceDetailsArr['ProductPriceDetails'][] = $productpriceStr ;
            }
			
		
		}
		
	}

	private function getmypriceASINResponseScraped($response){
		foreach ($response as $key => $value) {
			$asin=$key;
			
			if(isset($value['BuyingPrice']['LandedPrice']['Amount']))
				$LandedPrice = $value['BuyingPrice']['LandedPrice']['Amount'];
			else
				$LandedPrice = 0.00;
			$scrapedproductpriceStr = "('$asin','$LandedPrice'),";
					

			if(strlen(trim($scrapedproductpriceStr))>0){
                $this->ScrapedProductPriceDetailsArr['ScrapedProductPriceDetails'][] = $scrapedproductpriceStr ;
            }
			
		
		}
		
	}

	private function saveReportRequestId() {
        $INSERT_QRY = "INSERT INTO `mwsReports` (requestId,reportType,status )VALUES('$this->reportRequestId','$this->reportType', '_SUBMITTED_')";
        $result = query_db($INSERT_QRY);
        if ($result['status'] == 'success') {
            echo "ReportRequestId : $this->reportRequestId saved ";
        } else {
            echo "ERROR:- " . $result['status'];
        }
    }

	private function insertUpdateQueryMached(){
		if (count($this->ProductDetailsArr) > 0) {
		            $ProductDetailsStr = rtrim(implode(" ", $this->ProductDetailsArr['ProductDetails']), ",");
					$QURY = "INSERT INTO `productDescription`( `asin`, `Quantity`, `brand`, `smallImage`, `mediumImage`, `largeImage`,`Title`,`currency`) VALUES $ProductDetailsStr"
					." ON DUPLICATE KEY UPDATE Quantity=VALUES(`Quantity`),brand=VALUES(`brand`),smallImage=VALUES(`smallImage`),mediumImage=VALUES(`mediumImage`),largeImage=VALUES(`largeImage`),Title=VALUES(`Title`),currency=VALUES(`currency`)";
		            $queryResponse = query_db($QURY);
		            dbg($queryResponse,0);

		  }
	}
	private function insertUpdateQueryscraped(){
		if (count($this->ScrapedProductDetailsArr) > 0) {
		            $ProductDetailsStr = rtrim(implode(" ", $this->ScrapedProductDetailsArr['ScrapedProductDetails']), ",");
					$QURY = "INSERT INTO `productDescription`( `asin`, `Quantity`) VALUES $ProductDetailsStr"
					." ON DUPLICATE KEY UPDATE Quantity=VALUES(`Quantity`)";
		            $queryResponse = query_db($QURY);
		            dbg($queryResponse,0);

		  }
	}



	private function insertUpdateQueryMyprice(){
		if (count($this->ProductPriceDetailsArr) > 0) {
		            $ProductPriceDetailsStr = rtrim(implode(" ", $this->ProductPriceDetailsArr['ProductPriceDetails']), ",");
					$QURY = "INSERT INTO `productDescription`( `asin`, `sku`, `fulfillment`, `itemCondition`, `seller`, `firstPrice`,`currency`) VALUES $ProductPriceDetailsStr"
					." ON DUPLICATE KEY UPDATE sku=VALUES(`sku`),fulfillment=VALUES(`fulfillment`),itemCondition=VALUES(`itemCondition`),seller=VALUES(`seller`),firstPrice=VALUES(`firstPrice`),currency=VALUES(`currency`)";
		            $queryResponse = query_db($QURY);
		            dbg($queryResponse,0);

		  }
	}
	private function insertUpdateQueryMypricescraped(){
		if (count($this->ScrapedProductPriceDetailsArr) > 0) {
		            $ProductPriceDetailsStr = rtrim(implode(" ", $this->ScrapedProductPriceDetailsArr['ScrapedProductPriceDetails']), ",");
					$QURY = "INSERT INTO `productDescription`( `asin`, `updatedPrice`) VALUES $ProductPriceDetailsStr"
					." ON DUPLICATE KEY UPDATE updatedPrice=VALUES(`updatedPrice`)";
		            $queryResponse = query_db($QURY);
		            dbg($queryResponse,0);

		  }
	}


}
	$ScrapeData=new ScrapeData();
	$ScrapeData->startOfProgram();
	logMessage("Scrapeproductdetails.php Ends----> ".$date);

