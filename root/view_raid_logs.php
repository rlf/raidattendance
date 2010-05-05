<?php
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_raidattendance.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();
$user->add_lang(array('mods/logs_raidattendance'));
if ($user->data['user_id'] == ANONYMOUS)
{
    login_box('', $user->lang['LOGIN']);
}

page_header($user->lang['RAID_LOGS']);

// Real page start
global $db;

$template->set_filenames(array(
    'body' => 'view_raid_logs.html',
));

$sql = 'SELECT COUNT(*) total_lines FROM ' . RAIDER_HISTORY_TABLE;
$result = $db->sql_query($sql);
$total_lines = (int) $db->sql_fetchfield('total_lines');
$db->sql_freeresult($result);

$forum_id = $config['raidattendance_forum_id'];
$raids = get_raids();
foreach ($raids as $raid)
{
	$template->assign_block_vars('raids', array(
		'ID'				=> $raid['id'],
		'NAME'				=> $raid['name'],
	));
}
$lines_pr_page = 100;
$start = request_var('start', 0);

$template->assign_vars(array(
	'FORUM_ID'				=> $forum_id,
	'PAGINATION'			=> generate_pagination(append_sid("{$phpbb_root_path}view_raid_logs.$phpEx", "f=$forum_id"), $total_lines, $lines_pr_page, $start),
	));
$sql = 'SELECT raider.name raider_name, user.username username, r.id id, r.time time, r.action action FROM ' . RAIDER_HISTORY_TABLE . ' r JOIN ' . RAIDER_TABLE . ' raider ON r.raider_id = raider.id JOIN ' . USERS_TABLE . ' user ON user.user_id = r.user_id';
$sql2 = "SELECT raid.name raider_name, user.username username, r.id id, r.time time, r.action action FROM " . RAIDER_HISTORY_TABLE . ' r JOIN ' . RAIDS_TABLE . ' raid ON r.raid_id = raid.id JOIN ' . USERS_TABLE . ' user ON user.user_id = r.user_id';
$raider_id = request_var('raider_id', 0);
if ($raider_id > 0)
{
	$sql = $sql . ' WHERE raider.id=' . $raider_id;
}
$sql = $sql . ' UNION ' . $sql2;
$sql = $sql . ' ORDER BY time DESC';

$result = $db->sql_query_limit($sql, $lines_pr_page, $start);
$num = 0;
while ($row = $db->sql_fetchrow($result)) 
{
	$action = $row['action'];
	$action = explode(',', $action);
	if (sizeof($action) == 1) 
	{
		// Old lines - without raid info...
		$action[1] = $user->lang['RAID_UNKNOWN'];
	}
	$template->assign_block_vars('logs', array(
		'ID'			=> $row['id'],
		'USER'			=> $row['username'],
		'RAIDER'		=> $row['raider_name'],
		'TIME'			=> strftime($user->lang['TIMESTAMP_FORMAT'], $row['time']),
		'ACTION'		=> sprintf($user->lang['LOG_' . $action[0]], $action[1]),
		'ROW_CLASS'		=> $num % 2 == 0 ? 'even' : 'uneven',
	));
	$num++;
}
$db->sql_freeresult($result);

$raiders = get_raiders();
foreach ($raiders as $raider)
{
	$template->assign_block_vars('raiders', array(
		'ID'			=> $raider['id'],
		'NAME'			=> $raider['name'],
		'SELECTED'		=> false,
		));
}

// TODO: Add stuff to the template...

make_jumpbox(append_sid("{$phpbb_root_path}viewforum.$phpEx"));
page_footer();

function get_raiders()
{
	global $db, $user;
	$sql = 'SELECT DISTINCT r.id id, r.name name FROM ' . RAIDER_TABLE . ' r, ' . RAIDER_HISTORY_TABLE . ' rh WHERE r.id = rh.raider_id ORDER BY r.name ASC';
	$result = $db->sql_query($sql);
	$raiders = array(array('id'=>0, 'name'=>$user->lang['RAIDERS_ALL']));
	while ($row = $db->sql_fetchrow($result)) 
	{
		$raiders[] = $row;
	}
	$db->sql_freeresult($result);
	return $raiders;
}
?>

