<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  

/**
* DO NOT CHANGE 
*/
if (empty($lang) || !is_array($lang)) {
	$lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
// 
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine
$lang = array_merge($lang, array(
	// 
	// Installation
	//
		'INSTALL_RAIDATTENDANCE' => 'Install Raid Attendance',
		'INSTALL_RAIDATTENDANCE_CONFIRM' => 'Install Raid Attendance?',
		'UPDATE_RAIDATTENDANCE' => 'Update Raid Attendance',
		'UPDATE_RAIDATTENDANCE_CONFIRM' => 'Update Raid Attendance?',
		'UNINSTALL_RAIDATTENDANCE' => 'Uninstall Raid Attendance',
		'UNINSTALL_RAIDATTENDANCE_CONFIRM' => 'Uninstall Raid Attendance?',
		'ACP_CAT_RAIDATTENDANCE'	=> 'Raid Attendance',

		'NOT_CORRECT_VERSION'		=> 'Incorrect version %1$s, expected %2$s',
		'V103_110_UPDATE'			=> 'Auto-update from v1.0.3 - v1.1.0',
	//
	// ACP - Configuration
	//
		'ACP_RAIDATTENDANCE'	=> 'Raid Attendance',
		'ACP_RAIDATTENDANCE_SETTINGS'	=> 'Configuration',
		'ACP_RAIDATTENDANCE_SETTINGS_EXPLAIN'	=> 'MOD Configuration of the Hippie Eradicator',
		'GUILD_SETTINGS'			=> 'Guild Configuration',
		'GUILD_NAME'				=> 'Guild Name',
		'REALM_NAME'				=> 'Realm',
		'ARMORY_LINK'				=> 'Armory',
		'ARMORY_LINK_EXPLAIN'		=> 'Link to the relevant wowarmory, e.g. http://eu.wowarmory.com',
		'FORUM_SETTINGS'			=> 'Forum Configuration',
		'FORUM_NAME'				=> 'Raid Availability Forum Name',
		'FORUM_NAME_EXPLAIN'		=> 'The name of the forum on which raid-availability will be shown',
		'RAID_SETTINGS'				=> 'Raid Night Configuration',
		'RAID_NIGHTS'				=> 'Raid Nights',
		'RAID_NIGHT_MON'			=> 'Monday',
		'RAID_NIGHT_TUE'			=> 'Tuesday',
		'RAID_NIGHT_WED'			=> 'Wednesday',
		'RAID_NIGHT_THU'			=> 'Thursday',
		'RAID_NIGHT_FRI'			=> 'Friday',
		'RAID_NIGHT_SAT'			=> 'Saturday',
		'RAID_NIGHT_SUN'			=> 'Sunday',

		'RAIDER_RANKS'				=> 'Rank of Raiders',
		'MIN_LEVEL'					=> 'Raider Level',
		'MIN_LEVEL_EXPLAIN'			=> 'At which level a player of the below indicated rank is expected to behave as a raider',
		
		'acl_a_raidattendance'		=> array('lang' => 'Can configure Raid Attendance', 'cat' => 'misc'),
		'acl_m_raidattendance'		=> array('lang' => 'Can synchronize Raid Attendance', 'cat' => 'misc'),
		'acl_u_raidattendance'		=> array('lang' => 'Can view Raid Attendance', 'cat' => 'misc'),

		'ACP_RAIDATTENDANCE_SYNC'	=> 'Raiders',
		'ACP_RAIDATTENDANCE_SYNC_EXPLAIN' => 'Manages the raiders in the guild.',
		'RESYNC'					=> 'Resync with armory',
		'RESYNC_EXPLAIN'			=> 'Synchronises the raider-table with wow-armory.',
		'FORUM_USER'				=> 'Forum User',
		'UNKNOWN_USER'				=> '-',
		'DELETE_SELECTED'			=> 'Delete selected raiders',
		'ERROR_CONTACTING_ARMORY'	=> 'An error occurred during synchronization with wow-armory<br/>%s',
		'RAIDER_ADDED_FROM_ARMORY'	=> 'Added %s from armory',
		'ERROR_ADDING_RAIDER'		=> 'Error committing %1$s to the database<br/>%2$s',
		'ERROR_UPDATING_RAIDER'		=> 'Error updating %1$s to the database<br/>%2$s',
		'ERROR_DELETING_RAIDER'		=> 'Error deleting %1$s from the raider-list<br/>%2$s',
		'SUCCESS_DELETING_RAIDER'	=> '%s was deleted from the raider-list',
		'SUCCESS_ADDING_RAIDER'		=> '%1$s was successfully saved to the database.',
		'SUCCESS_UPDATING_RAIDER'	=> '%1$s was successfully updated in the database.',
		'SUCCESS'					=> 'Success',
		'WARNING'					=> 'Warning',
		'ADDED_RAIDERS'				=> '%s was successfully added to the raider-list',
		'NO_NEW_RAIDERS_IN_ARMORY'	=> 'There were no new raiders in the armory',
		'STATUS_NOT_IN_ARMORY'		=> 'not in armory',
		'STATUS_UPDATED'			=> 'updated',
		'STATUS_NEW'				=> 'new',
		'RAIDER_ALREADY_EXISTS'		=> 'A raider named %s already exists!',
		'ADD_RAIDER'				=> 'Add raider',
	//
	// ACP - WWS
	//
		'ACP_RAIDATTENDANCE_WWS'	=> 'World of Logs',
		'ACP_RAIDATTENDANCE_WWS_EXPLAIN' => 'Synchronizes the raids with WWS.',
		'WWS_GUILD_ID'				=> 'WWS Guild ID',
		'WWS_GUILD_ID_EXPLAIN'		=> 'The Guild ID from http://www.worldoflogs.com, set to 0 if no wws account!',
		'RESYNC_WWS'				=> 'Resync with World of Logs',
		'RESYNC_WWS_EXPLAIN'		=> 'Synchronizes the attendance of raiders bases on world-of-logs.',
		'DATE_TIME_FORMAT'			=> '%a %d. %b %Y %H:%M:%S',
		'DELETE_WWS'				=> 'Delete selected raids',
		'SYNCED'					=> 'Synced',
		'WWS_ID'					=> 'WWS-ID',
		'RAID'						=> 'Raid-ID',
		'ERROR_DELETING_WWS'		=> 'Error deleting %d raids',
		'SUCCESS_DELETING_WWS'		=> 'Successfully deleted %d raids',
		'SAVED'						=> 'Changes have been saved',
		'BETA_TITLE'				=> 'This functionality is still in beta!',
		'BETA_BODY'					=> "Please use at your own risk (or don't use it at all)",
	)
);
?>
