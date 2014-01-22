<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>IMREE Setup</title>
		<style>
			label {
				width:200px;
				float:left;
				display:box;
			}
			input {
				width:250px;
			}
		</style>
	</head>
	<body>
		<?php
			if(file_exists("../config.php")) {
				die("IMREE is already setup. To run this program again, delete the config.php from the folder 'above' the imree-php folder");
			} 
			
			echo "<h1>Starting IMREE Setup</h1>";
			
			if(file_put_contents("../config.php","") === false) {
				die("ERROR: PHP Cannot write to '../config.php'. the imree-php folder works best when it is NOT the root directory of your webserver. You can also solve this problem by creating a config.php file in setup.php's folder's parent's folder (../) and set its permissions to something more relaxed. Be sure to change the permissions back if/when setup.php is complete. ");
			}
			unlink('../config.php');
					
			$errors = array();
			
			if(isset($_POST['action']) AND $_POST['action'] === 'setup') {
				
				$require_fields = array(
				    "db_type", 
				    "db_host", 
				    "db_admin_username", 
				    "db_admin_password", 
				    "root_domain", 
				    "imree_absolute_path", 
				    "google_from", 
				    "google_username", 
				    "google_password", 
				    "google_signature",
				    "imree_admin_user",
				    "imree_admin_pass",
				    "imree_admin_pass_copy",
				);
				
				foreach($require_fields as $field) {
					if(!isset($_POST[$field]) AND strlen($_POST['field']) < 1) {
						$errors[] = $field . " is required";
					}
				}
				
				//Per entry checks
				if($_POST['imree_admin_pass'] !== $_POST['imree_admin_pass_copy']) {
					$errors[] = "The passwords you provided for the new IMREE admin account do not match";
				}
				if(substr($_POST['root_domain'], 0, 4) === "http" OR substr($_POST['root_domain'], -1) === "/") {
					$errors[] = "The Root Domain is invalid.";
				}
				if(substr($_POST['imree_absolute_path'], 0, 4) !== "http" OR substr($_POST['imree_absolute_path'], -1) !== "/") {
					$errors[] = "The Absolute IMREE Path in invalid.";
				}
				if(strpos($_POST['imree_absolute_path'], $_POST['root_domain']) === false) {
					$errors[] = "The Absolute IMREE Path does not include the Root Domain.";
				}
				
				try{
					$db = new PDO($_POST['db_type'] . ":host=" . $_POST['db_host'] . ";",$_POST['db_admin_username'],$_POST['db_admin_password']);
				} catch (PDOException $e) {
					$errors[] = "The username, password, db_type, or db_host you provided for the database connection information is incorrect. ".$e->getMessage();
				}
				
				
				if(count($errors) === 0) {
					if(isset($db) AND $db != null) {
						require_once('shared_functions/functions.core.php');
						$db->exec("
								SET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
								SET time_zone = '+00:00';

								DROP DATABASE IF EXISTS imree;
								CREATE DATABASE imree; 
								USE imree;
								CREATE TABLE IF NOT EXISTS `assets` (
									`asset_id` int(11) NOT NULL AUTO_INCREMENT,
									`asset_name` varchar(255) NOT NULL,
									`asset_type` enum('image','video','audio','text') NOT NULL,
									`asset_media_url` varchar(255) NOT NULL,
									`asset_thumb_url` varchar(255) NOT NULL,
									`asset_parent_id` int(11) NOT NULL,
									`asset_date_added` int(11) NOT NULL,
									`asset_date_created` datetime NOT NULL,
									`asset_date_start` datetime NOT NULL,
									`asset_date_end` datetime NOT NULL,
									PRIMARY KEY (`asset_id`)
								 ) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `asset_event_assignments` (
									  `asset_event_assignment_id` int(11) NOT NULL AUTO_INCREMENT,
									  `asset_id` int(11) NOT NULL,
									  `event_id` int(11) NOT NULL,
									  PRIMARY KEY (`asset_event_assignment_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `asset_group_assignments` (
									`asset_group_assignment_id` int(11) NOT NULL AUTO_INCREMENT,
									`asset_id` int(11) NOT NULL,
									`group_id` int(11) NOT NULL,
									PRIMARY KEY (`asset_group_assignment_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `asset_subject_assignments` (
									`asset_subject_assignment_id` int(11) NOT NULL AUTO_INCREMENT,
									`asset_id` int(11) NOT NULL,
									`subject_id` int(11) NOT NULL,
									PRIMARY KEY (`asset_subject_assignment_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `events` (
									`event_id` int(11) NOT NULL AUTO_INCREMENT,
									`event_name` int(11) NOT NULL,
									`event_date_start` datetime NOT NULL,
									`event_date_end` datetime NOT NULL,
									`event_date_start_approx` tinyint(1) NOT NULL DEFAULT '1',
									`event_date_end_approx` tinyint(1) NOT NULL DEFAULT '1',
									`event_parent_id` int(11) NOT NULL,
									PRIMARY KEY (`event_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `exhibits` (
									`exhibit_id` int(11) NOT NULL AUTO_INCREMENT,
									`exhibit_name` varchar(255) NOT NULL,
									`exhibit_date_start` datetime NOT NULL,
									`exhibit_date_end` datetime NOT NULL,
									`exhibit_department_id` int(11) NOT NULL,
									`theme_id` int(11) NOT NULL,
									PRIMARY KEY (`exhibit_id`)
								) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

								CREATE TABLE IF NOT EXISTS `groups` (
									`group_id` int(11) NOT NULL AUTO_INCREMENT,
									`group_name` varchar(255) NOT NULL,
									`group_type` enum('gallery','grid','list','narrative','linear','timeline','unset') NOT NULL DEFAULT 'gallery',
									`group_parent_id` int(11) NOT NULL,
									PRIMARY KEY (`group_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `group_exhibit_assignments` (
									`group_exhibit_assignment_id` int(11) NOT NULL AUTO_INCREMENT,
									`group_id` int(11) NOT NULL,
									`exhibit_id` int(11) NOT NULL,
									PRIMARY KEY (`group_exhibit_assignment_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `people` (
									`person_id` int(11) NOT NULL AUTO_INCREMENT,
									`person_name_last` varchar(255) NOT NULL,
									`person_name_first` varchar(255) NOT NULL,
									`person_title` varchar(255) NOT NULL,
									`person_department_id` int(11) NOT NULL,
									PRIMARY KEY (`person_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `person_role_assignment` (
								  `person_role_assignment_id` int(11) NOT NULL AUTO_INCREMENT,
								  `person_id` int(11) NOT NULL,
								  `role_id` int(11) NOT NULL,
								  `exhibit_id` int(11) NOT NULL,
								  PRIMARY KEY (`person_role_assignment_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `roles` (
								  `role_id` int(11) NOT NULL AUTO_INCREMENT,
								  `role_title` varchar(255) NOT NULL,
								  PRIMARY KEY (`role_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `subjects` (
								  `subject_id` int(11) NOT NULL AUTO_INCREMENT,
								  `subject_title` varchar(255) NOT NULL,
								  `subject_title_display` varchar(255) NOT NULL,
								  `subject_date_start` datetime NOT NULL,
								  `subject_date_end` datetime NOT NULL,
								  `subject_geolocation` varchar(255) NOT NULL,
								  PRIMARY KEY (`subject_id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;



								DROP DATABASE IF EXISTS ulogin;
								CREATE DATABASE ulogin; 
								USE ulogin;
								CREATE TABLE IF NOT EXISTS `ul_blocked_ips` (
								  `ip` varchar(39) CHARACTER SET ascii NOT NULL,
								  `block_expires` varchar(26) CHARACTER SET ascii NOT NULL,
								  PRIMARY KEY (`ip`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1;

								CREATE TABLE IF NOT EXISTS `ul_log` (
								  `timestamp` varchar(26) CHARACTER SET ascii NOT NULL,
								  `action` varchar(20) CHARACTER SET ascii NOT NULL,
								  `comment` varchar(255) CHARACTER SET ascii NOT NULL DEFAULT '',
								  `user` varchar(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
								  `ip` varchar(39) CHARACTER SET ascii NOT NULL
								) ENGINE=InnoDB DEFAULT CHARSET=latin1;

								CREATE TABLE IF NOT EXISTS `ul_logins` (
								  `id` int(11) NOT NULL AUTO_INCREMENT,
								  `username` varchar(400) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
								  `password` varchar(2048) CHARACTER SET ascii NOT NULL,
								  `date_created` varchar(26) CHARACTER SET ascii NOT NULL,
								  `last_login` varchar(26) CHARACTER SET ascii NOT NULL,
								  `block_expires` varchar(26) CHARACTER SET ascii NOT NULL,
								  PRIMARY KEY (`id`),
								  UNIQUE KEY `username` (`username`(255))
								) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

								CREATE TABLE IF NOT EXISTS `ul_nonces` (
								  `code` varchar(100) CHARACTER SET ascii NOT NULL,
								  `action` varchar(850) CHARACTER SET ascii NOT NULL,
								  `nonce_expires` varchar(26) CHARACTER SET ascii NOT NULL,
								  PRIMARY KEY (`code`),
								  UNIQUE KEY `action` (`action`(255))
								) ENGINE=InnoDB DEFAULT CHARSET=latin1;

								CREATE TABLE IF NOT EXISTS `ul_sessions` (
								  `id` varchar(128) CHARACTER SET ascii NOT NULL DEFAULT '',
								  `data` blob NOT NULL,
								  `session_expires` varchar(26) CHARACTER SET ascii NOT NULL,
								  `lock_expires` varchar(26) CHARACTER SET ascii NOT NULL,
								  PRIMARY KEY (`id`)
								) ENGINE=InnoDB DEFAULT CHARSET=latin1;

							");

						if((int)$db->errorCode() === 0) {
							echo "<p>Created databases imree, ulogin with table structures</p>";
						} else {
							echo "<div>There was a problem creating imree, ulogin databases with table structures: ".print_r($db->errorInfo(), true)."</div>";
						}
						
						$imree_username = "imree-php-conn";
						$imree_password = random_string(26);
						create_user($db,$imree_username, $imree_password, 'imree', '*', $_POST['db_host']);

						$UL_PDO_UPDATE_USER = "ulogin_update";
						$UL_PDO_UPDATE_PWD = random_string(26);
						if($_POST['db_host'] === 'localhost') {
							$host = "localhost";
						} else {
							$host = "%";
						}
						create_user($db, $UL_PDO_UPDATE_USER,$UL_PDO_UPDATE_PWD,'ulogin','ul_logins',$host,'SELECT, UPDATE, INSERT ');

						$UL_PDO_AUTH_USER = "ulogin_auth";
						$UL_PDO_AUTH_PWD = random_string(26);
						create_user($db, $UL_PDO_AUTH_USER,$UL_PDO_AUTH_PWD,'ulogin','*',$host,'SELECT, INSERT, UPDATE, DELETE');
						
						echo "<p>MySQL Users Created.</p>";
						
$cfg = '<?php
/**
 * This File was automatically generated by /imree-php/setup.php
 * For full documentation, or to create a custom config file, see /imree-php/config.sample.php
*/

$config_version =				0.0001;

$imree_assets_directory =		"imree_assets/";
$imree_directory =				"imree-php/";
$imree_admin_directory =			"imree-php/curator/";
$ulogin_directory =				"imree-php/external_packages/ulogin";
$swift_mailer_path =			"imree-php/external_packages/swiftmailer/lib/swift_required.php";
define("UL_DOMAIN",				"'.$_POST['root_domain'].'");
$imree_absolute_path =			"'.$_POST['imree_absolute_path'].'";
$imree_curator_absolute_path =	"'.$_POST['imree_absolute_path'].'curator/";
$google_analytics_account =		"'.$_POST['google_analytics_account'].'";
$google_analytics_account =		"'.$_POST['google_analytics_account'].'";
$cfg_db_type =					"'.$_POST['db_type'].'";
$cfg_db_host =					"'.$_POST['db_host'].'";
//Imree Connection
$cfg_db_name =					"imree";
$cfg_db_username =				"'.$imree_username.'"; 
$cfg_db_password =				"'.$imree_password.'";
$re_captcha_key_public =			"'.$_POST['re_captcha_key_public'].'";
$re_captcha_key_private =		"'.$_POST['re_captcha_key_private'].'";
$re_captcha_library =			"imree-php/shared_functions/recaptchalib.php";
$google_from_name =				"'.$_POST['google_from'].'";
$google_username =				"'.$_POST['google_username'].'";
$google_password =				"'.$_POST['google_password'].'";
$email_signature =				"'.$_POST['google_signature'].'";
$GLOBALS["session_name"] =		"imree";

require_once("imree-php/shared_functions/functions.api.php");
require_once("imree-php/shared_functions/functions.core.php");
require_once("imree-php/shared_functions/functions.db.php");
require_once("imree-php/shared_functions/functions.form.php");
require_once("imree-php/imree_functions/imree.template.php");

define("UL_SITE_KEY", "'.random_string(64).'");
define("UL_PDO_CON_STRING", $cfg_db_type . ":host=" . $cfg_db_host . ";dbname=ulogin");
define("UL_INC_DIR", $ulogin_directory);
define("UL_USES_AJAX", true);
define("UL_HTTPS", false);
define("UL_HSTS", 0);
define("UL_PREVENT_CLICKJACK", true);
define("UL_PREVENT_REPLAY", true);
define("UL_LOGIN_DELAY", 5);
define("UL_NONCE_EXPIRE", 900);
define("UL_AUTOLOGIN_EXPIRE", 5356800);
define("UL_MAX_USERNAME_LENGTH", 100);
define("UL_USERNAME_CHECK", "~^[\p{L}\p{M}\p{Nd}\._@/+-]*[\p{L}\p{M}\p{Nd}]+[\p{L}\p{M}\p{Nd}\._@/+-]*$~u");
define("UL_MAX_PASSWORD_LENGTH", 55);
define("UL_HMAC_FUNC", "sha256");
define("UL_PWD_FUNC", "{BCRYPT}");
define("UL_PWD_ROUNDS", 11);
define("UL_PROXY_HEADER", "");
define("UL_DEBUG", true);
define("UL_GENERIC_ERROR_MSG", "An error occured. Please try again or contact the administrator.");
define("UL_SITE_ROOT_DIR", $imree_directory);
define("UL_SESSION_AUTOSTART", true);
define("UL_SESSION_EXPIRE", 1200);
define("UL_SESSION_REGEN_PROB", 0);
define("UL_SESSION_BACKEND", "ulPdoSessionStorage");
define("UL_SESSION_CHECK_REFERER", true);
define("UL_SESSION_CHECK_IP", true);
define("UL_LOG", true);
define("UL_MAX_LOG_AGE", 5356800);
define("UL_MAX_LOG_RECORDS", 200000);
define("UL_BF_WINDOW", 300);
define("UL_BF_IP_ATTEMPTS", 5);
define("UL_BF_IP_LOCKOUT", 18000);
define("UL_BF_USER_ATTEMPTS", 10);
define("UL_BF_USER_LOCKOUT", 18000);
define("UL_DATETIME_FORMAT", "c");
define("UL_AUTH_BACKEND", "ulPdoLoginBackend");
define("UL_PDO_CON_INIT_QUERY", "");
define("UL_PDO_UPDATE_USER", "'.$UL_PDO_UPDATE_USER.'");
define("UL_PDO_UPDATE_PWD", "'.$UL_PDO_UPDATE_PWD.'");
define("UL_PDO_AUTH_USER", "'.$UL_PDO_AUTH_USER.'");
define("UL_PDO_AUTH_PWD", "'.$UL_PDO_AUTH_PWD.'");
define("UL_PDO_DELETE_USER", "'.$UL_PDO_AUTH_USER.'");
define("UL_PDO_DELETE_PWD", "'.$UL_PDO_AUTH_PWD.'");
define("UL_PDO_SESSIONS_USER", "'.$UL_PDO_AUTH_USER.'");
define("UL_PDO_SESSIONS_PWD", "'.$UL_PDO_AUTH_PWD.'");
define("UL_PDO_LOG_USER", "'.$UL_PDO_AUTH_USER.'");
define("UL_PDO_LOG_PWD", "'.$UL_PDO_AUTH_PWD.'");

require_once($ulogin_directory . "/pdo/include.inc.php");
require_once($ulogin_directory . "/main.inc.php");
init();

';						
						if(file_put_contents("../config.php", $cfg) === false) {
							echo "<p>We tried to create a config file but it failed. Below is the config data:</p>\n\n\n".$cfg;
							die();
						}
						$d = dir("../");
						set_include_path($d->path);
						require_once("../config.php");
						
						$ulogin = new uLogin();
						if($ulogin->CreateUser($_POST['imree_admin_user'], $_POST['imree_admin_pass'])) {
							echo "<p>User ".$_POST['imree_admin_user']." created succesfully.";
						} else {
							$errors[] = "We could not create the imree admin account. You may need to rerun this setup script as there is no other way to create the admin account.";
						}
						
						echo "<p>Configuration Complete. Please visit <a href='index.php'>your new IMREE</a></p>";
						
					} else {
						$errors[] = "Could not connect to database.";
					}
				}
			}
		if(!(isset($_POST['action']) AND $_POST['action'] === 'setup') OR count($errors)) {
			foreach($errors as $err) {
				echo "<div class='error'>".$err."</div>";
			}
		?>
		
		<form action='setup.php' method='POST'>
			<fieldset>
				<legend>Database Connections</legend>
				<label for='db_type'>Database Type</label><input id='db_type' name='db_type' type='text' value='mysql' ><br>
				<label for='db_host'>Database Host</label><input id='db_host' name='db_host' type='text' value='localhost'><br>
				<p>The following information will NOT be saved in the config file. It is used to create new users and database tables for you.</p>
				<label for='db_admin_username'>Database Admin Username</label><input id='db_admin_username' name='db_admin_username' type='text' ><br>
				<label for='db_admin_password'>Database Admin Password</label><input id='db_admin_password' name='db_admin_password' type='password' ><br>
			</fieldset>
			<fieldset>
				<legend>IMREE Admin Account</legend>
				<p>We're going to create an admin account for this installation of IMREE but we need to know what username and password you'd like to use for that. All other users in this system will be required to use an email as their username and we suggest the admin account be an email incase you need to reset your password. The email address should be an account that only IMREE admin(s) has(have) access to.</p>
				<label for='imree_admin_user'>IMREE Admin Username</label><input id='imree_admin_user' name='imree_admin_user' type='text' value='admin'><br>
				<label for='imree_admin_pass'>Database Admin Password</label><input id='imree_admin_pass' name='imree_admin_pass' type='password' ><br>
				<label for='imree_admin_pass_copy'>Database Admin Password</label><input id='imree_admin_pass_copy' name='imree_admin_pass_copy' type='password' ><br>
			</fieldset>
			<fieldset>
				<legend>Domain</legend>
				<p>This must be the same domain name that the browser uses to fetch your website, without the protocol specifier (don't use 'http(s)://'). For development on the local machine, use 'localhost'.  Takes the same format as the 'domain' parameter of the PHP setcookie function.</p>
				<label for='root_domain'>Website Domain</label><input id='root_domain' name='root_domain' type='text' value='imree.mysite.com' ><br>
				<p>The absolute IMREE Path should include the protocol (http(s)), and directory information, and end with a trailing slash</p>
				<label for='imree_absolute_path'>Absolute IMREE Path</label><input id='imree_absolute_path' name='imree_absolute_path' type='text' value='http://imree.mysite.com/imree-php/' ><br>
			</fieldset>
			<fieldset>
				<legend>3rd Party Accounts</legend>
				<p>Your Google Analytics Property ID (i.e. XXXXXXXXX-XX)</p>
				<label for='google_analytics_account'>Analytics ID</label><input id='google_analytics_account' name='google_analytics_account' type='text' value='' ><br>
				<p>A recaptcha account. <a href='http://www.google.com/recaptcha' target="_blank">http://www.google.com/recaptcha</a></p>
				<label for='re_captcha_key_public'>Recaptcha Public Key</label><input id='re_captcha_key_public' name='re_captcha_key_public' type='text' value='' ><br>
				<label for='re_captcha_key_private'>Recaptcha Private Key</label><input id="re_captcha_key_private" name='re_captcha_key_private' type='text' value='' ><br>
				<p>For ease of installation. IMREE sends email through a gmail address. This can be changed in shared_function/functions.core.php under send_gmail().</p>
				<label for='google_from'>From Name</label><input id='google_from' name='google_from' type='text' value='Library - no-reply' ><br>
				<p>Your google username EXCLUDES the @gmail.com. We just want the username, not the full email address</p>
				<label for='google_username'>Google Username</label><input id='google_username' name='google_username' type='text' value='' ><br>
				<label for='google_password'>Google Password</label><input id='google_password' name='google_password' type='password' value='' ><br>
				<label for='google_signature'>Email Signature Line</label><input id='google_signature' name='google_signature' type='text' value='-IMREE Admin Team' ><br>
			</fieldset>
			<input type='hidden' name='action' value='setup' >
			<button type='submit'>Create Configuration File</button>
		</form>
		
				<?php
			}
			
			function create_user($conn, $username, $password, $database, $table, $host, $permission = "SELECT, INSERT, UPDATE, DELETE ") {
				$conn->exec("DROP USER '$username'");
				
				if(strtolower($host) === "localhost") {
					$scope = "localhost";
				} else {
					$scope = "%";
				}
				$conn->exec("CREATE USER '$username'@'$scope' IDENTIFIED BY '$password';");
				$conn->exec("GRANT $permission ON $database.$table TO '$username'@'$scope';");
				
			}
			
			
		?>
	</body>
</html>
