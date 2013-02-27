<?php
/*
  +----------------------------------------------------------------------+
  | PHP.net Website Systems                                              |
  +----------------------------------------------------------------------+
  | Copyright (c) 2011 The PHP Group                                     |
  +----------------------------------------------------------------------+
  | This source file is subject to version 3.01 of the PHP license,      |
  | that is bundled with this package in the file LICENSE, and is        |
  | available through the world-wide-web at the following url:           |
  | http://www.php.net/license/3_01.txt                                  |
  | If you did not receive a copy of the PHP license and are unable to   |
  | obtain it through the world-wide-web, please send a note to          |
  | license@php.net so we can mail you a copy immediately.               |
  +----------------------------------------------------------------------+
  | Author:                                                              |
  |	Kalle Sommer Nielsen <kalle@php.net>                             |
  | Based on code by:                                                    |
  |     Jim Winstead <jimw@php.net>                                      |
  +----------------------------------------------------------------------+
*/

require 'include/common.inc';

if (!$nntp->command('LIST', 215)) {
    error($lang['index_error_list']);
}

$groups = '';

foreach ($nntp->getResults() as $line) {
    $group 		= new Template('frontpage_groupbit');
    $group['group']	= $line[0];
    $group['high']	= $line[1];
    $group['low']	= $line[2];

    $groups             .= $group;
}

$content 		= new Template('frontpage');
$content['groups']      = $groups;

echo new Template('header');
echo $content;
echo new Template('footer');

?>