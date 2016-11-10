<?php
/**
 ***********************************************************************************************
 * Init Admidio Logger
 *
 * @copyright 2004-2016 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
use Monolog\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Processor\IntrospectionProcessor;

$gLogger = new Logger('Admidio');

$logLevel = Logger::WARNING;
if ($gDebug)
{
    $logLevel = Logger::DEBUG;
}

// Append line/file/class/function where the log message came from
$gLogger->pushProcessor(new IntrospectionProcessor($logLevel));

$formatter = new LineFormatter(null, null, false, true);
$streamHandler = new StreamHandler(ADMIDIO_PATH . FOLDER_DATA . '/logs/admidio.log', $logLevel, true, 0777);
$errorLogHandler = new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::ERROR);

$streamHandler->setFormatter($formatter);
$errorLogHandler->setFormatter($formatter);

$gLogger->pushHandler($streamHandler);
$gLogger->pushHandler($errorLogHandler);

$gLogger->notice('#################################################################################################');
$gLogger->notice('URL: ' . CURRENT_URL);
$gLogger->notice('MEMORY USAGE: ' . round(memory_get_usage() / 1024, 1) . ' KB');

// Log Constants
$constants = array(
    'ADMIDIO_HOMEPAGE' => ADMIDIO_HOMEPAGE,
    // Basic Stuff
    'HTTPS'             => HTTPS,
    'PORT'              => PORT,
    'HOST'              => HOST,
    'DOMAIN'            => DOMAIN,
    'ADMIDIO_SUBFOLDER' => ADMIDIO_SUBFOLDER,
    // URLs
    'SERVER_URL'  => SERVER_URL,
    'ADMIDIO_URL' => ADMIDIO_URL,
    'FILE_URL'    => FILE_URL,
    'CURRENT_URL' => CURRENT_URL,
    // Paths
    'WWW_PATH'     => WWW_PATH, // Will get "SERVER_PATH" in v4.0
    'ADMIDIO_PATH' => ADMIDIO_PATH,
    'CURRENT_PATH' => CURRENT_PATH,
    // Folders
    'FOLDER_DATA'        => FOLDER_DATA,
    'FOLDER_CLASSES'     => FOLDER_CLASSES,
    'FOLDER_LIBS_SERVER' => FOLDER_LIBS_SERVER,
    'FOLDER_LIBS_CLIENT' => FOLDER_LIBS_CLIENT,
    'FOLDER_LANGUAGES'   => FOLDER_LANGUAGES,
    'FOLDER_THEMES'      => FOLDER_THEMES,
    'FOLDER_MODULES'     => FOLDER_MODULES,
    'FOLDER_PLUGINS'     => FOLDER_PLUGINS
);
$gLogger->info('CONSTANTS: URLS & PATHS & FOLDERS', $constants);
