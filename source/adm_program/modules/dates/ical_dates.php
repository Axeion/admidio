<?php
/******************************************************************************
 * ical - Feed fuer Termine
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Erzeugt einen RSS 2.0 - Feed mit Hilfe der RSS-Klasse fuer die 10 naechsten Termine
 *
 * Spezifikation von RSS 2.0: http://www.feedvalidator.org/docs/rss2.html
 *
 * Parameters:
 *
 * headline: - Ueberschrift fuer den Ics-Feed
 *             (Default) Termine
 * mode:     1 - Textausgabe
 *           2 - Download
 *
 *****************************************************************************/

require_once('../../system/common.php');
require_once('../../system/classes/rss.php');
require_once('../../system/classes/table_date.php');


// prüfen ob das Modul überhaupt aktiviert ist
if($gPreferences['enable_dates_module'] == 0)
{
    // das Modul ist deaktiviert
    $gMessage->show($gL10n->get('SYS_MODULE_DISABLED'));
}
elseif($gPreferences['enable_dates_module'] == 2)
{
    // nur eingelochte Benutzer dürfen auf das Modul zugreifen
    require_once('../../system/login_valid.php');
}

// Nachschauen ob ical ueberhaupt aktiviert ist bzw. das Modul oeffentlich zugaenglich ist
if ($gPreferences['enable_dates_ical'] != 1)
{
    $gMessage->setForwardUrl($gHomepage);
    $gMessage->show($gL10n->get('SYS_ICAL_DISABLED'));
}

// Initialize and check the parameters
$getHeadline = admFuncVariableIsValid($_GET, 'headline', 'string', $gL10n->get('DAT_DATES'));
$getMode   = admFuncVariableIsValid($_GET, 'mode', 'numeric', 2);
$getCalendar = admFuncVariableIsValid($_GET, 'calendar', 'string');

// alle Organisationen finden, in denen die Orga entweder Mutter oder Tochter ist
$organizations = '';
$arr_orgas = $gCurrentOrganization->getReferenceOrganizations(true, true);

foreach($arr_orgas as $org_id => $value)
{
    $organizations = $organizations. $org_id. ', ';
}
$organizations = $organizations. $gCurrentOrganization->getValue('org_id');

//Einschränkungen
$sqlConditions = '';
$sqlConditionCalendar = '';
$sqlConditionLogin = '';
$sqlOrderBy = ' ORDER BY dat_begin ASC ';

//Geschützte Termine
if ($gValidLogin == false)
{
    // Wenn User nicht eingeloggt ist, Kategorien, die hidden sind, aussortieren
    $sqlConditions .= ' AND cat_hidden = 0 ';
}

//Bestimmter Kalender
if (strlen($getCalendar) > 0)
{
    // alle Termine zu einer Kategorie anzeigen
    $sqlConditionCalendar .= ' AND cat_name   = \''. $getCalendar. '\' ';
}

//Rückwertige Tage die angezeigt werden sollen
$date_past = date('Y-m-d H:i:s',time()-$gPreferences['dates_ical_days_past']*86400);
$sqlConditions .= ' AND (  dat_begin >= \''.$date_past.'\' 
                    OR dat_end   >= \''.$date_past.'\' ) ';

//Tage in Zukunft die angezeigt werden sollen
$date_future = date('Y-m-d H:i:s',time()+$gPreferences['dates_ical_days_future']*86400);
$sqlConditions .='  AND (  dat_begin <= \''.$date_future.'\' 
                    OR dat_end   <= \''.$date_future.'\' ) ';

// Bedingungen für die Rollenfreigabe hinzufügen
if($gCurrentUser->getValue('usr_id') > 0)
{
    $sqlConditionLogin = '
    AND (  dtr_rol_id IS NULL 
        OR dtr_rol_id IN (SELECT mem_rol_id 
                            FROM '.TBL_MEMBERS.' mem2
                           WHERE mem2.mem_usr_id = '.$gCurrentUser->getValue('usr_id').'
                             AND mem2.mem_begin  <= dat_begin
                             AND mem2.mem_end    >= dat_end) ) ';
}
else
{
    $sqlConditionLogin = ' AND dtr_rol_id IS NULL ';
}

// Gucken wieviele Datensätze die Abfrage ermittelt kann...
$sql = 'SELECT COUNT(DISTINCT dat_id) as count
          FROM '.TBL_DATE_ROLE.', '. TBL_DATES. ', '. TBL_CATEGORIES. '
         WHERE dat_cat_id = cat_id
           AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id'). '
               OR (   dat_global   = 1
                  AND cat_org_id IN (\''.$organizations.'\') 
                  )
               )
           AND dat_id = dtr_dat_id
               '.$sqlConditionLogin. $sqlConditions. $sqlConditionCalendar;

$result = $gDb->query($sql);
$row    = $gDb->fetch_array($result);
$num_dates = $row['count'];

// aktuelle Termine aus DB holen die zur Orga passen
$sql = 'SELECT DISTINCT cat.*, dat.*, mem.mem_usr_id as member_date_role, mem.mem_leader,
               cre_surname.usd_value as create_surname, cre_firstname.usd_value as create_firstname,
               cha_surname.usd_value as change_surname, cha_firstname.usd_value as change_firstname
          FROM '.TBL_DATE_ROLE.' dtr, '. TBL_CATEGORIES. ' cat, '. TBL_DATES. ' dat
          LEFT JOIN '. TBL_USER_DATA .' cre_surname 
            ON cre_surname.usd_usr_id = dat_usr_id_create
           AND cre_surname.usd_usf_id = '.$gProfileFields->getProperty('LAST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cre_firstname 
            ON cre_firstname.usd_usr_id = dat_usr_id_create
           AND cre_firstname.usd_usf_id = '.$gProfileFields->getProperty('FIRST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cha_surname
            ON cha_surname.usd_usr_id = dat_usr_id_change
           AND cha_surname.usd_usf_id = '.$gProfileFields->getProperty('LAST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_USER_DATA .' cha_firstname
            ON cha_firstname.usd_usr_id = dat_usr_id_change
           AND cha_firstname.usd_usf_id = '.$gProfileFields->getProperty('FIRST_NAME', 'usf_id').'
          LEFT JOIN '. TBL_MEMBERS. ' men
            ON mem.mem_usr_id = '.$gCurrentUser->getValue('usr_id').'
           AND mem.mem_rol_id = dat_rol_id
           AND mem_begin <= \''.DATE_NOW.'\'
           AND mem_end    > \''.DATE_NOW.'\'
         WHERE dat_cat_id = cat_id
           AND (  cat_org_id = '. $gCurrentOrganization->getValue('org_id'). '
               OR (   dat_global   = 1
                  AND cat_org_id IN ('.$organizations.') ))
           AND dat_id = dtr_dat_id
               '.$sqlConditionLogin.'
               '.$sqlConditions. $sqlConditionCalendar. $sqlOrderBy. '
         LIMIT '.$num_dates;
$dates_result = $gDb->query($sql);

// ab hier wird der iCal zusammengestellt

// Ein icalfeed-Objekt erstellen
$date = new TableDate($gDb);
$iCal = $date->getIcalHeader();

// Dem RSSfeed-Objekt jetzt die RSSitems zusammenstellen und hinzufuegen
while ($row = $gDb->fetch_array($dates_result))
{
    // ausgelesene Termindaten in Date-Objekt schieben
    $date->clear();
    $date->setArray($row);
    $iCal .= $date->getIcalVEvent($_SERVER['HTTP_HOST']);
}
$iCal .= $date->getIcalFooter();

if($getMode == 2)
{
    header('Content-Type: text/calendar');
    header('Content-Disposition: attachment; filename='. urlencode($getHeadline). '.ics');
    // noetig fuer IE, da ansonsten der Download mit SSL nicht funktioniert
    header('Cache-Control: private');
    header('Pragma: public');
}    
echo $iCal;
?>