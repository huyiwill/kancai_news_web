<?php
/**
 * ϵͳȨ����༭
 *
 * @version        $Id: sys_group_edit.php 1 22:28 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Group');
if(empty($dopost)) $dopost = "";

if($dopost=='save')
{
    if($rank==10)
    {
        ShowMsg('��������Ա��Ȩ�޲��������!', 'sys_group.php');
        exit();
    }
    $purview = "";
    if(is_array($purviews))
    {
        foreach($purviews as $p)
        {
            $purview .= "$p ";
        }
        $purview = trim($purview);
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__admintype` SET typename='$typename',purviews='$purview' WHERE CONCAT(`rank`)='$rank'");
    ShowMsg('�ɹ������û����Ȩ��!', 'sys_group.php');
    exit();
}
else if($dopost=='del')
{
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__admintype` WHERE CONCAT(`rank`)='$rank' AND system='0';");
    ShowMsg("�ɹ�ɾ��һ���û���!","sys_group.php");
    exit();
}
$groupRanks = Array();
$groupSet = $dsql->GetOne("SELECT * FROM `#@__admintype` WHERE CONCAT(`rank`)='{$rank}' ");
$groupRanks = explode(' ', $groupSet['purviews']);
include DedeInclude('templets/sys_group_edit.htm');

//����Ƿ��Ѿ��д�Ȩ��
function CRank($n)
{
    global $groupRanks;
    return in_array($n,$groupRanks) ? ' checked' : '';
}