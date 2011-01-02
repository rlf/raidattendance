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
	'NUM_RAIDS'		=> 'Number of raids',
	'NUM_CANCELLED'	=> ' - Cancelled',
	'AVG_RAIDERS'	=> 'Average attendance',
	'FRACTION_FORMAT' => '%0.2f',
	'GRAPHS'		=> 'Graphs',
	'TANKS'			=> 'Tanks',
	'HEALERS'		=> 'Healers',
	'MELEE'			=> 'Melee',
	'RANGED'		=> 'Ranged',
	'CLASS_COLOR_1'	=> 'C79C6E',
	'CLASS_COLOR_2' => 'F58CBA',
	'CLASS_COLOR_3' => 'ABD473',
	'CLASS_COLOR_4' => 'FFF569',
	'CLASS_COLOR_5' => 'FFFFFF',
	'CLASS_COLOR_6' => 'C41F3B',
	'CLASS_COLOR_7' => '2459FF',
	'CLASS_COLOR_8' => '69CCF0',
	'CLASS_COLOR_9' => '9482C9',
	'CLASS_COLOR_11' => 'FF7D0A',
	)
);
?>
