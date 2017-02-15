<?php
/**
 ***********************************************************************************************
 * Installation and configuration of Admidio database and config file
 *
 * @copyright 2004-2017 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 *
 * Parameters:
 *
 * step = choose_language      : (Default) Choose language
 *        welcome              : Welcome to installation
 *        connect_database     : Enter database access information
 *        create_organization  : Creating organization
 *        create_administrator : Creating administrator
 *        create_config        : Creating configuration file
 *        download_config      : Download configuration file
 *        start_installation   : Start installation
 ***********************************************************************************************
 */

// if config file already exists then load file with their variables
$configFile = '../../adm_my_files/config.php';
if (is_file($configFile))
{
    require_once($configFile);
}
else
{
    $g_organization = '';
}

if (!isset($_SESSION['create_config_file']))
{
    $_SESSION['create_config_file'] = true;
}

if (!isset($g_tbl_praefix))
{
    if (isset($_SESSION['prefix']))
    {
        $g_tbl_praefix = $_SESSION['prefix'];
    }
    else
    {
        // default praefix is "adm" because of compatibility to older versions
        $g_tbl_praefix = 'adm';
    }
}

$rootPath = substr(__FILE__, 0, strpos(__FILE__, DIRECTORY_SEPARATOR . 'adm_program'));
require_once($rootPath . '/adm_program/system/init_globals.php');
require_once($rootPath . '/adm_program/system/constants.php');

// check PHP version and show notice if version is too low
if (version_compare(PHP_VERSION, MIN_PHP_VERSION, '<'))
{
    exit('<div style="color: #cc0000;">Error: Your PHP version '.PHP_VERSION.' does not fulfill
        the minimum requirements for this Admidio version. You need at least PHP '.MIN_PHP_VERSION.' or higher.</div>');
}

require_once(ADMIDIO_PATH . '/adm_program/installation/install_functions.php');
require_once(ADMIDIO_PATH . '/adm_program/system/function.php');
require_once(ADMIDIO_PATH . '/adm_program/system/string.php');
require_once(ADMIDIO_PATH . '/adm_program/system/logging.php');

// Initialize and check the parameters
Session::start('ADMIDIO');

define('THEME_URL', 'layout');

$availableSteps = array('choose_language', 'welcome', 'connect_database', 'create_organization', 'create_administrator', 'create_config', 'download_config', 'start_installation');

if (empty($_GET['step']))
{
    $step = $availableSteps[0];
}
else
{
    $step = $_GET['step'];
}

if (!in_array($step, $availableSteps, true))
{
    admRedirect(ADMIDIO_URL . '/adm_program/installation/installation.php?step=choose_language');
    // => EXIT
}

$message = '';

// create language and language data object to handle translations
$language = '';

if (isset($_SESSION['language']))
{
    $language = $_SESSION['language'];
}

$gL10n = new Language();
$gLanguageData = new LanguageData($language);

$gL10n->addLanguageData($gLanguageData);
$language = $gL10n->getLanguage();

$pathConfigFile = ADMIDIO_PATH . FOLDER_DATA . '/config.php';

$hostnameRegex = '/^(([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z]|[A-Za-z][A-Za-z0-9\-]*[A-Za-z0-9])$/';
$sqlIdentifiersRegex = '/^[a-zA-Z]([a-zA-Z0-9_]*[a-zA-Z0-9])?$/';

// if config file exists then connect to database
if (is_file($pathConfigFile))
{
    try
    {
        $db = new Database($gDbType, $g_adm_srv, $g_adm_port, $g_adm_db, $g_adm_usr, $g_adm_pw);
    }
    catch (AdmException $e)
    {
        showNotice(
            $gL10n->get('SYS_DATABASE_NO_LOGIN', $e->getText()),
            'installation.php?step=connect_database',
            $gL10n->get('SYS_BACK'),
            'layout/back.png'
        );
        // => EXIT
    }

    // now check if a valid installation exists.
    $sql = 'SELECT org_id FROM '.TBL_ORGANIZATIONS;
    $pdoStatement = $db->query($sql, false);

    // Check the query for results in case installation is runnnig at this time and the config file is already created but database is not installed so far
    if ($pdoStatement !== false && $pdoStatement->rowCount() > 0)
    {
        // valid installation exists -> exit installation
        showNotice(
            $gL10n->get('INS_INSTALLATION_EXISTS'),
            '../index.php',
            $gL10n->get('SYS_OVERVIEW'),
            'layout/application_view_list.png'
        );
        // => EXIT
    }

    // if config exists then take parameters out of this file
    if ($step === 'choose_language' || $step === 'welcome')
    {
        // save database parameters of config.php in session variables
        $_SESSION['db_type']     = $gDbType;
        $_SESSION['db_host']     = $g_adm_srv;
        $_SESSION['db_port']     = $g_adm_port;
        $_SESSION['db_database'] = $g_adm_db;
        $_SESSION['db_user']     = $g_adm_usr;
        $_SESSION['db_password'] = $g_adm_pw;
        $_SESSION['prefix']      = $g_tbl_praefix;

        admRedirect(ADMIDIO_URL . '/adm_program/installation/installation.php?step=create_organization');
        // => EXIT
    }
}

switch ($step)
{
    case 'choose_language': // (Default) Choose language
        $gLogger->info('INSTALLATION: Choose language');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/choose_language.php');
        break;

    case 'welcome': // Welcome to installation
        $gLogger->info('INSTALLATION: Welcome to installation');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/welcome.php');
        break;

    case 'connect_database': // Enter database access information
        $gLogger->info('INSTALLATION: Enter database access information');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/connect_database.php');
        break;

    case 'create_organization': // Creating organization
        $gLogger->info('INSTALLATION: Creating organisation');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/create_organization.php');
        break;

    case 'create_administrator': // Creating administrator
        $gLogger->info('INSTALLATION: Creating administrator');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/create_administrator.php');
        break;

    case 'create_config': // Creating configuration file
        $gLogger->info('INSTALLATION: Creating configuration file');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/create_config.php');
        break;

    case 'download_config': // Download configuration file
        $gLogger->info('INSTALLATION: Download configuration file');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/download_config.php');
        break;

    case 'start_installation': // Start installation
        $gLogger->info('INSTALLATION: Start installation');
        require_once(ADMIDIO_PATH . '/adm_program/installation/install_steps/start_installation.php');
        break;
}
