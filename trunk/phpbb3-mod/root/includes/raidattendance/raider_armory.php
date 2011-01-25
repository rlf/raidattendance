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

function get_class_as_number($class_name) 
{
	switch ($class_name) 
	{
	case 'warrior': return CLASS_WARRIOR;
	case 'paladin': return CLASS_PALADIN;
	case 'hunter': return CLASS_HUNTER;
	case 'rogue': return CLASS_ROGUE;
	case 'priest': return CLASS_PRIEST;
	case 'death knight': return CLASS_DEATH_KNIGHT;
	case 'shaman': return CLASS_SHAMAN;
	case 'mage': return CLASS_MAGE;
	case 'warlock': return CLASS_WARLOCK;
	case 'druid': return CLASS_DRUID;
	}
	throw new Exception("Unsupported class : " . $class_name);
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
		$url = $armory_link . '/wow/en/guild/' . rawurlencode($realm) . '/' . rawurlencode($guild) . '/roster';
		$this->newly_added = array();
		$this->get_raiders($url, $ranks, $min_level);
		foreach ($raiders as $raider)
		{
			if ($raider->__status != 'NEW' && $raider->__status != 'UPDATED' && $raider->__status != 'DEMOTED')
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

	function get_raiders($url, $ranks, $min_level)
	{
		global $success, $error;
		ob_start();
		var_dump($ranks);
		$ranks_text = ob_get_contents();
		ob_end_clean();
		$doc = new DOMDocument();
		$doc->strictErrorChecking = false;
		$doc->resolveExternals = false;
		$doc->preserveWhiteSpace = false;
		ob_start();
		$doc_data = file_get_contents($url);
		// TODO: Localize
		if (@$doc->loadHTML($doc_data))
		{
			$success[] = 'Successfully retrieved : ' . $url;
		}
		else
		{
			$error[] = 'Error loading : ' . $url;
		}
		if (ob_get_length() > 0) 
		{
			// TODO: Localize
			$error[] = 'Warnings during import: ' . ob_get_contents();
		}
		ob_end_clean(); // TODO: Check the output?
		$xpath = new DOMXpath($doc);

		$char_rows = $xpath->query("//div[@id='roster']/table/tbody/tr");
		if ($char_rows->length <= 0) 
		{
			throw new Exception("Could not locate any raiders in guild");
		}
		$success[] = 'Number of guildies ' . $char_rows->length;
		foreach ($char_rows as $row) 
		{
//<tr xmlns="http://www.w3.org/1999/xhtml" class="row1" data-level="71">
//	<td class="name"><a href="/wow/en/character/bloodhoof/abi/" class="color-c9">Abi</a></td>
//	<td class="race" data-raw="human"><img src="/wow/static/images/icons/race/1-1.gif" class="img" alt="" data-tooltip="Human" /></td>
//	<td class="cls" data-raw="warlock"><img src="/wow/static/images/icons/class/9.gif" class="img" alt="" data-tooltip="Warlock" /></td>
//	<td class="lvl">71</td>
//	<td class="rank" data-raw="6"><span>Rank 6</span></td>
//	<td class="ach-points"><span class="ach-icon">1080</span></td>
//	<td class="lifetime">0</td>
//	<td class="weekly">0</td>
//</tr>
			if ($row->getAttribute('class') == 'no-results')
			{
				// Skip it
				continue;
			}
			$name_node = $xpath->query(".//td[@class='name']/a", $row)->item(0);
			$rank_node = $xpath->query(".//td[@class='rank']", $row)->item(0);
			$class_node = $xpath->query(".//td[@class='cls']", $row)->item(0);
			if (!$name_node || !$rank_node || !$class_node) 
			{
				$node_text = $doc->saveXML($row);
				throw new Exception("Could not locate name, rank and class for " . htmlentities($node_text));
			}
			$data = array(
				'name'		=> utf8_decode($name_node->textContent),
				'level'		=> $row->getAttribute('data-level'),
				'rank'		=> $rank_node->getAttribute('data-raw'),
				'class'		=> $class_node->getAttribute('data-raw')
			);
			// class is now a textual representation.
			$data['class'] = get_class_as_number($data['class']);
			if ($data['level'] >= $this->min_level)
			{
				//$name = utf8_decode($data['name']);
				//$name = utf8_encode ($data['name']);
				$name = $data['name'];
				if (array_search($data['rank'], $ranks) !== FALSE) 
				{
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
				else if (array_key_exists($name, $this->raiders))
				{
					$this->raiders[$name]->update($data);
					$this->raiders[$name]->__status = 'DEMOTED';
				}
			}
		}
	}
}
?>
