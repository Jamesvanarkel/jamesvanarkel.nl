<?php
ini_set("SMTP","smtp.gmail.com");
ini_set("smtp_port", 465); 
function text_to_html($plainText, $makeLineBreaks = true) {
# Just to make things a little easier, pad the end.
$output = $plainText."\n";
$output = preg_replace('|<br />\s*<br />|', "\n\n", $output);

# Space things out a little.
$output = preg_replace('!(<(?:table|ul|ol|li|pre|form|blockquote|h[1-6])[^>]*>)!', "\n$1", $output);
$output = preg_replace('!(</(?:table|ul|ol|li|pre|form|blockquote|h[1-6])>)!', "$1\n", $output); // Space things out a little.

# Cross-platform newlines.
$output = preg_replace("/(\r\n|\r)/", "\n", $output);

# Take care of duplicates.
$output = preg_replace("/\n\n+/", "\n\n", $output);
$output = preg_replace('/\n?(.+?)(?:\n\s*\n|\z)/s', "\t<p>$1</p>\n", $output);

# Make paragraphs, including one at the end.
# Under certain strange conditions, it could create a P of entirely whitespace.
$output = preg_replace('|<p>\s*?</p>|', '', $output);

# Problem with nested lists
$output = preg_replace("|<p>(<li.+?)</p>|", "$1", $output);
$output = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $output);
$output = str_replace('</blockquote></p>', '</p></blockquote>', $output);
$output = preg_replace('!<p>\s*(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)!', "$1", $output);
$output = preg_replace('!(</?(?:table|tr|td|th|div|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*</p>!', "$1", $output);

# Optionally make line breaks.
if ($makeLineBreaks){
$output = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $output);
}

$output = preg_replace('!(</?(?:table|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|form|blockquote|p|h[1-6])[^>]*>)\s*<br />!', "$1", $output);
$output = preg_replace('!<br />(\s*</?(?:p|li|div|th|pre|td|ul|ol)>)!', '$1', $output);
$output = preg_replace('/&([^#])(?![a-z]{1,8};)/', '&#038;$1', $output);

$output = preg_replace('|(?<!href=")(https?://[A-Za-z0-9+\-=._/*(),@\'$:;&!?%]+)|i', '<a href="$1">$1</a>', $plainText);

return $output;
}

function handle_form(){
    $formSubmitted = $_SERVER['REQUEST_METHOD'] == "POST";
    if($formSubmitted) {
        $errors = false;
        $voornaam_error = "";
        $achternaam_error = "";
        $email_error = "";
        $bericht_error = "";

        if(!isset($_POST['voornaam']) || $_POST['voornaam'] == "") {
            $voornaam_error = "U heeft geen voornaam ingevuld!<br />";
            $errors = true;
        }
        if(!isset($_POST['achternaam']) || $_POST['achternaam'] == "") {
            $achternaam_error  = "U heeft geen achternaam ingevuld!<br />";
            $errors = true;
        }
        if(!isset($_POST['email']) || $_POST['email'] == "" ) {
            $email_error = "U heeft geen email adres ingevoerd!<br />";
            $errors = true;
        }
        if(!preg_match('~^[a-z0-9][a-z0-9_.\-]*@([a-z0-9]+\.)*[a-z0-9][a-z0-9\-]+\.([a-z]{2,6})$~i', $_POST['email'])) {
            $email_error= "Uw heeft geen geldig email adres ingevoerd!<br />";
            $errors = true;
        }

        if(!isset($_POST['bericht']) || $_POST['bericht'] == "") {
            $bericht_error = "Uw heeft geen bericht ingevoerd!<br />";
            $errors = true;
        }
        $voornaam = htmlentities($_POST['voornaam']);
        $achternaam = htmlentities($_POST['achternaam']);
        $straatnaam = htmlentities($_POST['straatnaam']);
        $postcode = htmlentities($_POST['postcode']); 
        $telefoonnummer = htmlentities($_POST['telefoonnummer']);
        $bericht = text_to_html($_POST['bericht']);
        $email = htmlentities($_POST['mail']);
        if($errors == false) {
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'From: p.p.bot@live.nl' . "\r\n";
            echo $voornaam, $achternaam, $straatnaam, $postcode, $telefoonnummer, $bericht, $email;
            
            if(!mail("p.p.bot@live.nl","test subject","test body", $headers)){
                echo'mail mislukt';
            }


        } else {
            echo'<form method="post" form action="' . $_SERVER['PHP_SELF'] . '">
                <table id="contactform">
                    <tr>
                        <td><label for="voornaam">Voornaam* </label></td>
                        <td><span class="error">'. $voornaam_error . '</span>
                            <input type="text" class="veld" id="voornaam" name="voornaam" value="'. $voornaam .'" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="achternaam">Achternaam* </label></td>
                        <td><span class="error">'. $achternaam_error . '</span>
                            <input type="text" class="veld" id="achternaam" name="achternaam" value="'. $achternaam .'" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="adres">Straatnaam </label></td>
                        <td><input type="text" class="veld" id="straatnaam" name="straatnaam" value="'. $straatnaam .'"/></td>
                    </tr>
                    <tr>
                        <td><label for="woonplaats">Postcode en woonplaats </td>
                        <td>
                            <input type="text" class="veld" id="postcode" name="postcode" value="'. $postcode .'" />
                            <input type="text"  class="veld" id="woonplaats" name="adres" placeholder="Uw woonplaats" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="telefoonnummer">Telefoonnummer</label></td>
                        <td><input type="text" class="veld" id="telefoonnummer" name="telefoonnummer"'. $telefoonnummer .'" /></td>
                    </tr>
                    <tr>
                        <td><label for="email">Email adres* </label></td>
                        <td><span class="error">'. $email_error . '</span>
                            <input type="email" class="veld" name="email" id="email" value="'. $email .'" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="vraag">Opmerkingen* </td>
                        <td><span class="error">'. $bericht_error . '</span>
                            <textarea name="bericht" id="bericht" value="'. $bericht .'"></textarea>
                        </td>
                    </tr>  
                </table>
                <button name="submit" id="verstuur">Verzenden</button>
            </form>
            ';
        }
    } else {
        echo'<form method="post" form action="' . $_SERVER['PHP_SELF'] . '">
                <table id="contactform">
                    <tr>
                        <td><label for="voornaam">Voornaam* </label></td>
                        <td><input type="text" class="veld" id="voornaam" name="voornaam" placeholder="Uw voornaam" /></td>
                    </tr>
                    <tr>
                        <td><label for="achternaam">Achternaam* </label></td>
                        <td><input type="text" class="veld" id="achternaam" name="achternaam" placeholder="Uw achternaam" /></td>
                    </tr>
                    <tr>
                        <td><label for="adres">Straatnaam </label></td>
                        <td><input type="text" class="veld" id="straatnaam" name="straatnaam" placeholder="Uw straatnaam" /></td>
                    </tr>
                    <tr>
                        <td><label for="woonplaats">Postcode en woonplaats </td>
                        <td><input type="text" class="veld" id="postcode" name="postcode" placeholder="Uw postcode" /><input type="text"  class="veld" id="woonplaats" name="adres" placeholder="Uw woonplaats" />
                        </td>
                    </tr>
                    <tr>
                        <td><label for="telefoonnummer">Telefoonnummer</label></td>
                        <td><input type="text" class="veld" id="telefoonnummer" name="telefoonnummer" placeholder="Uw telefoonnummer" /></td>
                    </tr>
                    <tr>
                        <td><label for="email">Email adres* </label></td>
                        <td><input type="email" class="veld" name="email" id="email" placeholder="Uw email" /></td>
                    </tr>
                    <tr>
                        <td><label for="vraag">Opmerkingen* </td>
                        <td><textarea name="bericht" id="bericht" placeholder="Uw opmerking"></textarea></td>
                    </tr>  
                </table>
                <button name="submit" id="verstuur">Verzenden</button>
            </form>
            ';
    }
}

?>