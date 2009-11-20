<?php
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
		'acl_a_raidattendance'		=> array('lang' => 'Can configure Raid Attendance', 'cat' => 'misc'),
	//
	// ACP - Sync
	//
		'ACP_RAIDATTENDANCE_SYNC'	=> 'Synchronise with wowarmory',
		'ACP_RAIDATTENDANCE_SYNC_EXPLAIN' => 'Synchronises the list of raiders with wow-armory.',
		'RAIDERS'					=> 'Raiders',
		'LEVEL'						=> 'Level',
		'RANK'						=> 'Rank',
		'CLASS'						=> 'Class',

		'CLASS_1'					=> 'Warrior',
		'CLASS_2'					=> 'Paladin',
		'CLASS_3'					=> 'Hunter',
		'CLASS_4'					=> 'Rogue',
		'CLASS_5'					=> 'Priest',
		'CLASS_6'					=> 'Death Knight',
		'CLASS_7'					=> 'Shaman',
		'CLASS_8'					=> 'Mage',
		'CLASS_9'					=> 'Warlock',
		'CLASS_11'					=> 'Death Knight',
		'RANK_1'					=> 'Officer',
		'RANK_3'					=> 'Raider',
	)
);
?>
