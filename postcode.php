<?php 
require_once('includes/a6Postcode.inc.php');
$oPostcode = new a6Postcode($_GET['postcode'], $_GET['huisnummer']);
header("content-type: application/json");
echo $oPostcode->toJSON($oPostcode);
?>
