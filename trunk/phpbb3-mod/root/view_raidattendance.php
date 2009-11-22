<?php

/**
*
* @author Rapal
* @package raidattendance
* @copyright (c) 2008 TA
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

global $forum_id, $phpbb_root_path, $phpEx, $template;

include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

if (is_raidattendance_forum($forum_id)) 
{
	$template->assign_vars(array(
		'RAIDATTENDANCE_TITLE' => 'Correct forum',
		'S_RAIDATTENDANCE'	=> true,
		));
}
?>
