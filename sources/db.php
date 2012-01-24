<?php

/*
* Basic handler for database interaction
*/
class pDB
{
	/*
	* How to create a key
	*/
	public function generateKey()
	{
		return substr(md5(uniqid(mt_rand(), true)), 0, 3) . substr(md5(uniqid(mt_rand(), true)), 0, 2);
	}
}