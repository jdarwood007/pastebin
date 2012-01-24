<?php

/*
* SMF User handler for Pastebin.
*/
class pUser_smf extends pUser
{
	/*
	* The SMF user handler
	*/
	private $usr = null;

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

		$this->usr = userInfo::_();
	}

	/*
	* The users ID.
	* @return int The id of the user.
	*/
	public function id()
	{
		return $this->usr->id;
	}

	/*
	* The users language
	* @return String english version of the language (ie german).
	*/
	public function language()
	{
		return $this->usr->language;
	}

	/*
	* Is the user a guest?
	* @return bool True if guest, false otherwise
	*/
	public function is_guest()
	{
		return $this->usr->is_guest;
	}

	/*
	* Is the user an admin?
	* @return bool True if admin, false otherwise
	*/
	public function is_admin()
	{
		return $this->usr->is_admin;
	}

	/*
	* What is their name?
	* @return String The name of the user. Guest is fine.
	*/
	public function name()
	{
		return $this->usr->name;
	}

	/*
	* What is their email?
	* @return String The email of the user. A default one is fine.
	*/
	public function email()
	{
		return $this->usr->email;
	}
}

/*
* userInfo as a class.  We kinda do a poor method, but its the best way for now.
*/
class userInfo
{
	public static $instanceID = 0;

	public static function _()
	{
		if (self::$instanceID == 0)
			self::$instanceID = new userInfo;
		return self::$instanceID;
	}

	public function __set($key, $value)
	{
		global $user_info;
		$user_info[$key] = $value;
	}

	public function __get($key)
	{
		global $user_info;
		return isset($user_info[$key]) ? $user_info[$key] : null;
	}

	public function __isset($key)
	{
		global $user_info;
		return isset($user_info[$key]);
	}

	public function __unset($key)
	{
		global $user_info;
		unset($user_info[$key], $user_info[$key]);
	}
}