<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/stat_raidattendance.' . $phpEx);
include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

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
foreach ($arr as $m => $marr)
{
	$template->assign_block_vars('months', array(
		'NAME'			=> $m,
		'NUM_RAIDS'		=> $marr['count'],
		'AVG'			=> sprintf($user->lang['FRACTION_FORMAT'], $marr['avg']),
	));
	foreach ($marr['roles'] as $role => $rarr)
	{
		if ($role == 0)
		{
			$role = ROLE_UNASSIGNED;
		}
		$template->assign_block_vars('months.roles', array(
			'ROLE_ID'		=> $role,
			'NAME'			=> $user->lang['ROLE_' . $role],
			'AVG'			=> sprintf($user->lang['FRACTION_FORMAT'], $rarr['avg']),
			'COUNT'		=> sprintf($user->lang['FRACTION_FORMAT'], $rarr['count']),
		));
		foreach ($rarr['classes'] as $class => $carr)
		{
			$template->assign_block_vars('months.roles.classes', array(
				'CLASS_ID'	=> $class,
				'NAME'		=> $user->lang['CLASS_' . $class],
				'AVG'		=> sprintf($user->lang['FRACTION_FORMAT'], $carr['avg']),
				'COUNT'		=> sprintf($user->lang['FRACTION_FORMAT'], $carr['count']),
			));
		}
	}
}
$template->assign_vars(array(
	'RAID_ID'		=> $raid_id,
	'FORUM_ID'		=> $forum_id,
	'S_ERROR'		=> sizeof($error) ? true : false,
	'ERROR_MSG'		=> implode('<br/>', $error),
	));

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

function month_sort($a, $b)
{
	$a1 = strptime($a, '%B');
	$b1 = strptime($b, '%B');
	return $a1['tm_mon'] - $b1['tm_mon'];
}
?>

