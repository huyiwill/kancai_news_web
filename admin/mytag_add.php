<?php
/**
 * �����Զ�����
 *
 * @version        $Id: mytag_add.php 1 15:35 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('temp_Other');
require_once(DEDEINC."/typelink.class.php");
if(empty($dopost)) $dopost = "";

if($dopost=="save")
{
    $tagname = trim($tagname);
    $row = $dsql->GetOne("SELECT typeid FROM #@__mytag WHERE typeid='$typeid' AND tagname LIKE '$tagname'");
    if(is_array($row))
    {
        ShowMsg("����ͬ��Ŀ���Ѿ�����ͬ���ı�ǣ�","-1");
        exit();
    }
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $inQuery = "INSERT INTO #@__mytag(typeid,tagname,timeset,starttime,endtime,normbody,expbody)
     VALUES('$typeid','$tagname','$timeset','$starttime','$endtime','$normbody','$expbody'); ";
    $dsql->ExecuteNoneQuery($inQuery);
    ShowMsg("�ɹ�����һ���Զ����ǣ�","mytag_main.php");
    exit();
}
$startDay = time();
$endDay = AddDay($startDay,30);
$startDay = GetDateTimeMk($startDay);
$endDay = GetDateTimeMk($endDay);
include DedeInclude('templets/mytag_add.htm');