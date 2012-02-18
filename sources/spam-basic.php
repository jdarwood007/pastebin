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
class pAS_basic extends pAS
{
	/*
	* Test whether this anti-spam method is enabled or not.
	*/
	public function classActive()
	{
		if (pBS::get('human_check') === false || pBS::get('human_question') === null || pBS::get('human_answer') === null)
			return false;
		return true;
	}

	/*
	* Verify the that we correctly entered the anti-spam stuff.
	*/
	public function verify($warnings)
	{
		// Luck you, get a free pass.
		if (pBS::get('human_check') === false)
			return true;

		if (!isset($_POST['ru_human']) || $_POST['ru_human'] != pBS::get('human_answer'))
		{
			$warnings[] = 'Invalid human verification';
			return false;
		}

		return true;
	}

	/*
	* For the template.
	*/
	public function template()
	{
		if (pBS::get('human_check') === false)
			return;

			echo pBS::get('human_question'), ':<input type="text" name="ru_human" value="', isset($_POST['ru_human']) ? $_POST['ru_human'] : '', '" />';
	}
}