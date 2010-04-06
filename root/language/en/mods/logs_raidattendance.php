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

//
// Common Language Resources for Raid Attendance
//
$lang = array_merge($lang, array(
	'VIEW_RAID_LOGS'	=> 'History',
	'RAID_LOGS_TITLE'	=> 'Raidattendance History',
	'ID'				=> 'ID',
	'USER'				=> 'User',
	'RAIDER'			=> 'Raider',
	'TIME'				=> 'Timestamp',
	'ACTION'			=> 'Action',
	'TIMESTAMP_FORMAT'	=> '%Y-%m-%d %H:%M',
	'RAIDERS_ALL'		=> 'All',
	'LOG_SIGNOFF'		=> 'Signed of from raid %s',
	'LOG_SIGNON'		=> 'Signed on to raid %s',
	'LOG_CLEAR'			=> 'Cleared attendancy for raid %s',
	'LOG_NOSHOW'		=> 'Was AWOL for raid %s',
	'LOG_LATE'			=> 'Was late for raid %s',
	'RAID_UNKNOWN'		=> 'Unknown',
	'FILTER'			=> 'Filter',
	)
);
?>
