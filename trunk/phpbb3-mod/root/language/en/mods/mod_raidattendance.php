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
		'CLASS_11'					=> 'Druid',

		'RANK_0'					=> 'Guild Leader',
		'RANK_1'					=> 'Officer',
		'RANK_2'					=> 'Officer Alt',
		'RANK_3'					=> 'Raider',
		'RANK_4'					=> 'Member',
		'RANK_5'					=> 'Member Alt',
		'RANK_6'					=> 'Initiate',

		'SAVE'						=> 'Save',

		'SIGNON'					=> 'Sign on',
		'SIGNOFF'					=> 'Sign off',
		'NOSHOW'					=> "Raider didn't show",
		'EMPTY'						=> 'Clear selection',
	)
);
?>
