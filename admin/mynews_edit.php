<?php
/**
 * վ�����Ź���
 *
 * @version        $Id: mynews_edit.php 1 15:28 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('plus_վ�����ŷ���');
if(empty($dopost)) $dopost = "";

$aid = preg_replace("#[^0-9]#", "", $aid);
if($dopost=="del")
{
    $dsql->ExecuteNoneQuery("DELETE FROM #@__mynews WHERE aid='$aid';");
    ShowMsg("�ɹ�ɾ��һ��վ�����ţ�","mynews_main.php");
    exit();
}
else if($dopost=="editsave")
{
    $inquery = "UPDATE #@__mynews SET title='$title',typeid='$typeid',writer='$writer',senddate='".GetMKTime($sdate)."',body='$body' WHERE aid='$aid';";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("�ɹ�����һ��վ�����ţ�","mynews_main.php");
    exit();
}
$myNews = $dsql->GetOne("SELECT #@__mynews.*,#@__arctype.typename FROM #@__mynews LEFT JOIN #@__arctype ON #@__arctype.id=#@__mynews.typeid WHERE #@__mynews.aid='$aid';");
include DedeInclude('templets/mynews_edit.htm');