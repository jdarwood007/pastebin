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
	}

	/*
	* Do the header
	* @Note: Because we used ssi earlier to star the SMF theme, we have nothing to do here
	*/
	public function html_head($title)
	{
		global $context;

		$context['page_title'] = $title;
	}

	/*
	* The very last thing before we close up shop.
	*/
	public function html_footer()
	{
		ssi_shutdown();
	}
}