<?php
/**
 * �ļ�������
 *
 * @version        $Id: templets_tagsource.php 1 23:44 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('plus_�ļ�������');

$libdir = DEDEINC.'/taglib';
$helpdir = DEDEINC.'/taglib/help';

//��ȡĬ���ļ�˵����Ϣ
function GetHelpInfo($tagname)
{
    global $helpdir;
    $helpfile = $helpdir.'/'.$tagname.'.txt';
    if(!file_exists($helpfile))
    {
        return '�ñ�ǩû������Ϣ';
    }
    $fp = fopen($helpfile,'r');
    $helpinfo = fgets($fp,64);
    fclose($fp);
    return $helpinfo;
}

include DedeInclude('templets/templets_tagsource.htm');