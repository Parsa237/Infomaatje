<?php
/**
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
**/

require_once("includes/config.inc.php"); 
$oPage = new DomDocument();
libxml_use_internal_errors(true);
$oPage->loadHTMLFile("public/HTML/dnAanmelden.html"); 

if(!isset($_REQUEST['t'])){
  header("location: index.html");
  exit();
}

$sToken = rawurldecode($_REQUEST['t']);

function strip($sString){
  return (get_magic_quotes_runtime()
          ? stripslashes($sString)
          : $sString);
}

function aToUTF8($aRow){
  $aReturn = array();
  foreach($aRow as $sAttribute => $uValue){
    if(is_array($uValue)){
      $aReturn[$sAttribute] = aToUTF8($uValue);
    } elseif(!is_numeric($uValue)) {
      $aReturn[$sAttribute] = utf8_encode(strip($uValue));   
    } else {
      $aReturn[$sAttribute] = $uValue;
    }
  }
  return $aReturn;
}

function textToLines($sText){
  $aLines = preg_split("/\r?\n/", utf8_encode($sText));
  return $aLines;  
}
try{
  $oSoap = new SoapClient(A6_INFO_SERVER . "/bpmsSoap.wsdl");
  switch($_SERVER['REQUEST_METHOD']) {
    case "POST":
      $aAanmelding = array();
      $aAanmelding['t'] = $sToken;
      
      /*** AANMELDER IF NOT DEELNEMER ***/
      if(isset($_POST['input0242'])) {
        $aAanmelding['aanmelder']= array('aanmelder' => $_POST['aanmelder'],
                                        'naam_instantie' => $_POST['input0235'],
                                        'voornaam' => $_POST['input0236a'],
                                        'tussenvoegsel' => $_POST['input023b'],
                                        'achternaam' => $_POST['input0236c'],
                                        'geslacht' => $_POST['input0237'],
                                        'werkdagen' => $_POST['input0238'],
                                        'postcode' => $_POST['input0239'],
                                        'huisnummer' => $_POST['input0240'],
                                        'straatnaam' => $_POST['input0239a'],
                                        'plaatsnaam' => $_POST['input0239b'],
                                        'telefoon' => $_POST['input0241'],
                                        'mobiel' => $_POST['input0241a'],
                                        'email' => $_POST['input0242']
                                      );
      }
      /*** DEELNEMER ***/
      $aAanmelding['deelnemer'] = array(
                                          'naam' => $_POST['input0201'],
                                          'tussenvoegsel' => $_POST['input0501'],
                                          'achternaam' => $_POST['input0202'],
                                          'geslacht' => $_POST['input0203'],
                                          'postcode' => $_POST['input0208'],
                                          'huisnummer' => $_POST['input0209'],
                                          'straatnaam' => $_POST['input0208a'],
                                          'plaatsnaam' => $_POST['input0208b'],
                                          'telefoon' => $_POST['input0210'],
                                          'mobiel' => $_POST['input0210a'],
                                          'email' => $_POST['input0211'],
                                          'beschikbaar' => json_encode($_POST['input020101'])
                                        );
      
      $aAanmelding['antwoorden'] = $_POST['antwoorden'];
      
      if($_POST['input0500']=='on'){
        $aAanmelding['afronden'] = array('status' => 'aangemeld');
      }

      $aMessage = $oSoap->setAanmelding(aToUTF8($aAanmelding));
      if($aMessage['code'] > 0) throw new Exception($aMessage['message']);
      if($_POST['input0500']=='on'){
        $aVragenlijsten = $oSoap->GetVragenlijsten(205);
        foreach($aVragenlijsten as $aVragenlijst){
          $aAanmelding['vragenlijst'][$aVragenlijst['vragenlijst']['vragenlijst']]['vragenlijst'] = array('vragenlijst' => $aVragenlijst['vragenlijst']['vragenlijst']);
          
          foreach($aVragenlijst['vragen'] as $aVraag){
            $aAanmelding['vragenlijst'][$aVragenlijst['vragenlijst']['vragenlijst']]['vragen'][] = array('vraag' => $aVraag['vraag'],
                                                    'antwoord' => $_POST['input_vraag_'.$aVragenlijst['vragenlijst']['vragenlijst'].'_'.$aVraag['vraag']]
                                                  );
          }
        }

        $oPage = new DomDocument();
        $oPage->loadHTMLFile("public/HTML/melding.html");
        $oMessage = $oPage->getElementById("message");
        if(isset($aAanmelding['aanmelder'])) {
          $oP = $oPage->createElement('p', 'Bedankt voor de aanmelding. U ontvangt ter bevestiging een e-mail met verdere informatie.');
        } else {
          $oP = $oPage->createElement('p', 'Dank voor de aanmelding. Je ontvangt ter bevestiging een e-mail met verdere informatie.');
        }
        $oMessage->appendChild($oP);
        
      } else {
        header("Content-Type: application/json");
        print(json_encode($aMessage));
        exit();
      }
      break;
    case "GET":
      $aAanmelding = $oSoap->getAanmelding($sToken);
      if(isset($aAanmelding['code']) && $aAanmelding['code'] != 0){
        throw new Exception($aAanmelding['message'], $aAanmelding['code']);
      }

      $aVragenlijsten = $aAanmelding['vragenlijsten'];
      
      /*** INIT PAGE & FORM ***/
      $oInput = $oPage->getElementById('t');
      $oInput->setAttribute('value', $sToken);
      
      /*** AANMELDER IF ANY ***/
      if(isset($aAanmelding['aanmelder'])) {
        /*** ZETTEN VAN INTRO TEKST TBV AANMELDER ***/
        $oFieldset = $oPage->getElementById('fieldset_0');
        $oFieldset->nodeValue = NULL;
        $oLegend = $oPage->createElement('legend', 'Welkom bij het aanmeldingsformulier voor verwijzers');
        $oFieldset->appendChild($oLegend);
        $oP = $oPage->createElement('p', 'We willen u vragen een aantal vragen over uw cliënt in te vullen.  Aan de bolletjes boven in het blad kunt u zien hoe ver u met de aanmelding gevorderd bent.');
        $oFieldset->appendChild($oP);
        $oP = $oPage->createElement('p', 'Bezig met invullen maar nog niet klaar? U kunt gewoon uitloggen en op een ander moment verder gaan. U logt weer in via de link in uw e-mail.');
        $oFieldset->appendChild($oP);

        $oInput = $oPage->getElementById('aanmelder');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['persoon']);
        $oInput = $oPage->getElementById('id0236a');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['voornaam']);
        $oInput = $oPage->getElementById('id0236b');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['tussenvoegsel']);
        $oInput = $oPage->getElementById('id0236c');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['achternaam']);
        if(!empty($aAanmelding['aanmelder']['persoon']['geslacht'])){
          $oInput = $oPage->getElementById('id0237_'.$aAanmelding['aanmelder']['persoon']['geslacht']);
          $oInput->setAttribute('checked', 'checked');
        }
        $oInput = $oPage->getElementById('id0241a');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['mobiel']);
        $oInput = $oPage->getElementById('id0242');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['email']);
        $oInput = $oPage->getElementById('id0238');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['persoon']['werkdagen']);
        
        $oInput = $oPage->getElementById('id0235');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['naam']);
        $oInput = $oPage->getElementById('id0239');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['postcode']);
        $oInput = $oPage->getElementById('id0239a');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['straatnaam']);
        $oInput = $oPage->getElementById('id0239b');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['plaatsnaam']);
        $oInput = $oPage->getElementById('id0240');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['huisnummer']);
        $oInput = $oPage->getElementById('id0241');
        $oInput->setAttribute('value', $aAanmelding['aanmelder']['organisatie']['telefoon']);
        $iFieldSetNumber = 3;
      } else {
        $cFieldsets = $oPage->getElementsByTagName('fieldset');
        $iFieldSetNumber = 0;
        $oDelField = null;
        foreach($cFieldsets as $oFieldset) {
          if(!$oFieldset->hasAttribute('id')) continue;
          if($oFieldset->getAttribute('id') == "fieldset_1") {
            $oDelField = $oFieldset;
          } else {
            $oFieldset->setAttribute('id', 'fieldset_' . $iFieldSetNumber++);
          }
        }
        if($oDelField) $oDelField->parentNode->removeChild($oDelField);
      }

      /*** DEELNEMER ***/
      $oInput = $oPage->getElementById('id0201');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['voornaam']);
      $oInput = $oPage->getElementById('id0501');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['tussenvoegsel']);
      $oInput = $oPage->getElementById('id0202');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['achternaam']);
      if(!empty($aAanmelding['deelnemer']['geslacht'])){    
        $oInput = $oPage->getElementById('id0203_'.$aAanmelding['deelnemer']['geslacht']);
        $oInput->setAttribute('checked', 'checked');
      }
      $oInput = $oPage->getElementById('id0204');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['geboortedatum']);
      $oInput = $oPage->getElementById('id0208');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['postcode']);
      $oInput = $oPage->getElementById('id0208a');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['straatnaam']);
      $oInput = $oPage->getElementById('id0208b');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['plaatsnaam']);
      $oInput = $oPage->getElementById('id0209');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['huisnummer']);
      $oInput = $oPage->getElementById('id0210');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['telefoon']);
      $oInput = $oPage->getElementById('id0210a');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['mobiel']);
      $oInput = $oPage->getElementById('id0211');
      $oInput->setAttribute('value', $aAanmelding['deelnemer']['email']);
        
      /*** AANVULLENDE INFOMATIE ***/
      $oDiv = $oPage->getElementById('para020101');
      $cInputs = $oDiv->getElementsByTagName('input');
      $aBeschikbaar = json_decode($aAanmelding['deelnemer']['beschikbaar']);
      if(is_array($aBeschikbaar)) {
        foreach($cInputs as $oInput) {
          if(in_array($oInput->getAttribute("value"), $aBeschikbaar)) {
            $oInput->setAttribute("checked", "checked");
          }
        }
      }
      /*** VRAGEN ***/
      $oDivVragenlijst = $oPage->getElementById('vragenlijsten');
      foreach($aVragenlijsten as $aVragenlijst) {
        $oFieldset = $oPage->createElement('fieldset');
        $oFieldset->setAttribute('id', 'fieldset_'.$iFieldSetNumber);
        $oFieldset->setAttribute('class', 'hidden');
        $oLegend = $oPage->createElement('legend', $iFieldSetNumber.' '.$aVragenlijst['vragenlijst']['vragenlijst_omschrijving']);
        $oLegend->setAttribute('id', 'vragenlijst['.$aVragenlijst['vragenlijst']['vragenlijst'].']');
        $oFieldset->appendChild($oLegend);
        
        foreach($aVragenlijst['vragen'] as $aVraag) {
          $oDiv = $oPage->createElement('div');
          $oDiv->setAttribute('id','vraag['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
          $oDiv->setAttribute('class', 'entry');
          $oLabel = $oPage->createElement('label', $aVraag['omschrijving']);
          $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
          $oLabel->setAttribute('class', 'entry');
          if(!empty($aVraag['toelichting'])){
            $oLabel->setAttribute('class', 'entry infotxtarea');
          }
          $oDiv->appendChild($oLabel);           
          switch($aVraag['type']){
            case 'alphanumeriek':
              $oInput = $oPage->createElement('textarea', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
            case 'numeriek':
              $oInput = $oPage->createElement('input');
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('value', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
            case 'select':
              $aOpties = explode(",", $aVraag['waarde']);
              $oInput = $oPage->createElement('select');
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              foreach($aOpties as $oOptie){
                $oOption = $oPage->createElement('option', $oOptie);
                $oOption->setAttribute('value', $oOptie);
                if($oOptie == $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]){
                  $oOption->setAttribute('selected', 'selected');
                }
                $oInput->appendChild($oOption);
              }
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
            case 'checkbox':
              $aOpties = explode(",", $aVraag['waarde']);
              foreach($aOpties as $oOptie){
                $aAntwoordWaardes = explode(",", $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
                $oInput = $oPage->createElement('input');
                $oInput->setAttribute('type', 'checkbox');
                $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
                $oInput->setAttribute('value', $oOptie);
                if(in_array($oOptie ,$aAntwoordWaardes)){
                  $oInput->setAttribute('checked', 'checked'); 
                }
                $oLabel = $oPage->createElement('label');
                $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oLabel->appendChild($oInput);
                $oLabel->appendChild($oPage->createTextNode($oOptie));
                $oDiv->appendChild($oLabel);
              } 
              break;
            case 'radio':
              $aOpties = explode(",", $aVraag['waarde']);
              foreach($aOpties as $oOptie){
                $aAntwoordWaardes = explode(",", $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
                $oInput = $oPage->createElement('input');
                $oInput->setAttribute('type', 'radio');
                $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
                $oInput->setAttribute('value', $oOptie);
                if(in_array($oOptie ,$aAntwoordWaardes)){
                  $oInput->setAttribute('checked', 'checked'); 
                }
                $oLabel = $oPage->createElement('label');
                $oLabel->setAttribute('for', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']['.$oOptie.']');
                $oLabel->appendChild($oInput);
                $oLabel->appendChild($oPage->createTextNode($oOptie));
                $oDiv->appendChild($oLabel);
              } 
              break;
            default:
              $oInput = $oPage->createElement('textarea', $aAanmelding['antwoorden'][$aVragenlijst['vragenlijst']['vragenlijst']][$aVraag['vraag']]);
              $oInput->setAttribute('id', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('name', 'antwoorden['.$aVragenlijst['vragenlijst']['vragenlijst'].']['.$aVraag['vraag'].']');
              $oInput->setAttribute('placeholder', $aVraag['waarde']);
              if($aVraag['verplicht']){
                $oInput->setAttribute('class', 'required');
              }
              $oDiv->appendChild($oInput);
              break;
          }
          if(!empty($aVraag['toelichting'])){
            $oDivInfo = $oPage->createElement('div');
            $oDivInfo->setAttribute('class', 'infobubbletxtarea hidden');
            $oDivInfoP = $oPage->createElement('p', $aVraag['toelichting']);
            
            $oDivInfo->appendChild($oDivInfoP);
            $oDiv->appendChild($oDivInfo); 
          }
          $oFieldset->appendChild($oDiv);
        }
        $iFieldSetNumber++;
        $oDivVragenlijst->appendChild($oFieldset);
      }
      $oDivCheck = $oPage->createElement('div');
      $oDivCheck->setAttribute('id', 'para0500');
      $oDivCheck->setAttribute('class', 'entry required');
      $oLabel = $oPage->createElement('label');
      $oLabel->setAttribute('for', 'id0500');
      $oInput = $oPage->createElement('input');
      $oInput->setAttribute('id', 'id0500');
      $oInput->setAttribute('type', 'checkbox');
      $oInput->setAttribute('name', 'input0500');
      $oInput->setAttribute('class', 'required');
      $oInput->setAttribute('value', 'on');
      $oLabel->appendChild($oInput);
      $oLabel->appendChild($oPage->createTextNode('Hiermee bevestig ik het formulier volledig naar waarheid te hebben ingevuld en dat deze aanmelding aan Budgetmaatjes 070 verzonden kan worden.'));
      $oDivCheck->appendChild($oLabel);
      $oFieldset->appendChild($oDivCheck);
      
      /*** OPSCHONEN INVOERVELDEN ***/
      $cInputs = $oPage->getElementsByTagName('input');
      foreach($cInputs as $oInput){
        $oInput->setAttribute('value', html_entity_decode($oInput->getAttribute('value')));
      }
      break;
  }
} catch(Exception $oError){
  $oPage = new DomDocument();
  $oPage->loadHTMLFile("public/HTML/melding.html");
  $oMessage = $oPage->getElementById("message");
  $oP = $oPage->createElement("p", ($oError->getMessage()));
  $oMessage->appendChild($oP);
  if(is_soap_fault($oError)){
    $oP = $oPage->createElement("pre", $oSoap->__getLastResponse());
    $oP->setAttribute('style', 'white-space: pre-line;');
    $oMessage->appendChild($oP);
  }
}
$cInputs = $oPage->getElementsByTagName('input');
foreach($cInputs as $oInput){
  $oInput->setAttribute('value', html_entity_decode($oInput->getAttribute('value')));
}
echo $oPage->saveHTML();
?>
