<?php
/**
*
* @author Rapal
* @package raidattendance
* @copyright (c) 2008 TA
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  

global $forum_id, $phpbb_root_path, $phpEx, $template;

include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

if (is_raidattendance_forum($forum_id)) 
{
	global $user, $auth;
	$user->add_lang(array('mods/mod_raidattendance', 'mods/info_acp_raidattendance'));
	$success = array();
	$error = array();
	$tstamp = request_var('tstamp', 0);
	$tstamp = $tstamp > 0 ? $tstamp : time();
	$today = strftime('%Y%m%d', time());
	$raids = get_raiding_days($tstamp);
	$raider_db = new raider_db();
	$raiders = array();
	$raider_db->get_raider_list($raiders);
	$action = request_var('u_action', '');
	if ($action)
	{
		handle_action($action, $raiders);
	}
	$day_names = get_raiding_day_names($raids);
	$attendance = get_attendance($raids);
	$static_attendance = get_static_attendance($raids);
	add_static_attendance($raids, $attendance, $static_attendance);

	$rowno = 0;
	$names = array_keys($raiders);
	foreach ($raids as $raid)
	{
		$tm = strptime($raid, '%Y%m%d');
		$time = tm2time($tm);
		$template->assign_block_vars('raid_days', array(
			'DATE'			=> sprintf($user->lang['DAY_MONTH'], post_num(strftime('%e', $time)), strftime('%B', $time)),
			'DAY'			=> strftime('%A', $time),
		));
	}
	$statusses = array(1=>'on', 2=>'off', 3=>'noshow', 0=>'future', -1=>'past', -2=>'unset');
	foreach ($raiders as $name => $raider) 
	{
		$template->assign_block_vars('raiders', array(
			'ROWNO'				=> $rowno+1,
			'ID'				=> $raider->id,
			'NAME'				=> $raider->name,
			'RANK'				=> $raider->get_rank_name(),
			'LEVEL'				=> $raider->level,
			'CLASS'				=> $user->lang['CLASS_' . $raider->class],
			'USER'				=> $raider->user_id,
			'STATUS'			=> $raider->get_status(),
			'ROW_CLASS'			=> $rowno % 2 == 0 ? 'even' : 'uneven',
			'CHECKED'			=> $raider->is_checked() ? ' checked' : '',
			'CSS_CLASS'			=> 'class_' . $raider->class,
			'S_EDITABLE'		=> ($user->data['user_id'] == $raider->user_id or ($raider->user_id == 0 and $user->data['username'] == $raider->name)),
		));
		foreach ($day_names as $day)
		{
			$status = $static_attendance[$raider->name][$day];
			$status = $status ? $status : -2;
			$tooltip_key = array(2=>'STATIC_SIGNOFF_CLEAR', -2=>'STATIC_SIGNOFF');
			$template->assign_block_vars('raiders.days', array(
				'DAY'			=> isset($user->lang['DAY_' . $day]) ? $user->lang['DAY_' . $day] : $day,
				'DAY_KEY'		=> $day,
				'STATUS'		=> $statusses[$status],
				'TOOLTIP'		=> sprintf($user->lang[$tooltip_key[$status]], $user->lang['DAY_LONG_' . $day]),
				));
		}
		foreach ($raids as $raid)
		{
			$future = $raid >= $today;
			$status = $attendance[$raider->name][$raid];
			$day_name = get_raiding_day_name($raid);
			$is_static = $static_attendance[$raider->name][$day_name] == 2;
			$template->assign_block_vars('raiders.raids', array(
				'RAID'			=> $raid,
				'STATUS'		=> $statusses[$status ? $status : ($future ? 0 : -1)],
				'S_FUTURE'		=> $future ? '1' : '0',
				'S_EDITABLE'	=> ($user->data['user_id'] == $raider->user_id or ($raider->user_id == 0 and $user->data['username'] == $raider->name)) and $future ? true : false,
				'S_STATIC'		=> $is_static ? 1 : 0,
				));
		}
		$rowno++;
	}
	$date_array = getdate($tstamp);
	$last_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-7, $date_array['year']);
	$next_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']+7, $date_array['year']);
	$template->assign_vars(array(
		'RAIDATTENDANCE_TITLE' 	=> 'Correct forum',
		'S_RAIDATTENDANCE'		=> true,
		'S_SUCCESS'				=> sizeof($success) ? true : false,
		'SUCCESS_MSG'			=> implode('<br/>', $success),
		'S_ERROR'				=> sizeof($error) ? true : false,
		'ERROR_MSG'				=> implode('<br/>', $error),
		'S_MODERATOR'			=> $auth->acl_get('m_') or $auth->acl_get('a_'),
		'TSTAMP_NEXT'			=> $next_week,
		'TSTAMP_PREV'			=> $last_week,
		));
}
function handle_action($action, $raiders)
{
	global $success, $user, $error;
	$rid = request_var('rid', 0);
	$raid = request_var('raid', '');
	if (!$action or !$rid or !$raid)
	{
		return;
	}
	// TODO: Additional checking
	$raider = get_raider_with_id($raiders, $rid);
	if (!$raider) 
	{
		return;
	}
	$day = $raid;
	if (strlen($raid) == 8)
	{
		$tm = strptime($raid, '%Y%m%d');
		$time = tm2time($tm);
		$day = sprintf($user->lang['DAY_MONTH'], post_num(strftime('%e', $time)), strftime('%B', $time));
	}
	$username = $user->data['username'];

	if ($action == '+')
	{
		$raider->signon($raid);
	}
	else if ($action == '-')
	{
		$raider->signoff($raid);
	}
	else if ($action == 'x')
	{
		$raider->clear_attendance($raid);
	}
	else if ($action == '!')
	{
		$raider->noshow($raid);
	}

	$lang_array = array(
		'+' => 'STATUS_CHANGE_ON', 
		'-' => 'STATUS_CHANGE_OFF', 
		'x' => 'STATUS_CHANGE_CLEAR', 
		'!' => 'STATUS_CHANGE_NOSHOW', 
	);
	$lang_key = $lang_array[$action];
	if ($username && $raider->name && $day && $user->lang[$lang_key]) 
	{
		$success[] = sprintf($user->lang[$lang_key], $username, $raider->name, $day);
	}
	else 
	{
		$error[] = "Error! $username, $raider->name, $day, $lang_key, " . $user->lang[$lang_key];
	}
}
?>