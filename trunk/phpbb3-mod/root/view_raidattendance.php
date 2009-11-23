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
	global $user, $auth;
	$success = array();
	$tstamp = time();
	$raids = get_raiding_days($tstamp);
	$raider_db = new raider_db();
	$raiders = array();
	$raider_db->get_raider_list($raiders);
	if (isset($_POST['u_action']))
	{
		handle_action($raiders);
	}
	$attendance = get_attendance($raids);
	$rowno = 0;
	$names = array_keys($raiders);
	foreach ($raids as $raid)
	{
		$tm = strptime($raid, '%Y%m%d');
		$time = tm2time($tm);
		$template->assign_block_vars('raid_days', array(
			'DATE'			=> post_num(strftime('%e', $time)) . ' of ' . strftime('%B', $time),
			'DAY'			=> strftime('%A', $time),
		));
	}
	sort($names);
	$statusses = array(1=>'on', 2=>'off', 3=>'noshow', 0=>'nothing');
	foreach ($names as $name) {
		$raider = $raiders[$name];
		$template->assign_block_vars('raiders', array(
			'ROWNO'				=> $rowno+1,
			'ID'				=> $raider->id,
			'NAME'				=> $raider->name,
			'RANK'				=> $user->lang['RANK_' . $raider->rank],
			'LEVEL'				=> $raider->level,
			'CLASS'				=> $user->lang['CLASS_' . $raider->class],
			'USER'				=> $raider->user_id,
			'STATUS'			=> $raider->get_status(),
			'ROW_CLASS'			=> $rowno % 2 == 0 ? 'even' : 'uneven',
			'CHECKED'			=> $raider->is_checked() ? ' checked' : '',
			'CSS_CLASS'			=> 'class_' . $raider->class,
			'S_EDITABLE'		=> (($user->data['user_id'] == $raider->user_id) or ($auth->acl_get('m_'))) ? true : false,
		));
		foreach ($raids as $raid)
		{
			$status = $attendance[$raider->name][$raid];
			$template->assign_block_vars('raiders.raids', array(
				'RAID'			=> $raid,
				'STATUS'		=> $statusses[$status ? $status : 0],
				));
		}
		$rowno++;
	}
	$template->assign_vars(array(
		'RAIDATTENDANCE_TITLE' 	=> 'Correct forum',
		'S_RAIDATTENDANCE'		=> true,
		'S_SUCCESS'				=> sizeof($success) ? true : false,
		'SUCCESS_MSG'			=> implode('<br/>', $success),
		));
}
function handle_action($raiders)
{
	global $success;
	if (!isset($_POST['u_action']) or !isset($_POST['rid']) or !isset($_POST['raid']))
	{
		return;
	}
	// TODO: Additional checking
	$raider = get_raider_with_id($raiders, $_POST['rid']);
	$raid = $_POST['raid'];
	$action = $_POST['u_action'];
	if ($raider and $raid and $action) 
	{
		if ($action == 'signon')
		{
			$raider->signon($raid);
			$success[] = $raider->name . ' signed ON ' . $raid;
		}
		else if ($action == 'signoff')
		{
			$raider->signoff($raid);
			$success[] = $raider->name . ' signed OFF ' . $raid;
		}
	}
}
?>
