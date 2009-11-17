<?php
class acp_raidattendance_info {
	function module()
	{
		return array(
			'filename'	=> 'acp_raidattendance',
			'title'		=> 'ACP_RAIDATTENDANCE',
			'version'	=> '1.0',
			'modes'		=> array(
			    'settings' => array(
					'title'	=> 'ACP_RAIDATTENDANCE',
					'auth'	=> '',
					'cat'	=> array('ACP_DOT_MODS')
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
