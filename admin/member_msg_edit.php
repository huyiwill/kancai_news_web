<?php
/**
 * ��Ա����Ϣ����
 *
 * @version        $Id: member_msg_edit.php 1 11:24 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Log');
if(empty($dopost))
{
    ShowMsg("��ûָ���κβ�����","javascript:;");
    exit();
}
if(empty($dellog)) $dellog = 0;

//ɾ��ѡ��״̬
if($dopost=="del")
{
    $bkurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "member_msg_main.php";
    $ids = explode('`',$ids);
    $dquery = "";
    foreach($ids as $id)
    {
        if($dquery=="")
        {
            $dquery .= "id='$id' ";
        }
        else
        {
            $dquery .= " OR id='$id' ";
        }
    }
    if($dquery!="") $dquery = " WHERE ".$dquery;
    $dsql->ExecuteNoneQuery("DELETE FROM #@__member_msg $dquery");
    ShowMsg("�ɹ�ɾ��ָ���ļ�¼��",$bkurl);
    exit();
}
//���ѡ��״̬
else if($dopost=="check")
{
    $bkurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "member_msg_main.php";
    $ids = explode('`',$ids);
    $dquery = "";
    foreach($ids as $id)
    {
        if($dquery=="") $dquery .= " id='$id' ";
        else $dquery .= " Or id='$id' ";
    }
    if($dquery!="") $dquery = " where ".$dquery;
    $dsql->ExecuteNoneQuery("UPDATE #@__member_msg SET ischeck=1 $dquery");
    ShowMsg("�ɹ����ָ���ļ�¼��",$bkurl);
    exit();
}
else
{
    ShowMsg("�޷�ʶ���������","javascript:;");
    exit();
}