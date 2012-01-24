<?php
// Show me the sauce.
if (isset($_GET['sauce2']))
{
	echo '<!DOCTYPE html><html><head><title>PasteBin Source code</title></head><body>';

	if (isset($_GET['f']) && $_GET['f'] == 'settings')
		highlight_file(dirname(__FILE__) . '/settings.php');
	elseif (isset($_GET['f']) && $_GET['f'] == 'language')
		highlight_file(dirname(__FILE__) . '/languages/english.php');
	else
		highlight_file(__FILE__);

	exit('</body></html>');
}

$_SERVER['REQUEST_URI'] = '/pastebin';
require_once('../wp-ssi.php');
$specialPage['noClear'] = true;
$specialPage['class'] = 'post';
$specialPage['contentClass'] = '';
$specialPage['headerTitle'] = 'PasteBin';
$specialPage['pageTitlePrefix'] = 'PasteBin';

// Start the PasteBin up.
$pasteBin = new pB();

// Modified for WordPress, but this handles the sidebar and css.
if (defined('WPLANG'))
{
	wp_enqueue_style('pastebin', pBS::get('css'));
	ob_start();
	$pasteBin->showRecent();
	$specialPage['sidebar'] = ob_get_contents();
	ob_end_clean();
}

// Handles the actions.
if (isset($_POST['submit']))
	$pasteBin->action_paste();
if (isset($_GET['view']))
	$pasteBin->action_view($_GET['view']);
else
	$pasteBin->action_index();

if (defined('WPLANG'))
	$specialPage['title'] = $pasteBin->title;

/*
* Main PasteBin class
*/
class pB
{
	/*
	* Some basic stuff
	*/
	public $title = 'PasteBin';
	private $action = 'index';
	private $geshi_languages = array();
	private $db = null;
	private $usr = null;

	/*
	* Setup the settings when creating the object.
	*/
	public function __construct()
	{
		// Load the primary file.
		require_once(dirname(__FILE__) . '/settings.php');

		// Load up any settings that apply only to this pastebin.
		if (file_exists(dirname(__FILE__) . '/settings-' . pathinfo(basename($_SERVER['SCRIPT_FILENAME']), PATHINFO_FILENAME) . '.php'))
			require_once(dirname(__FILE__) . '/settings-' . pathinfo(basename($_SERVER['SCRIPT_FILENAME']), PATHINFO_FILENAME). '.php');

		// Start up our database.
		require_once(pBS::get('sources') . '/db.php');
		if (file_exists(pBS::get('sources') . '/db-' . pBS::get('db') . '.php'))
		{
			require_once(pBS::get('sources') . '/db-' . pBS::get('db') . '.php');

			$class = 'pDB_' . pBS::get('db');
			if (class_exists($class))
				$this->db = new $class;
		}

		// Start up our User handler.
		require_once(pBS::get('sources') . '/user.php');
		if (file_exists(pBS::get('sources') . '/user-' . pBS::get('user') . '.php'))
		{
			require_once(pBS::get('sources') . '/user-' . pBS::get('user') . '.php');

			$class = 'pUser_' . pBS::get('user');
			if (class_exists($class))
				$this->usr = new $class;
		}

		// Start getting things going.
		$this->loadLanguage();
		$this->loadGeshi();

		// At this point we are ready to go.
		if (pBS::get('use_smf_theme'))
			echo '
			<div id="paste_recent" class="alignright">', $this->showRecent(), '</div>';
	}

	/*
	* Load up the language, taking into account a session or selection.
	*/
	public function loadLanguage()
	{
		if (isset($_SESSION['user_language']))
			$language = $_SESSION['user_language'];
		// allow_url_include shouldn't be enabled!
		elseif (isset($_GET['lang']) && file_exists(pBS::get('languages') . '/' . strtolower(htmlspecialchars($_GET['lang'])) . '.php'))
		{
			if (strpos($_GET['lang'], 'http://') !== false || strpos($_GET['lang'], 'ftp://') !== false)
				exit('Hacking attempt');

			$language = strtolower(htmlspecialchars($_GET['lang']));
		}
		elseif ($this->usr->id() > 0 && file_exists(pBS::get('languages') . '/' . $this->usr->language() . '.php'))
			$language = $this->usr->language();
		else
			$language = pBS::get('default_language');

		// Load the language.
		require(pBS::get('languages') . '/' . $language . '.php');
	}

	/*
	* Setup GeSHI
	*/
	public function loadGeshi()
	{
		if (!pBS::get('use_geshi'))
			return false;

		if (!($dir = @opendir(pBS::get('geshi_location') . '/geshi')))
			return false;

		$languages = array();
		while ($file = readdir($dir))
		{
			if (substr ($file, 0, 1) == '.' || !stristr($file, '.') || $file == 'css-gen.cfg' )
				continue;
			$languages[] = substr($file, 0,  strpos($file, '.'));
		}
		closedir($dir);
		sort($languages);

		$this->geshi_languages = $languages;
	}

	/*
	* Format a URL
	* @param $act string The action we want to use
	* @param $sa string The value of the action
	* @param $extras array An array of key => value containing extras to add to the url.
	* @return string Return the url.
	*/
	public function URL($act, $sa = '', $extras = array())
	{
		static $url_prefix;

		// Build the first part of the url.
		if (empty($url_prefix))
		{
			$url_prefix = 'http' . (isset($_SERVER['HTTPS']) ? 's': '')  . '://' . $_SERVER['HTTP_HOST'];

			if ($_SERVER['SERVER_PORT'] != 80)
				$url_perfix .= ':' . $_SERVER['SERVER_PORT'];

			if (pBS::get('url_sef'))
				$url_prefix .= pBS::get('sef_base');
			elseif (pBS::get('use_portal'))
				$url_prefix .= pBS::get('portal_url');
			else
				$url_prefix .= str_replace($_SERVER['DOCUMENT_ROOT'], '', __FILE__) . '?';
		}

		if ($act == 'index' && $url_prefix{strlen($url_prefix) -1} == '?')
			return substr($url_prefix, 0, -1);
		elseif ($act == 'index')
			return $url_prefix;
		elseif ($act == 'post')
			return $url_prefix . '/?';

		$url = $url_prefix;

		if ($sa == '')
			$url .= '?' . $act;
		elseif (pBS::get('url_sef'))
			$url .= '/' . $sa;
		else
			$url .= $act . '=' . $sa;

		if (!empty($extras))
		{
			foreach ($extras AS $k => $v)
			{
				if (pBS::get('url_sef') && !empty($v))
					$url .= '/' . $v;
				elseif (!empty($v))
					$url .= ';' . $k . '=' . $v;
			}
		}

		// This may happen with a portal..
		if (pBS::get('use_portal'))
			$url = str_replace('?;', '?', $url);

		return $url;
	}

	/*
	* Show a new past form
	*/
	public function action_index()
	{
		$this->title = pBL('index_title');

		// Trying to save this paste?
		if (isset($_POST['save']))
			$errors = $this->makePaste(0);

		// Show any errors
		if (!empty($errors))
			echo '<div class="error_message">', implode('<br />', $errors), '</div>';

		$this->postForm((!empty($_POST['code']) ? $_POST['code'] : ''));
	}

	/*
	* Show a paste
	* @param $id int The id of the paste
	*/
	public function action_view($id)
	{
		$this->title = pBL('view_title', $id);

		$paste = $this->showPaste($id);

		if (!empty($paste['key']) && $this->usr->is_admin())
			echo '
			<div class="information"><b>Key:</b> ', $paste['key'], '</div>';

		if (!empty($paste['parsed']))
			echo '
			<div id="formated">
				<h2>', pBL('formated_paste'), '</h2>
				<div id="formated_paste">', $paste['parsed'], '</div>
			</div>';

		$this->postForm($paste['body'], $id, $paste['use_geshi'], $paste['language']);
	}

	/*
	* Actually make the paste.
	*/
	public function action_paste()
	{
		$do_create = true;

		if ($this->usr->id() > 0 && (empty($_POST['name']) || empty($_POST['email'])))
			$do_create = false;

		if (empty($_POST['code']))
			$do_create = false;

		// Get the data ready.
		$data = array(
			'paste_id' => !empty($_POST['view']) ? $_POST['view'] : 0,
			'new_key' => isset($_POST['force_new_pw']) && $this->usr->is_admin(),
			'name' => !empty($_POST['name']) ? $_POST['name'] : 'Guest',
			'email' => !empty($_POST['email']) ? $_POST['email'] : 'guest@noemail.com',
			'use_geshi' => !empty($_POST['use_geshi']),
			'language' => !empty($_POST['type']) ? $_POST['type'] : 'php',
			'body' => $_POST['code']
		);

		// Do a test.
		$this->db->addPasteTest(&$data);

		if (!$do_create)
		{
			$this->errors[] = 'Missing information (Username/email)';

			if (!empty($_POST['view']))
				$this->action_view($_POST['view']);
			else
				$this->action_index();

			return false;
		}

		// Valid Numbers only..
		$result = $this->db->addPaste($data);

		// Send us there.
		redirectexit($this->URL('view', $result['id'], array(
			'update' => isset($data['updated']) ? 't' . time() : '',
			'key' => !empty($data['key']) ? $data['key'] : '',
		)));
	}

	/*
	* Show some recent pastes
	*/
	public function showRecent()
	{
		$recent_limit = $this->usr->is_admin() ? pBS::get('recent_limit_admin') : pBS::get('recent_limit');

		$recent = array();
		if ($recent_limit > 0)
			$recent = $this->db->fetchRecent($recent_limit);

		// Output this.
		echo '
			<ul>		
				<li class="widget">
					<h2 class="widgettitle" title="I am not a Easter Egg">', pBL('recent'), '</h2>
					<ul>
						<li><a href="', $this->URL('index'), '">', pBL('create_new'), '</a></li>';

			foreach ($recent as $rec)
				echo '
						<li><a href="', $this->URL('view', $rec), '">#', $rec, '</a></li>';

		echo '
					</ul>
				</li>
			</ul>';

		// Added this for myself.
		if (defined('WPLANG2'))
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
	}

	/*
	* Shows a form for making/editing a paste
	* @param $code string A string containing the actual code for the code box
	* @param $id (optiona) int The id of the paste
	* @param $use_geshi (optiona) bool Whether to use geshi or not.
	* @param $geshi_language (optional) string The default language to use, ie php
	*/
	public function postForm($code, $id = 0, $use_geshi = true, $geshi_language = 'php')
	{
		echo '
			<form method="post" action="', $this->URL('post'), '">
				<div id="name_container">
					<span id="name_text" class="container_text">', pBL('user_name'), ':</span>
					<span id="name_value" class="container_value">';

		if ($this->usr->is_guest())
			echo '
						<input type="text" name="name" value="', !empty($_POST['name']) ? htmlspecialchars($_POST['name']) : 'Guest', '" />';
		else
			echo '
						', $this->usr->name(), '<input type="hidden" name="name" value="', $this->usr->name(), '" /></span>';

		echo '
					</span>
				</div>

				<div id="email_container">
					<span id="email_text" class="container_text">', pBL('email'), ':</span>
					<span id="email_value class="container_value">';

		if ($this->usr->is_guest())
			echo '
						<input type="text" name="email" value="', !empty($_POST['email']) ? htmlspecialchars($_POST['email']) : 'your+name@domain.com', '" />';
		else
			echo '
						', $this->usr->email(), '<input type="hidden" name="email" value="', $this->usr->email(), '" />';

		echo '
					</span>
				</div>

				<div id="code_container">
					<div id="code_text" class="container"text">', pBL('code'), ':</div>
					<div id="code_value" class="container_value">
						<textarea name="code" style="width: 100%;" rows="30">', $code, '</textarea>
					</div>
				</div>

				<ul id="settings_container">';

		if (pBS::get('use_geshi'))
		{
			echo '
					<li><input type="checkbox" name="use_geshi"', $use_geshi ? ' checked="checked"' : '', '/><span id="setting_geshi" class="setting_text">', pBL('enable_geshi'), '</span></li>';

			if (!empty($this->geshi_languages))
			{
				echo '
					<li><span id="setting_geshi_lang">Code Language:</span><select name="type">';

				foreach ($this->geshi_languages AS $lang)
					echo '
						<option value="', $lang, '"', ($geshi_language == $lang ? 'selected="selected"' : ''), '>', $lang, '</option>';

				echo '
					</select></li>';
			}
		}

		if (pBS::get('private') && $this->usr->is_admin())
			echo '
					<li><input type="checkbox" name="force_new_pw" /><strong>', pBL('force_new_key'), '</strong></li>';

		if (pBS::get('human_check'))
			echo '
					<li>', pBS::get('human_question'), ':<input type="text" name="ru_human" value="', isset($_POST['ru_human']) ? $_POST['ru_human'] : '', '" /></li>';

		echo '
				</ul>';

		if (!empty($id))
			echo '
				<input type="hidden" name="view" value="', $id, '" />';

		echo '
				<input id="submit" type="submit" name="submit" value="', pBL('submit'), '" />
			</form>';
	}

	/*
	* Shows an existing paste
	* @param $id int The id of the paste to load up.
	*/
	public function showPaste($id)
	{
		// Get it from the database.
		$paste = $this->db->fetchPaste($id);

		if (!$this->usr->is_admin() && !empty($Paste['key']) && (empty($_REQUEST['key']) || $Paste['key'] != $_REQUEST['key']))
			$this->error(pBL('error_no_access'), true);
		elseif ($paste['board_id'] != pBS::get('paste_board'))
			$this->error(pBL('error_no_access'), true);
		elseif (empty($paste['approved']))
			$this->error(pBL('error_approval'), true);

		if (pBS::get('use_geshi') && !empty($paste['use_geshi']))
		{
			$type = !empty($_REQUEST['type']) ? $_REQUEST['type'] : (!empty($paste['language']) ? $Paste['language'] : 'php');

			include_once(pBS::get('geshi_location')  . '/geshi.php');
			
			$geshi =& new GeSHi('', $type);
			$geshi->set_header_type(GESHI_HEADER_PRE);
			$geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS);

			$geshiErr =& new GeSHi($paste['body'], $type);
			$geshiErr->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
			$topic_parsed = $geshiErr->parse_code();
			$topic_parsed = str_replace('&lt;?php', '<&#063;php', $topic_parsed);

			$paste['parsed'] = $topic_parsed;
		}

		return $paste;
	}
}

/*
* This is a function that passes the language calls to the
* language class without using ugly $$var in the template.
*/
function pBL($string)
{
	$args = func_get_args();

	if (count($args) == 1)
		return pBL::$$string;
	else
	{
		// Override it.
		$args[0] = pBL::$$string;
		return call_user_func_array('sprintf', $args);
	}
}
