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
define('CLASS_DEATH_KNIGHT', 6);
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
?>
