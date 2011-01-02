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
		$raider_ids = array();
		while ($row = $db->sql_fetchrow($result))
		{
			//$name = utf8_decode($row['name']);
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
			$raider = $raiders[$name];
			$raider_ids[$raider->id] = $name;
		}
		$db->sql_freeresult($result);
		// Add the raids info
		if (sizeof($raider_ids) > 0)
		{
			$sql = 'SELECT raid_id, raider_id FROM ' . RAIDERRAIDS_TABLE . ' WHERE ' . $db->sql_in_set('raider_id', array_keys($raider_ids));
			//$error[] = 'SQL: ' . $sql;
			$result = $db->sql_query($sql);
			while ($row = $db->sql_fetchrow($result))
			{
				if (isset($raider_ids[$row['raider_id']]))
				{
					$raiders[$raider_ids[$row['raider_id']]]->add_raid($row['raid_id']);
				} 
			}
			$db->sql_freeresult($result);
		}
		foreach ($raiders as $name => $raider)
		{
			$raider->set_clean();
		}
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
				if (!$raider->is_dirty()) {
					// Skip the ones not dirty
					continue;
				}
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
		if (sizeof($updated)) 
		{
			$success[] = sprintf($user->lang['UPDATED_RAIDERS'], implode(', ', $updated));
		}
	}

	function delete_checked_raiders(&$rows)
	{
		global $error, $success, $user, $db;
		// TODO: Optimize this so we use the checked array directly
		$deleted = array();
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
					$deleted[] = $raider->name;
				}
				unset($rows[$k]);
			}
		}
		$success[] = sprintf($user->lang['SUCCESS_DELETING_RAIDER'], implode(', ', $deleted));
	}

	/**
	 * Merges a number of raiders together.
	 * The first ID on the list is the "main character".
	 **/
	function merge_raiders($primary_id, $alt_ids)
	{
		global $error, $success, $db;
		if (sizeof($alt_ids) < 1)
		{
			$error[] = get_text('ERROR_AT_LEAST_2_RAIDERS_FOR_MERGE');
			return;
		}
		$in_raider_id = $db->sql_in_set('raider_id', $alt_ids);

		// 1. Clean out RAIDERRAIDS_TABLE (except for primary_id).
		$sql = 'DELETE FROM ' . RAIDERRAIDS_TABLE . ' t WHERE ' . $in_raider_id;
		$res = $db->sql_query($sql);

		// 2. UPDATE all entries in RAIDER_HISTORY_TABLE so IN (ids) become primary_id.
		$sql = 'UPDATE ' . RAIDER_HISTORY_TABLE . ' t SET raider_id=' . $primary_id . ' WHERE ' . $in_raider_id;
		$res = $db->sql_query($sql);

		// 3. UPDATE all entries in RAIDATTENDANCE_TABLE so IN (ids) become primary_id.
		$sql = 'UPDATE ' . RAIDATTENDANCE_TABLE . ' t SET raider_id=' . $primary_id . ' WHERE ' . $in_raider_id;
		$res = $db->sql_query($sql);

		// 4. DELETE all RAIDERS not primary_id IN (ids).
		// DON'T DELETE IF ANY ERRORS!!!
		// How to handle conflicts in UPDATEs???
	}
}
?>
