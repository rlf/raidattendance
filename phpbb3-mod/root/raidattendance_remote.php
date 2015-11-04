<?php
/**
*
* Login script for phpBB using username/password
* Used for website authentication
*
*/
define('IN_PHPBB', true);
$phpbb_root_path = dirname(__FILE__) . '/./';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include("common.php");
// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup();

$username = request_var('username', '');
$password = request_var('password', '');

if(isset($username) && isset($password))
{
  $auth->login($username, $password, true);
  include("view_raidattendance.php");
}
?>
