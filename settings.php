<?php

/*
* PasteBin Settings
*/
class pBS
{
	// Private pastebin?
	private static $private = false;

	// Where are the sources?
	private static $sources = './sources';

	// Database Handler.
	private static $db = 'smf';

	// User Handler.
	private static $user = 'smf';

	// Language files location and default.
	private static $languages = './languages';
	private static $default_language = 'english';

	// Where SMF is at.
	private static $smf_dir = '../forum/';
	private static $paste_board = 4;
	private static $increase_postcout = true;
	private static $enable_post_approval = false;
	private static $use_smf_theme = false	;
	private static $smf_theme_id = 1;

	// The URLs
	private static $url_sef = true;
	private static $sef_base = '/pastebin';
	private static $use_portal = false;
	private static $portal_url = '';

	// Where is the CSS?
	private static $css = '/../pastebin/pb.css';

	// Geshi stuff.
	private static $use_geshi = true;
	private static $geshi_location = './geshi';
	private static $geshi_default = 'php';

	// Be ye robot?
	private static $human_check = true;
	private static $human_question = 'A duck, cat and a goose walk into a bar. How many animals walked into a bar?';
	private static $human_answer = '3';

	// Recent limits.
	private static $recent_limit = 10;
	private static $recent_limit_admin = 50;

	/*
	* DO NOT MODIFY THIS.
	* Allows applications to request settings without ability to change them.
	*/
	public static function get($var)
	{
		if (is_callable('pBSe::get') && pBSe::get($var) !== null)
			return pBSe::get($var);
		return self::$$var;
	}
}
