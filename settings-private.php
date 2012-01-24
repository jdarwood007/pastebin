<?php

/*
* PasteBin Settings
*/
class pBSe extends pBS
{
	private static $private = true;
	private static $url_sef = false;
	private static $smf_paste_board = 17;
	private static $recent_limit = 0;
	private static $use_portal = true;
	private static $portal_url = '/pastebin/private.php?';

	public static function get($var)
	{
		if (isset(self::$$var))
			return self::$$var;
		return null;
	}
}