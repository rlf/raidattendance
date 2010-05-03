<?php
/**
*
* @author Rapal
* @package raidattendance
* @copyright (c) 2008 TA
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

define('IN_PHPBB', true);
// Specify the path to you phpBB3 installation directory.
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
// The common.php file is required.
include($phpbb_root_path . 'common.' . $phpEx);
 
// Start session management
$user->session_begin();
$auth->acl($user->data);
 
$user->setup();
$user->add_lang(array('mods/dump_raidattendance'));

// TODO: Add check for login...
global $forum_id, $phpbb_root_path, $phpEx, $template;

include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx); 

$start=request_var('start', '');
$end=request_var('end', '');
$raid_id=request_var('raid_id', 0);
$output=request_var('output', 'HTML');
if ($user->lang['OUTPUT_MIME_' . $output])
{
	header('Content-Type: ' . $user->lang['OUTPUT_MIME_' . $output]);
}

if ($user->lang['OUTPUT_HEADER_' . $output])
{
	printf('%s', $user->lang['OUTPUT_HEADER_' . $output]);
}
$output_row = '%s';
$output_cell = '%s';
$output_summary = '%02$1f/%02$2f/%02$3f/%02$4f/%02$5f';
$output_colheader_cancelled = '%s [%s]';
if ($user->lang['OUTPUT_COLHEADER_CANCELLED_' . $output])
{
	$output_colheader_cancelled = $user->lang['OUTPUT_COLHEADER_CANCELLED_' . $output];
}
if ($user->lang['OUTPUT_ROW_' . $output])
{
	$output_row = $user->lang['OUTPUT_ROW_' . $output];
}
if ($user->lang['OUTPUT_CELL_' . $output])
{
	$output_cell = $user->lang['OUTPUT_CELL_' . $output];
}
if ($user->lang['OUTPUT_SUMMARY_' . $output])
{
	$output_summary = $user->lang['OUTPUT_SUMMARY_' . $output];
}

$attendance = get_attendance_for_time($start, $end, $raid_id);

$is_first = true;
$first_line = '';
foreach ($attendance as $raider => $nights)
{
	if ($raider == '__RAID__')
	{
		continue;
	}
    $line = '';
	if ($is_first) 
    {
		$first_line = sprintf($output_cell, $user->lang['NAME']);
	}
    $line = $line . sprintf($output_cell, $raider);
	ksort(&$nights);
	foreach ($nights as $night => $status)
	{
		if (!is_numeric($night))
		{
			continue; // Skip columns that are not raids...
		}
        if ($is_first)
		{
			$header = $night;
			if ($attendance['__RAID__'][$night] == STATUS_CANCELLED) 
			{
				$header = sprintf($output_colheader_cancelled, $night, $user->lang['CANCELLED_' . $output]);
			}
            $first_line = $first_line . sprintf($output_cell, $header);
        }
        $line = $line . sprintf($output_cell, $user->lang('STATUS_' . $status ));
    }
	// TODO: add the ratios...
	if ($is_first)
	{
		$first_line = $first_line . sprintf($output_cell, $user->lang['SUMMARY']);
		printf($output_row, $first_line);
	}
	$sum_0 = $nights['summary_0'] or 0;
	$sum_1 = $nights['summary_1'] or 0;
	$sum_2 = $nights['summary_2'] or 0;
	$sum_3 = $nights['summary_3'] or 0;
	$sum_4 = $nights['summary_4'] or 0;
	$sum_5 = $nights['summary_5'] or 0;
	$sum_6 = $nights['summary_6'] or 0;
	$line = $line . sprintf($output_summary, $sum_1, $sum_2, $sum_3, $sum_4, $sum_5, $sum_6);
    printf($output_row, $line);
    $is_first = false;
}
if ($user->lang['OUTPUT_FOOTER_' . $output])
{
	printf('%s', $user->lang['OUTPUT_FOOTER_' . $output]);
}

?>
