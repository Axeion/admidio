<?php
/**
 ***********************************************************************************************
 * Constants that will be used within Admidio
 *
 * @copyright 2004-2016 The Admidio Team
 * @see https://www.admidio.org/
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License v2.0 only
 ***********************************************************************************************
 */
if (basename($_SERVER['SCRIPT_FILENAME']) === 'constants.php')
{
    exit('This page may not be called directly!');
}

// ##################
// ###  VERSIONS  ###
// ##################

// !!! Please do not edit these version numbers !!!
define('MIN_PHP_VERSION', '5.3.7');

define('ADMIDIO_VERSION_MAIN', 3);
define('ADMIDIO_VERSION_MINOR', 2);
define('ADMIDIO_VERSION_PATCH', 0);
define('ADMIDIO_VERSION_BETA', 1);
define('ADMIDIO_VERSION', ADMIDIO_VERSION_MAIN . '.' . ADMIDIO_VERSION_MINOR . '.' . ADMIDIO_VERSION_PATCH);

if(ADMIDIO_VERSION_BETA > 0)
{
    define('ADMIDIO_VERSION_TEXT', ADMIDIO_VERSION . ' Beta ' . ADMIDIO_VERSION_BETA);
}
else
{
    define('ADMIDIO_VERSION_TEXT', ADMIDIO_VERSION);
}

// ######################
// ###  URLS & PATHS  ###
// ######################

// Admidio Homepage
define('ADMIDIO_HOMEPAGE', 'https://www.admidio.org/');

// BASIC STUFF
// https://secure.php.net/manual/en/reserved.variables.server.php => $_SERVER['HTTPS']
define('HTTPS', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'); // true
define('PORT', (int) $_SERVER['SERVER_PORT']); // 443

$port = ((!HTTPS && PORT === 80) || (HTTPS && PORT === 443)) ? '' : ':' . PORT;
define('HOST', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'] . $port); // www.example.org:1324

$hostParts = explode(':', HOST);
define('DOMAIN', $hostParts[0]); // www.example.org

$admParts = explode('/adm_', dirname($_SERVER['SCRIPT_NAME']));
define('ADMIDIO_SUBFOLDER', $admParts[0] === DIRECTORY_SEPARATOR ? '' : $admParts[0]); // /subfolder

// URLS
define('SERVER_URL',  (HTTPS ? 'https://' : 'http://') . HOST); // https://www.example.org:1234
define('ADMIDIO_URL', SERVER_URL . ADMIDIO_SUBFOLDER); // https://www.example.org:1234/subfolder
define('FILE_URL',    SERVER_URL . $_SERVER['SCRIPT_NAME']); // https://www.example.org:1234/subfolder/adm_program/index.php
define('CURRENT_URL', SERVER_URL . $_SERVER['REQUEST_URI']); // https://www.example.org:1234/subfolder/adm_program/index.php?param=value

// PATHS
$admParts = explode(DIRECTORY_SEPARATOR . 'adm_', __DIR__);
define('WWW_PATH',     realpath($_SERVER['DOCUMENT_ROOT'])); // /var/www    Will get "SERVER_PATH" in v4.0
define('ADMIDIO_PATH', $admParts[0]); // /var/www/subfolder
define('CURRENT_PATH', realpath($_SERVER['SCRIPT_FILENAME'])); // /var/www/subfolder/adm_program/index.php

// FOLDERS
define('FOLDER_DATA', '/adm_my_files');
define('FOLDER_CLASSES', '/adm_program/system/classes');
define('FOLDER_LIBS_SERVER', '/adm_program/libs'); // PHP libs
define('FOLDER_LIBS_CLIENT', '/adm_program/libs'); // JS/CSS libs
define('FOLDER_LANGUAGES', '/adm_program/languages');
define('FOLDER_THEMES', '/adm_themes');
define('FOLDER_MODULES', '/adm_program/modules');
define('FOLDER_PLUGINS', '/adm_plugins');

// ####################
// ###  DATE-STUFF  ###
// ####################

// Define default timezone
date_default_timezone_set($gTimezone);

// date and time for use in scripts
define('DATE_NOW', date('Y-m-d', time()));
define('DATETIME_NOW', date('Y-m-d H:i:s', time()));
define('DATE_MAX', '9999-12-31');

// ###################
// ###  DB-TABLES  ###
// ###################

define('TBL_ANNOUNCEMENTS',       $g_tbl_praefix . '_announcements');
define('TBL_AUTO_LOGIN',          $g_tbl_praefix . '_auto_login');
define('TBL_CATEGORIES',          $g_tbl_praefix . '_categories');
define('TBL_COMPONENTS',          $g_tbl_praefix . '_components');
define('TBL_DATE_ROLE',           $g_tbl_praefix . '_date_role');
define('TBL_DATES',               $g_tbl_praefix . '_dates');
define('TBL_FILES',               $g_tbl_praefix . '_files');
define('TBL_FOLDERS',             $g_tbl_praefix . '_folders');
define('TBL_GUESTBOOK',           $g_tbl_praefix . '_guestbook');
define('TBL_GUESTBOOK_COMMENTS',  $g_tbl_praefix . '_guestbook_comments');
define('TBL_IDS',                 $g_tbl_praefix . '_ids');
define('TBL_INVENT',              $g_tbl_praefix . '_invent');
define('TBL_INVENT_DATA',         $g_tbl_praefix . '_invent_data');
define('TBL_INVENT_FIELDS',       $g_tbl_praefix . '_invent_fields');
define('TBL_LINKS',               $g_tbl_praefix . '_links');
define('TBL_LIST_COLUMNS',        $g_tbl_praefix . '_list_columns');
define('TBL_LISTS',               $g_tbl_praefix . '_lists');
define('TBL_MEMBERS',             $g_tbl_praefix . '_members');
define('TBL_MESSAGES',            $g_tbl_praefix . '_messages');
define('TBL_MESSAGES_CONTENT',    $g_tbl_praefix . '_messages_content');
define('TBL_ORGANIZATIONS',       $g_tbl_praefix . '_organizations');
define('TBL_PHOTOS',              $g_tbl_praefix . '_photos');
define('TBL_PREFERENCES',         $g_tbl_praefix . '_preferences');
define('TBL_REGISTRATIONS',       $g_tbl_praefix . '_registrations');
define('TBL_ROLE_DEPENDENCIES',   $g_tbl_praefix . '_role_dependencies');
define('TBL_ROLES',               $g_tbl_praefix . '_roles');
define('TBL_ROLES_RIGHTS',        $g_tbl_praefix . '_roles_rights');
define('TBL_ROLES_RIGHTS_DATA',   $g_tbl_praefix . '_roles_rights_data');
define('TBL_ROOMS',               $g_tbl_praefix . '_rooms');
define('TBL_SESSIONS',            $g_tbl_praefix . '_sessions');
define('TBL_TEXTS',               $g_tbl_praefix . '_texts');
define('TBL_USERS',               $g_tbl_praefix . '_users');
define('TBL_USER_DATA',           $g_tbl_praefix . '_user_data');
define('TBL_USER_FIELDS',         $g_tbl_praefix . '_user_fields');
define('TBL_USER_LOG',            $g_tbl_praefix . '_user_log');
define('TBL_USER_RELATIONS',      $g_tbl_praefix . '_user_relations');
define('TBL_USER_RELATION_TYPES', $g_tbl_praefix . '_user_relation_types');

// #####################
// ###  OTHER STUFF  ###
// #####################

// constants for column rol_leader_rights
define('ROLE_LEADER_NO_RIGHTS', 0);
define('ROLE_LEADER_MEMBERS_ASSIGN', 1);
define('ROLE_LEADER_MEMBERS_EDIT', 2);
define('ROLE_LEADER_MEMBERS_ASSIGN_EDIT', 3);

// Password settings
define('PASSWORD_MIN_LENGTH', 8);
define('PASSWORD_GEN_LENGTH', 16);
define('PASSWORD_GEN_CHARS', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
