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

include 'constants_raidattendance.' . $phpEx;

/**
 * Returns an associative array with the following syntax:
 * [month-name] 		=> array(
 *   'num_raids' 			=> number of raids
 *   'num_raids_cancelled' 	=> number of raids cancelled
 *   'roles'			=> array(
 *   	'healer'		=> array(
 *   		'avg'			=> average number of healers per raid night
 *   		'classes'		=> array(
 *   			class_num		=> average number of that kind per raid night
 *   		)
 *   	),
 *   	'tank'			=> array(
 *   		'avg'			=> average number of healers per raid night
 *   		'classes'		=> array(
 *   			class_num		=> average number of that kind per raid night
 *   		)
 *   	),
 *   	'melee'			=> array(
 *   		'avg'			=> average number of healers per raid night
 *   		'classes'		=> array(
 *   			class_num		=> average number of that kind per raid night
 *   		)
 *   	),
 *   	'ranged'		=> array(
 *   		'avg'			=> average number of healers per raid night
 *   		'classes'		=> array(
 *   			class_num	=> average number of that kind per raid night
 *   		)
 *   	),
 *   )
 * )
 **/
function get_stats_for_months($starttime, $endtime, $raid_id = 0)
{
	global $db, $error;
	$arr = array();
	$role_class = array();
	// Find total signons per raid...
	$sql = "SELECT count(n.night) sum, n.night night, DATE_FORMAT(STR_TO_DATE(n.night,'%Y%m%d'),'%M') m FROM " . RAIDATTENDANCE_TABLE 
		. ' n, ' . RAIDERRAIDS_TABLE . ' rr, ' . RAIDS_TABLE . ' raids'
		. ' WHERE ' . $db->sql_in_set('n.status', array(STATUS_ON, STATUS_SUBSTITUTE)) 
		. " AND n.night >= '$starttime' AND n.night <= '$endtime'" 
		. ' AND n.raider_id = rr.raider_id AND rr.raid_id = ' . $raid_id
		. " AND raids.id = rr.raid_id AND raids.days LIKE CONCAT('%',LOWER(DATE_FORMAT(STR_TO_DATE(n.night,'%Y%m%d'),'%a')),'%')"
		. ' AND n.night NOT IN (SELECT n2.night FROM ' . RAIDATTENDANCE_TABLE . ' n2 WHERE n2.raid_id = ' . $raid_id . ' AND n2.status = ' . STATUS_CANCELLED . ')'
		. ' GROUP BY m, n.night';
	$res = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($res)) 
	{
		if (!is_array($arr[$row['m']])) 
		{
			$arr[$row['m']] = array(
				'sum' 		=> 0, 
				'count' 	=> 0, 
				'avg'		=> 0,
				'roles' 	=> array()
			);
		}
		$arr[$row['m']]['sum'] = $arr[$row['m']]['sum'] + $row['sum'];
		$arr[$row['m']]['count'] = $arr[$row['m']]['count'] + 1;
	}
	$db->sql_freeresult($res);
	$sql = "SELECT count(n.night) sum, r.role role, r.class class, DATE_FORMAT(STR_TO_DATE(n.night,'%Y%m%d'),'%M') m FROM " 
		. RAIDATTENDANCE_TABLE . ' n, ' 
		. RAIDER_TABLE . ' r, ' 
		. RAIDERRAIDS_TABLE . ' rr, ' . RAIDS_TABLE . ' raids' 
		. ' WHERE ' 
		. $db->sql_in_set('n.status', array(STATUS_ON, STATUS_SUBSTITUTE)) 
		. " AND n.raider_id = r.id AND n.night >= '$starttime' AND n.night <= '$endtime' " 
		. ' AND n.raider_id = rr.raider_id AND rr.raid_id = ' . $raid_id
		. " AND raids.id = rr.raid_id AND raids.days LIKE CONCAT('%',LOWER(DATE_FORMAT(STR_TO_DATE(n.night,'%Y%m%d'),'%a')),'%') "
		. ' AND n.night NOT IN (SELECT n2.night FROM ' . RAIDATTENDANCE_TABLE . ' n2 WHERE n2.raid_id = ' . $raid_id . ' AND n2.status = ' . STATUS_CANCELLED . ')'
		. ' GROUP BY m, role, class';
	$res = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($res)) 
	{
		$m = $row['m'];
		if ($arr[$m]['count'] > 0) 
		{
			$arr[$m]['avg'] = $arr[$m]['sum']/$arr[$m]['count'];
		}
		$role =  '' . $row['role'];
		if (!isset($arr[$m]['roles'][$role]))
		{
			$arr[$m]['roles'][$role] = array('sum' => 0, 'avg' => 0, 'classes' => array(), 'count' => 0);
		}
		$class = '' . $row['class'];
		if (!isset($arr[$m]['roles'][$role]['classes'][$class]))
		{
			$arr[$m]['roles'][$role]['classes'][$class] = array('sum' => 0, 'avg' => 0, 'count' => 0);
		}
		if (!isset($role_class[$role]))
		{
			$role_class[$role] = array();
		}
		if (!isset($role_class[$role][$class]))
		{
			$role_class[$role][$class] = 1;
		}
		$arr[$m]['roles'][$role]['sum'] = $arr[$m]['roles'][$role]['sum'] + $row['sum'];
		$arr[$m]['roles'][$role]['classes'][$class]['sum'] = $row['sum'];
	}
	$db->sql_freeresult($res);
	// NOTE: This will not count "static sign offs"
	$sql = "SELECT r.role role, r.class class, count(r.class) cnt FROM " 
		. RAIDER_TABLE . ' r, ' 
		. RAIDERRAIDS_TABLE . ' rr, ' . RAIDS_TABLE . ' raids' 
		. ' WHERE ' 
		. ' rr.raid_id = ' . $raid_id
		. " AND raids.id = rr.raid_id"
		. ' AND rr.raider_id = r.id'
		. ' GROUP BY role, class';
	$res = $db->sql_query($sql);
	while ($row = $db->sql_fetchrow($res))
	{
		foreach ($arr as $m => &$rolearr) {
			$role =  '' . $row['role'];
			if (!isset($rolearr['roles'][$role]))
			{
				$rolearr['roles'][$role] = array('sum' => 0, 'avg' => 0, 'classes' => array(), 'count' => 0);
			}
			$class = '' . $row['class'];
			if (!isset($rolearr['roles'][$role]['classes'][$class]))
			{
				$rolearr['roles'][$role]['classes'][$class] = array('sum' => 0, 'avg' => 0, 'count' => 0);
			}
			$rolearr['roles'][$role]['count'] = $rolearr['roles'][$role]['count'] + $row['cnt'];
			$rolearr['roles'][$role]['classes'][$class]['count'] = $row['cnt'];
		}
	}
	$db->sql_freeresult($res);
	// Now, calc the avg. for roles + classes
	foreach ($arr as $m => &$montharr)
	{
		foreach ($montharr['roles'] as $role => &$rolearr)
		{
			if ($montharr['count'] > 0) 
			{
				$rolearr['avg'] = $rolearr['sum']/$montharr['count'];
			}
			foreach ($role_class[$role] as $class => $num)
			{
				if (!isset($rolearr['classes'][$class]))
				{
					$rolearr['classes'][$class] = array('sum' => 0, 'avg' => 0, 'count' => 0);
				}
				if ($montharr['count'] > 0) 
				{
					$rolearr['classes'][$class]['avg'] = $rolearr['classes'][$class]['sum']/$montharr['count'];
				}
			}
		}
	}
	return $arr;
}

?>
