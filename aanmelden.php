<?php
/*
Plugin Name:  Aanmelden
Plugin URI:   http://localhost
Description:  Stuur een mail!
Version:      2018.01
Author:       Anan6.com
Author URI:   https://developer.wordpress.org/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  wporg
*/

/*** TOEVOEGEN AANMELDEN DEELNEMER ***/
add_shortcode('dnAanmelden','fDNAanmelden');
function fDNAanmelden($atts, $content = '', $tag){
    $html  = '<link href="/wp-content/plugins/aanmelden/public/CSS/styles.css" rel="stylesheet" type="text/css" />';
    $html .= '<script src="/wp-content/plugins/aanmelden/public/JS/aanmelden.js" type="text/javascript"></script>';
    $html .= '<form id="dnAanmelden" action="#">';
    $html .= '<input type="hidden" name="wie" value="M">';
    $html .= '<legend>Aanmeldingsformulier deelnemer:</legend>';
    $html .= '<div class="entry">';
    $html .= '<label for="dnAkkoord" id="dnIntro">Voordat je de aanmelding start, is het van belang dat je kennis neemt van en akkoord gaat met onze privacyverklaring. Deze kun je <a href="https://stekdenhaag.nl/sites/default/files/Privacyverklaring%20Stek.pdf" target="__pdf">hier</a> lezen en hier accorderen<input type="checkbox" id="dnAkkoord" name="dnAkkoord" value="OK"></label></div>';
    $html .= '<div class="entry">';
    $html .= '<label class="red" for="aanEmail">E-mailadres deelnemer:</label><input type="text" name="email" id="dnEmail" placeholder="Vergeet niet eerst onze privacyverklaring te accoderen ..." class="required formatEmail" disabled><button id="dnSubmit" type="submit" disabled="disabled" opacity="1">Aanmelden</button></div>';
    $html .= '<p id="message"></p></form>';
    return $html;
}

/*** TOEVOEGEN AANMELDEN DEELNEMER ***/
add_shortcode('aanAanmelden','fAanAanmelden');
function fAanAanmelden($atts, $content = '', $tag){
    $html  = '<link href="/wp-content/plugins/aanmelden/public/CSS/styles.css" rel="stylesheet" type="text/css" />';
    $html .= '<script src="/wp-content/plugins/aanmelden/public/JS/aanmelden.js" type="text/javascript"></script>';
    $html .= '<form id="dnAanmelden" action="#">';
    $html .= '<input type="hidden" name="wie" value="A">';
    $html .= '<legend>Aanmeldingsformulier verwijzer:</legend>';
    $html .= '<div class="entry">';
    $html .= '<label for="dnAkkoord" id="dnIntro">Voordat je de aanmelding start, is het van belang dat je kennis neemt van en akkoord gaat met onze privacyverklaring. Deze kun je <a href="https://stekdenhaag.nl/sites/default/files/Privacyverklaring%20Stek.pdf" target="__pdf">hier</a> lezen en hier accorderen<input type="checkbox" id="dnAkkoord" name="dnAkkoord" value="OK"></label></div>';
    $html .= '<div class="entry">';
    $html .= '<label class="red" for="aanEmail">E-mailadres verwijzer:</label><input type="text" name="email" id="dnEmail" placeholder="Vergeet niet eerst onze privacyverklaring te accoderen ..." class="required formatEmail" disabled><button id="dnSubmit" type="submit" disabled="disabled" opacity="1">Aanmelden</button></div>';
    $html .= '<p id="message"></p></form>';
    return $html;
}

/*** TOEVOEGEN VRIJWILLIGER DEELNEMER ***/
add_shortcode('vwAanmelden', 'fVWAanmelden');
function fVWAanmelden() {
    $html  = '<link href="/wp-content/plugins/aanmelden/public/CSS/styles.css" rel="stylesheet" type="text/css" />';
    $html .= '<script src="/wp-content/plugins/aanmelden/public/JS/aanmelden.js" type="text/javascript"></script>';
    $html .= '<form id="vwAanmelden" action="#"><fieldset>';
    $html .= '<legend>Aanmeldingsformulier maatje</legend>';
    $html .= '<div class="entry">';
    $html .= '<label for="vwAkkoord">Voordat je de aanmelding start, is het van belang dat je kennis neemt van en akkoord gaat met onze privacyverklaring. Die kun je <a href="https://stekdenhaag.nl/sites/default/files/Privacyverklaring%20Stek.pdf" target="__pdf">hier</a> lezen en hier accorderen<input type="checkbox" id="vwAkkoord" name="vwAkkoord" value="OK"></label></div>';
    $html .= '<div class="entry">';
    $html .= '<label class="red" for="aanEmail">E-mailadres maatje:</label><input type="email" name="email" id="vwEmail" placeholder="Vergeet niet eerst onze privacyverklaring te accorderen ..." class="required formatEmail" disabled><button id="vwSubmit" type="submit" disabled="disabled" opacity="1" style="opacity: 0.5">Aanmelden</button></div>';
    $html .= '<p id="message"></p>';
    $html .= '</fieldset></form>';
    return $html;
}
