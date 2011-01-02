<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/raidattendance/stat_raidattendance.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang(array('mods/mod_raidattendance', 'mods/stats_raidattendance'));
if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}

page_header($user->lang['RAID_STATS']);

// Real page start

$template->set_filenames(array(
    'body' => 'stats_raidattendance.html',
));

$error = array();
$starttime = request_var('starttime', 0);
$endtime = request_var('endtime', 0);
$raid_id = request_var('raid_id', 0);
if ($starttime == 0)
{
	$tm = getdate();
	$starttime = mktime(0,0,0, $tm['mon']-3, 0, $tm['year']);
	$starttime = date('Ymd', $starttime);
}
if ($endtime == 0)
{
	$tm = getdate();
	$endtime = mktime(0,0,0, $tm['mon']+1, -1, $tm['year']);
	$endtime = date('Ymd', $endtime);
}
$forum_id = $config['raidattendance_forum_id'];

$arr = get_stats_for_months($starttime, $endtime, $raid_id);

$raids = get_raids();
foreach ($raids as $raid)
{
	$template->assign_block_vars('raids', array(
		'ID'				=> $raid['id'],
		'NAME'				=> $raid['name'],
	));
}

uksort($arr, month_sort);
$graph_data = array('global' => array('chxl' => '0:|'), 'roles' => array());
foreach ($arr as $m => $marr)
{
	$template->assign_block_vars('months', array(
		'NAME'			=> $m,
		'NUM_RAIDS'		=> $marr['count'],
		'NUM_CANCELLED' => $marr['cancelled'],
		'AVG'			=> sprintf($user->lang['FRACTION_FORMAT'], $marr['avg']),
	));
	$graph_data['global']['chxl'] = $graph_data['global']['chxl'] . "$m|";
	foreach ($marr['roles'] as $role => $rarr)
	{
		if ($role == 0)
		{
			$role = ROLE_UNASSIGNED;
		}
		if (!isset($graph_data['roles'][$role]))
		{
			$graph_data['roles'][$role] = array('chdl' => '', 'chm' => 'N*f2*,FF0000,0,,11,,::5|', 'chco' => 'FF0000,', '_classes' => array(), '_min' => 1000, '_max' => 0, 'chd' => 't:');
			// Note: the '|' will have to be truncated later
			$graph_data['roles'][$role]['chdl'] .= $user->lang['ROLE_' . $role] . '|'; 
		}
		// Last ',' will have to be truncated later
		$graph_data['roles'][$role]['chd'] .= sprintf($user->lang['FRACTION_FORMAT'], $rarr['avg']) . ',';
		$template->assign_block_vars('months.roles', array(
			'ROLE_ID'		=> $role,
			'NAME'			=> $user->lang['ROLE_' . $role],
			'AVG'			=> sprintf($user->lang['FRACTION_FORMAT'], $rarr['avg']),
			'COUNT'		=> sprintf($user->lang['FRACTION_FORMAT'], $rarr['count']),
		));
		if ($graph_data['roles'][$role]['_min'] > $rarr['avg']) 
		{
			$graph_data['roles'][$role]['_min'] = $rarr['avg'];
		}
		if ($graph_data['roles'][$role]['_max'] < $rarr['avg']) 
		{
			$graph_data['roles'][$role]['_max'] = $rarr['avg'];
		}
		
		$series_index = 1;
		foreach ($rarr['classes'] as $class => $carr)
		{
			if (!isset($graph_data['roles'][$role]['_classes'][$class]))
			{
				$graph_data['roles'][$role]['chdl'] .= $user->lang['CLASS_' . $class] . '|';
				$class_col = $user->lang['CLASS_COLOR_' . $class];
				$graph_data['roles'][$role]['chco'] .=  $class_col . ',';
				$graph_data['roles'][$role]['_classes'][$class] = array('chd' => '');
				$graph_data['roles'][$role]['chm'] .= 'N*f2*,' . $class_col . ',' . $series_index . ',,11,,::5|';
			}
			$graph_data['roles'][$role]['_classes'][$class]['chd'] .= sprintf($user->lang['FRACTION_FORMAT'], $carr['avg']) . ',';
			$template->assign_block_vars('months.roles.classes', array(
				'CLASS_ID'	=> $class,
				'NAME'		=> $user->lang['CLASS_' . $class],
				'AVG'		=> sprintf($user->lang['FRACTION_FORMAT'], $carr['avg']),
				'COUNT'		=> sprintf($user->lang['FRACTION_FORMAT'], $carr['count']),
			));
			if ($graph_data['roles'][$role]['_min'] > $carr['avg']) 
			{
				$graph_data['roles'][$role]['_min'] = $carr['avg'];
			}
			if ($graph_data['roles'][$role]['_max'] < $carr['avg']) 
			{
				$graph_data['roles'][$role]['_max'] = $carr['avg'];
			}
			$series_index += 1;
		}
	}
}
$template->assign_vars(array(
	'RAID_ID'		=> $raid_id,
	'FORUM_ID'		=> $forum_id,
	'S_ERROR'		=> sizeof($error) ? true : false,
	'ERROR_MSG'		=> implode('<br/>', $error),
	));

// Generate graph-data
foreach ($graph_data['roles'] as $r => &$rarr)
{
	$url = '';
	foreach ($graph_data['global'] as $k => $v)
	{
		$url .= '&' . $k . '=' . substr($v, 0, strlen($v)-1);
	}
	foreach ($rarr as $k => $v)
	{
		if ($k == '_classes') 
		{
			$rarr['chd'] = substr($rarr['chd'], 0, strlen($rarr['chd'])-1);
			foreach ($v as $c => $chd)
			{
				$rarr['chd'] .= '|' . substr($chd['chd'], 0, strlen($chd['chd'])-1);
			}
		}
		else if (substr($k,0,1) != '_') 
		{
			$url .= '&' . $k . '=' . substr($v, 0, strlen($v)-1);
		}
	}
	$url .= '&chds=' . $rarr['_min'] . ',' . $rarr['_max'];
	$url .= '&chxr=1,' . $rarr['_min'] . ',' . $rarr['_max'] . ',0.5';
	$template->assign_block_vars('graphs', array(
		'TITLE'	=> $user->lang['ROLE_' . $r],
		'DATA'	=> $url,
		)
	);
}

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

function month_sort($a, $b)
{
	$a1 = strptime($a, '%B');
	$b1 = strptime($b, '%B');
	return $a1['tm_mon'] - $b1['tm_mon'];
}
?>

