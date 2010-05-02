<?php
/**
* @ignore
*/
if (!defined('IN_PHPBB'))
{
	exit;
}  
/**
* DO NOT CHANGE 
*/
if (empty($lang) || !is_array($lang)) {
	$lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
// 
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

//
// Common Language Resources for Raid Attendance
//
$lang = array_merge($lang, array(
		'STATUS_0'					=> '-',
		'STATUS_1'					=> 'ON',
		'STATUS_2'					=> 'OFF',
		'STATUS_3'					=> 'NOSHOW',
		'STATUS_4'					=> 'LATE',
		'STATUS_5'					=> 'SUBST',
		'STATUS_6'					=> 'CANCELLED',

		'OUTPUT_MIME_HTML'			=> 'text/html',
		'OUTPUT_HEADER_HTML'		=> '<html><body><table>',
		'OUTPUT_FOOTER_HTML'		=> '</table></body></html>',
		'OUTPUT_ROW_HTML'			=> "<tr>%s</tr>\n",
		'OUTPUT_CELL_HTML'			=> '<td>%s</td>',

		'OUTPUT_MIME_CSV'			=> 'application/octet-stream',
		'OUTPUT_HEADER_CSV'			=> '',
		'OUTPUT_FOOTER_CSV'			=> '',
		'OUTPUT_ROW_CSV'			=> '%s',
		'OUTPUT_CELL_CSV'			=> "%s\t",

		'OUTPUT_MIME_XML'			=> 'text/xml',
		'OUTPUT_FOOTER_XML'			=> '</AttendancyDump>',
		'OUTPUT_ROW_XML'			=> "<Row>%s</Row>\n",
		'OUTPUT_CELL_XML'			=> '<Cell>%s</Cell>',
		'OUTPUT_HEADER_XML'			=> "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<AttendancyDump>\n",

	)
);
?>
