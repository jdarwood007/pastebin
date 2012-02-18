<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* Basic Anti-Spam handler for Pastebin.
*/
class pAS
{
	/*
	* Setup the anti-spam method, for basic we do nothing.
	*/
	public function setup()
	{	
	}

	/*
	* Verify the that we correctly entered the anti-spam stuff.
	*/
	public function verify($warnings)
	{
	}

	/*
	* For the template.
	*/
	public function template()
	{
	}
}