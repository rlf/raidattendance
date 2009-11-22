<?php
class mcp_raidattendance {
	var $u_action;
	var $new_config;
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang(array('mods/info_acp_raidattendance', 'mods/info_mcp_raidattendance'));
		
		switch($mode)
		{         
			case 'view':
				$this->showView($id, $mode);
				break;
			default:
				$template->tpl_name = 'mcp_raidattendance_error';
				break;
		}
	}
	// ------------------------------------------------------------------------
	// Mode: view
	// ------------------------------------------------------------------------
	function showView($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->tpl_name = 'mcp_raidattendance_view';
	}
}
?>
