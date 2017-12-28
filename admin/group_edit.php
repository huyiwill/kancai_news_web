<?php
/**
 *   �༭Ȧ��
 *
 * @version        $Id: group_edit.php 1 15:34 2011-1-21 tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
CheckPurview('group_Edit');

$id = preg_replace("#[^0-9]#", "", $id);
$action = isset($action) ? trim($action) : '';

if($id < 1)
{
    ShowMsg("���зǷ�����!.","-1");
    exit();
}

//ȡ��Ȧ����Ϣ
$row = $db->GetOne("SELECT * FROM #@__groups WHERE groupid='{$id}'");
$groupsname = $row['groupname'];
$groupstoreid = $row['storeid'];
$groupishidden = $row['ishidden'];
$groupissystem = $row['issystem'];
$groupcreater = $row['creater'];
$groupimg     = $row['groupimg'];
$ismaster     = $row['ismaster'];
$groupdes     = htmlspecialchars($row['des']);
$groupisindex = $row['isindex'];
$groupsmalltype = $row['smalltype'];

//����С���������
$smalltypes    = $row['smalltype'];
$lists            = array();
$smalltypes    = @explode(",", $smalltypes);
foreach($smalltypes as $k)
{
    $kk = @explode("|",$k);
    @array_push($lists,$kk[1]);
}


//====����Ȧ����Ϣ=====//
if($action=="save")
{
    $groupname = cn_substr($groupname,75);
    $storeid = preg_replace("#[^0-9]#", "", $store);
    $issystem = preg_replace("#[^0-1]#", "", $issystem);
    $ishidden = preg_replace("#[^0-1]#", "", $ishidden);
    if(!isset($isindex))
    {
        $isindex = $groupisindex;
    }
    $isindex  = preg_replace("#[^0-1]#", "", $isindex);
    $creater =  cn_substr($creater, 15);
    $master =  cn_substr($master, 70);
    $description = cn_substr($des, 100);
    $row = $db->GetOne("SELECT tops FROM #@__store_groups WHERE storeid='{$storeid}'");
    if($row['tops'] >0 )
    {
        $rootstoreid = $row['tops'];
    }
    else
    {
        $rootstoreid = $storeid;
    }

    //�����ϴ�������ͼ
    if(empty($ddisremote))
    {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('litpic', $picname, $ddisremote);
    if(empty($litpic))
    {
        $litpic = $groupimg;
    }

    if($isindex < 1)
    {
        $isindex = 0;
    }
    $inQuery = "UPDATE #@__groups SET groupname='".$groupname."',des='".$description."',groupimg='".$litpic."',rootstoreid='{$rootstoreid}',storeid='{$storeid}',creater='".$creater."',ismaster='".$master."',issystem='{$issystem}',ishidden='{$ishidden}',isindex='".$isindex."' WHERE groupid='{$id}'";
    if(!$db->ExecuteNoneQuery($inQuery))
    {
        ShowMsg("�����ݸ��µ����ݿ�groups��ʱ�������飡","-1");
        exit();
    }
    else
    {
        ShowMsg("�ɹ�����Ȧ�����ã�","-1");
        exit();
    }
}

//�����������
if(!$groupimg||empty($groupimg))
{
    $groupimg = "img/pview.gif";
}

//��Ŀ�ݹ�
$db->SetQuery("SELECT * FROM #@__store_groups WHERE tops=0 ORDER BY orders ASC");
$db->Execute(1);
$option = '';
while($rs = $db->GetArray(1))
{
    $selected = "";
    if($rs['storeid']==$groupstoreid)
    {
        $selected = "selected='selected'";
    }
    $option .= "<option value='".$rs['storeid']."' ".$selected.">".$rs['storename']."</option>\n";
    $v = $rs['storeid'];
    $db->SetQuery("SELECT * FROM #@__store_groups WHERE tops='{$v}' ORDER BY orders ASC");
    $db->Execute(2);
    while($rs = $db->GetArray(2))
    {
        $selected = "";
        if($rs['storeid']==$groupstoreid)
        {
            $selected = "selected='selected'";
        }
        $option .= "<option value='".$rs['storeid']."' ".$selected.">--".$rs['storename']."</option>\n";
    }
}
$db->SetQuery("SELECT * FROM #@__group_smalltypes ORDER BY disorder ASC");
$db->Execute();
$smalltypes_option = '';
while($rs = $db->GetArray())
{
    $selected = "";
    if(in_array($rs['id'],$lists))
    {
        $selected = "selected='selected'";
    }
    $smalltypes_option .= "<option value='".$rs['name']."|".$rs['id']."' ".$selected.">".$rs['name']."</option>\n";
}

require_once(DEDEADMIN."/templets/group_edit.htm");

?>