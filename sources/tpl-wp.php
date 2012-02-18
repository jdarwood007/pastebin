<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* WordPress Theme handler for Pastebin.
*/
class pTPL_wp extends pTPL
{
	/*
	* Lets get things cooking.
	*/
	public function __construct()
	{
		wp_enqueue_style('pastebin', pBS::get('css'));
	}

	/*
	* Do the header.
	* @param $title String the page title.
	*/
	public function htmlHead($title)
	{
		global $specialPage;

		$specialPage['title'] = $title;
	}

	/*
	* Custom code before the top part of recent.
	*/
	public function recentTop()
	{
		ob_start();
	}

	/*
	* Custom code before the bottom part of recent.
	*/
	public function recentBottom()
	{
		global $specialPage;

		// Lets not always show the source links.
		if (defined('PB_SHOW_SOURCE'))
			echo '
			<br />
			<ul>
				<li class="widget">
					<h2 class="widgettitle">See the Source</h2>
					<ul>
						<li><a href="http://git.sleepycode.com/?a=summary&p=SMF%20Pastebin">Local Source</li>
						<li><a href="https://github.com/jdarwood007/pastebin">GitHub</li>
					</ul>
				</li>
			</ul>';

		$specialPage['sidebar'] = ob_get_contents();
		ob_end_clean();
	}
}