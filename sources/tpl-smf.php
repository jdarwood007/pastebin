<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* SMF Theme handler for Pastebin.
*/
class pTPL_smf extends pTPL
{
	/*
	* We will simply start SMF up if it wasn't already.
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
	* Do the header.
	* @param $title String the page title.
	* @Note: Because we used ssi earlier to star the SMF theme, we have nothing to do here.
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
			<!-- Start Recent Box -->
			<div id="paste_recent" class="floatright">';
	}

	/*
	* Custom code before the bottom part of recent.
	*/
	public function recentBottom()
	{
		echo '
			</div>
			<!-- End Recent Box -->';
	}
}