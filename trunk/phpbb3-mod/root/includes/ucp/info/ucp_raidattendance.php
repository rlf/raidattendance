<?php
class ucp_raidattendance_info {
	function module()
	{
		return array(
			'filename'	=> 'ucp_raidattendance',
			'title'		=> 'UCP_RAIDATTENDANCE',
			'version'	=> '0.0.4',
			'modes'		=> array(
			    'config' => array(
					'title'	=> 'UCP_RAIDATTENDANCE_CONFIG',
					'auth'	=> 'acl_u_raidattendance',
					'cat'	=> array('UCP_MAIN')
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
