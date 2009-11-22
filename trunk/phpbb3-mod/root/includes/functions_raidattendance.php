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
			}
		}
		if (sizeof($this->newly_added)) 
		{
			$success[] = sprintf($user->lang['RAIDER_ADDED_FROM_ARMORY'], implode(',', $this->newly_added));
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
		$sql = 'SELECT * FROM ' . RAIDER_TABLE . ' ORDER BY name, rank, level DESC';
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
				if (strpos($res, '<br />Success') === FALSE)
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
				if (strpos($res, '<br />Success') === FALSE)
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
				if (is_array($res) && sizeof($res) == 2 && $res[1] != 'SUCCESS')
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
}
?>
