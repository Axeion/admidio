<?php
/******************************************************************************
 * Passwort vergessen
 *
 * Copyright    : (c) 2004 - 2007 The Admidio Team
 * Homepage     : http://www.admidio.org
 * Module-Owner : Roland Eischer 
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *****************************************************************************/
 
require("common.php");
require("email_class.php");

//URL auf Navigationstack ablegen
$_SESSION['navigation']->addUrl(CURRENT_URL);


/*********************HTML_TEIL*******************************/

// Html-Kopf ausgeben
$g_layout['title'] = $g_organization." - Passwort vergessen?";

require(THEME_SERVER_PATH. "/overall_header.php");
getVars();

// Falls der User nicht eingeloggt ist, aber ein Captcha geschaltet ist,
// muss natuerlich der Code ueberprueft werden
if (! empty($abschicken) && !$g_valid_login && $g_preferences['enable_mail_captcha'] == 1 && !empty($captcha))
{
    if ( !isset($_SESSION['captchacode']) || strtoupper($_SESSION['captchacode']) != strtoupper($_POST['captcha']) )
    {
        $g_message->show("captcha_code");
		die();
    }
}
if($g_valid_login)
{
	echoMessagesAndErrors("Angemeldet !!!","Du bist am System angemeldet folglich kennst du ja dein Passwort!");
	echo"<script language='javascript'> setTimeout('window.location.href = \'".$g_root_path."/adm_program/\'',3000); 
	</script>";	
	die();
}

if(! empty($abschicken) && ! empty($empfaenger_email) && !empty($captcha))
{
	$sender_email	= $g_preferences['email_administrator'];
	$sender_name	= "Administrator";
	
	$user_id		= "";
	$benutzername	= "";
	$empfaengername	= "";
	
	list($user_id,$benutzername,$empfaengername) = getUserDataFromEmail($empfaenger_email);
	if($user_id == "" || $empfaengername == "")
	{
		echoMessagesAndErrors("E-Mail FALSCH ?!?!",'<font color="red">Es konnte die E-Mail: "'.$empfaenger_email.'" nicht gefunden werden!</font><br />Gib bitte <a href="'.$g_root_path.'/adm_program/system/lost_password.php" target="_self">hier</a> eine gültige E-Mail Addresse ein!');
		die();
	}
	
	$neues_passwort	= generatePassword();
	$activation_id	= generateActivationId($empfaenger_email);
	
	$email_text		.= " Hallo ".$empfaengername."!\n\n";
	$email_text		.= " Du hast ein neues Passwort angefordert!\n\n";
	$email_text		.= " Hier sind deine Daten:\n";
	$email_text		.= " Benutzername: ".$benutzername."\n";
	$email_text		.= " Passwort: ".$neues_passwort."\n";
	$email_text		.= " Aktivierungs Id: ".$activation_id."\n\n";
	$email_text		.= " Um jetzt dein neues Passwort benutzen zu können musst du jetzt noch auf den Link klicken!"."\n\n";
	$email_text		.= "".$g_root_path."/adm_program/system/password_activation.php?usr_id=3D".$user_id."&aid=3D".$activation_id.""."\n\n";
	$email_text		.= " Du kannst jederzeit das generierte Passwort ändern!\n\n";
	$email_text		.= "*******************************************************************\n";
	$email_text		.= " Bitte halte in Errinnerung das wir dich nie um deine Benutzdaten fragen!\n";
	$email_text		.= "*******************************************************************\n";

	$email = new Email();
	$email->setSender($sender_email,$sender_name);
	$email->setSubject('Neues Passwort!');
	$email->addRecipient($empfaenger_email,$empfaengername);
	$email->setText($email_text);
	
	
	if($email->sendEmail())
	{
		saveActivationlinkAndNewPassword($activation_id,md5($neues_passwort),$user_id);
		echoMessagesAndErrors("Passwort erfolgreich verschickt!",'Das neue Passwort wurde an "'.$empfaenger_email.'" geschickt!');
	}
	else
	{
		echoMessagesAndErrors("ERROR aufgetreten !!!",'<font color="red">Es ist ein ERROR beim Senden an "'.$empfaenger_email.'" aufgetreten!<br /> Bitte versuch es später wieder!</font>');
	}
}
else
{
	echo'
	<div class="formLayout" id="profile_form">
		<div class="formHead">Passwort vergessen?</div>
			<div class="formBody">
			<form name="password_form" action="'.$g_root_path.'/adm_program/system/lost_password.php" method="post">
				<ul class="formFieldList">
					<li>
						<div>
							Wenn Sie Ihr Passwort vergessen haben, kann das System ein neues erstellen und an Ihre E-Mail Adresse senden. Geben Sie einfach Ihre E-Mail Adresse in das untenstehende Formular ein und klicken Sie auf die Schaltfläche "Neues Passwort zusenden".
						</div>
					</li>
					<li>
						<hr />
					</li>
					<li>
						<dl>
							<dt>
								<label>E-Mail:</label>
							</dt>
							<dd>
								<input type="text" name="empfaenger_email" style="margin-bottom:3px; width: 200px;" maxlength="50" />
							</dd>
						</dl>
					</li>';
				// Nicht eingeloggte User bekommen jetzt noch das Captcha praesentiert,
                // falls es in den Orgaeinstellungen aktiviert wurde...
                if (!$g_valid_login && $g_preferences['enable_mail_captcha'] == 1)
                {
                    echo "
                    <li>
                        <dl>
                            <dt>&nbsp;</dt>
                            <dd>
                                <img src=\"$g_root_path/adm_program/system/captcha_class.php?id=". time(). "\" alt=\"Captcha\" />
                            </dd>
                        </dt>
                    </li>
                    <li>
                        <dl>
                            <dt><label for=\"captcha\">Best&auml;tigungscode:</label></dt>
                            <dd>
                                <input type=\"text\" id=\"captcha\" name=\"captcha\" style=\"width: 200px;\" maxlength=\"8\" value=\"\" />
                                <span class=\"mandatoryFieldMarker\" title=\"Pflichtfeld\">*</span>
                                <img class=\"iconHelpLink\" src=\"". THEME_PATH. "/icons/help.png\" alt=\"Hilfe\" onmouseover=\"ajax_showTooltip('$g_root_path/adm_program/system/msg_window.php?err_code=captcha_help',this);\" onmouseout=\"ajax_hideTooltip()\" />
                            </dd>
                        </dl>
                    </li>";
                }
				echo'									
				<button name="abschicken" type="submit" value="abschicken">
					<img src="'. THEME_PATH.'/icons/email.png" alt="Abschicken" />&nbsp;Neues Passwort zusenden
				</button>
				</ul>
			</form>
			</div>
		</div>
	</div>
	';
}
/************************Buttons********************************/
//Uebersicht
echo "
    <ul class=\"iconTextLinkList\">
        <li>
            <span class=\"iconTextLink\">
                <a href=\"$g_root_path/adm_program/system/back.php\"><img 
                src=\"". THEME_PATH. "/icons/back.png\" alt=\"Zurück\"></a>
                <a href=\"$g_root_path/adm_program/system/back.php\">Zurück</a>
            </span>
        </li>
    </ul>";

/***************************Seitenende***************************/
require(THEME_SERVER_PATH. "/overall_footer.php");

//************************* Funktionen/Unterprogramme ***********/

// Diese Funktion holt alle Variablen ab und speichert sie in einem array
function getVars() 
{
  global $HTTP_POST_VARS;
  foreach ($HTTP_POST_VARS as $key => $value) 
  {
    global $$key;
    $$key = $value;
  }
}
function generatePassword()
{
	// neues Passwort generieren
	$password = "";
	$password = substr(md5(time()), 0, 8);
	return $password;
}
function generateActivationId($text)
{
	$aid = "";
	$aid = substr(md5(uniqid($text.time())),0,10);
	return $aid;
}
function getUserDataFromEmail($empfaenger_email)
{
	global $g_current_organization;
	global $g_current_user;
	global $g_db;
	
	$sql = "SELECT distinct usr_id, usr_login_name, last_name.usd_value as last_name, first_name.usd_value as first_name
			FROM ". TBL_ROLES. ", ". TBL_CATEGORIES. ", ". TBL_MEMBERS. ", ". TBL_USERS. "
			LEFT JOIN ". TBL_USER_DATA. " as email
				ON email.usd_usr_id = usr_id
				AND email.usd_usf_id = ".$g_current_user->getProperty("E-Mail", "usf_id")."
				AND email.usd_value = \"".$empfaenger_email."\"
			LEFT JOIN ". TBL_USER_DATA. " as last_name
				ON last_name.usd_usr_id = usr_id
				AND last_name.usd_usf_id = ".$g_current_user->getProperty("Nachname", "usf_id")."
			LEFT JOIN ". TBL_USER_DATA. " as first_name
				ON first_name.usd_usr_id = usr_id
				AND first_name.usd_usf_id = ".$g_current_user->getProperty("Vorname", "usf_id")."
			WHERE rol_cat_id = cat_id
			AND cat_org_id = ".$g_current_organization->getValue("org_id")."
			AND rol_id = mem_rol_id
			AND mem_valid = 1
			AND mem_usr_id = usr_id
			AND usr_valid = 1
			AND email.usd_value = \"".$empfaenger_email."\"";	
	$result	= $g_db->query($sql);
	while ($row = $g_db->fetch_object($result))
	{
		return array($row->usr_id,$row->usr_login_name,$row->first_name);
	}
}
function saveActivationlinkAndNewPassword($activation_id,$neues_passwort,$usr_id)
{
	global $g_db;
	$sql = "UPDATE ". TBL_USERS. " SET `usr_activation_code` = '".$activation_id."',`usr_new_password` = '".$neues_passwort."'  WHERE `". TBL_USERS. "`.`usr_id` =".$usr_id." LIMIT 1";
	$result = $g_db->query($sql);
}
function echoMessagesAndErrors($header,$text)
{
	echo '<div class="formLayout" id="profile_form">
				<div class="formHead">'.$header.'</div>
					<div class="formBody">
						<div>'.$text.'</div>
					</div>
				</div>
			</div>';
}
?>