<?php
require_once("includes/config.inc.php"); 
header("content-type: application/json");
try {
  if(isset($_POST['email'])){
    $oSoap = new SoapClient(A6_INFO_SERVER . "/bpmsSoap.wsdl");
    
    $sEmail = $_POST['email'];
    $aResult = $oSoap->makeVrijwilligerAanmelding($sEmail);
  } else {
    throw new Exception("E-mailadres is verplicht ...");
  }
} catch(Exception $oError) {
  $aResult = array();
  $aResult["code"] = 999;
  $aResult["message"] = $oError->getMessage();
}
echo json_encode($aResult);
?>  
