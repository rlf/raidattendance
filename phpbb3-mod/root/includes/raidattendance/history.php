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
global $phpEx;

require_once 'constants.' . $phpEx;
require_once 'raider.' . $phpEx;

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

?>
