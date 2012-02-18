<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

/*
* SMF handler for database interaction.
*/
class pDB_smf extends pDB
{
	/*
	* Gets SMF started up and ready for action.
	*/
	public function __construct()
	{
		// Wordpress does something with the cookie, so we need to pull in settings file to fix it.
		global $time_start, $maintenance, $msubject, $mmessage, $mbname, $language;
		global $boardurl, $boarddir, $sourcedir, $webmaster_email, $cookiename;
		global $db_server, $db_name, $db_user, $db_prefix, $db_persist, $db_error_send, $db_last_error;
		global $db_connection, $modSettings, $context, $sc, $user_info, $topic, $board, $txt;
		global $smcFunc, $ssi_db_user, $scripturl, $ssi_db_passwd, $db_passwd, $cachedir;
		global $ssi_theme, $ssi_layers;

		// We strip the slashes from the cookie which resolves the issue.
		require_once(pBS::get('smf_dir') . '/Settings.php');
		if (isset($_COOKIE[$cookiename]))
			$_COOKIE[$cookiename] = stripslashes($_COOKIE[$cookiename]);

		// We need to setup this before we continue
		if (pBS::get('smf_use_theme') && pBS::get('tpl') == 'smf')
		{
			$ssi_theme = pBS::get('smf_theme_id');
			$ssi_layers = array('html', 'body');

			// Since SSI is loading the theme, we can't do this later.
			$context['html_headers'] = '
	<link rel="stylesheet" type="text/css" href="' . pBS::get('css')  . '" />';
		}

		require_once(pBS::get('smf_dir') . '/SSI.php');
		require_once($sourcedir . '/Subs-Post.php');
	}

	/*
	* Fetches the most recent from the SMF database.
	* @param $limit int The limit on the recent pastes
	*/
	public function fetchRecent($limit)
	{
		$request = smcFunc::db_query('', '
			SELECT t.id_topic as topic_id
			FROM {db_prefix}topics AS t
			WHERE t.id_board = {int:paste_board}
			ORDER BY id_last_msg DESC
			LIMIT {int:limit_recent}',
			array(
				'paste_board' => pBS::get('smf_paste_board'),
				'limit_recent' => $limit,
				));

		while($re = smcFunc::db_fetch_assoc($request))
			$recent[] = $re['topic_id'];
		smcFunc::db_free_result($request);
		$recent = array_unique($recent);

		return $recent;
	}

	/*
	* Fetches all the information abouta a paste.
	* @param $id int The id of the paste.
	*/
	public function fetchPaste($id)
	{
		// Do the query..
		$request = smcFunc::db_query('', '
			SELECT id_msg, id_topic, id_board as board_id, body, subject, approved
			FROM {db_prefix}messages
			WHERE id_topic = {int:id_topic}
			AND id_board = {int:paste_board}
			ORDER BY poster_time DESC',
			array(
				'paste_board' => pBS::get('smf_paste_board'),
				'id_topic' => $id,
				));
		$topic = smcFunc::db_fetch_assoc($request);
		smcFunc::db_free_result($request);

		// Need to check their access.
		if ($topic['board_id'] != pBS::get('smf_paste_board'))
			return array('error' => pBL('error_no_access'));

		$ops = explode(':v:', $topic['subject']);
		unset($ops[0]);

		foreach($ops as $op)
		{
			$temp = explode('-', $op);
			$Paste[$temp[0]] = $temp[1];
		}

		// This is how the data should return.
		return array(
			'id' => $topic['id_topic'],
			'key' => isset($Paste['p']) ? $Paste['p'] : '',
			'board_id' => $topic['board_id'],
			'approved' => $topic['approved'],
			'use_geshi' => $Paste['use_geshi'],
			'language' => $Paste['type'],
			'body' => htmlspecialchars_decode($topic['body']),
			'parsed' => '',
		);	
	}

	/*
	* Tests adding/updating a paste to the database.
	* @param $data array The data we are testing.
		$data[paste_id] Int The id of the paste, default is 0.
		$data[new_key] Bool To force a new key or not.
		$data[name] String The name of the paster.
		$data[email] String the email of the paster.
		$data[use_geshi] Bool If we should use geshi highlighting or not.
		$data[language] String The language of the code, default is php.
		$data[body] String the actual contents of the paste.
	* @param $do_create Should we create this or not?  Only set this to false when we shouldn't.
	*/
	public function addPasteTest($data, $do_create)
	{
	}

	/*
	* Actually adding/updating a paste to the database.
	* @param $data array The data we are testing.
	*	$data[paste_id] Int The id of the paste, default is 0.
	*	$data[new_key] Bool To force a new key or not.
	*	$data[name] String The name of the paster.
	*	$data[email] String the email of the paster.
	*	$data[use_geshi] Bool If we should use geshi highlighting or not.
	*	$data[language] String The language of the code, default is php.
	*	$data[body] String the actual contents of the paste.
	* @return $result array The data we are returning.
	*	$result[id] int The id of the paste.
	*	$result[key] String The key of the paste, default is empty.
	*	$result[updated] Bool Whether this was an update or not.
	*/
	public function addPaste($data)
	{
		// Fetch any data we need to know.
		if (!empty($data['paste_id']))
			$paste = $this->fetchPaste($data['paste_id']);

		// Try to keep the key correct unless it should change.
		if ((pBS::get('private') && empty($paste['key'])) || (!empty($paste['key']) && userInfo::_()->is_admin && isset($_POST['force_new_pw'])))
			$data['key'] = $this->generateKey();
		elseif (!empty($paste['key']))
			$data['key'] = $paste['key'];

		if (function_exists('wp_magic_quotes'))
		{
			$data['body'] = stripslashes($data['body']);
		}

		// Options needed for our post.
		$topicOptions = array(
			'id'		=> (!empty($paste['id']) ? $paste['id'] : 0) ,
			'board'		=> pBS::get('smf_paste_board'),
			'mark_as_read'	=> false,
			);
		$posterOptions = array(
			'id'		=> (isset(userInfo::_()->id) ? userInfo::_()->id: 0),
			'name'		=> $data['name'],
			'email'		=> $data['email'],
			'ip'		=> userInfo::_()->ip,
			'update_post_count'	=> (pBS::get('smf_increase_postcout') && isset(userInfo::_()->id) ? 1 : 0),
			);
		$msgOptions = array(
			'id'		=> 0,
			'subject'	=> 'Paste-' . time() . ':v:use_geshi-' . (!empty($data['use_geshi']) ? 1 : 0) . ':v:type-' . (!empty($data['language']) ? $data['language'] : 'php') . (!empty($data['key']) ? ':v:p-' . $data['key'] : ''),
			'body'		=> htmlspecialchars($data['body']),
			'approved'	=> pBS::get('smf_post_approval') ? 0 : 1,
			);

		// Actually create the paste.
		createPost($msgOptions, $topicOptions, $posterOptions);

		// Return some info
		return array(
			'id' => $topicOptions['id'],
			'key' => isset($data['key']) ? $data['key'] : '',
			'updated' => $data['id'] ? true : false,
		);
	}
}

/*
* smcFunc as a class. Uses callStatic to emulate it.
*/
class smcFunc
{
	public static function __callStatic($name, $arguments)
	{
		global $smcFunc;
		return call_user_func_array($smcFunc[$name], $arguments);
	}
}