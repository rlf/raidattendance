<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  

global $phpbb_root_path, $phpEx;
include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

global $error, $success;
class acp_raidattendance {
	var $u_action;
	var $new_config;
	function main($id, $mode)
	{
		global $db, $user, $auth, $template;

		$user->add_lang(array('viewtopic', 'mods/info_acp_raidattendance', 'mods/mod_raidattendance'));
		
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

				'legend4'		=> 'RAIDER_RANKS',
				'raidattendance_min_level'		=> array('lang' => 'MIN_LEVEL',	'validate' => 'int', 'type' => 'text:2:2', 'explain' => true),
				'raidattendance_raider_rank0'	=> array('lang' => 'RANK_0',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank1'	=> array('lang' => 'RANK_1',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank2'	=> array('lang' => 'RANK_2',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank3'	=> array('lang' => 'RANK_3',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank4'	=> array('lang' => 'RANK_4',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank5'	=> array('lang' => 'RANK_5',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank6'	=> array('lang' => 'RANK_6',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank7'	=> array('lang' => 'RANK_7',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank8'	=> array('lang' => 'RANK_8',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
				'raidattendance_raider_rank9'	=> array('lang' => 'RANK_9',	'type' => 'custom', 'explain' => false, 'function' => 'raider_rank'),
			)
		);
		$this->saveConfig($display_vars);
	}
	// 
	// Validate and Save Config Data
	// 
	function saveConfig($display_vars)
	{
		global $db, $user, $auth, $template, $config, $error;
		if (!is_array($error))
		{
			$error = array();
		}

		$submit = request_var('submit', '');

		$form_key = 'acp_raidattendance';
		add_form_key($form_key);
		
		if (isset($display_vars['lang']))
		{
			$user->add_lang($display_vars['lang']);
		}

		$this->new_config = $config;
		$cfg_array = request_var('config', array(''=>''));
		$cfg_array = sizeof($cfg_array) > 1 ? utf8_normalize_nfc($cfg_array) : $this->new_config;

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
		// Add the "names"...
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
		// Set the names
		if ($submit) 
		{		
			for ($ix = 0; $ix < 10; ++$ix) 
			{
				$config_name = 'raidattendance_raider_rank' . $ix . '_name';
				$config_value = $cfg_array[$config_name];
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
		global $db, $user, $auth, $template, $config;
		global $error, $success;
		$this->tpl_name = 'acp_raidattendance_sync';
		$resync	= request_var('resync', '');
		$save = request_var('save', '');
		$delete = request_var('delete', '');

		$raider_db = new raider_db();
		$rows = array();
		$raider_db->get_raider_list($rows);
		$this->merge_data($rows);
		if ($resync) 
		{
			$this->resync($rows);
		}
		if ($save or $resync) 
		{
			$raider_db->save_raider_list($rows);
		}
		else if ($delete)
		{
			$raider_db->delete_checked_raiders($rows);
		}
		$rowno = 0;
		$users = $this->get_user_list();
		foreach ($rows as $name => $raider) {
			$template->assign_block_vars('raiders', array(
				'ROWNO'				=> $rowno+1,
				'ID'				=> $raider->id,
				'NAME'				=> $raider->name,
				'RANK'				=> $raider->get_rank_name(),
				'LEVEL'				=> $raider->level,
				'CLASS'				=> $user->lang['CLASS_' . $raider->class],
				'USER'				=> $raider->user_id,
				'STATUS'			=> $raider->get_status(),
				'ROW_CLASS'			=> $rowno % 2 == 0 ? 'even' : 'uneven',
				'USER_OPTIONS'		=> $this->get_user_options($users, $raider->name, $raider->user_id),
				'CHECKED'			=> $raider->is_checked() ? ' checked' : '',

				'CSS_CLASS'			=> 'class_' . $raider->class,
			));
			$rowno++;
		}
		if (!is_array($error)) 
		{
			$error = array();
		}
		if (!is_array($success))
		{
			$success = array();
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['ACP_RAIDATTENDANCE_SYNC'],
			'L_TITLE_EXPLAIN'	=> $user->lang['ACP_RAIDATTENDANCE_SYNC_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br/>', $error),

			'S_SUCCESS'			=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'		=> implode('<br/>', $success),

			'U_ACTION'			=> $this->u_action,
			'OPTIONS_NEW_RANK'	=> $this->get_rank_options(),
			'OPTIONS_NEW_CLASS'	=> $this->get_class_options(),
			'OPTIONS_NEW_USER'	=> $this->get_user_options($users, ''),
		)
		);
	}
	function resync(&$old_rows)
	{
		global $config;
		$armory = $config['raidattendance_armory_link'];
		$guild = $config['raidattendance_guild_name'];
		$realm = $config['raidattendance_realm_name'];
		$min_level = $config['raidattendance_min_level'];

		$extractor = new raider_armory();
		$extractor->get_raider_list($armory, $realm, $guild, get_raider_ranks(), $min_level, $old_rows);
	}
	function get_class_options()
	{
		global $user;
		$s = '';
		for ($i = 0; $i <= 11; ++$i)
		{
			$class_name = $user->lang['CLASS_' . $i];
			if ($class_name)
			{
				$s .= '<option value="' . $i . '">' . $class_name . '</option>';
			}
		}
		return $s;
	}
	function get_rank_options()
	{
		global $user, $config;
		$s = '';
		$basekey = 'raidattendance_raider_rank';
		for ($i = 0; $i < 10; ++$i)
		{
			$rank_name = $config[$basekey . $i . '_name'];
			$rank_name = $rank_name ? $rank_name : $user->lang['RANK_' . $i];
			$s .= '<option value="' . $i . '">' . $rank_name . '</option>';
		}
		return $s;
	}
	function get_user_options($users, $raider_name, $user_id = 0)
	{
		$s = '';
		foreach ($users as $usr)
		{
			$id = $usr['id'];
			$name = $usr['name'];
			$selected = '';
			if ($user_id == $id)
			{
				$selected = ' selected';
			}
			else if ($user_id == 0 && $raider_name == $name)
			{
				$selected = ' selected';
			}
			$s .= '<option value="' . $usr['id'] . '"' . $selected . '>' . $name . '</option>';
		}
		return $s;
	}
	function get_user_list()
	{
		global $db, $user;
		$sql = 'SELECT user_id, username FROM ' . USERS_TABLE . " WHERE user_email <> ''";
		$result = $db->sql_query($sql);
		$users = array(array('id' => 0, 'name' => $user->lang['UNKNOWN_USER']));
		while ($row = $db->sql_fetchrow($result)) 
		{
			$users[] = array('id' => $row['user_id'], 'name' => $row['username']);
		}
		$db->sql_freeresult($result);
		return $users;
	}
	/** 
	 * Merges the rows with whatever was supplied in the POST.
	 **/
	function merge_data(&$rows) 
	{
		global $error;
		$user_ids = array();
		$checked = array();
		$user_ids = request_var('user_id', array());
		$checked = request_var('checked', array());
		$new_raider = null;
		$new_name = request_var('new_name', '');
		if (strlen($new_name) > 0)
		{
			$row = array(
				'name' => $new_name,
				'level' => request_var('new_level', 80),
				'rank' => request_var('new_rank', 9),
				'class' => request_var('new_class', 1),
				'user_id' => request_var('new_user_id', 0),
			);
			// TODO verify input!
			$new_raider = new raider($row);
		}
		foreach ($rows as $raider)
		{
			$name = $raider->name;
			if (isset($user_ids[$raider->id])) 
			{
				$raider->set_user_id($user_ids[$raider->id]);
			}
			if (isset($checked[$raider->id]))
			{
				$raider->set_checked(true);
			}
			else
			{
				$raider->set_checked(false);
			}
			if ($new_raider and $name == $new_raider->name)
			{
				$error[] = sprintf($user->lang['RAIDER_ALREADY_EXISTS'], $name);
				$new_raider = null;
			}
		}
		if ($new_raider)
		{
			$rows[$new_raider->name] = $new_raider;
		}
	}
}
function raider_rank($default, $key)
{
	global $user, $config;
	$rank_name = $config[$key . '_name'];
	$checked = $config[$key] ? true : false;

	$name = 'config[' . $key . ']';
	$name_yes	= ($checked) ? ' checked="checked"' : '';
	$name_no	= (!$checked) ? ' checked="checked"' : '';

	$tpl_no = '<label><input type="radio" name="' . $name . '" value="0"' . $name_no . ' class="radio" /> ' . $user->lang['NO'] . '</label>';
	$tpl_yes = '<label><input type="radio" id="' . $name . '" name="' . $name . '" value="1"' . $name_yes . ' class="radio" /> ' . $user->lang['YES'] . '</label>';

	return '<input id="config[' . $key . '_name]" name="config[' . $key . '_name]" type="text" value="' . $rank_name . '"/>' .
		'&nbsp;' . $user->lang['IS_RAIDER'] . '&nbsp;' .
		$tpl_yes . $tpl_no;
}

?>