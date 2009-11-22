<?php
class mcp_raidattendance_info {
	function module()
	{
		return array(
			'filename'	=> 'mcp_raidattendance',
			'title'		=> 'MCP_RAIDATTENDANCE',
			'version'	=> '0.0.1',
			'modes'		=> array(
				'view' => array(
					'title' => 'MCP_RAIDATTENDANCE_VIEW',
					'auth'	=> 'acl_u_raidattendance',
					'cat'	=> array('MCP_CAP_RAIDATTENDANCE'),
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
