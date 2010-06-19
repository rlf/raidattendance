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
define('IN_INSTALL', true);

$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : '../';
$phpEx = substr(strrchr(__FILE__, '.'), 1);

include($phpbb_root_path . 'common.' . $phpEx);

$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
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
	'wws_guild_id'	=> array('lang' => 'WWS_GUILD_ID',			'validate' => 'int',	'type' => 'text:5:5', 'explain' => true, 'default' => '15320'),

	'legend2'		=> 'FORUM_SETTINGS',
	'forum_name'	=> array('lang' => 'FORUM_NAME',			'validate' => 'string',	'type' => 'text:40:255', 'explain' => true, 'default' => 'Raid Availability'),

	'legend3'		=> 'RAID_SETTINGS',
	'raid_time'		=> array('lang' => 'RAID_TIME', 'validate' => 'time', 'type' => 'text:5:5', 'explain' => true, 'default' => '19:45'),
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
	'1.0.0'	=> array(
		// Lets add a config setting named test_enable and set it to true
		'config_add' => array(
			// v0.0.1
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
			// v0.0.2
			array('raidattendance_min_level', request_var('min_level', 80)),
			array('raidattendance_raider_rank0', request_var('rank0', false)),
			array('raidattendance_raider_rank1', request_var('rank1', true)),
			array('raidattendance_raider_rank2', request_var('rank2', false)),
			array('raidattendance_raider_rank3', request_var('rank3', true)),
			array('raidattendance_raider_rank4', request_var('rank4', false)),
			array('raidattendance_raider_rank5', request_var('rank5', false)),
			array('raidattendance_raider_rank6', request_var('rank6', true)),
			array('raidattendance_raider_rank7', request_var('rank7', false)),
			array('raidattendance_raider_rank8', request_var('rank8', false)),
			array('raidattendance_raider_rank9', request_var('rank9', false)),
			// v0.0.4
			array('raidattendance_raider_rank0_name', request_var('rank0_name', 'Guild Leader')),
			array('raidattendance_raider_rank1_name', request_var('rank1_name', 'Rank 1')),
			array('raidattendance_raider_rank2_name', request_var('rank2_name', 'Rank 2')),
			array('raidattendance_raider_rank3_name', request_var('rank3_name', 'Rank 3')),
			array('raidattendance_raider_rank4_name', request_var('rank4_name', 'Rank 4')),
			array('raidattendance_raider_rank5_name', request_var('rank5_name', 'Rank 5')),
			array('raidattendance_raider_rank6_name', request_var('rank6_name', 'Rank 6')),
			array('raidattendance_raider_rank7_name', request_var('rank7_name', 'Rank 7')),
			array('raidattendance_raider_rank8_name', request_var('rank8_name', 'Rank 8')),
			array('raidattendance_raider_rank9_name', request_var('rank9_name', 'Rank 9')),
		),

		// Now to add some permission settings

		'permission_add' => array(
			array('a_raidattendance', true),
			array('m_raidattendance', true),
			array('u_raidattendance', true),
		),


		// How about we give some default permissions then as well?
		'permission_set' => array(
			// Global Role permissions
			array('ROLE_ADMIN_FULL', 		'a_raidattendance'),
			array('ROLE_ADMIN_STANDARD', 	'a_raidattendance'),
			array('ROLE_ADMIN_FULL', 		'm_raidattendance'),
			array('ROLE_MOD_FULL', 			'm_raidattendance'),
			array('ROLE_MOD_STANDARD', 		'm_raidattendance'),

			array('ROLE_FORUM_FULL', 		'u_raidattendance'),
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
	), // end of 1.0.0
	'1.0.2' => array(
		// No actual changes in 1.0.2, but we like the option
	), // end of 1.0.2
	'1.0.3' => array(
		'module_add' => array(
			// Add the wws mode 
			array('acp', 'ACP_CAT_RAIDATTENDANCE', 
				array(
					'module_basename'		=> 'raidattendance',
					'module_langname'		=> 'ACP_RAIDATTENDANCE_WWS',
					'module_mode'			=> 'wws',
					'module_auth'			=> 'acl_a_raidattendance',
				), 
			),
		),
		'table_column_add' => array(
			array('phpbb_raidattendance', 'comment', array('VCHAR_UNI', '')
		)),
		'table_add' => array(
			array('phpbb_raidattendance_wws', array(
				'COLUMNS'		=> array(
					'id'		=> array('UINT', NULL, 'auto_increment'),
					'raid'		=> array('VCHAR:10', ''),
					'synced'	=> array('TIMESTAMP', 0),
					'wws_id'	=> array('VCHAR:16', ''),
					'raiders'	=> array('MTEXT_UNI', ''),
				),
				'PRIMARY_KEY' => 'id',
			)),
		),
		'config_add' => array(
			
			array('raidattendance_wws_guild_id', request_var('wws_guild_id', 15320)),
		),
	),// v1.0.3
	'1.1.0' => array(
		'table_add' => array(
			array('phpbb_raidattendance_raids', array(
				'COLUMNS'		=> array(
					'id'		=> array('UINT', NULL, 'auto_increment'),
					'name'		=> array('VCHAR_UNI:255', ''),
					'days'		=> array('VCHAR:255', 'mon:wed:thu'),
				),
				'PRIMARY_KEY'	=> 'id',
				'KEYS'			=> array(
					'days'		=> array('INDEX', 'days'),
				),
			)),
			array('phpbb_raidattendance_raidersraid', array(
				'COLUMNS'		=> array(
					'raider_id'	=> array('UINT', 0),
					'raid_id'	=> array('UINT', 0),
				),
				'PRIMARY_KEY'	=> array('raider_id','raid_id'),
			)),
		),
		'custom'	=> 'v103_110',
	),// v1.1.0
	'1.1.1' => array(
		'config_add' => array(
			array('raidattendance_raid_time', request_var('raid_time', '19:45')),
		),
	), // v1.1.1
	'1.1.2' => array(
		'table_column_add' 		=> array(
			array('phpbb_raidattendance_raiders', 'role', array('UINT', 9)),
		),
	), // V1.1.2
	'1.1.3' => array(
	), // v1.1.3
	'1.1.4' => array(
	), // v1.1.3
	'1.1.5' => array(
	), // v1.1.5
	'1.1.6' => array(
		'table_column_add'		=> array(
			array('phpbb_raidattendance', 'raid_id', array('UINT', 0)),
			array('phpbb_raidattendance_history', 'raid_id', array('UINT', 0)),
		),
		'custom'	=> 'convert_attendance_for_raids',
	),
	'1.1.7' => array(
	), // v1.1.7
	'1.1.8' => array(
	), // v1.1.8
	'1.2.0' => array(
	), // v1.2.0
);
 
function v103_110($action, $version)
{
	global $db, $table_prefix, $umil, $config, $version_config_name, $user;
	$return_value = array('command' => 'V103_110_UPDATE', 'result' => 'FAIL');
	$current_version = $umil->config_exists($version_config_name, true);
	if (is_array($current_version)) 
	{
		$current_version = $current_version['config_value'];
	}

	if (($action == 'update' || $action == 'install') && $current_version == '1.0.3')
	{
		// Find days - from config
		$days = array();
		$k = 'raidattendance_raid_night_';
		$day_map = array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
		foreach ($day_map as $day)
		{
			if ($umil->config_exists($k . $day, true))
			{
				$days[] = $day;
			}
		}

		// Initialize raids
		$data = array(array(
			'name'			=> $user->lang('DEFAULT_RAID_NAME'),
			'days'			=> implode(':', $days),
		));
		$umil->table_row_insert('phpbb_raidattendance_raids', $data);
		$raid_id = $db->sql_nextid();

		// Initialize all raiders
		$raider_ids = array();
		$sql = 'SELECT id FROM ' . $table_prefix . 'raidattendance_raiders';
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result)) 
		{
			$raider_ids[] = $row['id'];
		}
		$db->sql_freeresult($result);
		$ary = array();
		foreach ($raider_ids as $raider_id)
		{
			$ary[] = array('raider_id' => $raider_id, 'raid_id' => $raid_id);
		}
		$umil->table_row_insert('phpbb_raidattendance_raidersraid', $ary);

		// Find forum_id
		$sql = 'SELECT forum_id FROM ' . FORUMS_TABLE . " WHERE forum_name='{$config['raidattendance_forum_name']}'";
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		if ($row) 
		{
			$forum_id = $row['forum_id'];
			$umil->config_add('raidattendance_forum_id', $forum_id);
		}
		$db->sql_freeresult($result);
		// Cleanup config
		foreach ($day_map as $day)
		{
			$umil->config_remove($k . $day);
		}
		$umil->config_remove('raidattendance_forum_name');

		$return_value['result'] = 'SUCCESS';
	}
	else if ($action == 'uninstall')
	{
		$return_value['result'] = 'SUCCESS';
	}
	else 
	{
		$return_value['command'] = array('NOT_CORRECT_VERSION', "$current_version/$action", '1.0.3/install');
	}
	return $return_value;
}
function convert_attendance_for_raids($action, $version)
{
	global $db, $table_prefix, $umil, $config, $version_config_name, $user;
	$current_version = $umil->config_exists($version_config_name, true);
	if (is_array($current_version)) 
	{
		$current_version = $current_version['config_value'];
	}
	if ($current_version >= '1.1.5' && $version <= '1.1.6' && $action == 'update')
	{
		// Only do the conversion if there's a chance some data have been registered wrongly
		// find default raid_id
		$sql = 'SELECT id FROM ' . $table_prefix . 'raidattendance_raids ORDER BY id';
		$result = $db->sql_query_limit($sql, 1);
		$raid_id = 0;
		if ($row = $db->sql_fetchrow($result))
		{
			$raid_id = $row['id'];
		}
		$db->sql_freeresult($result);
		$sql = 'UPDATE ' . $table_prefix . 'raidattendance SET raid_id=' . $raid_id . ' WHERE raider_id=0';
		$db->sql_query($sql);
	}	
}
// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);
?> 
