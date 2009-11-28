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

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
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

$language_file = array('mods/info_acp_raidattendance', 'mods/mod_raidattendance');

 
/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*/

$options = array(
	'legend1'		=> 'GUILD_SETTINGS',
	'guild'			=> array('lang' => 'GUILD_NAME',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => false, 'default' => 'The Awakening'),
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

	'legend4'		=> 'RAIDER_RANKS',
	'min_level'		=> array('lang' => 'MIN_LEVEL',				'validate' => 'int', 	'type' => 'text:2:2', 	'explain' => true, 'default' => '80'),
	'raider_rank0'	=> array('lang' => 'RANK_0',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false),
	'raider_rank1'	=> array('lang' => 'RANK_1',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false, 'default' => true),
	'raider_rank2'	=> array('lang' => 'RANK_2',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false, 'default' => true),
	'raider_rank3'	=> array('lang' => 'RANK_3',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false, 'default' => true),
	'raider_rank4'	=> array('lang' => 'RANK_4',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false),
	'raider_rank5'	=> array('lang' => 'RANK_5',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false),
	'raider_rank6'	=> array('lang' => 'RANK_6',				'validate' => 'bool', 	'type' => 'radio', 	'explain' => false),
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
	), // end of 0.0.1	
	'0.0.2' => array(
		'config_add' => array(
			array('raidattendance_min_level', request_var('min_level', 80)),
			array('raidattendance_raider_rank0', request_var('raider_rank1', false)),
			array('raidattendance_raider_rank1', request_var('raider_rank1', true)),
			array('raidattendance_raider_rank2', request_var('raider_rank2', false)),
			array('raidattendance_raider_rank3', request_var('raider_rank3', true)),
			array('raidattendance_raider_rank4', request_var('raider_rank4', false)),
			array('raidattendance_raider_rank5', request_var('raider_rank5', false)),
			array('raidattendance_raider_rank6', request_var('raider_rank6', true)),
		),
		'permission_add' => array(
			array('m_raidattendance', true),
			array('u_raidattendance', true),
		),
		'permission_set' => array(
			array('ROLE_ADMIN_FULL', 	'm_raidattendance'),
			array('ROLE_MOD_FULL', 		'm_raidattendance'),
			array('ROLE_MOD_STANDARD', 	'm_raidattendance'),

			array('ROLE_FORUM_FULL', 	'u_raidattendance'),
		),		
		'module_add' => array(
			array('mcp', 0, 'MCP_RAIDATTENDANCE'),
			array('mcp', 'MCP_RAIDATTENDANCE',
				array(
					'module_basename'		=> 'raidattendance',
					'module_langname'		=> 'MCP_RAIDATTENDANCE_VIEW',
					'module_mode'			=> 'view',
					'module_auth'			=> 'acl_u_raidattendance',
				),
			),
		),
		'table_add' => array(
			array('phpbb_raidattendance_history', array(
				'COLUMNS'		=> array(
					'id'			=> array('UINT', NULL, 'auto_increment'),
					'user_id'		=> array('UINT', 0),
					'raider_id'		=> array('UINT', 0),
					'time'			=> array('TIMESTAMP', 0),
					'action'		=> array('VCHAR:255', ''),
				),
				'PRIMARY_KEY'		=> 'id',
				'KEYS'				=> array(
					'user_id'		=> array('INDEX', 'user_id'),
					'raider_id'		=> array('INDEX', 'raider_id'),
					),
				),
			),
			array('phpbb_raidattendance_raiders', array(
				'COLUMNS'		=> array(
					'id'			=> array('UINT', NULL, 'auto_increment'),
					'user_id'		=> array('UINT', NULL),
					'name'			=> array('VCHAR_UNI:255', ''),
					'class'			=> array('TINT:4', 0),
					'rank'			=> array('TINT:4', 0),
					'level'			=> array('TINT:4', 0),
					'synced'		=> array('TIMESTAMP', NULL),
					'created'		=> array('TIMESTAMP', 0),
					'edited'		=> array('TIMESTAMP', 0),
				),
				'PRIMARY_KEY'		=> 'id',
				'KEYS'				=> array(
					'name'			=> array('UNIQUE', 'name'),
				),
			)),
			array('phpbb_raidattendance', array(
				'COLUMNS'		=> array(
					'id'		=> array('UINT', NULL, 'auto_increment'),
					'raider_id'	=> array('UINT', 0),
					'night'		=> array('VCHAR:10', ''),
					'status'	=> array('TINT:4', 0),
					'time'		=> array('TIMESTAMP', 0),
				),
				'PRIMARY_KEY'	=> 'id',
				'KEYS'			=> array(
					'rid_night'	=> array('UNIQUE', array('raider_id', 'night')),
					'night'		=> array('INDEX', 'night'),
				),
			)),
		),
	), // end of 0.0.2
	'0.0.4' => array(
		'table_add' => array(
			array('phpbb_raidattendance_user_config' => array(
				'COLUMNS'		=> array(
					'id'		=> array('UINT', NULL, 'auto_increment'),
					'user_id'	=> array('UINT', 0),
					'night'		=> array('TINT:4', 0),
					'status'	=> array('TINT:4', 0),
				),
				'PRIMARY_KEY'	=> 'id',
				'KEYS'			=> array(
					'uniq'		=> array('UNIQUE', array('user_id', 'night')),
				),
			)
			),
		),
		'module_add' => array(
			array('ucp', 'UCP_MAIN',
				array(
					'module_basename'		=> 'raidattendance',
					'module_langname'		=> 'UCP_RAIDATTENDANCE_CONFIG',
					'module_mode'			=> 'config',
					'module_auth'			=> 'acl_u_raidattendance',
				),
			),		
		),
	), // end of 0.0.4
);
 
// Include the UMIF Auto file and everything else will be handled automatically.
include($install_root_path . 'umil/umil_auto.' . $phpEx);
?> 
