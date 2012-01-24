<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* Basic handler for database interaction.
*/
class pDB
{
	/*
	* How to create a key.
	*/
	public function generateKey()
	{
		return substr(md5(uniqid(mt_rand(), true)), 0, 3) . substr(md5(uniqid(mt_rand(), true)), 0, 2);
	}
}