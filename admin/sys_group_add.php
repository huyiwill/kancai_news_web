<?php
/**
 * ϵͳȨ�������
 *
 * @version        $Id: sys_group_add.php 1 22:28 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Group');
if(!empty($dopost))
{
    $row = $dsql->GetOne("SELECT * FROM #@__admintype WHERE rank='".$rankid."'");
    if(is_array($row))
    {
        ShowMsg('�������������ļ���ֵ�Ѵ��ڣ��������ظ�!', '-1');
        exit();
    }
    if($rankid > 10)
    {
        ShowMsg('�鼶��ֵ���ܴ���10�� ����һ��Ȩ�����þ���Ч!', '-1');
        exit();
    }
    $AllPurviews = '';
    if(is_array($purviews))
    {
        foreach($purviews as $pur)
        {
            $AllPurviews = $pur.' ';
        }
        $AllPurviews = trim($AllPurviews);
    }
    $dsql->ExecuteNoneQuery("INSERT INTO #@__admintype(rank,typename,system,purviews) VALUES ('$rankid','$groupname', 0, '$AllPurviews');");
    ShowMsg("�ɹ�����һ���µ��û���!", "sys_group.php");
    exit();
}
include DedeInclude('templets/sys_group_add.htm');