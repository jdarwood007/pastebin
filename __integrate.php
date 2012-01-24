<?php
define('PB_SHOW_SOURCE', $_SERVER['HTTP_HOST'] == 'sleepycode.com' ? true : false);

error_reporting(E_ALL);
$_SERVER['REQUEST_URI'] = '/pastebin';
require_once('../wp-ssi.php');
$specialPage['noClear'] = true;
$specialPage['class'] = 'post';
$specialPage['contentClass'] = '';
$specialPage['headerTitle'] = 'PasteBin';
$specialPage['pageTitlePrefix'] = 'PasteBin';
