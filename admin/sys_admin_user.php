<?php
/**
 * �û�����
 *
 * @version        $Id: sys_admin_user.php 1 16:22 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_User');
require_once(DEDEINC."/datalistcp.class.php");
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
if(empty($rank)) $rank = '';
else $rank = " WHERE CONCAT(#@__admin.usertype)='$rank' ";

$dsql->SetQuery("SELECT rank,typename FROM `#@__admintype` ");
$dsql->Execute();
while($row = $dsql->GetObject())
{
    $adminRanks[$row->rank] = $row->typename;
}
$query = "SELECT #@__admin.*,#@__arctype.typename FROM #@__admin LEFT JOIN #@__arctype ON #@__admin.typeid = #@__arctype.id $rank ";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/sys_admin_user.htm");
$dlist->SetSource($query);
$dlist->Display();

function GetUserType($trank)
{
    global $adminRanks;
    if(isset($adminRanks[$trank])) return $adminRanks[$trank];
    else return "��������";
}

function GetChannel($c)
{
    if($c==""||$c==0) return "����Ƶ��";
    else return $c;
}