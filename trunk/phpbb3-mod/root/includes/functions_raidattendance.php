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

// UMIL is used for database updates
include_once($phpbb_root_path . 'umil/umil.' . $phpEx);

$error = array();
$success = array();

/**
 * Error handler used around the armory-lookup.
 **/
function armory_error_handler($errno, $errstr, $errfile, $errline)
{
	global $error, $user;
	$error[] = sprintf($user->lang['ERROR_CONTACTING_ARMORY'], $errstr);
}
// ---------------------------------------------------------------------------
// Functions
// ---------------------------------------------------------------------------
/**
 * Returns an array of the ranks expected to raid.
 **/
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
	global $config, $db;
	$forum_name = $config['raidattendance_forum_name'];
	$sql = 'SELECT COUNT(*) cnt FROM ' . FORUMS_TABLE . " f WHERE f.forum_name = '$forum_name' AND f.forum_id = $forum_id";
	$result = $db->sql_query($sql);
	$row = $db->sql_fetchrow($result);
	$found_it = $row['cnt'] > 0;
	$db->sql_freeresult($result);
	return $found_it;
}

function get_raiding_days($current_week)
{
	$date_array = getdate($current_week);
	$this_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-$date_array['wday'], $date_array['year']);
	$date_array = getdate($this_week);
	$last_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']-7, $date_array['year']);
	$next_week = mktime(0, 0, 0, $date_array['mon'], $date_array['mday']+7, $date_array['year']);
	$day_numbers = get_raiding_day_numbers();
	$days = get_raiding_day_numbers();
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
	return $raiding_days;
}
function get_raiding_day_numbers()
{
	global $config;
	$days = array();
	$k = 'raidattendance_raid_night_';
	$day_map = array('mon' => 1, 'tue' => 2, 'wed' => 3, 'thu' => 4, 'fri' => 5, 'sat' => 6, 'sun' => 0);
	foreach ($day_map as $day => $number)
	{
		if ($config[$k . $day])
		{
			$days[] = $number;
		}
	}
	return $days;
}
function get_raiding_day_keys($numbers = null)
{
	if (!$numbers) 
	{
		$numbers = get_raiding_day_numbers();
	}
	$key_base = 'RAID_NIGHT_';
	$num2name = array(0=>'SUN', 1=>'MON', 2=>'TUE', 3=>'WED', 4=>'THU', 5=>'FRI', 6=>'SAT');
	$names = array();
	foreach ($numbers as $num)
	{
		$names[] = $key_base . $num2name[$num];
	}
	return $names;
}
function tm2time($tm) 
{
	extract($tm);
	return mktime(
		(int) $tm_hour,
		(int) $tm_min,
		(int) $tm_sec,
		((int) $tm_mon)+1,
		(int) $tm_mday,
		((int) $tm_year)+1900);
}

/**
 * Takes a number and returns it as a string with 'st', 'nd', 'rd' 'th' etc.
 * added.
 **/
function post_num($num)
{
	if ($num == 1 or ($num >= 20 and (($num % 10) == 1)))
	{
		return $num . 'st';
	}
	else if ($num == 2 or ($num >= 20 and (($num % 10 == 2)))) 
	{
		return $num . 'nd';
	}
	else if ($num == 3 or ($num >= 20 and (($num % 10 == 3))))
	{
		return $num . 'rd';
	}
	return $num . 'th';
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
function is_umil_error($res)
{
	global $user;
	$success = $user->lang['SUCCESS'] ? $user->lang['SUCCESS'] : 'SUCCESS';
	if (strpos($res, '<br />' . $success) === FALSE) 
	{
		return TRUE;
	}
	return FALSE;
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

		$old_err = set_error_handler('armory_error_handler');
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
				$raider->__checked = true;
			}
			else 
			{
				$raider->__checked = false;
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
	function get_raider_list(&$raiders)
	{
		global $db, $error;
		$sql = 'SELECT * FROM ' . RAIDER_TABLE . ' ORDER BY rank, name, level DESC';
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
		global $error, $debug, $user, $success;
		$umil = new umil(true);
		$added = array();
		$updated = array();
		foreach ($rows as $raider) 
		{
			$res = 'nothing happened';
			if ($raider->is_saved_in_db()) 
			{
				// we have a row, update it
				$old = array('id' => $raider->id);
				$res = $umil->table_row_update(RAIDER_TABLE, $old, $raider->as_row());
				if (is_umil_error($res))
				{
					$error[] = sprintf($user->lang['ERROR_UPDATING_RAIDER'], $raider->name, $res);
				}
				else 
				{
					$updated[] = $raider->name;
				}
			}
			else 
			{
				// we need to create a row
				$res = $umil->table_row_insert(RAIDER_TABLE, array($raider->as_row()));
				if (is_umil_error($res))
				{
					$error[] = sprintf($user->lang['ERROR_ADDING_RAIDER'], $raider->name, $res);
				}
				else 
				{
					//$success[] = sprintf($user->lang['SUCCESS_ADDING_RAIDER'], $raider->name);
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
		global $error, $success, $user;
		$umil = new umil(true);
		// TODO: Optimize this so we use the checked array directly
		foreach ($rows as $name => $raider)
		{
			if ($raider->is_checked()) 
			{
				$res = $umil->table_row_remove(RAIDER_TABLE, array('id' => $raider->id));
				if (is_umil_error($res))
				{
					$error[] = sprintf($user->lang['ERROR_DELETING_RAIDER'], $raider->name, $res[1]);
				}
				else 
				{
					$success[] = sprintf($user->lang['SUCCESS_DELETING_RAIDER'], $raider->name);
				}
				unset($rows[$name]);
			}
		}
	}
}
// ---------------------------------------------------------------------------
// History Functions
// ---------------------------------------------------------------------------
function add_history($raider, $action)
{
	global $user;
	$umil = new umil(true);
	$data = array(
		'user_id' 		=> $user->data['user_id'],
		'raider_id'		=> $raider->id,
		'time'			=> time(),
		'action'		=> is_array($action) ? implode(',', $action) : $action,
		);
	$umil->table_row_insert(RAIDER_HISTORY_TABLE, $data);
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
	}
}
// ---------------------------------------------------------------------------
// Attendance Access
// ---------------------------------------------------------------------------
function set_attendance($raider, $night, $status)
{
	global $error, $success;
	$umil = new umil(true);
	$status_map = array('signon' => 1, 'signoff' => 2, 'noshow' => 3);
	$ident = array(
		'raider_id'			=> $raider->id,
		'night'				=> $night,
		);
	$data = array(
		'raider_id'			=> $raider->id,
		'night'				=> $night,
		'status'			=> $status_map[$status],
		'time'				=> time(),
		);
	if ($status === false) 
	{
		$res = $umil->table_row_remove(RAIDATTENDANCE_TABLE, $ident);
		if (is_umil_error($res))
		{
			$error[] = $res;
		}
		return;
	}
	$res = $umil->table_row_insert(RAIDATTENDANCE_TABLE, $data);
	if (is_umil_error($res))
	{
		$res = $umil->table_row_update(RAIDATTENDANCE_TABLE, $ident, $data);
		if (is_umil_error($res)) 
		{
			$error[] = $res;
		}
	}
}
/**
 * Returns an associative array with _raidername_ => array(_raidnight_ => _status)
 **/
function get_attendance($nights)
{
	global $db;
	$in_night = "'" . implode("','", $nights) . "'";
	$sql = 'SELECT n.status status, r.name name, n.night night FROM ' 
		. RAIDATTENDANCE_TABLE . ' n, ' . RAIDER_TABLE . ' r WHERE r.id = n.raider_id AND n.night IN (' . $in_night . ')';
	$result = $db->sql_query($sql);
	$attendance = array();
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
	}
	$db->sql_freeresult($result);
	return $attendance;
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
		$this->synced 	= $row['synced'] or $this->synced;
		$this->edited	= $row['edited'] or $this->edited;
		$this->created	= $row['created'] or $this->created;
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
	}
	function signon($raid)
	{
		add_history($this, 'SIGNON');
		set_attendance($this, $raid, 'signon');
	}
	function signoff($raid)
	{
		add_history($this, 'SIGNOFF');
		set_attendance($this, $raid, 'signoff');
	}
	function clear_attendance($raid)
	{
		add_history($this, 'CLEAR');
		set_attendance($this, $raid, false);
	}
	function noshow($raid)
	{
		add_history($this, 'NOSHOW');
		set_attendance($this, $raid, 'noshow');
	}
}
?>
