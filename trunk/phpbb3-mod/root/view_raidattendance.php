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
	global $user, $auth, $config;
	$raid_id = request_var('raid_id', 0);
	$sort_order = request_var('sort_order', '1');
	if ($raid_id == 0) 
	{
		$raid_id = get_default_raid_id();
	}
	$user->add_lang(array('mods/mod_raidattendance', 'mods/info_acp_raidattendance', 'mods/logs_raidattendance'));
	$success = array();
	$error = array();
	$tstamp = request_var('tstamp', 0);
	$tstamp = $tstamp > 0 ? $tstamp : time();
	$now = strftime('%H:%M', time());
	//$error[] = 'time: ' . $now . ', timezone : ' . $user->data['user_timezone'];
	$today = strftime('%Y%m%d', time());
	$raid_time =  $config['raidattendance_raid_time'];
	
	$raids = get_raiding_days($tstamp, $raid_id);
	$raider_db = new raider_db();
	$raiders = array();
	$raider_db->get_raider_list($raiders, $raid_id, $sort_order);
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
	$statusses = array(STATUS_ON=>'on', STATUS_OFF=>'off', STATUS_NOSHOW=>'noshow', STATUS_LATE=>'late', STATUS_SUBSTITUTE=>'substitute', 0=>'future', -1=>'past', -2=>'unset');
	$sums = array();
	$raidData = array(); // data used in the addon...
	$armory_link = $config['raidattendance_armory_link'];
	$realm = $config['raidattendance_realm_name'];
	$url_base = $armory_link . '/character-sheet.xml?r=' . urlencode($realm) . '&cn=';
	foreach ($raiders as $name => $raider) 
	{
		$template->assign_block_vars('raiders', array(
			'ROWNO'				=> $rowno+1,
			'ID'				=> $raider->id,
			'NAME'				=> $raider->name,
			'RANK'				=> $raider->get_rank_name(),
			'ROLE'				=> $raider->get_role_name(),
			'LEVEL'				=> $raider->level,
			'CLASS'				=> $user->lang['CLASS_' . $raider->class],
			'USER'				=> $raider->user_id,
			'STATUS'			=> $raider->get_status(),
			'ROW_CLASS'			=> $rowno % 2 == 0 ? 'even' : 'uneven',
			'CHECKED'			=> $raider->is_checked() ? ' checked' : '',
			'CSS_CLASS'			=> 'class_' . $raider->class,
			'S_EDITABLE'		=> ($user->data['user_id'] == $raider->user_id or ($raider->user_id == 0 and $user->data['username'] == $raider->name)),
			'ARMORY_LINK'		=> $url_base . urlencode($raider->name),
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
			$future = $raid > $today || (($raid == $today) && $now <= $raid_time);
			$status = $attendance[$raider->name][$raid];
			$status = isset($status) ? $status : ($future ? 0 : -1);
			if (!is_array($sums[$raid])) 
			{
				$sums[$raid] = array(STATUS_OFF=>0, STATUS_NOSHOW=>0);
			}
			$sums[$raid][$status] = $sums[$raid][$status] + 1;
			$day_name = get_raiding_day_name($raid);
			$is_static = $static_attendance[$raider->name][$day_name] == 2;
			$template->assign_block_vars('raiders.raids', array(
				'RAID'			=> $raid,
				'STATUS'		=> $statusses[$status],
				'S_FUTURE'		=> $future,
				'S_EDITABLE'	=> ($user->data['user_id'] == $raider->user_id or ($raider->user_id == 0 and $user->data['username'] == $raider->name)) and $future ? true : false,
				'S_STATIC'		=> $is_static ? 1 : 0,
			));
			if (!is_array($raidData[$raid]))
			{
				$raidData[$raid] = array('raid'=>$raid,'raid_time'=>$raid_time,'raiders'=>array());
			}
			$raidData[$raid]['raiders'][] = array(
				'name'=>$raider->name, 
				'class'=>$raider->class, 
				'role'=>$raider->role, 
				'rank'=>$raider->rank, 
				'status'=>$status
			);
		}
		$rowno++;
	}
	$num_raiders = sizeof($raiders);
	foreach ($raids as $raid)
	{
		$tm = strptime($raid, '%Y%m%d');
		$time = tm2time($tm);
		$future = $raid > $today || (($raid == $today) && $now <= $raid_time);
		$date = sprintf($user->lang['DAY_MONTH'], post_num(strftime('%e', $time)), strftime('%b', $time));
		$template->assign_block_vars('raid_days', array(
			'DATE'			=> $date,
			'DAY'			=> strftime('%a', $time),
			'SUM_NOSHOW'	=> $sums[$raid][STATUS_NOSHOW],
			'SUM_OFF'		=> $sums[$raid][STATUS_OFF],
			'SUM_ON'		=> $num_raiders - $sums[$raid][STATUS_NOSHOW] - $sums[$raid][STATUS_OFF],
			'S_FUTURE'		=> $future,
			'RAID_DATA'		=> get_raid_data_as_string($raidData[$raid]),
		));
	}
	$date_array = getdate($tstamp);
	$last_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-7, $date_array['year']);
	$next_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']+7, $date_array['year']);

	$mode = request_var('mode', 'normal');
	$is_admin = $auth->acl_get('m_') or $auth->acl_get('a_');
	$is_moderator = $is_admin && $mode == 'admin';
	$num_cols = 5 + sizeof($raids);
	$col_sort = explode(',', $sort_order);

	$dir_sort = array(
		1 => ($col_sort[0] == 1 ? ' v' : ($col_sort[0] == -1 ? ' ^' : '')),
		2 => ($col_sort[0] == 2 ? ' v' : ($col_sort[0] == -2 ? ' ^' : '')),
		3 => ($col_sort[0] == 3 ? ' v' : ($col_sort[0] == -3 ? ' ^' : '')),
		4 => ($col_sort[0] == 4 ? ' v' : ($col_sort[0] == -4 ? ' ^' : '')),
		);
	$template->assign_vars(array(
		'NUM_COLS'				=> $num_cols,
		'NUM_COLS_LEGEND'		=> $num_cols - 2,
		'NUM_COLS_ACTION'		=> 2,
		'S_RAIDATTENDANCE'		=> true,
		'S_SUCCESS'				=> sizeof($success) ? true : false,
		'SUCCESS_MSG'			=> implode('<br/>', $success),
		'S_ERROR'				=> sizeof($error) ? true : false,
		'ERROR_MSG'				=> implode('<br/>', $error),
		'S_MODERATOR'			=> $is_moderator,
		'TSTAMP_NEXT'			=> $next_week,
		'TSTAMP_PREV'			=> $last_week,
		'MODE'					=> $mode,
		'S_ADMIN'				=> $is_admin,
		'MOD_VERSION'			=> $config['raidattendance_version'],
		'RAID_ID'				=> $raid_id,
		'SORT_ORDER'			=> $sort_order,
		'DIR_NAME'				=> $dir_sort[1],
		'SORT_NAME'				=> ($dir_sort[1] == ' v' ? '-1,2,3' : '1,2,3'),
		'DIR_ROLE'				=> $dir_sort[2],
		'SORT_ROLE'				=> ($dir_sort[2] == ' v' ? '-2,4,3,1' : '2,4,3,1'),
		'DIR_RANK'				=> $dir_sort[3],
		'SORT_RANK'				=> ($dir_sort[3] == ' v' ? '-3,2,4,1' : '3,2,4,1'),
		'DIR_CLASS'				=> $dir_sort[4],
		'SORT_CLASS'			=> ($dir_sort[4] == ' v' ? '-4,2,1' : '4,2,1'),
	));

	$raids = get_raids();
	foreach ($raids as $raid)
	{
		$template->assign_block_vars('raids', array(
			'ID'				=> $raid['id'],
			'SELECTED'			=> $raid['id'] == $raid_id,
			'NAME'				=> $raid['name'],
		));
	}
}
function get_array_as_string($ary)
{
	if (!is_array($ary)) {
		return '{}';
	}
	$buffer = '{';
	foreach ($ary as $key => $value)
	{
		if (!is_numeric($key)) 
		{
			$buffer = $buffer . $key . '=';
		}
		if (is_array($value)) 
		{
			$buffer = $buffer . get_array_as_string($value);
		} 
		else
		{
			$buffer = $buffer . $value;
		}
		$buffer = $buffer . ',';
	}
	$buffer = $buffer . '}';
	return $buffer;
}
function get_raid_data_as_string($raidData)
{
	global $user;
	$buffer = get_array_as_string($raidData);
	$crc = crc32($user->data['username'] . $raidData['raid'] . $buffer);
	return dechex($crc) . ';' . $buffer;
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
	else if ($action == '%')
	{
		$raider->late($raid);
	}
	else if ($action == 'z')
	{
		$raider->substitute($raid);
	}

	$lang_array = array(
		'+' => 'STATUS_CHANGE_ON', 
		'-' => 'STATUS_CHANGE_OFF', 
		'x' => 'STATUS_CHANGE_CLEAR', 
		'!' => 'STATUS_CHANGE_NOSHOW', 
		'%'	=> 'STATUS_CHANGE_LATE',
		'z' => 'STATUS_CHANGE_SUBSTITUTE',
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
