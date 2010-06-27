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



define('RAIDER_TABLE', $table_prefix . 'raidattendance_raiders');
define('RAIDER_HISTORY_TABLE', $table_prefix . 'raidattendance_history');
define('RAIDATTENDANCE_TABLE', $table_prefix . 'raidattendance');
define('RAIDER_CONFIG', $table_prefix . 'raidattendance_config');
define('TABLE_WWS_RAID', $table_prefix . 'raidattendance_wws');
define('RAIDS_TABLE', $table_prefix . 'raidattendance_raids');
define('RAIDERRAIDS_TABLE', $table_prefix . 'raidattendance_raidersraid');

// Roles
define('ROLE_UNASSIGNED', 9);
define('ROLE_TANK', 1);
define('ROLE_HEALER', 2);
define('ROLE_RANGED_DPS', 3);
define('ROLE_MELEE_DPS', 4);
// Classes
define('CLASS_WARRIOR', 1);
define('CLASS_PALADIN', 2);
define('CLASS_HUNTER', 3);
define('CLASS_ROGUE', 4);
define('CLASS_PRIEST', 5);
define('CLASS_DEATH KNIGHT', 6);
define('CLASS_SHAMAN', 7);
define('CLASS_MAGE', 8);
define('CLASS_WARLOCK', 9);
define('CLASS_DRUID', 11);
// STATUS
define('STATUS_CLEAR', 0);
define('STATUS_ON', 1);
define('STATUS_OFF', 2);
define('STATUS_NOSHOW', 3);
define('STATUS_LATE', 4);
define('STATUS_SUBSTITUTE', 5);
define('STATUS_CANCELLED', 6);

$error = array();
$success = array();

function get_text($key)
{
	global $user;
	return $user->lang[$key] ? $user->lang[$key] : $key;
}
/**
 * Error handler used around the armory-lookup.
 **/
function url_error_handler($errno, $errstr, $errfile, $errline)
{
	global $error, $user;
	$error[] = sprintf($user->lang['ERROR_CONTACTING_ARMORY'], $errstr);
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
	for ($i = 0; $i <= 6; $i++) 
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
// Raiders from the wowarmory
// ---------------------------------------------------------------------------
class raider_armory 
{
	/**
	 * Returns an array of raiders from the specified armory.
	 * All raiders are specified to be those with a rank as specified in 
	 * the rank-array, and of minimum the supplied level.
	 **/
	function get_raider_list($armory_link, $realm, $guild, $ranks, $min_level, &$raiders) 
	{
		global $error, $user, $success;

		$this->raiders = &$raiders;
		$this->min_level = $min_level;
		$this->ranks = $ranks;
		$url = $armory_link . '/guild-info.xml?r=' . urlencode($realm) . '&gn=' . urlencode($guild) . '&rhtml=n';
		//$this->raiders['url'] = array('raider_name' => $url);

		$old_err = set_error_handler('url_error_handler');
		$data = file_get_contents($url, false);
		set_error_handler($old_err);
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler($parser,  array($this, 'start_elem'), array($this, 'end_elem'));
		$this->newly_added = array();
		xml_parse($parser, $data);
		xml_parser_free($parser);
		foreach ($raiders as $raider)
		{
			if ($raider->__status != 'NEW' && $raider->__status != 'UPDATED')
			{
				$raider->__status = 'NOT_IN_ARMORY';
				$raider->set_checked(true);
			}
			else 
			{
				$raider->set_checked(false);
			}
		}
		if (sizeof($this->newly_added)) 
		{
			$success[] = sprintf($user->lang['RAIDER_ADDED_FROM_ARMORY'], implode(', ', $this->newly_added));
		}
		else
		{
			$success[] = $user->lang['NO_NEW_RAIDERS_IN_ARMORY'];
		}
	}

	function start_elem($parser, $name, array $attrs)
	{
		global $error, $success, $user;
		if ($name == 'character') 
		{
			$data = array(
				'name' 			=> $attrs['name'],
				'level'			=> $attrs['level'],
				'rank'			=> $attrs['rank'],
				'class'			=> $attrs['classId']
			);
			if ($data['level'] >= $this->min_level && array_search($data['rank'], $this->ranks) !== FALSE) 
			{
				$name = $attrs['name'];
				if (array_key_exists($name, $this->raiders))
				{
					$this->raiders[$name]->update($data);
					$this->raiders[$name]->__status = 'UPDATED';
				}
				else
				{
					$this->raiders[$name] = new raider($data);
					$this->raiders[$name]->__status = 'NEW';
					$this->newly_added[] = $name;
					//$success[] = sprintf($user->lang['RAIDER_ADDED_FROM_ARMORY'], $name);
				}
			}
		}
	}

	function end_elem($parser, $name)
	{
		// don't care
	}
}

// ---------------------------------------------------------------------------
// Raiders from the DB
// ---------------------------------------------------------------------------
class raider_db
{
	function get_raider_list(&$raiders, $raid_id = false, $sort_order = '1')
	{
		global $db, $error;
		$sql = 'SELECT r.* FROM ' . RAIDER_TABLE . ' r';
		if ($raid_id != false) 
		{
			$sql = $sql . ' JOIN ' . RAIDERRAIDS_TABLE . ' rr ON rr.raider_id=r.id WHERE rr.raid_id=' . $raid_id;
		}
		$sort = array();
		$sort_array = explode(',', $sort_order);
		$sort_key_map = array(1=>'name', 2=>'role', 3=>'rank', 4=>'class');
		foreach ($sort_array as $column) 
		{
			$col = intval($column);
			$sort_exp = $sort_key_map[abs($col)];
			// Only add sorting cols, if this is one of the "database columns" (i.e. don't handle 7).
			if ($sort_exp)
			{
				if ($col < 0) 
				{
					$sort_exp = $sort_exp . ' DESC';
				}
				$sort[] = $sort_exp;
			}
		}
		if (sizeof($sort) == 0) 
		{
			$sort[] = 'name';
		}
		$sql = $sql . ' ORDER BY ' . implode(',', $sort);
		$result = $db->sql_query($sql);
		while ($row = $db->sql_fetchrow($result))
		{
			$name = $row['name'];
			$data = array(
				'name'		=> $name,
				'level'		=> $row['level'],
				'rank'		=> $row['rank'],
				'class'		=> $row['class'],
				'id'		=> $row['id'],
				'user_id'	=> $row['user_id'],
				'role'		=> $row['role'],
			);
			if (array_key_exists($name, $raiders))
			{
				$raiders[$name]->update($data);
			}
			else 
			{
				$raiders[$name] = new raider($data);
			}
		}
		$db->sql_freeresult($result);
	}

	function save_raider_list(&$rows)
	{
		global $error, $debug, $user, $success, $db;
		$added = array();
		$updated = array();
		foreach ($rows as $raider) 
		{
			$num_errors_before = sizeof($errors);
			if ($raider->is_saved_in_db()) 
			{
				// we have a row, update it
				$old = array('id' => $raider->id);
				$sql = 'UPDATE ' . RAIDER_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $raider->as_row()) . ' WHERE id=' . $raider->id;
				$db->sql_query($sql);

				// Update Raids
				$raids = $raider->raids;
				if (!is_array($raids)) 
				{
					$raids = array();
				}
				$sql = 'DELETE FROM ' . RAIDERRAIDS_TABLE . ' WHERE raider_id=' . $raider->id;
				$db->sql_query($sql);

				$ary = array();
				foreach ($raids as $raid)
				{
					$ary[] = array(
						'raid_id' 	=> $raid,
						'raider_id' => $raider->id,
					);
				}
				$db->sql_multi_insert(RAIDERRAIDS_TABLE, $ary);
				if (sizeof($errors) == $num_errors_before) 
				{
					$updated[] = $raider->name;
				}
			}
			else 
			{
				// we need to create a row
				$ary = array($raider->as_row());
				$res = $db->sql_multi_insert(RAIDER_TABLE, $ary);
				if (is_dbal_error())
				{
					$error[] = sprintf($user->lang['ERROR_ADDING_RAIDER'], $raider->name, $res);
				}
				if (sizeof($errors) == $num_errors_before) 
				{
					$added[] = $raider->name;
				}
			}
		}
		if (sizeof($added)) 
		{
			$success[] = sprintf($user->lang['ADDED_RAIDERS'], implode(', ', $added));
		}
	}
	function delete_checked_raiders(&$rows)
	{
		global $error, $success, $user, $db;
		// TODO: Optimize this so we use the checked array directly
		foreach ($rows as $k => $raider)
		{
			if ($raider->is_checked()) 
			{
				$sql = 'DELETE FROM ' . RAIDER_TABLE . " WHERE id={$raider->id}";
				$res = $db->sql_query($sql);
				if (is_dbal_error())
				{
					$error[] = sprintf($user->lang['ERROR_DELETING_RAIDER'], $raider->name, $res);
				}
				else 
				{
					$success[] = sprintf($user->lang['SUCCESS_DELETING_RAIDER'], $raider->name);
				}
				unset($rows[$k]);
			}
		}
	}
}

// ---------------------------------------------------------------------------
// WWS from the DB
// ---------------------------------------------------------------------------
function wws_delete($checked)
{
	global $db, $success, $error, $user;
	$sql = 'DELETE FROM ' . TABLE_WWS_RAID . ' WHERE ' . $db->sql_in_set('id', $checked);
	$res = $db->sql_query($sql);
	if ($res === FALSE) 
	{
		$error[] = sprintf($user->lang['ERROR_DELETING_WWS'], sizeof($checked));
	}
	else 
	{
		$success[] = sprintf($user->lang['SUCCESS_DELETING_WWS'], sizeof($checked));
	}
}

class wws_db
{
	// TODO: Add a time-filter... for when many raids are in the database
	function get_raid_list()
	{
		global $db, $error, $success;
		$sql = 'SELECT * FROM ' . TABLE_WWS_RAID . ' ORDER BY synced DESC';
		$result = $db->sql_query($sql);
		$list = array();
		while ($row = $db->sql_fetchrow($result))
		{
			$list[] = new wws_entry($row);
		}
		$db->sql_freeresult($result);
		return $list;
	}

	function refetch(&$list)
	{
		global $db, $error, $success, $config;
		if (!$config['raidattendance_wws_guild_id'])
		{
			$error[] = get_text('NO_WWS_CONFIGURED');
		}
		$url = 'http://www.worldoflogs.com/feeds/guilds/' . $config['raidattendance_wws_guild_id'] . '/raids/?t=xml';

		$old_err = set_error_handler('url_error_handler');
		$data = file_get_contents($url, false);
		set_error_handler($old_err);
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler($parser,  array($this, 'start_elem'), array($this, 'end_elem'));

		$this->raids = $list;
		$this->raiders = array();
		$raider_db = new raider_db();
		$this->raiders = array();
		$raider_db->get_raider_list($this->raiders);

		xml_parse($parser, $data);
		xml_parser_free($parser);
		
		return $this->raids;
	}

	function start_elem($parser, $name, array $attrs)
	{
		if ($name == 'Raid') 
		{
			$this->raiders = array(); // Clean the array... no exception handling
			$date = $attrs['date'];
			$raid = strftime('%Y%m%d', $date * 1000);
			$this->raid = array('date'=>$attrs['date'], 'wws_id'=>$attrs['id'], 'raid'=>$raid);
		}
		else if ($name == 'Participant')
		{
			$this->raiders[] = $attrs['name'];
		}
	}

	function end_elem($parser, $name)
	{
		global $success, $error;
		if ($name == 'Raid' && $this->raid)
		{
			$this->raid['synced'] = time();
			$wws = new wws_entry($this->raid);
			if ($this->raids[$wws->raid])
			{
				$wws->id = $this->raids[$wws->raid]->id;
			}
			$wws->raiders = $this->raiders;
			$wws->save();
			$this->raids[$wws->raid] = $wws;
			$this->raid = NULL;
		}
	}
}

// ---------------------------------------------------------------------------
// Class WWS Raid Entry
//---------------------------------------------------------------------------
class wws_entry
{
	function __construct($row)
	{
		if ($row['id']) 
		{
			$this->id = $row['id'];
		}
		$this->wws_id = $row['wws_id'] ? $row['wws_id'] : '';
		$this->raid = $row['raid'] ? $row['raid'] : '';
		$this->synced = $row['synced'] ? $row['synced'] : 0;
		$this->raiders = $row['raiders'];
		if ($this->raiders and is_string($this->raiders))
		{
			$this->raiders = explode(',', $this->raiders); 
		}
	}

	function save()
	{
		global $error, $success, $db;
		if ($this->id) 
		{
			$sql = 'UPDATE ' . TABLE_WWS_RAID . ' SET ' . $db->sql_build_array('UPDATE', $this->as_row()) . " WHERE id={$this->id}";
			if (is_dbal_error()) 
			{
				$error[] = 'Error updating WWS entry with id ' . $this->id . '<br/>' . $res;
			}
			else
			{
				$success[] = 'Successfully updated WWS entry with wws_id ' . $this->wws_id . '<br/>' . $res;
			}
		}
		else
		{
			$ary = array($this->as_row());
			$res = $db->sql_multi_insert(TABLE_WWS_RAID, $ary);
			if (is_dbal_error()) 
			{
				$error[] = 'Error inserting WWS entry with wws_id ' . $this->wws_id . '<br/>' . $res;
			}
			else
			{
				$success[] = 'Successfully inserted WWS entry with wws_id ' . $this->wws_id . ' <br/>' . $res;
			}
		}
	}

	function as_row()
	{
		$ary = array(
			'raid'		=> $this->raid,
			'wws_id'	=> $this->wws_id,
			'synced'	=> $this->synced,
			'raiders'	=> $this->get_raiders(),
			);
		if ($this->id) 
		{
			$ary['id'] = $this->id;
		}
		return $ary;
	}

	function get_raiders()
	{
		return is_array($this->raiders) ? implode(', ', $this->raiders) : '';
	}
}
// ---------------------------------------------------------------------------
// History Functions
// ---------------------------------------------------------------------------
function add_history($raider, $action)
{
	global $user, $db;
	$data = array(array(
		'user_id' 		=> $user->data['user_id'],
		'raider_id'		=> $raider->id,
		'time'			=> time(),
		'action'		=> is_array($action) ? implode(',', $action) : $action,
		));
	$db->sql_multi_insert(RAIDER_HISTORY_TABLE, $data);
}

function add_raid_history($raid_id, $action)
{
	global $user, $db;
	$data = array(array(
		'user_id' 		=> $user->data['user_id'],
		'raider_id'		=> 0xfff000 ^ $raid_id,
		'time'			=> time(),
		'action'		=> is_array($action) ? implode(',', $action) : $action,
		'raid_id'		=> $raid_id,
		));
	$db->sql_multi_insert(RAIDER_HISTORY_TABLE, $data);
}

function get_history($starttime, $endtime = 0)
{
	if ($endtime = 0) 
	{
		$endtime = time();
	}
	global $db;
	$sql = 'SELECT * FROM ' . RAIDER_HISTORY_TABLE . ' WHERE time >= ' . $starttime . ' AND time <= ' . $endtime . ' ORDER BY time DESC';
	$result = $db->sql_query($sql);
	$history = array();
	while ($row = $db->sql_fetchrow($result))
	{
		$history[] = new history($row);
	}
	$db->sql_freeresult($result);
	return $history;
}

class history
{
	function __construct($row)
	{
		$this->id 			= $row['id'];
		$this->user_id 		= $row['user_id'];
		$this->raider_id	= $row['raider_id'];
		$this->time			= $row['time'];
		$this->action		= $row['action'];
		$this->raid_id		= $row['raid_id'];
	}
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
		if (is_array($attendance[$row['name']]))
		{
			$attendance[$row['name']][$row['night']] = $row['status'];
		}
		else
		{
			$attendance[$row['name']] = array($row['night'] => $row['status']);
		}
		if (is_array($nights[$row['night']]))
		{
			$nights[$row['night']][$row['name']] = $row['status'];
		}
		else
		{
			$nights[$row['night']] = array($row['name'] => $row['status']);
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
		if (!is_array($attendance[$row['name']]))
		{
			$attendance[$row['name']] = array();
		}
		$attendance[$row['name']][$row['night']] = array('status' => $row['status'], 'comment' => $row['comment']);
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
		if (!is_array($raider_day_attendance[$row['name']]))
		{
			$raider_day_attendance[$row['name']] = array();
		}
		$raider_day_attendance[$row['name']][$row['night']] = array(
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

// ---------------------------------------------------------------------------
// Raider Class
// ---------------------------------------------------------------------------
class raider
{
	function __construct($row)
	{
		$this->update($row);
	}

	function is_saved_in_db()
	{
		return $this->id;
	}

	function as_row()
	{
		$data = array(
			'name' 		=> $this->name,
			'level' 	=> $this->level,
			'rank' 		=> $this->rank,
			'class' 	=> $this->class,
			'id' 		=> $this->id,
			'user_id' 	=> isset($this->user_id) ? $this->user_id : 0,
			'edited'	=> time(),
			'synced'	=> $this->synced or 0,
			'created'	=> $this->created or time(),
			'role'		=> isset($this->role) ? $this->role : 0,
		);
		return $data;
	}

	function compare($raider)
	{
		$cmp = strcmp($a->name, $b->name);
		if ($cmp === 0) 
		{
			$cmp = $a->rank - $b->rank;
		}
		return $cmp;
	}

	function is_checked()
	{
		return $this->__checked;
	}

	function get_status()
	{
		global $user;
		return ($this->__status != 'same' && $this->__status != '')
			? '<b class="STATUS_' . $this->__status . '">*' . $user->lang['STATUS_' . $this->__status] . '*</b>&nbsp;'
			: '';
	}

	function set_user_id($id)
	{
		$this->user_id = $id;
	}

	function set_checked($b)
	{
		$this->__checked = $b;
	}

	function get_rank_name()
	{
		global $config, $user;
		$rank_name = $config['raidattendance_raider_rank' . $this->rank . '_name'];
		if (!$rank_name) 
		{
			$rank_name = $user->lang['RANK_' . $raider->rank];
		}
		return $rank_name;
	}

	function get_role_name()
	{
		global $user;
		return $user->lang['ROLE_' . $this->role];
	}

	function update($row)
	{
		if ($this->id and $this->name == $row['name'] 
			and $this->level == $row['level'] 
			and $this->rank == $row['rank'] 
			and $this->class == $row['class'])
		{
			$this->__status = 'same';
		}
		else if ($this->id)
		{
			$this->__status = 'update';
		}
		$this->name 	= $row['name'];
		$this->level 	= $row['level'];
		$this->rank		= $row['rank'];
		$this->class	= $row['class'];
		$this->synced 	= isset($row['synced']) ? $row['synced'] : $this->synced;
		$this->edited	= isset($row['edited']) ? $row['edited'] : $this->edited;
		$this->created	= isset($row['created']) ? $row['created'] : $this->created;
		if (isset($row['id'])) 
		{
			$this->id	= $row['id'];
		}
		else 
		{
			$this->synced = time();
		}
		if (isset($row['user_id'])) 
		{
			$this->user_id = $row['user_id'];
		}
		if (isset($row['role'])) 
		{
			$this->role = $row['role'];
		}
	}

	// RAIDS
	function get_raids()
	{
		$raids = array();
		if ($this->id) 
		{
			global $db;
			$sql = 'SELECT raid_id FROM ' . RAIDERRAIDS_TABLE . ' WHERE raider_id=' . $this->id;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				$raids[] = $row['raid_id'];
			}
			$db->sql_freeresult($result);
		}
		$this->raids = $raids;
		return $this->raids;
	}

	// HISTORY

	function signon($raid)
	{
		add_history($this, array('SIGNON', $raid));
		set_attendance($this, $raid, STATUS_ON);
	}

	function signoff($raid, $comment = '')
	{
		add_history($this, array('SIGNOFF', $raid));
		set_attendance($this, $raid, STATUS_OFF, $comment);
	}

	function clear_attendance($raid)
	{
		add_history($this, array('CLEAR', $raid));
		set_attendance($this, $raid, STATUS_CLEAR);
	}

	function noshow($raid)
	{
		add_history($this, array('NOSHOW', $raid));
		set_attendance($this, $raid, STATUS_NOSHOW);
	}

	function late($raid)
	{
		add_history($this, array('LATE', $raid));
		set_attendance($this, $raid, STATUS_LATE);
	}

	function substitute($raid)
	{
		add_history($this, array('SUBSTITUTE', $raid));
		set_attendance($this, $raid, STATUS_SUBSTITUTE);
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
