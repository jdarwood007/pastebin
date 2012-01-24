<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* PasteBin language
*/
class pBL
{
	/* Our language */
	public static $language = 'english';

	/* Create Paste Page */
	public static $index_title = 'Create new';
	public static $user_name = 'Name';
	public static $email = 'Email';
	public static $code = 'Code/text to paste';

	/* Additional options */
	public static $enable_geshi = 'Enable code highlighting';
	public static $force_new_key = 'Force new key';
	public static $submit = 'Submit';

	/* Recent menu */
	public static $recent = 'Recent';
	public static $create_new = 'Create new';

	/* Some language defined errors */
	public static $error_no_access = 'Invlaid Key used';
	public static $error_approval = 'This Paste requires approval';

	/* Viewing a paste */
	// %1$s = ID of the paste.
	public static $view_title = 'Viewing Paste %1$s';
	public static $formated_paste = 'Formated Paste';
	public static $plain_paste = 'Plain Paste';
}
