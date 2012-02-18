<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* PasteBin Settings
*/
class pBS
{
	/* Private pastebin? */
	private static $private = false;

	/* Where are the sources? */
	private static $sources = './sources';

	/* Database Handler. */
	private static $db = 'smf';

	/* User Handler. */
	private static $usr = 'smf';

	/* Template Handler. */
	private static $tpl = 'wp';

	/* Template Handler. */
//	private static $antispam = 'recaptcha';
	private static $antispam = 'basic';

	/* Any preloader file needed? */
	private static $preload = '__integrate.php';

	/* Language files location and default. */
	private static $languages = './languages';

	/* The default language. */
	private static $default_language = 'english';

	/* Where is the CSS? */
	private static $css = '/../pastebin/pb.css';

	/* Recent limits. First is default secondary is administrator. */
	private static $recent_limit = 10;
	private static $recent_limit_admin = 50;

	/* SEF: Should we use SEF? */
	private static $url_sef = true;

	/* SEF: The base url for SEF */
	private static $sef_base = '/pastebin';

	/* PORTAL: Should we use a portal? SEF has presidence. */
	private static $use_portal = false;

	/* PORTAL: The url to the portal page this is on. */
	private static $portal_url = '';

	/* GESHI: Enable Geshi. */
	private static $use_geshi = true;

	/* GESHI: The location to the geshi.php file. */
	private static $geshi_location = './geshi';

	/* GESHI: The default language to use. */
	private static $geshi_default = 'php';

	/* ANTI-SPAM (BASIC): Enable the human check? */
	private static $human_check = true;

	/* ANTI-SPAM (BASIC): The question to ask. */
	private static $human_question = 'A duck, cat and a goose walk into a bar. How many animals walked into a bar?';

	/* ANTI-SPAM (BASIC): The answer they need to provide. */
	private static $human_answer = '3';

	/* ANTI-SPAM (RECAPTCHA): If using Recaptcha as your anti-spam handler enter your key here */
	private static $recaptcha_key = '';

	/* ANTI-SPAM (RECAPTCHA): If using Recaptcha as your anti-spam handler enter your private key here */
	private static $recaptcha_private_key = '';
	
	/* SMF: When we are using SMF, we need to know where it is. */
	private static $smf_dir = '../forum/';

	/* SMF: The paste board ID. Does not need to be a public board. */
	private static $smf_paste_board = 4;

	/* SMF: Should we increase the users post count? */
	private static $smf_increase_postcout = true;

	/* SMF: Should we check whether this needs post approval? */
	private static $smf_post_approval = false;

	/* SMF: Use SMF themes? Only works when $tpl above is set to smf. */
	private static $smf_use_theme = true;

	/* SMF: The SMF theme ID to use when using its theme. */
	private static $smf_theme_id = 1;

	/*
	* DO NOT MODIFY THIS.
	* Allows applications to request settings without ability to change them.
	*/
	public static function get($var)
	{
		if (is_callable('pBSe::get') && pBSe::get($var) !== null)
			return pBSe::get($var);

		if (!isset(self::$$var))
			return null;

		return self::$$var;
	}
}
