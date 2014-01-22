<?php
/**
 * This is a sample config file that needs to be saved somewhere that your PHP
 * program can access but apache (or other webserver) can not. You will need
 * to reference this file in the index file of the imree program
 * @author Jason Steelman <uscart@gmail.com>
 * 
 * 
 * Move this file "back" one directory
 * ===================================
 * By default, this file wants to placed immediately below the imree index file 
 * so that in the index file "../config.php" will point to this file.
 */

/** 
 * Directories
 * ===========
 * Should be relative to the target index file
 */
$imree_assets_directory =   "/var/www/imree_assets/";
$imree_directory =          "/var/www/imree-php/";
$imree_admin_directory =    "/var/www/imree-php/curator/";
$ulogin_directory =         "/var/www/imree-php/external_packages/ulogin/";
$swift_mailer_path =        "/var/www/imree-php/external_packages/swiftmailer/lib/swift_required.php";


// Domain name of your site.
// This must be the same domain name that the browser uses to 
// fetch your website, without the protocol specifier (don't use 'http(s)://').
// For development on the local machine, use 'localhost'.
// Takes the same format as the 'domain' parameter of the PHP setcookie function.
define('UL_DOMAIN', 'XXXXXXXXXXXXXXXXXXXXXX');

//Absolute URL to IMREE directory (containing index.php). End with a trailing
//Slash:
$imree_absolute_path =          "http://mysite.com/imree-php/";
$imree_curator_absolute_path =  "http://mysite.com/imree-php/curator/";


/**
 * Google Analytics (optional)
 * ===========================
 * Set the google_analytics_account to FALSE and it will be ignored, otherwise
 * an unset account will generate a JS error in the browser. 
 */
$google_analytics_account = ""; //UA-XXXXXXX-XX

/**
 * Shared Functions Settings
 * =========================
 * These settings are copied from shared_functions/config.sample.php. If this
 * config file is placed correctly (see step 1), the paths should not need to be
 * changed.
 */

/**
 * The paths to the function files 
 * ===============================
 */
require_once('imree-php/shared_functions/functions.api.php');
require_once('imree-php/shared_functions/functions.core.php');
require_once('imree-php/shared_functions/functions.db.php');
require_once('imree-php/shared_functions/functions.form.php');
require_once('imree-php/imree_functions/imree.template.php');


/**
 * Database Connection Information
 * ===============================
 * Below are two database connection areas. One is for the IMREE system, the 
 * other is for the ulogin system. These should use two DIFFERENT accounts
 * to minimize security risks. 
 */
$cfg_db_type =          "mysql";
$cfg_db_host =          "localhost"; //or library.site.com, etc...

//Imree Connection
$cfg_db_name =          "imree";
$cfg_db_username =      "XXXXXXXXXXXXXXXXXXXXXX"; 
$cfg_db_password =      "XXXXXXXXXXXXXXXXXXXXXX";

//ulogin Connection
$cfg_ulogin_db_name =   "ulogin";
$cfg_ulogin_username =  "XXXXXXXXXXXXXXXXXXXXXX";
$cfg_ulogin_password =  "XXXXXXXXXXXXXXXXXXXXXX";


/**
 * Site Crypto Key
 * ===============
 * A random string. Make it as random as possible and keep it secure.
 * This is a crypthographic key that uLogin will use to generate some data
 * and later verify its identity. The longer the better, should be 40+ 
 * characters. Once set and your site is live, do not change this.
 */
define('UL_SITE_KEY', 'XXXXXXXXXXXXXXXXXXXXXX');


/**
 * Recaptcha setup. There is a copy of recaptchalib.php within this project,
 * but you'll need to setup an account at http://www.google.com/recaptcha.
 */
$re_captcha_library ='imree/shared_functions/recaptchalib.php';
$re_captcha_key_public = "";
$re_captcha_key_private = "";


/**
 * Default "from" email for mail sent within the PHP application.
 * To send emails you'll also need the swift_mailer which is included in 
 * the github repository as a merged sub-tree. For the full documentation
 * visit https://github.com/swiftmailer/swiftmailer
 */
$reply_email = "no-reply@mysite.com";



/**
 * Non-standard Settings
 * =====================
 */


/**
 * The name of the session variable to use when multiple systems that use
 * shared_functions on the same server. If you have a ticket system and a 
 * web admin section that need to be different, they need to have different 
 * session names set here with a different copy of this config file for
 * each project.
 * @global string $GLOBALS['session_name']
 * @name $session_name 
 */
$GLOBALS['session_name'] = 'imree';

/**
 * Should-not be changed settings unless really really odd setup. Documentation
 * on these settings are found in the ulogin project.
 */

define('UL_PDO_CON_STRING', $cfg_db_type . ":host=" . $cfg_db_host . ";dbname=" . $cfg_ulogin_db_name);
define('UL_INC_DIR', $ulogin_directory);
define('UL_USES_AJAX', true);
define('UL_HTTPS', false);
define('UL_HSTS', 0);
define('UL_PREVENT_CLICKJACK', true);
define('UL_PREVENT_REPLAY', true);
define('UL_LOGIN_DELAY', 5);
define('UL_NONCE_EXPIRE', 900);
define('UL_AUTOLOGIN_EXPIRE', 5356800);
define('UL_MAX_USERNAME_LENGTH', 100);
define('UL_USERNAME_CHECK', '~^[\p{L}\p{M}\p{Nd}\._@/+-]*[\p{L}\p{M}\p{Nd}]+[\p{L}\p{M}\p{Nd}\._@/+-]*$~u');
define('UL_MAX_PASSWORD_LENGTH', 55);
define('UL_HMAC_FUNC', 'sha256');
define('UL_PWD_FUNC', '{BCRYPT}');
define('UL_PWD_ROUNDS', 11);
define('UL_PROXY_HEADER', '');
define('UL_DEBUG', true);
define('UL_GENERIC_ERROR_MSG', 'An error occured. Please try again or contact the administrator.');
define('UL_SITE_ROOT_DIR', $imree_directory);
define('UL_SESSION_AUTOSTART', true);
define('UL_SESSION_EXPIRE', 1200);
define('UL_SESSION_REGEN_PROB', 0);
define('UL_SESSION_BACKEND', 'ulPdoSessionStorage');
define('UL_SESSION_CHECK_REFERER', true);
define('UL_SESSION_CHECK_IP', true);
define('UL_LOG', true);
define('UL_MAX_LOG_AGE', 5356800);
define('UL_MAX_LOG_RECORDS', 200000);
define('UL_BF_WINDOW', 300);
define('UL_BF_IP_ATTEMPTS', 5);
define('UL_BF_IP_LOCKOUT', 18000);
define('UL_BF_USER_ATTEMPTS', 10);
define('UL_BF_USER_LOCKOUT', 18000);
define('UL_DATETIME_FORMAT', 'c');
define('UL_AUTH_BACKEND', 'ulPdoLoginBackend');
define('UL_PDO_CON_INIT_QUERY', "");
define('UL_PDO_UPDATE_USER', $cfg_ulogin_username);
define('UL_PDO_UPDATE_PWD', $cfg_ulogin_password);
define('UL_PDO_AUTH_USER', $cfg_ulogin_username);
define('UL_PDO_AUTH_PWD', $cfg_ulogin_password);
define('UL_PDO_DELETE_USER', $cfg_ulogin_username);
define('UL_PDO_DELETE_PWD', $cfg_ulogin_password);
define('UL_PDO_SESSIONS_USER', $cfg_ulogin_username);
define('UL_PDO_SESSIONS_PWD', $cfg_ulogin_password);
define('UL_PDO_LOG_USER', $cfg_ulogin_username);
define('UL_PDO_LOG_PWD', $cfg_ulogin_password);

require_once($ulogin_directory . 'main.inc.php');