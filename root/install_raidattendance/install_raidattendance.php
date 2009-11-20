<?php

/**
*
* @author Rapal
* @package raidattendance
* @copyright (c) 2008 TA
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
$install_root_path = './';

include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($install_root_path . 'umil/umil_auto.' . $phpEx))
{
        trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

 
// The name of the mod to be displayed during installation.
$mod_name = 'Raid Attendance';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/

$version_config_name = 'raidattendance_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/

$language_file = 'mods/info_acp_raidattendance';

 
/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*/

$options = array(
	'legend1'		=> 'GUILD_SETTINGS',
	'guild'			=> array('lang' => 'GUILD_NAME',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => false, 'default' => 'My Guild'),
	'realm'			=> array('lang' => 'REALM_NAME',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => false, 'default' => 'Bloodhoof'),
	'armory_link'	=> array('lang' => 'ARMORY_LINK',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true, 'default' => 'http://eu.wowarmory.com'),

	'legend2'		=> 'FORUM_SETTINGS',
	'forum_name'	=> array('lang' => 'FORUM_NAME',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true, 'default' => 'Raid Availability'),

	'legend3'		=> 'RAID_SETTINGS',
	'raid_night_mon'=> array('lang' => 'RAID_NIGHT_MON',		'validate' => 'bool', 'type' => 'radio', 'explain' => false, 'default' => true),
	'raid_night_tue'=> array('lang' => 'RAID_NIGHT_TUE',		'validate' => 'bool', 'type' => 'radio', 'explain' => false),
	'raid_night_wed'=> array('lang' => 'RAID_NIGHT_WED',		'validate' => 'bool', 'type' => 'radio', 'explain' => false, 'default' => true),
	'raid_night_thu'=> array('lang' => 'RAID_NIGHT_THU',		'validate' => 'bool', 'type' => 'radio', 'explain' => false, 'default' => true),
	'raid_night_fri'=> array('lang' => 'RAID_NIGHT_FRI',		'validate' => 'bool', 'type' => 'radio', 'explain' => false),
	'raid_night_sat'=> array('lang' => 'RAID_NIGHT_SAT',		'validate' => 'bool', 'type' => 'radio', 'explain' => false),
	'raid_night_sun'=> array('lang' => 'RAID_NIGHT_SUN',		'validate' => 'bool', 'type' => 'radio', 'explain' => false),
);

 
/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/

//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

 
/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/

$versions = array(
	// Version 0.0.1 - Initial version
	'0.0.1'	=> array(
		// Lets add a config setting named test_enable and set it to true
		'config_add' => array(
			array('raidattendance_enable', true),
			array('raidattendance_guild_name', request_var('guild', 'no guild given')),
			array('raidattendance_realm_name', request_var('realm', 'no realm given')),
			array('raidattendance_armory_link', request_var('armory_link', 'no armory')),
			array('raidattendance_forum_name', request_var('forum_name', 'no forum')),
			array('raidattendance_raid_night_mon', request_var('raid_night_mon', false)),
			array('raidattendance_raid_night_tue', request_var('raid_night_tue', false)),
			array('raidattendance_raid_night_wed', request_var('raid_night_wed', false)),
			array('raidattendance_raid_night_thu', request_var('raid_night_thu', false)),
			array('raidattendance_raid_night_fri', request_var('raid_night_fri', false)),
			array('raidattendance_raid_night_sat', request_var('raid_night_sat', false)),
			array('raidattendance_raid_night_sun', request_var('raid_night_sun', false)),
		),

		// Now to add some permission settings

		'permission_add' => array(
			array('a_raidattendance', true),
		),


		// How about we give some default permissions then as well?
		'permission_set' => array(
			// Global Role permissions
			array('ROLE_ADMIN_FULL', 'a_raidattendance'),
		),

		// Alright, now lets add some modules to the ACP
		'module_add' => array(
			// Add a main category
			array('acp', 'ACP_CAT_FORUMS', 'ACP_CAT_RAIDATTENDANCE'),

			array('acp', 'ACP_CAT_RAIDATTENDANCE', 
				array(
					'module_basename'        => 'raidattendance',
					'module_langname'        => 'ACP_RAIDATTENDANCE_SETTINGS',
					'module_mode'                => 'settings',
					'module_auth'                => 'acl_a_raidattendance',
				), 
			),

			array('acp', 'ACP_CAT_RAIDATTENDANCE', 
				array(
					'module_basename'        => 'raidattendance',
					'module_langname'        => 'ACP_RAIDATTENDANCE_SYNC',
					'module_mode'                => 'sync',
					'module_auth'                => 'acl_a_raidattendance',
				), 
			),
		),
	), // end of 0.0.0	
);

 
// Include the UMIF Auto file and everything else will be handled automatically.
include($install_root_path . 'umil/umil_auto.' . $phpEx);
?> 
