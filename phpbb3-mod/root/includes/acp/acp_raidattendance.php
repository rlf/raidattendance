<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  
define('DAYS', 'mon:tue:wed:thu:fri:sat:sun');
define('NL', "\n");

global $phpbb_root_path, $phpEx;
include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

global $error, $success;
class acp_raidattendance 
{
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
			case 'wws':
				$this->wwsSync($id, $mode);
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
				'raidattendance_wws_guild_id'=> array('lang' => 'WWS_GUILD_ID',	'validate' => 'int',	'type' => 'text:5:5', 'explain' => true),

				'legend2'					=> 'FORUM_SETTINGS',
				'raidattendance_forum_id'	=> array('lang' => 'FORUM_NAME',	'validate' => 'string',	'type' => 'custom', 'explain' => true, 'function' => 'forum_id'),

				'legend3'					=> 'RAIDS',
				'raidattendance_raidsetup'	=> array('lang' => 'RAID_SETUP',	'type' => 'custom', 'explain' => true, 'function' => 'raid_setup'),
				'raidattendance_raid_time'	=> array('lang' => 'RAID_TIME', 	'validate' => 'time', 'type' => 'text:5:5', 'explain' => true),

				'legend4'						=> 'RAIDER_RANKS',
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
		// Save non-"normal" configs
		if ($submit) 
		{		
			for ($ix = 0; $ix < 10; ++$ix) 
			{
				$config_name = 'raidattendance_raider_rank' . $ix . '_name';
				$config_value = $cfg_array[$config_name];
				set_config($config_name, $config_value);
			}
			// save the raids
			$this->saveRaidSetup();
			add_log('admin', 'LOG_CONFIG_RAIDATTENDANCE_SETTINGS');

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

	function saveRaidSetup()
	{
		global $db, $error, $success, $user;
		$raids = request_var('raid', array(0=>array(''=>'')));
		foreach ($raids as $id => $raid)
		{
			//$raid = $raids[$i];
			$days = array();
			foreach (explode(':', DAYS) as $day)
			{
				if ($raid[$day]) 
				{
					$days[] = $day;
				}
			}
			$data = array(
				'name'	=> $db->sql_escape($raid['name']),
				'days'	=> implode(':', $days),
			);
			$sql = false;
			if ($id > 0 && $data['name'] != '')
			{
				// Update
				$sql = 'UPDATE ' . RAIDS_TABLE . ' SET ' . $db->sql_build_array('UPDATE', $data) . ' WHERE id=' . $id;
			}	
			else if ($id > 0 && $data['name'] == '')
			{
				// Delete
				$sql = 'DELETE FROM ' . RAIDERRAIDS_TABLE . ' WHERE raid_id=' . $id;
				$db->sql_query($sql);
				$sql = 'DELETE FROM ' . RAIDS_TABLE . ' WHERE id=' . $id;
			}
			else if ($data['name'] != '')
			{
				// Add
				$sql = 'INSERT INTO ' . RAIDS_TABLE . ' ' . $db->sql_build_array('INSERT', $data);
			}
			if ($sql)
			{
				$db->sql_query($sql);
				if ($db->sql_affectedrows() > 0) 
				{
					$success[] = sprintf($user->lang['RAID_SAVED'], $data['name']);
				}
				else
				{
					$error[] = sprintf($user->lang['RAID_SAVED_ERROR'], $data['name']);
				}
			}
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
		$raider_db->get_raider_list($rows, false, 3);
		$this->merge_data($rows);
		if ($resync) 
		{
			$this->resync($rows);
		}
		if ($save or $resync) 
		{
			$raider_db->save_raider_list($rows);
			if ($save) 
			{
				$success[] = get_text('SAVED');
			}
		}
		else if ($delete)
		{
			$raider_db->delete_checked_raiders($rows);
		}
		$rowno = 0;
		$users = $this->get_user_list();
		$roles = $this->get_raider_role_list();
		$raids = get_raids();
		foreach ($rows as $name => $raider) {
			$raider_raids = $raider->get_raids();
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
				'ROLE_OPTIONS'		=> $this->get_raider_role_options($roles, $raider),
				'CHECKED'			=> $raider->is_checked() ? ' checked' : '',

				'CSS_CLASS'			=> 'class_' . $raider->class,
			));
			foreach ($raids as $ix => $raid)
			{
				$in_raid = in_array($raid['id'], $raider_raids);
				$template->assign_block_vars('raiders.raids', array(
					'DEBUG'			=> implode(':',$raider_raids).'['.$in_raid.']',
					'ID'			=> $raid['id'],
					'S_IN_RAID'		=> $in_raid,
					'CHECKED'		=> $in_raid ? ' checked' : '',
				));
				if ($in_raid)
				{
					$raid['sum'] = (isset($raid['sum']) ? $raid['sum'] : 0) + 1;
				}
			}
			$rowno++;
		}
		foreach ($raids as $raid)
		{
			$template->assign_block_vars('raids', array(
				'ID'	=> $raid['id'],
				'SUM'	=> $raid['sum'],
				'NAME'	=> $raid['name'],
				));
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
		$result_html = '';
		for ($i = 0; $i <= 11; ++$i)
		{
			$class_name = $user->lang['CLASS_' . $i];
			if ($class_name)
			{
				$result_html .= '<option value="' . $i . '">' . $class_name . '</option>';
			}
		}
		return $result_html;
	}

	function get_rank_options()
	{
		global $user, $config;
		$result_html = '';
		$basekey = 'raidattendance_raider_rank';
		for ($i = 0; $i < 10; ++$i)
		{
			$rank_name = $config[$basekey . $i . '_name'];
			$rank_name = $rank_name ? $rank_name : $user->lang['RANK_' . $i];
			$result_html .= '<option value="' . $i . '">' . $rank_name . '</option>';
		}
		return $result_html;
	}

	function get_user_options($users, $raider_name, $user_id = 0)
	{
		$result_html = '';
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
			$result_html .= '<option value="' . $id . '"' . $selected . '>' . $name . '</option>';
		}
		return $result_html;
	}

	function get_user_list()
	{
		global $db, $user;
		$sql = 'SELECT user_id, username FROM ' . USERS_TABLE . ' WHERE user_type <> ' . USER_IGNORE;
		$result = $db->sql_query($sql);
		$users = array(array('id' => 0, 'name' => $user->lang['UNKNOWN_USER']));
		while ($row = $db->sql_fetchrow($result)) 
		{
			$users[] = array('id' => $row['user_id'], 'name' => $row['username']);
		}
		$db->sql_freeresult($result);
		return $users;
	}

	function get_raider_role_list()
	{
		global $user;
		$roles = array();
		for ($i = 0; $i < 5; $i++)
		{
			$roles[] = array('id' => $i, 'name' => $user->lang['ROLE_' . $i]);
		}
		return $roles;
	}
	function get_raider_role_options($roles, $raider)
	{
		$result_html = '';
		$current_role = $raider->role;
		if ($current_role == 0) 
		{
			$current_role = ROLE_UNASSIGNED;
		}
		if ($current_role == ROLE_UNASSIGNED)
		{
			// Try to guess the role
			switch ($raider->class)
			{
			case CLASS_WARLOCK:
			case CLASS_MAGE:
			case CLASS_HUNTER: 
				$current_role = ROLE_RANGED_DPS; 
				break;
			case CLASS_ROGUE:
				$current_role = ROLE_MELEE_DPS;
				break;
			}
		}
		foreach ($roles as $role)
		{
			$id = $role['id'];
			$name = $role['name'];
			$selected = '';
			if ($id == $current_role) 
			{
				$selected = ' selected';
			}
			$result_html = $result_html . '<option value="' . $id . '"' . $selected . '>' . $name . '</option>';
		}
		return $result_html;
	}

	/** 
	 * Merges the rows with whatever was supplied in the POST.
	 **/
	function merge_data(&$rows) 
	{
		global $error;
		$user_ids = request_var('user_id', array(0=>0));
		$checked = request_var('checked', array(0=>'false'));
		$raider_role = request_var('raider_role', array(0=>0));
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
		// Handle Raids
		$raiderraid = request_var('raiderraid', array(0=>array(0=>'false')));
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
			if (isset($raiderraid[$raider->id])) 
			{
				$raider->raids = array();
				foreach ($raiderraid[$raider->id] as $raid_id => $check)
				{
					$raider->raids[] = $raid_id;
				}
			}
			if (isset($raider_role[$raider->id])) 
			{
				$raider->role = $raider_role[$raider->id];
			}
		}
		if ($new_raider)
		{
			$rows[$new_raider->name] = $new_raider;
		}
	}

	// ------------------------------------------------------------------------
	// Mode: wws
	// ------------------------------------------------------------------------
	function wwsSync($id, $mode)
	{
		global $db, $user, $auth, $template, $config;
		global $error, $success;
		if (!is_array($error)) 
		{
			$error = array();
		}
		if (!is_array($success))
		{
			$success = array();
		}
		
		$this->tpl_name = 'acp_raidattendance_wws';
		$resync	= request_var('resync', '');
		$save = request_var('save', '');
		$delete = request_var('delete', '');

		if ($delete && isset($_POST['checked'])) 
		{
			$checked = request_var('checked', array(0=>''));
			wws_delete($checked);
		}
		$wws_db = new wws_db();
		if ($resync)
		{
			$wws_db->refetch($list);
			// TODO: Update the attendance based on $list[]->raiders
		}
		$list = $wws_db->get_raid_list();

		$rowno = 0;
		foreach ($list as $raid) {
			$template->assign_block_vars('raids', array(
				'ROWNO'				=> $rowno+1,
				'ID'				=> $raid->id,
				'RAID'				=> $raid->raid,
				'WWS_ID'			=> $raid->wws_id,
				'SYNCED'			=> strftime(get_text('DATE_TIME_FORMAT'), $raid->synced),
				'RAIDERS'			=> $raid->get_raiders(),
			));
			$rowno++;
		}

		$template->assign_vars(array(
			'L_TITLE'			=> $user->lang['ACP_RAIDATTENDANCE_WWS'],
			'L_TITLE_EXPLAIN'	=> $user->lang['ACP_RAIDATTENDANCE_WWS_EXPLAIN'],

			'S_ERROR'			=> (sizeof($error)) ? true : false,
			'ERROR_MSG'			=> implode('<br/>', $error),

			'S_SUCCESS'			=> (sizeof($success)) ? true : false,
			'SUCCESS_MSG'		=> implode('<br/>', $success),

			'U_ACTION'			=> $this->u_action,
		)
		);		
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

function forum_id($default, $key)
{
	global $user, $config, $db;
	$current_id = $config[$key];

	$name = "config[$key]";

	$html = '<select id="' . $key . '" name="' . $name . '" size="1">';

	$sql = 'SELECT forum_name, forum_id, parent_id FROM ' . FORUMS_TABLE . ' ORDER BY forum_name ASC';
	$result = $db->sql_query($sql);
	// NOTE: No structure... perhaps we need it?
	while ($row = $db->sql_fetchrow($result)) 
	{
		$html = $html . '<option value="' . $row['forum_id'] . '"';
		if ($current_id == $row['forum_id']) 
		{
			$html = $html . ' selected';
		}
		$html = $html . '>' . htmlentities($row['forum_name']) . '</option>';
	}
	$db->sql_freeresult($result);
	$html = $html . '</select>';
	return $html;
}

// raid_setup
// Configures the HTML to be used to configure the raid-setup
function raid_setup($default, $key)
{
	global $db, $user;
	$sql = 'SELECT * FROM ' . RAIDS_TABLE . ' ORDER BY id ASC';
	$result = $db->sql_query($sql);
	$html = '<table class="raids" id="raids" name="raids">';
	$html = $html . '<tr><th>' . $user->lang['NAME'] . '</th>';
	foreach (explode(':',DAYS) as $day)
	{
		$html = $html . '<th>' . $user->lang[$day] . '</th>';
	}
	$html = $html . '</tr>';
	while ($row = $db->sql_fetchrow($result))
	{
		$html = $html . get_raid_html_row($row);
	}
	$new_row = array('id' => 0, 'name' => '', 'days' => '');
	$html = $html . get_raid_html_row($new_row);
	$html = $html . '</table>';
	$db->sql_freeresult($result);
	return $html;
}

function get_raid_html_row($row)
{
	$html = '';
	// name, mon,tue,wed,thu,fri,sat,sun
	$id = $row['id'];
	$name = 'raid[' . $id . ']';
	$html = $html . '<tr id="raid_' . $id .'"><td>' . NL;
	$html = $html . '<input type="hidden" name="' . $name . '[id]" id="'. $name . '[id]" value="' . $id . '"/>' . NL;
	$html = $html . '<input type="text" name="' . $name . '[name]" id="' . $name . '[name]" value="' . $row['name'] . '" size="20"/></td>' . NL;
	$days = explode(':', $row['days']);
	$all_days = explode(':', DAYS);
	foreach ($all_days as $day) 
	{
		$key = $name . '[' . $day . ']';
		$html = $html . '<td><input type="checkbox" id="' . $key . '" name="' . $key . '"';
		if (array_search($day, $days) !== FALSE) 
		{
			$html = $html . ' checked';
		}
		$html = $html . '/></td>' . NL;
	}
	$html = $html . '</tr>' . NL;
	return $html;
}

?>
