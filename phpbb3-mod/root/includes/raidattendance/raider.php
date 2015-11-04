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
			'level' 	=> intval($this->level),
			'rank' 		=> intval($this->rank),
			'class' 	=> intval($this->class),
			'id' 		=> intval($this->id),
			'user_id' 	=> isset($this->user_id) ? intval($this->user_id) : 0,
			'edited'	=> time(),
			'synced'	=> $this->synced or 0,
			'created'	=> $this->created or time(),
			'role'		=> isset($this->role) ? intval($this->role) : 0,
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
		return ($this->__status != 'same' && $this->__status != '') ? $this->__status : '';
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

	function add_raid($raid_id)
	{
		if (!isset($this->raids)) 
		{
			$this->raids = array();
		}
		$this->raids[] = intval($raid_id);
	}

	function &get_raids()
	{
		if (!isset($this->raids))
		{
			$this->raids = array();
		}
		return $this->raids;
	}

	function is_dirty()
	{
		$hash = $this->_hash;
		$new_hash = $this->get_hash();
		/*
		global $error;
		if ($hash != $new_hash) {
			$error[] = 'Raider ' . $this->name . ' is dirty! [' . $hash . ' != ' . $new_hash . "]";
		}
		 */
		return $hash != $new_hash;
	}

	/**
	 * Returns a hash-code that only changes if the storable fields of this raider changes.
	 **/
	function get_hash()
	{
		$ary = $this->as_row();
		unset($ary['edited']);
		$ary['raids'] = $this->get_raids();
 		$ser = serialize($ary);
		/* debug
		global $error;
		$error[] = 'Hash = ' . hash('crc32', $ser) . ' for ' . $this->name . ' object-hash = ' . spl_object_hash($this) . ' ser = ' . $ser;
		 */
		return hash('crc32', $ser);
	}

	function set_clean()
	{
		$this->_hash = $this->get_hash();
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

	function set_star($raid, $star) 
	{
		add_history($this, array('STAR', $raid, $star));
		set_star($this, $raid, $star);
	}
}

?>
