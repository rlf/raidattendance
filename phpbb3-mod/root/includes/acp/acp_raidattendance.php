<?php
class acp_raidattendance {
	var $u_action;
	var $new_config;
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$user->add_lang(array('viewtopic', 'mods/info_acp_raidattendance'));
		
		switch($mode)
		{         
			case 'settings':
				$this->settings($id, $mode);
				break;      
			case 'sync':
				$this->showSync($id, $mode);
				break;
			default:
				$template->tpl_name = 'acp_raidattendance_error';
				break;
		}
	}
	// ------------------------------------------------------------------------
	// Mode: settings
	// ------------------------------------------------------------------------
	function settings($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$this->tpl_name = 'acp_raidattendance_settings';

		$display_vars = array(
			'title'	=> 'ACP_RAIDATTENDANCE_SETTINGS',
			'vars'	=> array(
				'legend1'					=> 'GUILD_SETTINGS',
				'raidattendance_guild_name'	=> array('lang' => 'GUILD_NAME',	'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
				'raidattendance_realm_name'	=> array('lang' => 'REALM_NAME',	'validate' => 'string',	'type' => 'text:40:255', 'explain' => false),
				'raidattendance_armory_link'=> array('lang' => 'ARMORY_LINK',	'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),

				'legend2'					=> 'FORUM_SETTINGS',
				'raidattendance_forum_name'	=> array('lang' => 'FORUM_NAME',	'validate' => 'string',	'type' => 'text:40:255', 'explain' => true),

				'legend3'					=> 'RAID_SETTINGS',
				'raidattendance_raid_night_mon'		=> array('lang' => 'RAID_NIGHT_MON','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_tue'		=> array('lang' => 'RAID_NIGHT_TUE','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_wed'		=> array('lang' => 'RAID_NIGHT_WED','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_thu'		=> array('lang' => 'RAID_NIGHT_THU','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_fri'		=> array('lang' => 'RAID_NIGHT_FRI','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_sat'		=> array('lang' => 'RAID_NIGHT_SAT','validate' => 'bool', 'type' => 'radio', 'explain' => false),
				'raidattendance_raid_night_sun'		=> array('lang' => 'RAID_NIGHT_SUN','validate' => 'bool', 'type' => 'radio', 'explain' => false),
			)
		);
		$this->saveConfig($display_vars);
	}
	// 
	// Validate and Save Config Data
	// 
	function saveConfig($display_vars)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$action	= request_var('action', '');
		$submit = (isset($_POST['submit'])) ? true : false;

		$form_key = 'acp_raidattendance';
		add_form_key($form_key);
		
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = (isset($_REQUEST['config'])) ? utf8_normalize_nfc(request_var('config', array('' => ''), true)) : $this->new_config;
		$error = array();

		// We validate the complete config if whished
		validate_config_vars($display_vars['vars'], $cfg_array, $error);

		if ($submit && !check_form_key($form_key))
		{
			$error[] = $user->lang['FORM_INVALID'];
		}
		// Do not write values if there is an error
		if (sizeof($error))
		{
			$submit = false;
		}

		// We go through the display_vars to make sure no one is trying to set variables he/she is not allowed to...
		foreach ($display_vars['vars'] as $config_name => $null)
		{
			if (!isset($cfg_array[$config_name]) || strpos($config_name, 'legend') !== false)
			{
				continue;
			}

			if ($config_name == 'auth_method')
			{
				continue;
			}

			$this->new_config[$config_name] = $config_value = $cfg_array[$config_name];

			if ($submit)
			{
				set_config($config_name, $config_value);
			}
		}		

		if ($submit)
		{
			add_log('admin', 'LOG_CONFIG_' . strtoupper($mode));

			trigger_error($user->lang['CONFIG_UPDATED'] . adm_back_link($this->u_action));
		}

		$this->page_title = $display_vars['title'];

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang[$display_vars['title']],
			'L_TITLE_EXPLAIN'	=> $user->lang[$display_vars['title'] . '_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);

		// Output relevant page
		foreach ($display_vars['vars'] as $config_key => $vars)
		{
			if (!is_array($vars) && strpos($config_key, 'legend') === false)
			{
				continue;
			}

			if (strpos($config_key, 'legend') !== false)
			{
				$template->assign_block_vars('options', array(
					'S_LEGEND'		=> true,
					'LEGEND'		=> (isset($user->lang[$vars])) ? $user->lang[$vars] : $vars)
				);

				continue;
			}

			$type = explode(':', $vars['type']);

			$l_explain = '';
			if ($vars['explain'] && isset($vars['lang_explain']))
			{
				$l_explain = (isset($user->lang[$vars['lang_explain']])) ? $user->lang[$vars['lang_explain']] : $vars['lang_explain'];
			}
			else if ($vars['explain'])
			{
				$l_explain = (isset($user->lang[$vars['lang'] . '_EXPLAIN'])) ? $user->lang[$vars['lang'] . '_EXPLAIN'] : '';
			}

			$content = build_cfg_template($type, $config_key, $this->new_config, $config_key, $vars);

			if (empty($content))
			{
				continue;
			}

			$template->assign_block_vars('options', array(
				'KEY'			=> $config_key,
				'TITLE'			=> (isset($user->lang[$vars['lang']])) ? $user->lang[$vars['lang']] : $vars['lang'],
				'S_EXPLAIN'		=> $vars['explain'],
				'TITLE_EXPLAIN'	=> $l_explain,
				'CONTENT'		=> $content,
				)
			);

			unset($display_vars['vars'][$config_key]);
		}
		
	}
	// ------------------------------------------------------------------------
	// Mode: sync
	// ------------------------------------------------------------------------
	function showSync($id, $mode)
	{
		global $db, $user, $auth, $template;
		global $config, $phpbb_root_path, $phpbb_admin_path, $phpEx;

		$error = array();
		$armory = $config['raidattendance_armory_link'];
		$guild = $config['raidattendance_guild_name'];
		$realm = $config['raidattendance_realm_name'];

		$this->tpl_name = 'acp_raidattendance_sync';

		include_once($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);
		$extractor = new raider_extractor();
		$rows = $extractor->get_raider_list($armory, $realm, $guild, array(1, 3), 80);

		foreach ($rows as $row) {
			$template->assign_block_vars('raiders', array(
				'RAIDER_NAME'		=> $row['raider_name'],
				'RANK'				=> $user->lang['RANK_' . $row['rank']],
				'LEVEL'				=> $row['level'],
				'CLASS'				=> $user->lang['CLASS_' . $row['class']],
			));
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['ACP_RAIDATTENDANCE_SYNC'],
			'L_TITLE_EXPLAIN'	=> $user->lang['ACP_RAIDATTENDANCE_SYNC_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br />', $error),

			'U_ACTION'			=> $this->u_action)
		);
	}
}
?>
