<?php
/**
 *
 * @package raidattendance
 * @version $Id: functions_raidattendance.php 9462 2009-04-17 15:35:56Z acydburn $
 * @copyright (c) 2009 TA
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
global $table_prefix, $phpbb_root_path, $phpEx;

require_once 'constants.' . $phpEx;
require_once 'raider.' . $phpEx;
require_once 'raider_db.' . $phpEx;
require_once 'raider_armory.' . $phpEx;
require_once 'history.' . $phpEx;
require_once 'wws.' . $phpEx;

$error = array();
$success = array();

function get_text($key)
{
	global $user;
	return $user->lang[$key] ? $user->lang[$key] : $key;
}
// ---------------------------------------------------------------------------
// Functions
// ---------------------------------------------------------------------------
//
// Returns an array of the ranks expected to raid.
//
function get_raider_ranks()
{	
	global $config;
	$ranks = array();
	$key = 'raidattendance_raider_rank';
	for ($i = 0; $i <= MAX_RANK; $i++) 
	{
		if ($config[$key . $i]) 
		{
			$ranks[] = $i;
		}
	}
	return $ranks;
}

function is_raidattendance_forum($forum_id)
{
	global $config;
	return $forum_id == $config['raidattendance_forum_id'];
}

function get_default_raid_id()
{
	global $db;
	$sql = 'SELECT id FROM ' . RAIDS_TABLE . ' ORDER BY id ASC';
	$result = $db->sql_query($sql);
	$raid_id = 0;
	$row = $db->sql_fetchrow($result);
	if ($row)
	{
		$raid_id = $row['id'];
	}
	$db->sql_freeresult($result);
	return $raid_id;
}

/**
 * Takes the raiding-day-name (e.g. "Mon") and returns the corresponding weekday-number.
 **/
function get_day_number($raid_day_name)
{
	$day_nums = array('Mon' => 1, 'Tue' => 2, 'Wed' => 3, 'Thu' => 4, 'Fri' => 5, 'Sat' => 6, 'Sun' => 0);
	return $day_nums[$raid_day_name];
}
/**
 * Takes a range and an array of day-indices and returns all the "real-raiding-days" in between
 * the start_time and end_time (of the format %Y%m%d).
 **/
function get_raiding_dates($start_time, $end_time, $day_nums)
{
	global $error;
	$all_days = array();
	$d = getdate($start_time);
	$week_start = mktime(0, 0, 0, $d['mon'], $d['mday']-$d['wday'], $d['year']);
	while ($week_start <= $end_time) 
	{
		$d = getdate($week_start);
		foreach ($day_nums as $day_num) 
		{
			$raid_time = mktime(0, 0, 0, $d['mon'], $d['mday'] + $day_num, $d['year']);
			if ($raid_time >= $start_time && $raid_time <= $end_time) 
			{
				$raid = strftime('%Y%m%d', $raid_time);
				$all_days[] = $raid;
			}
		}
		$week_start = mktime(0, 0, 0, $d['mon'], $d['mday']+7, $d['year']);
	}
	return $all_days;
}

function get_raiding_days($current_week, $raid_id)
{
	$date_array = getdate($current_week);
	$this_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-$date_array['wday'], $date_array['year']);
	$date_array = getdate($this_week);
	$last_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-7, $date_array['year']);
	$next_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']+7, $date_array['year']);
	$days = get_raiding_day_numbers($raid_id);
	$raiding_days = array();
	$weeks = array($last_week, $this_week, $next_week);
	foreach ($weeks as $week)
	{
		$date_array = getdate($week);
		foreach ($days as $day)
		{
			$raiding_days[] = strftime('%Y%m%d', mktime(0,0,0, $date_array['mon'], $date_array['mday'] + $day, $date_array['year']));
		}
	}
	asort($raiding_days);
	return $raiding_days;
}

function get_raiding_day_name($raid)
{
	$tm = strptime($raid, '%Y%m%d');
	$time = tm2time($tm);
	$day_name = date('D', $time);
	return $day_name;
}

//
// Converts an array of 'YYYYMMDD' timestamps to an array of day-names 'MON', 'TUE' .. 'SUN'
// 
function get_raiding_day_names($raiding_days)
{
	// On purpose we use date()
	$day_names = array();
	foreach ($raiding_days as $raid)
	{
		$day_name = get_raiding_day_name($raid);
		if (array_search($day_name, $day_names) === FALSE)
		{
			$day_names[] = $day_name;
		}
		else
		{
			// Means we've already scanned a whole week.
			break;
		}
	}
	return $day_names;
}
function get_raiding_day_numbers($raid_id)
{
	global $db;
	$days = array();
	$sql = 'SELECT days FROM ' . RAIDS_TABLE . " WHERE id=$raid_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$days_str = '';
	if ($row) 
	{
		$days_str = $row['days'];
	}
	$days = explode(':', $days_str);
	$db->sql_freeresult($sql);
	$day_map = array('mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 0);
	$day_numbers = array();
	foreach ($days as $day)
	{
		$day_numbers[] = $day_map[$day];
	}
	return $day_numbers;
}

function tm2time($tm) 
{
	extract($tm);
	return mktime(
		intval($tm_hour),
		intval($tm_min),
		intval($tm_sec),
		intval($tm_mon)+1,
		intval($tm_mday),
		intval($tm_year)+1900);
}
/**
 * Takes a number and returns it as a string with 'st', 'nd', 'rd' 'th' etc.
 * added.
 **/
function post_num($num)
{
	global $user;
	if ($num == 1 or ($num >= 20 and (($num % 10) == 1)))
	{
		return sprintf($user->lang['DAY_NUMBER1'], $num);
	}
	else if ($num == 2 or ($num >= 20 and (($num % 10 == 2)))) 
	{
		return sprintf($user->lang['DAY_NUMBER2'], $num);
	}
	else if ($num == 3 or ($num >= 20 and (($num % 10 == 3))))
	{
		return sprintf($user->lang['DAY_NUMBER3'], $num);
	}
	return sprintf($user->lang['DAY_NUMBER_OTHER'], $num);
}

function get_raider_with_id($raiders, $id)
{
	foreach ($raiders as $name => $raider)
	{
		if ($raider->id == $id) 
		{
			return $raider;
		}
	}
	return null;
}

function is_dbal_error()
{
	global $db;
	return $db->sql_affectedrows() <= 0;
}

// ---------------------------------------------------------------------------
// Attendance Access
// ---------------------------------------------------------------------------
function set_raid_status($raid_id, $night, $status)
{
	global $error, $success, $db;
	$data = array(
		'raider_id'			=> 0xfff000 ^ $raid_id,
		'raid_id'			=> $raid_id,
		'night'				=> $night,
		'status'			=> $status,
		'time'				=> time(),
		);
	$sql = 'DELETE FROM ' . RAIDATTENDANCE_TABLE . " WHERE raid_id={$raid_id} AND night='{$night}'"; 
	$res = $db->sql_query($sql);
	// Bail out (don't add) - perhaps we should add... if it's a static signoff??
	if ($status === STATUS_CLEAR)
	{
		return;
	}
	$ary = array($data);
	$res = $db->sql_multi_insert(RAIDATTENDANCE_TABLE, $ary);
	if (is_dbal_error())
	{
		$error[] = $res;
	}
}
function set_attendance($raider, $night, $status, $comment = '')
{
	global $error, $success, $db;
	$data = array(
		'raider_id'			=> $raider->id,
		'night'				=> $night,
		'status'			=> $status,
		'time'				=> time(),
		'comment'			=> $comment,
		);
	if (!is_numeric($night) && $status == STATUS_CLEAR) 
	{
		// A range of nights, convert all nights from start till end to the state
		$sql = 'SELECT time, comment, status FROM ' . RAIDATTENDANCE_TABLE . " WHERE raider_id={$raider->id} AND night='{$night}'";
		$res = $db->sql_query($sql);
		$row = $db->sql_fetchrow($res);
		$time = $row['time'];
		$old_comment = $row['comment'];
		$old_status = $row['status'];
		$db->sql_freeresult($res);
		$day_num = get_day_number($night);
		$all_nights = get_raiding_dates($time, $data['time'], array($day_num));
		$ary = array();
		foreach ($all_nights as $raid_night)
		{
			// Check whether the night already has another status
			$sql = 'SELECT COUNT(status) cnt FROM ' . RAIDATTENDANCE_TABLE . " WHERE raider_id={$raider->id} AND night='{$raid_night}'";
			$res = $db->sql_query($sql);
			$row = $db->sql_fetchrow($res);
			$already_have_status = $row['cnt'] > 0;
			$db->sql_freeresult($res);
			if (!$already_have_status) 
			{
				$ary[] = array(
					'raider_id' => $raider->id, 
					'night' => $raid_night, 
					'status' => $old_status, 
					'time' => $time,
					'comment' => $old_comment,
				);
			}
		}
		$res = $db->sql_multi_insert(RAIDATTENDANCE_TABLE, $ary);
		if (is_dbal_error()) 
		{
			$error[] = $res;
		}
	}
	// One night
	$sql = 'DELETE FROM ' . RAIDATTENDANCE_TABLE . " WHERE raider_id={$raider->id} AND night='{$night}'"; 
	$res = $db->sql_query($sql);

	// Bail out (don't add) - perhaps we should add... if it's a static signoff??
	// If affectedrows == 0 we assume we have hit a "static"
	if ($status === STATUS_CLEAR and $db->sql_affectedrows() != 0)
	{
		return;
	}
	$ary = array($data);
	$res = $db->sql_multi_insert(RAIDATTENDANCE_TABLE, $ary);
	if (is_dbal_error())
	{
		$error[] = $res;
	}
}

function convert_to_ratio($value, $sum)
{
	return $sum == 0 ? 0 : $val*100/$sum;
}

function get_attendance_for_time($starttime, $endtime, $raid_id = 0)
{
	global $db, $error;
	$raiding_days = get_raiding_days($endtime, $raid_id);
	$raiding_day_names = array();
	// raiding_days is for 3 weeks
	for ($i = 0; $i < sizeof($raiding_days)/3; $i++)
	{
		$raiding_day_names[] = get_raiding_day_name($raiding_days[$i]); 
	}
	$sql = 'SELECT n.status status, r.name name, n.night night FROM ' 
		. RAIDATTENDANCE_TABLE . ' n, ' . RAIDER_TABLE . " r WHERE r.id = n.raider_id AND ((n.night >='$starttime' AND n.night <= '$endtime' AND (" 
		. $db->sql_in_set("DATE_FORMAT(STR_TO_DATE(n.night,'%Y%m%d'),'%a')", $raiding_day_names) . ')) OR ('
		. $db->sql_in_set('n.night', $raiding_day_names) . '))';
	$sql = $sql . " UNION SELECT n.status status, '__RAID__' name, n.night FROM " . RAIDATTENDANCE_TABLE . ' n WHERE n.raid_id=' . $raid_id . " AND n.night >='$starttime' AND n.night <= '$endtime'";
	$result = $db->sql_query($sql);
	$attendance = array();
	$nights = array();
	// Add summary columns
	for ($i = STATUS_CLEAR; $i <= STATUS_CANCELLED; $i++)
	{
		$nights['summary_' . $i] = 0;
	}
	while ($row = $db->sql_fetchrow($result))
	{
		//$name = utf8_decode($row['name']);
		$name = $row['name'];
		if (is_array($attendance[$name]))
		{
			$attendance[$name][$row['night']] = $row['status'];
		}
		else
		{
			$attendance[$name] = array($row['night'] => $row['status']);
		}
		if (is_array($nights[$row['night']]))
		{
			$nights[$row['night']][$name] = $row['status'];
		}
		else
		{
			$nights[$row['night']] = array($name => $row['status']);
		}
	}
	$db->sql_freeresult($result);
	// Static attendance
	$raider_active = array();
	ksort($nights);
	foreach ($nights as $night => $raiders)
	{
		$day_name = is_numeric($night) ? get_raiding_day_name($night) : $night;
		foreach ($attendance as $raider => &$rnights)
		{
			if (!is_numeric($night))
			{
				if (strncmp('summary_', $night, 8) == 0 && !isset($rnights[$night]))
				{
					$rnights[$night] = 0; 
				}
				continue; // Only handle the "real nights" this way...
			}
			// If raider is not active, but have a status for this night - make him active
			if (!isset($raider_active[$raider]) && isset($rnights[$night]))
			{
				$raider_active[$raider] = true;
			}
			// If raider is active, but don't have an entry for this night, but one for the name
			if ($raider_active[$raider] && !isset($rnights[$night]) && isset($rnights[$day_name]))
			{
				$rnights[$night] = $rnights[$day_name];
			}
			if (!isset($rnights[$night]))
			{
				// Explicitly set the "NOTHING" status
				$rnights[$night] = STATUS_CLEAR;
			}
			// Should not be needed (we have it above) - but just in case...
			if (!isset($rnights['summary_' . $rnights[$night]])) 
			{
				$rnights['summary_' . $rnights[$night]] = 0;
			}
			$rnights['summary_' . $rnights[$night]] = 1 + ($rnights['summary_' . $rnights[$night]]);
		}
	}
	foreach ($attendance as $raider => &$nights)
	{
		$sum = $nights['summary_1'] + $nights['summary_2'] + $nights['summary_3'] + $nights['summary_4'] + $nights['summary_5'] + $nights['summary_6'];
		if ($sum == 0) 
		{
			$sum = 1;
		}
		for ($i = 1; $i <= 6; $i++)
		{
			$nights['summary_' . $i . '_num'] = $nights['summary_' . $i];
			$nights['summary_' . $i] = $nights['summary_' . $i]*100/$sum;
		}
	}
	return $attendance;	
}
/**
 * Returns an associative array with _raidername_ => array(_raidnight_ => _status)
 * Note: raider_id = 0, name = '__RAID__' denotes the actual raid, and is used to mark cancellations of the whole raid.
 **/
function get_attendance($nights, $raid_id = 0)
{
	global $db;
	$sql = 'SELECT n.status status, r.name name, n.night night, n.comment comment FROM ' 
		. RAIDATTENDANCE_TABLE . ' n, ' . RAIDER_TABLE . ' r WHERE r.id = n.raider_id AND ' . $db->sql_in_set('n.night', $nights);
	$sql = $sql . "UNION SELECT n.status status, '__RAID__' name, n.night, n.comment comment FROM " . RAIDATTENDANCE_TABLE . ' n WHERE n.raid_id=' . $raid_id . ' AND ' . $db->sql_in_set('n.night', $nights);
	$result = $db->sql_query($sql);
	$attendance = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$name = $row['name'];
		if (!is_array($attendance[$name]))
		{
			$attendance[$name] = array();
		}
		$attendance[$name][$row['night']] = array('status' => $row['status'], 'comment' => $row['comment']);
	}
	$db->sql_freeresult($result);
	return $attendance;
}

function get_static_attendance($raids)
{
	$day_names = get_raiding_day_names($raids);
	return get_static_attendance_days($day_names);
}

/**
 * Returns an array of [raider-name][night-name] => array(status,time)
 **/
function get_static_attendance_days($day_names)
{
	global $db, $success;
	$sql = 'SELECT n.status status, r.name name, n.night, n.time time, n.comment comment FROM ' . RAIDATTENDANCE_TABLE . ' n, ' . RAIDER_TABLE . ' r WHERE r.id = n.raider_id AND ' . $db->sql_in_set('n.night', $day_names);
	$result = $db->sql_query($sql);
	$raider_day_attendance = array();
	while ($row = $db->sql_fetchrow($result))
	{
		//$name = utf8_decode($row['name']);
		$name = $row['name'];
		if (!is_array($raider_day_attendance[$name]))
		{
			$raider_day_attendance[$name] = array();
		}
		$raider_day_attendance[$name][$row['night']] = array(
			'status' => $row['status'], 
			'time' => $row['time'], 
			'comment' => $row['comment']);
	}
	$db->sql_freeresult($result);
	return $raider_day_attendance;
}

/**
 * Merges the two attendance arrays.
 **/
function add_static_attendance($raids, &$attendance, $raider_day_attendance)
{
	global $error;
	foreach ($raider_day_attendance as $raider => $days)
	{
		if (!is_array($attendance[$raider]))
		{
			$attendance[$raider] = array();
		}
		foreach ($raids as $raid)
		{
			$day_name = get_raiding_day_name($raid);
			if (!isset($attendance[$raider][$raid]) and array_key_exists($day_name, $days) and strftime('%Y%m%d', $days[$day_name]['time']) <= $raid)
			{
				$attendance[$raider][$raid] = $days[$day_name];
			}
		}
	}
}

// ----------------------------------------------------------------------------
// Raids
// ----------------------------------------------------------------------------
function get_raids()
{
	global $db;
	$sql = 'SELECT * FROM ' . RAIDS_TABLE . ' ORDER BY id ASC';
	$result = $db->sql_query($sql);
	$raids = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$raids[] = $row;
	}
	$db->sql_freeresult($result);
	return $raids;
}
?>
