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

class raider_extractor 
{
	/**
	 * Returns an array of raiders from the specified armory.
	 * All raiders are specified to be those with a rank as specified in 
	 * the rank-array, and of minimum the supplied level.
	 **/
	function get_raider_list($armory_link, $realm, $guild, $ranks, $min_level) 
	{
		global $phpbb_root_path;
		$this->raiders = array();
		$this->min_level = $min_level;
		$this->ranks = $ranks;
		$url = $armory_link . '/guild-info.xml?r=' . urlencode($realm) . '&gn=' . urlencode($guild) . '&rhtml=n';
		//$this->raiders['url'] = array('raider_name' => $url);

		$data = file_get_contents($url, false);
		//$this->raiders['data'] = array('raider_name' => htmlentities($data));
		if ($data === FALSE) 
		{
			$this->raiders['error'] = array('raider_name' => 'Error opening url : ' . $url);
		}
		$parser = xml_parser_create('UTF-8');
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, false);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_set_element_handler($parser,  array($this, 'startElem'), array($this, 'endElem'));
		xml_parse($parser, $data);
		xml_parser_free($parser);
		usort($this->raiders, 'raider_cmp');
		return $this->raiders;
	}

	function startElem($parser, $name, array $attrs)
	{
		if ($name == 'character') 
		{
			$data = array(
				'raider_name' 	=> $attrs['name'],
				'level'			=> $attrs['level'],
				'rank'			=> $attrs['rank'],
				'class'			=> $attrs['classId']
			);
			if ($data['level'] >= $this->min_level && array_search($data['rank'], $this->ranks) !== FALSE) 
			{
				$this->raiders[$attrs['name']] = $data;
			}
		}
	}
	function endElem($parser, $name)
	{
		// don't care
	}
}
function raider_cmp($a, $b)
{
	$cmp = strcmp($a['raider_name'], $b['raider_name']);
	if ($cmp === 0) 
	{
		$cmp = $a['rank'] - $b['rank'];
	}
	return $cmp;
}
?>
