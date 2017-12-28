<?php
/**
 * Ȧ���û�����
 *
 * @version        $Id: group_user.php 1 15:34 2011-1-21 tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/datalistcp.class.php');
CheckPurview('group_Edit');

$gid = isset($gid) && is_numeric($gid) ? $gid : 0;
$id = isset($id) && is_numeric($id) ? $id : 0;
$action = isset($action) ? trim($action) : '';

$username = isset($username) ? trim($username) : '';
$username = stripslashes($username);
$username = preg_replace("#[\"\r\n\t\*\?\(\)\$%']#", " ", trim($username));
$username = addslashes($username);

if($gid < 1)
{
    ShowMsg("���зǷ�����!.","-1");
    exit();
}

$row = $db->GetOne("SELECT ismaster,uid FROM #@__groups WHERE groupid='{$gid}'");
$ismaster     = $row['ismaster'];
$ismasterid        = $row['uid'];

if($action=="del")
{
    if($ismasterid == $id)
    {
        ShowMsg("Ȧ����������Ⱥ��ϵ!","-1");
        exit();
    }
    $row = $db->GetOne("SELECT username FROM #@__group_user WHERE uid='$id' AND gid='$gid'");
    if(is_array($row))
    {
        $username = $row['username'];
        $master = explode(",",$ismaster);
        if(in_array($username,$master))
        {
            //�����Ա�����Ա�ֶν��Ƴ�
            $k = array_search($username,$master);
            unset($master[$k]);
        }
        $master = array_filter($master, "filter");
        $ismaster = implode(",",$master);
        $db->ExecuteNoneQuery("UPDATE #@__groups SET ismaster='{$ismaster}' WHERE groupid='{$gid}'");
    }
    if($id > 0)
    {
        $db->ExecuteNoneQuery("DELETE FROM #@__group_user WHERE uid='$id' AND gid='$gid'");
    }
    ShowMsg("�ѽ��û�Ա�Ƴ���Ⱥ!.","-1");
    exit();
}
else if($action=="admin")
{
    if($ismasterid == $id)
    {
        ShowMsg("Ȧ��Ӧͬʱ�й���Ȩ!","-1");
        exit();
    }
    $row = $db->GetOne("SELECT username FROM #@__group_user WHERE uid='$id' AND gid='$gid'");
    if(is_array($row))
    {
        $username = $row['username'];
        $master = explode(",",$ismaster);
        if(in_array($username,$master))
        {
            //�����Ա�����Ա�ֶν��Ƴ�
            $k = array_search($username,$master);
            unset($master[$k]);
            $msg = "�ѽ� {$username},��Ϊ��ͨ��Ա!";
        }
        else
        {
            //������뵽����Ա����
            array_push($master,$username);
            $msg = "�ѽ� {$username},��Ϊ����Ա!";
        }
        $master = array_filter($master, "filter");
        $ismaster = implode(",",$master);
        $db->ExecuteNoneQuery("UPDATE #@__groups SET ismaster='{$ismaster}' WHERE groupid='{$gid}'");
    }
    ShowMsg("{$msg}","-1");
    exit();
}
else if($action=="add")
{
    $uname = cn_substr($uname,15);
    if(empty($uname))
    {
        ShowMsg("����д�û���!.","-1");
        exit();
    }
    $rs = $db->GetOne("SELECT COUNT(*) AS c FROM #@__group_user WHERE username like '$uname' AND gid='$gid'");
    if($rs['c'] > 0)
    {
        ShowMsg("�û��Ѽ����Ȧ��!.","-1");
        exit();
    }
    $row = $db->GetOne("SELECT userid,mid FROM #@__member WHERE userid like '$uname'");
    if(!is_array($row))
    {
        ShowMsg("վ�ڲ����ڸ��û�!.","-1");
        exit();
    }
    else
    {
        $row['id'] = $row['mid'];
        $db->ExecuteNoneQuery("INSERT INTO #@__group_user(uid,username,gid,jointime) VALUES('".$row['id']."','".$row['userid']."','$gid','".time()."');");
        //�����ɹ���Ա
        if($setmaster)
        {
            $master = explode(",",$ismaster);
            array_push($master,$uname);
            $master = array_filter($master, "filter");
            $ismaster = implode(",",$master);
            $db->ExecuteNoneQuery("UPDATE #@__groups SET ismaster='{$ismaster}' WHERE groupid='{$gid}'");
        }
    }
    ShowMsg("�ɹ�����û�:{$uname}","-1");
    exit();
}

//�б����ģ��
$wheresql = "WHERE gid='{$gid}'";
if(!empty($username))
{
    $wheresql .= " AND username like '%".$username."%'";
}
$sql = "SELECT * FROM #@__group_user $wheresql ORDER BY jointime DESC";


$dl = new DataListCP();
$dl->pageSize = 20;
$dl->SetParameter("username",$username);
$dl->SetParameter("id",$id);
$dl->SetParameter("gid",$gid);

//�������˳���ܸ���
$dl->SetTemplate(DEDEADMIN."/templets/group_user.htm");      //����ģ��
$dl->SetSource($sql);            //�趨��ѯSQL
$dl->Display();                  //��ʾ


function filter($var)
{
    return $var == '' ? false : true;
}

function GetMaster($user)
{
    global $ismaster;
    $master = explode(",",$ismaster);
    if(in_array($user,$master))
    {
        return "<img src='img/adminuserico.gif'> ����Ա";
    }
    else
    {
        return "��ͨ��Ա";
    }
}

?>