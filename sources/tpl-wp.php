<?php

/*
* WordPress Theme handler for Pastebin.
*/
class pTPL_wp extends pTPL
{
	/*
	* Do the header.
	*/
	public function htmlHead($title)
	{
		global $specialPage;

		$specialPage['title'] = $title;
	}

	/*
	* Lets get things cooking.
	*/
	public function __construct()
	{
		wp_enqueue_style('pastebin', pBS::get('css'));
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
		if (pBS::get('show_source_link'))
			echo '
			<br />
			<ul>
				<li class="widget">
					<h2 class="widgettitle">See the Source</h2>
					<ul>
						<li><a href="./?sauce">Main Script</li>
						<li><a href="./?sauce&f=settings">Settings</li>
						<li><a href="./?sauce&f=language">Language</li>
					</ul>
				</li>
			</ul>';

		$specialPage['sidebar'] = ob_get_contents();
		ob_end_clean();
	}
}