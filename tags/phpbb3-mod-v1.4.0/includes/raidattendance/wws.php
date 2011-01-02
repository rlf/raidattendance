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

?>
