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
		'RAID_NIGHTS'				=> 'Raid Nights',
		'RAID_NIGHT_MON'			=> 'Monday',
		'RAID_NIGHT_TUE'			=> 'Tuesday',
		'RAID_NIGHT_WED'			=> 'Wednesday',
		'RAID_NIGHT_THU'			=> 'Thursday',
		'RAID_NIGHT_FRI'			=> 'Friday',
		'RAID_NIGHT_SAT'			=> 'Saturday',
		'RAID_NIGHT_SUN'			=> 'Sunday',

		'RAIDERS'					=> 'Raiders',
		'LEVEL'						=> 'Level',
		'RANK'						=> 'Rank',
		'NAME'						=> 'Name',
		'CLASS'						=> 'Class',
		'ROLE'						=> 'Role',
		'ROLE_9'					=> 'Unassigned',
		'ROLE_1'					=> 'Tank',
		'ROLE_2'					=> 'Healer',
		'ROLE_3'					=> 'Ranged DPS',
		'ROLE_4'					=> 'Melee DPS',

		'CLASS_1'					=> 'Warrior',
		'CLASS_2'					=> 'Paladin',
		'CLASS_3'					=> 'Hunter',
		'CLASS_4'					=> 'Rogue',
		'CLASS_5'					=> 'Priest',
		'CLASS_6'					=> 'Death Knight',
		'CLASS_7'					=> 'Shaman',
		'CLASS_8'					=> 'Mage',
		'CLASS_9'					=> 'Warlock',
		'CLASS_11'					=> 'Druid',

		'RANK_0'					=> 'Guild Leader',
		'RANK_1'					=> 'Rank 1',
		'RANK_2'					=> 'Rank 2',
		'RANK_3'					=> 'Rank 3',
		'RANK_4'					=> 'Rank 4',
		'RANK_5'					=> 'Rank 5',
		'RANK_6'					=> 'Rank 6',
		'RANK_7'					=> 'Rank 7',
		'RANK_8'					=> 'Rank 8',
		'RANK_9'					=> 'Rank 9',

		'SAVE'						=> 'Save',

		'SIGNON'					=> 'Raider participated in raid',
		'SIGNOFF'					=> 'Sign off',
		'NOSHOW'					=> "Raider didn't show",
		'LATE'						=> 'Raider showed, but late',
		'SUBSTITUTE'				=> 'Raider showed, but was asked to sit out',
		'CANCELLED'					=> 'Raid was cancelled',
		'EMPTY'						=> 'Clear selection',
		'IS_RAIDER'					=> 'Expected to raid?',

		'DAY_MONTH'					=> '%1$s %2$s', // 1st Nov
		'DAY_NUMBER1'				=> '%dst', // 1st, 21st, 31st ...
		'DAY_NUMBER2'				=> '%dnd', // 2nd, 22nd, 32nd ...
		'DAY_NUMBER3'				=> '%drd', // 3rd, 23rd, 33rd ... 
		'DAY_NUMBER_OTHER'			=> '%dth', // 4th .. 19th, 24th-30th, etc.

		'STATUS_CHANGE_ON'			=> '%1$s marked %2$s as being online for raid %3$s',
		'STATUS_CHANGE_OFF'			=> '%1$s signed off for %2$s from raid %3$s',
		'STATUS_CHANGE_CLEAR'		=> '%1$s cleared status for %2$s on raid %3$s',
		'STATUS_CHANGE_NOSHOW'		=> '%1$s marked %2$s as being AWOL from raid %3$s',
		'STATUS_CHANGE_LATE'		=> '%1$s marked %2$s as being late for raid %3$s',
		'STATUS_CHANGE_SUBSTITUTE'	=> '%1$s marked %2$s as being a substitute for raid %3$s',
		'STATUS_CHANGE_CANCELLED'	=> '%1$s cancelled the raid on %2$s',	
		'STATUS_CHANGE_RAID_CLEAR'	=> '%1$s reactivated the raid on %2$s',	
		'STATIC_SIGNOFF'			=> "Sorry, but I'm never able to raid on %s",
		'STATIC_SIGNOFF_CLEAR'		=> 'Forget it, I might be able raid on %s anyway!',

		'DAY_LONG_Mon'				=> 'Mondays',
		'DAY_LONG_Tue'				=> 'Tuesdays',
		'DAY_LONG_Wed'				=> 'Wednesdays',
		'DAY_LONG_Thu'				=> 'Thursdays',
		'DAY_LONG_Fri'				=> 'Fridays',
		'DAY_LONG_Sat'				=> 'Saturdays',
		'DAY_LONG_Sun'				=> 'Sundays',

		'STATUS'					=> 'Status',
		'LEGEND_STATUS_ON'			=> 'Raider was available for raiding',
		'LEGEND_STATUS_OFF'			=> 'Raider signed off',
		'LEGEND_STATUS_NOSHOW'		=> 'Raider didn\'t show for raid',
		'LEGEND_STATUS_LATE'		=> 'Raider showed, but was late',
		'LEGEND_STATUS_SUBSTITUTE'	=> 'Raider was available, but had to sit out',
		'LEGEND_STATUS_CANCELLED'	=> 'Raid was cancelled',
		'ACTIONS'					=> 'Actions',
		'LEGEND_ACTION_ON'			=> 'Indicate raider was available for raid',
		'LEGEND_ACTION_OFF'			=> 'Sign-off from raid',
		'LEGEND_ACTION_NOSHOW'		=> 'Mark raider as being AWOL',
		'LEGEND_ACTION_LATE'		=> 'Mark raider as being late for raid',
		'LEGEND_ACTION_SUBSTITUTE'	=> 'Mark raider as being a substitute for raid',
		'LEGEND_ACTION_CANCELLED'	=> 'Cancel the raid',
		'LEGEND_ACTION_CLEAR'		=> 'Undo previous sign-off',
		'ADMIN'						=> '[ Admin ]',
		'NORMAL'					=> '[ Normal ]',
		'FAQ'						=> '[ FAQ ]',

		'ADDON'						=> 'WoW Addon',
		'ADDON_CODE'				=> 'Copy and paste the following code into the WoW Addon Raid Attendancy (/att)',

		'EXPORT_AS'					=> 'Export as ',
		'DUMP_HTML'					=> '[HTML]',
		'DUMP_CSV'					=> '[CSV]',
		'DUMP_XML'					=> '[XML]',

		'SUMMARY'					=> 'Attendancy',
		'SUMMARY_LINK'				=> 'http://chart.apis.google.com/chart?cht=bhs&chs=115x24&chd=t:%1$01.0f|%2$01.0f|%3$01.0f|%4$01.0f|%5$01.0f&chco=086800,003c8f,900e03,c5a701,008d89&chbh=a&chf=bg,s,00000000',
		'SUMMARY_DETAIL_LINK'		=> 'http://chart.apis.google.com/chart?cht=p&chs=800x375&chd=t:%1$01.0f,%2$01.0f,%3$01.0f,%4$01.0f,%5$01.0f&chco=086800|003c8f|900e03|c5a701|008d89&chl=%6$s|%7$s|%8$s|%9$s|%10$s&chma=120,120,0,0|0,0',
		'SUMMARY_TOOLTIP'			=> 'Availability: %1$01.1f%% &#013;Attendance: %2$01.1f%%',
		'MONTHS'					=> 'months',

		'NEXT_WEEK'					=> '<b style="font-size:14pt">&gt;&gt;</b>',
		'PREV_WEEK'					=> '<b style="font-size:14pt">&lt;&lt;</b>',
		'NEXT_WEEK_TOOLTIP'			=> "See further into the future! Almost like fortune telling",
		'PREV_WEEK_TOOLTIP'			=> 'See into the past',
		'PROVIDE_A_COMMENT'			=> "I'm sorry, but that reason is simply not good enough! Please provide another",
		'SIGNOFF_COMMENT'			=> 'So, what RL issue is keeping you away from raid this time?',
		'NUM_DEFAULT_COMMENTS'		=> 6,
		'DEFAULT_COMMENT_1'			=> 'Type reason for signing off here',
		'DEFAULT_COMMENT_2'			=> "My first cousins baby-brothers mothers husbands sister is having a stomach-ache",
		'DEFAULT_COMMENT_3'			=> "The weather is simply too nice outside",
		'DEFAULT_COMMENT_4'			=> "Sry I cant raid. I am in the middle of huge discussion with God and he wont let me break it off",
		'DEFAULT_COMMENT_5'			=> "My house is on fire, but i just might be a little late",
		'DEFAULT_COMMENT_6'			=> "My mom won't let me!",

		'VIEW_STATS'				=> 'Statistics',

		'VIEW_RAID_LOGS'			=> 'History',
		
	)
);
?>
