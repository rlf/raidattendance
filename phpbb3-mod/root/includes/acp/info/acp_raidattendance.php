<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  
class acp_raidattendance_info 
{
	function module()
	{
		return array(
			'filename'	=> 'acp_raidattendance',
			'title'		=> 'ACP_RAIDATTENDANCE',
			'version'	=> '1.5.1',
			'cat'		=> 'ACP_BOARD_CONFIGURATION',
			'modes'		=> array(
			    'settings' => array(
					'title'	=> 'ACP_RAIDATTENDANCE_SETTINGS',
					'auth'	=> 'acl_a_raidattendance',
					'cat'	=> array('ACP_CAT_RAIDATTENDANCE'),
				),
			    'sync' => array(
					'title'	=> 'ACP_RAIDATTENDANCE_SYNC',
					'auth'	=> 'acl_a_raidattendance',
					'cat'	=> array('ACP_CAT_RAIDATTENDANCE'),
				),
			)
		);
	}
	function install()
	{
	}
	function uninstall()
	{
	}
}
?>
