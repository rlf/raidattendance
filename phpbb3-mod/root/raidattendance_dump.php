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
if ($user->lang['OUTPUT_ROW_' . $output])
{
	$output_row = $user->lang['OUTPUT_ROW_' . $output];
}
if ($user->lang['OUTPUT_CELL_' . $output])
{
	$output_cell = $user->lang['OUTPUT_CELL_' . $output];
}

$attendance = get_attendance_for_time($start, $end, $raid_id);

$is_first = true;
$first_line = '';
foreach ($attendance as $raider => $nights)
{
    $line = '';
	if ($is_first) 
    {
		$first_line = sprintf($output_cell, 'Name');
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
            $first_line = $first_line . sprintf($output_cell, $night);
        }
        $line = $line . sprintf($output_cell, $user->lang('STATUS_' . $status ));
    }
	// TODO: add the ratios...
	if ($is_first)
	{
		printf($output_row, $first_line);
	}
    printf($output_row, $line);
    $is_first = false;
}
if ($user->lang['OUTPUT_FOOTER_' . $output])
{
	printf('%s', $user->lang['OUTPUT_FOOTER_' . $output]);
}

?>
