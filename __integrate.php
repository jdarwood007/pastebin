<?php
/*
 * SMF PasteBin
 * Author: SleePy (JeremyD)
 * Repository: https://github.com/jdarwood007/pastebin
 * License: BSD 3 Clause; See license.txt
*/
if (!defined('SMFPasteBin')) { exit('[' . basename(__FILE__) . '] Direct access restricted');}

define('PB_SHOW_SOURCE', $_SERVER['HTTP_HOST'] == 'sleepycode.com' ? true : false);

$_SERVER['REQUEST_URI'] = '/pastebin';

require_once('../wp-ssi.php');
$specialPage['noClear'] = true;
$specialPage['class'] = 'post';
$specialPage['contentClass'] = '';
$specialPage['headerTitle'] = 'PasteBin';
$specialPage['pageTitlePrefix'] = 'PasteBin';
