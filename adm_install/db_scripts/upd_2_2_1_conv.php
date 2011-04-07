<?php
/******************************************************************************
 * Datenkonvertierung fuer die Version 2.2.0
 *
 * Copyright    : (c) 2004 - 2011 The Admidio Team
 * Homepage     : http://www.admidio.org
 * License      : GNU Public License 2 http://www.gnu.org/licenses/gpl-2.0.html
 *
 *****************************************************************************/

// englische Bezeichnung der Bereiche in Systememails einbauen
$sql = 'SELECT * FROM '. TBL_TEXTS. ' ORDER BY txt_id DESC';
$result_texts = $g_db->query($sql);

while($row_texts = $g_db->fetch_array($result_texts))
{
    $row_texts['txt_text'] = preg_replace ('/#Betreff#/', '#subject#',  $row_texts['txt_text']);
    $row_texts['txt_text'] = preg_replace ('/#Inhalt#/', '#content#',  $row_texts['txt_text']);

    $sql = 'UPDATE '. TBL_TEXTS. ' SET txt_text = "'.addslashes($row_texts['txt_text']). '"
             WHERE txt_id = '.$row_texts['txt_id'];
    $g_db->query($sql);    
}

// Laenderbezeichnung durch ISOCODES ersetzen, damit die Laender sprachabhaengig angezeigt werden
foreach($g_l10n->getCountries() as $key => $value)
{
	if($g_preferences['default_country'] == $value)
	{
		$sql = 'UPDATE '.TBL_PREFERENCES.' SET prf_value = "'.$key.'" 
		         WHERE prf_name LIKE "default_country" ';
		$g_db->query($sql);
	}
}

// Laenderbezeichnung durch ISOCODES ersetzen, damit die Laender sprachabhaengig angezeigt werden
$sql = 'SELECT distinct usd_value, usd_usf_id FROM '.TBL_USER_DATA.', '.TBL_USER_FIELDS.'
		 WHERE usf_name_intern LIKE "COUNTRY"
		   AND usd_usf_id = usf_id
		   AND length(usd_value) > 0 ';
$result_countries = $g_db->query($sql);

while($row_countries = $g_db->fetch_array($result_countries))
{
	foreach($g_l10n->getCountries() as $key => $value)
	{
		if($row_countries['usd_value'] == $value)
		{
			$sql = 'UPDATE '.TBL_USER_DATA.' SET usd_value = "'.$key.'" 
					 WHERE usd_usf_id = '.$row_countries['usd_usf_id'];
			$g_db->query($sql);
		}
	} 
}

// Laenderbezeichnung durch ISOCODES ersetzen, damit die Laender sprachabhaengig angezeigt werden
$sql = 'SELECT distinct dat_country, dat_id FROM '.TBL_DATES.'
		 WHERE length(dat_country) > 0 ';
$result_countries = $g_db->query($sql);

while($row_countries = $g_db->fetch_array($result_countries))
{
	foreach($g_l10n->getCountries() as $key => $value)
	{
		if($row_countries['dat_country'] == $value)
		{
			$sql = 'UPDATE '.TBL_DATES.' SET dat_country = "'.$key.'" 
					 WHERE dat_id = '.$row_countries['dat_id'];
			$g_db->query($sql);
		}
	} 
}

?>