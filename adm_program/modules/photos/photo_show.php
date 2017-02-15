<?php
/**
 ***********************************************************************************************
 * Photoresizer
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */

/******************************************************************************
 * Parameters:
 *
 * pho_id    : Id des Albums, aus dem das Bild kommen soll
 * photo_nr  : Nummer des Bildes, das angezeigt werden soll
 * max_width : maximale Breite auf die das Bild skaliert werden kann
 * max_height: maximale Hoehe auf die das Bild skaliert werden kann
 * thumb     : ist thumb === true wird ein Thumbnail in der Größe der
 *             Voreinstellung zurückgegeben
 *
 *****************************************************************************/

require_once(__DIR__ . '/../../system/common.php');

// Initialize and check the parameters
$getPhotoId   = admFuncVariableIsValid($_GET, 'pho_id',     'int', array('requireValue' => true));
$getPhotoNr   = admFuncVariableIsValid($_GET, 'photo_nr',   'int');
$getMaxWidth  = admFuncVariableIsValid($_GET, 'max_width',  'int');
$getMaxHeight = admFuncVariableIsValid($_GET, 'max_height', 'int');
$getThumbnail = admFuncVariableIsValid($_GET, 'thumb',      'bool');

// check if the module is enabled and disallow access if it's disabled
if ($gPreferences['enable_photo_module'] == 0)
{
    $gMessage->show($gL10n->get('SYS_MODULE_DISABLED'));
    // => EXIT
}
elseif ($gPreferences['enable_photo_module'] == 2)
{
    // nur eingeloggte Benutzer duerfen auf das Modul zugreifen
    require(__DIR__ . '/../../system/login_valid.php');
}

// lokale Variablen initialisieren
$image = null;

// read album data out of session or database
if (isset($_SESSION['photo_album']) && (int) $_SESSION['photo_album']->getValue('pho_id') === $getPhotoId)
{
    $photoAlbum =& $_SESSION['photo_album'];
    $photoAlbum->setDatabase($gDb);
}
else
{
    $photoAlbum = new TablePhotos($gDb, $getPhotoId);
    $_SESSION['photo_album'] = $photoAlbum;
}

// Bildpfad zusammensetzten
$albumFolder = ADMIDIO_PATH . FOLDER_DATA . '/photos/' . $photoAlbum->getValue('pho_begin', 'Y-m-d') . '_' . $getPhotoId;
$picPath      = $albumFolder . '/' . $getPhotoNr . '.jpg';
$picThumbPath = $albumFolder . '/thumbnails/' . $getPhotoNr . '.jpg';

// im Debug-Modus den ermittelten Bildpfad ausgeben
$gLogger->info('ImagePath: ' . $picPath);

// Wenn Thumbnail existiert laengere Seite ermitteln
if ($getThumbnail)
{
    if ($getPhotoNr > 0)
    {
        $thumbLength = 1;
        if (is_file($picThumbPath))
        {
            // Ermittlung der Original Bildgroesse
            $imageSize = getimagesize($picThumbPath);

            $thumbLength = $imageSize[1];
            if ($imageSize[0] > $imageSize[1])
            {
                $thumbLength = $imageSize[0];
            }
        }

        // Nachsehen ob Bild als Thumbnail in entsprechender Groesse hinterlegt ist
        // Wenn nicht anlegen
        if (!is_file($picThumbPath) || $thumbLength != $gPreferences['photo_thumbs_scale'])
        {
            // Nachsehen ob Thumnailordner existiert und wenn nicht SafeMode ggf. anlegen
            if (!is_dir($albumFolder . '/thumbnails'))
            {
                $folder = new Folder($albumFolder);
                $folder->createFolder('thumbnails', true);
            }

            // nun das Thumbnail anlegen
            $image = new Image($picPath);
            $image->scaleLargerSide($gPreferences['photo_thumbs_scale']);
            $image->copyToFile(null, $picThumbPath);
        }
        else
        {
            header('Content-Type: image/jpeg');
            echo readfile($picThumbPath);
        }
    }
    else
    {
        // kein Bild uebergeben, dann NoPix anzeigen
        $image = new Image(THEME_ADMIDIO_PATH . '/images/nopix.jpg');
        $image->scaleLargerSide($gPreferences['photo_thumbs_scale']);
    }
}
else
{
    if (!is_file($picPath))
    {
        $picPath = THEME_ADMIDIO_PATH . '/images/nopix.jpg';
    }
    // Bild einlesen und scalieren
    $image = new Image($picPath);
    $image->scale($getMaxWidth, $getMaxHeight);
}

if ($image !== null)
{
    // insert copyright text into photo if photo size is larger than 200px
    if (($getMaxWidth > 200) && $gPreferences['photo_image_text'] !== '')
    {
        if ($gPreferences['photo_image_text_size'] > 0)
        {
            $fontSize = $getMaxWidth / $gPreferences['photo_image_text_size'];
        }
        else
        {
            $fontSize = $getMaxWidth / 40;
        }
        $fontX = $fontSize;
        $fontY = $image->imageHeight-$fontSize;
        $fontColor = imagecolorallocate($image->imageResource, 255, 255, 255);
        $fontTtf = THEME_ADMIDIO_PATH.'/font.ttf';
        $text = $gPreferences['photo_image_text'];
        imagettftext($image->imageResource, $fontSize, 0, $fontX, $fontY, $fontColor, $fontTtf, $text);
    }

    // Rueckgabe des neuen Bildes
    header('Content-Type: '. $image->getMimeType());
    $image->copyToBrowser();
    $image->delete();
}
