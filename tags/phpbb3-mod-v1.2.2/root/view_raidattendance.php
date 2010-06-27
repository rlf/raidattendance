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
	$sort_order = request_var('sort_order', '2,4,3,1');
	$col_sort = explode(',', $sort_order);

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
	$attendance = get_attendance($raids, $raid_id);
	$static_attendance = get_static_attendance($raids);
	add_static_attendance($raids, $attendance, $static_attendance);

	$rowno = 0;
	$statusses = array(STATUS_ON=>'on', STATUS_OFF=>'off', STATUS_NOSHOW=>'noshow', STATUS_LATE=>'late', STATUS_SUBSTITUTE=>'substitute', 0=>'future', -1=>'past', -2=>'unset');
	$raid_sums = array();
	$raidData = array(); // data used in the addon...
	$armory_link = $config['raidattendance_armory_link'];
	$realm = $config['raidattendance_realm_name'];
	$url_base = $armory_link . '/character-sheet.xml?r=' . urlencode($realm) . '&cn=';

	$date_array = getdate($tstamp);
	$last_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-7, $date_array['year']);
	$next_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']+7, $date_array['year']);
	$dump_months = request_var('dump_months', 1);
	$dump_start = strftime('%Y%m%d', mktime(0, 0, 0, $date_array['mon']-$dump_months, $date_array['mday'], $date_array['year']));
	$dump_end = strftime('%Y%m%d', $tstamp);

	// For now... just for the summary... should be merged in the future...
	$summary_attendance = get_attendance_for_time($dump_start, $dump_end, $raid_id);

	// Sort, if were sorting on availability (col 7)
	if ($col_sort[0] == 7 or $col_sort[0] == -7)
	{
		$sort_obj = new summary_sort($summary_attendance, $col_sort[0]);
		uasort($raiders, array($sort_obj, 'sort'));
	}

	foreach ($raiders as $name => $raider) 
	{
		$sums = array(
			0,
			$summary_attendance[$raider->name]['summary_1'],
			$summary_attendance[$raider->name]['summary_2'],
			$summary_attendance[$raider->name]['summary_3'],
			$summary_attendance[$raider->name]['summary_4'],
			$summary_attendance[$raider->name]['summary_5'],
			$summary_attendance[$raider->name]['summary_6'],
		);
		$sum_attendance = $sums[1]+$sums[5];
		if ($sum_attendance > 0)
		{
			$sum_attendance = 100*$sums[1]/$sum_attendance;
		}
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
			'SUMMARY_LINK'		=> sprintf($user->lang['SUMMARY_LINK'], $sums[1], $sums[2], $sums[3], $sums[4], $sums[5]),
			'SUMMARY_TOOLTIP'	=> sprintf($user->lang['SUMMARY_TOOLTIP'], $sums[1]+$sums[5], $sum_attendance),
			'SUMMARY_DETAIL_LINK' => sprintf($user->lang['SUMMARY_DETAIL_LINK'], 
				$sums[1], $sums[2], $sums[3], $sums[4], $sums[5],
				$user->lang['LEGEND_STATUS_ON'], $user->lang['LEGEND_STATUS_OFF'], $user->lang['LEGEND_STATUS_NOSHOW'], $user->lang['LEGEND_STATUS_LATE'], $user->lang['LEGEND_STATUS_SUBSTITUTE']),
		));
		$num_days = floor(sizeof($raids) / 3);
		$raid_day_number = 0;
		foreach ($day_names as $day)
		{
			$status = $static_attendance[$raider->name][$day]['status'];
			$status = $status ? $status : -2;
			$tooltip_key = array(2=>'STATIC_SIGNOFF_CLEAR', -2=>'STATIC_SIGNOFF');
			$template->assign_block_vars('raiders.days', array(
				'DAY'			=> isset($user->lang['DAY_' . $day]) ? $user->lang['DAY_' . $day] : $day,
				'DAY_KEY'		=> $day,
				'STATUS'		=> $statusses[$status],
				'TOOLTIP'		=> sprintf($user->lang[$tooltip_key[$status]], $user->lang['DAY_LONG_' . $day]),
				'S_FIRST_DAY_IN_WEEK' => (($raid_day_number % $num_days) == 0),
				));
			$raid_day_number++;
		}
		$raid_day_number = 0;
		$last_future = false;
		foreach ($raids as $raid)
		{
			$future = $raid > $today || (($raid == $today) && $now <= $raid_time);
			$status = $attendance[$raider->name][$raid]['status'];
			$status = isset($status) ? $status : ($future ? 0 : -1);
			if (!is_array($raid_sums[$raid])) 
			{
				$raid_sums[$raid] = array(STATUS_OFF=>0, STATUS_NOSHOW=>0);
			}
			$raid_sums[$raid][$status] = $raid_sums[$raid][$status] + 1;
			$day_name = get_raiding_day_name($raid);
			$template->assign_block_vars('raiders.raids', array(
				'RAID'			=> $raid,
				'STATUS'		=> $statusses[$status],
				'COMMENT'		=> $attendance[$raider->name][$raid]['comment'],
				'S_FUTURE'		=> $future,
				'S_EDITABLE'	=> ($user->data['user_id'] == $raider->user_id or ($raider->user_id == 0 and $user->data['username'] == $raider->name)) and $future ? true : false,
				'S_CANCELLED'	=> $attendance['__RAID__'][$raid]['status'] == STATUS_CANCELLED ? 1 : 0,
				'S_FIRST_DAY_IN_WEEK' => (($raid_day_number % $num_days) == 0),
				'S_NEXT_RAID'	=> $last_future != $future,
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
			$raid_day_number++;
			$last_future = $future;
		}
		$rowno++;
	}
	$num_raiders = sizeof($raiders);
	$raid_day_number = 0;
	$wol_baseurl_calendar = false;
	$wol_baseurl_report = false;
	if (!$config['raidattendance_wws_guild_id'])
	{
		$wol_baseurl_calendar = 'http://www.worldoflogs.com/guilds/' . $config['raidattendance_wws_guild_id'] . '/calendar/';
	}
	$last_future = false;
	foreach ($raids as $raid)
	{
		$tm = strptime($raid, '%Y%m%d');
		$time = tm2time($tm);
		$future = $raid > $today || (($raid == $today) && $now <= $raid_time);
		$date = sprintf($user->lang['DAY_MONTH'], post_num(strftime('%e', $time)), strftime('%b', $time));
		
		$template->assign_block_vars('raid_days', array(
			'RAID'			=> $raid,
			'DATE'			=> $date,
			'DAY'			=> strftime('%a', $time),
			'SUM_NOSHOW'	=> $raid_sums[$raid][STATUS_NOSHOW],
			'SUM_OFF'		=> $raid_sums[$raid][STATUS_OFF],
			'SUM_ON'		=> $num_raiders - $raid_sums[$raid][STATUS_NOSHOW] - $raid_sums[$raid][STATUS_OFF],
			'S_FUTURE'		=> $future,
			'RAID_DATA'		=> get_raid_data_as_string($raidData[$raid]),
			'S_CANCELLED'	=> $attendance['__RAID__'][$raid]['status'] == STATUS_CANCELLED ? 1 : 0,
			'S_FIRST_DAY_IN_WEEK' => (($raid_day_number % $num_days) == 0),
			'S_NEXT_RAID'	=> $last_future != $future,
		));
		$raid_day_number++;
		$last_future = $future;
	}

	$mode = request_var('mode', 'normal');
	$is_admin = $auth->acl_get('m_') or $auth->acl_get('a_');
	$is_moderator = $is_admin && $mode == 'admin';
	$num_cols = 7 + sizeof($raids);

	$dir_sort = array(
		1 => ($col_sort[0] == 1 ? ' v' : ($col_sort[0] == -1 ? ' ^' : '')),
		2 => ($col_sort[0] == 2 ? ' v' : ($col_sort[0] == -2 ? ' ^' : '')),
		3 => ($col_sort[0] == 3 ? ' v' : ($col_sort[0] == -3 ? ' ^' : '')),
		4 => ($col_sort[0] == 4 ? ' v' : ($col_sort[0] == -4 ? ' ^' : '')),
		7 => ($col_sort[0] == 7 ? ' v' : ($col_sort[0] == -7 ? ' ^' : '')),
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
		'DIR_AVAILABILITY'		=> $dir_sort[7],
		'SORT_AVAILABILITY'		=> ($dir_sort[7] == ' v' ? '-7,1,2,3' : '7,1,2,3'),
		'DUMP_START'			=> $dump_start,
		'DUMP_END'				=> $dump_end,
		'DUMP_MONTHS'			=> $dump_months,
		'DEFAULT_COMMENT'		=> $user->lang['DEFAULT_COMMENT_' . rand(1, $user->lang['NUM_DEFAULT_COMMENTS'])],
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
/**
 * Enables sorting on the availability column
 **/
class summary_sort 
{
	/**
	 * Argument 1 - Array containing raider-name => array('summary_n') columns.
	 * Argument 2 - Direction, if negative, it's ascending, otherwise it's descending.
	 **/
	function summary_sort($sum_array, $direction)
	{
		$this->sum_array = $sum_array;
		$this->direction = $direction;
	}
	function sort($a, $b)
	{
		$sum_array_a = $this->sum_array[$a->name];
		$sum_array_b = $this->sum_array[$b->name];
		// ON + SUBSTITUTE
		$sum_a = $sum_array_a['summary_1'] + $sum_array_a['summary_5'];
		$sum_b = $sum_array_b['summary_1'] + $sum_array_b['summary_5'];
		if ($this->direction > 0) 
		{
			return $sum_b - $sum_a;
		}
		return $sum_a - $sum_b;
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
		if (strlen($buffer) > 1) 
		{
			$buffer = $buffer . ',';
		}
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
	$raid_id = request_var('raid_id', 0);
	$comment = request_var('comment', '');

	if (!$action or !$raid)
	{	
		return;
	}
	$raider = false;
	if ($rid != 0 && $raid && $action) 
	{
		$raider = get_raider_with_id($raiders, $rid);
		if (!$raider && !($rid == 0 && ($action == 'c' || $action == 'x')))
		{
			return;
		}
	}
	else if (!$raid_id || !$action || !$raid) 
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
		$raider->signoff($raid, $comment);
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
	else if ($action == 'cr')
	{
		add_raid_history($raid_id, array('CANCELLED', $raid));
		set_raid_status($raid_id, $raid, STATUS_CANCELLED);
	}
	else if ($action == 'xr')
	{
		add_raid_history($raid_id, array('CLEAR_RAID', $raid));		
		set_raid_status($raid_id, $raid, STATUS_CLEAR);
	}

	$lang_array = array(
		'+' => 'STATUS_CHANGE_ON', 
		'-' => 'STATUS_CHANGE_OFF', 
		'x' => 'STATUS_CHANGE_CLEAR', 
		'!' => 'STATUS_CHANGE_NOSHOW', 
		'%'	=> 'STATUS_CHANGE_LATE',
		'z' => 'STATUS_CHANGE_SUBSTITUTE',
		'cr' => 'STATUS_CHANGE_CANCELLED',
		'xr' => 'STATUS_CHANGE_RAID_CLEAR',
	);
	$lang_key = $lang_array[$action];
	if ($username && $raider->name && $day && $user->lang[$lang_key]) 
	{
		$success[] = sprintf($user->lang[$lang_key], $username, $raider->name, $day);
	}
	else if ($username && $day && $user->lang[$lang_key])
	{
		$success[] = sprintf($user->lang[$lang_key], $username, $day);
	}
	else 
	{
		$error[] = "Error! $username, $raider->name, $day, $lang_key, " . $user->lang[$lang_key];
	}
}
?>
