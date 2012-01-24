<?php

/*
* Sets up the basic Theme handler for Pastebin
*/
class pTPL_smf extends pTPL
{
	/*
	* We simply just create the object to the user_info handler.
	*/
	public function __construct()
	{
		// SMF isn't started yet.
		if (!defined('SMF'))
		{
			require_once(dirname(__FILE__) . '/db-smf.php');

			$discard = new pDB_smf;
			unset($discard);
		}
	}

	/*
	* Do the header
	* @Note: Because we used ssi earlier to star the SMF theme, we have nothing to do here
	*/
	public function htmlHead($title)
	{
		global $context;

		$context['page_title'] = $title;
	}

	/*
	* The very last thing before we close up shop.
	*/
	public function htmlFooter()
	{
		ssi_shutdown();
	}

	/*
	* Custom code before the top part of recent.
	*/
	public function recentTop()
	{
		echo '
			<div id="paste_recent" class="floatright">';
	}

	/*
	* Custom code before the bottom part of recent.
	*/
	public function recentBottom()
	{
		echo '
			</div>';
	}
}