<?php
/**
 * �Զ���ģ�͹���
 *
 * @version        $Id: mychannel_main.php 1 15:26 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_List');
require_once(DEDEINC.'/datalistcp.class.php');
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");

$sql = "SELECT id,nid,typename,addtable,isshow,issystem FROM `#@__channeltype` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplet(DEDEADMIN."/templets/mychannel_main.htm");
$dlist->SetSource($sql);
$dlist->display();

function GetSta($sta,$id)
{
    if($sta==1)
    {
        return ($id!=-1 ? "����  &gt; <a href='mychannel_edit.php?dopost=hide&id=$id'><u>����</u></a>" : "�̶���Ŀ");
    }
    else
    {
        return "���� &gt; <a href='mychannel_edit.php?dopost=show&id=$id'><u>����</u></a>";
    }
}

function IsSystem($s)
{
    return $s==1 ? "ϵͳ" : "�Զ�";
}