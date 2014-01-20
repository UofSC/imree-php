<?php

/**
 * This is a sample config file with all the possible parameters you need to set
 * to use ALL of the shared_functions power. If you're planning on contributing
 * other updates back to our github, you'll need to move this file OUT of the 
 * git tree (i.e. mv config.sample.php ../config.php ). 
 */

/**
 * The paths to the function files. By default, we assume that the config
 * file is sitting just outside the shared_functions folder. For example, 
 * the config file is moved to /var/www/  with your index file that calls the 
 * config file; then, all the functions are located at /var/www/shared_functions
 */
require_once('shared_functions/functions.api.php');
require_once('shared_functions/functions.core.php');
require_once('shared_functions/functions.db.php');
require_once('shared_functions/functions.catalog.php');
require_once('shared_functions/functions.form.php');


/**
 * Database Connection Information
 */
$cfg_db_type = "mysql";
$cfg_db_host = "localhost"; //or mysite.com
$cfg_db_username = ""; 
$cfg_db_password = "";
$cfg_db_name = "";

/**
 * Recaptcha setup. There is a copy of recaptchalib.php within this project,
 * but you'll need to setup an account.
 */
$re_captcha_library ='shared_functions/recaptchalib.php';
$re_captcha_key_public = "";
$re_captcha_key_private = "";


/**
 * Default "from" email for mail sent within the PHP application.
 * To send emails you'll also need the swift_mailer which is included in 
 * the github repository as a merged sub-tree. For the full documentation
 * visit https://github.com/swiftmailer/swiftmailer
 */
$reply_email = "no-reply@mysite.com";
$swift_mailer_path = "shared_functions/swiftmailer/lib/swift_required.php";



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
$GLOBALS['session_name'] = 'my_game';


/**
 * LDAP is only required if you're using user_rights.php 
 */
//$ldap_server = "cae145adcp03.ds.sc.edu";



