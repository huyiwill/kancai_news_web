<?php
/**
 * �༭����
 *
 * @version        $Id: sys_task_edit.php 1 23:07 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('sys_Task');
if(empty($dopost)) $dopost = '';

if($dopost=='save')
{
    $starttime = empty($starttime) ? 0 : GetMkTime($starttime);
    $endtime = empty($endtime) ? 0 : GetMkTime($endtime);
    $runtime = $h.':'.$m;
    $query = "UPDATE `#@__sys_task`
    SET `taskname` = '$taskname',
    `dourl` = '$dourl',
    `islock` = '$nislock',
    `runtype` = '$runtype',
    `runtime` = '$runtime',
    `starttime` = '$starttime',
    `endtime` = '$endtime',
    `freq` = '$freq',
    `description` = '$description',
    `parameter` = '$parameter'
    WHERE id='$id' ";
    $rs = $dsql->ExecuteNoneQuery($query);
    if($rs) 
    {
        ShowMsg('�ɹ��޸�һ������!', 'sys_task.php');
    }
    else
    {
        ShowMsg('�޸�����ʧ��!'.$dsql->GetError(), 'javascript:;');
    }
    exit();
}

$row = $dsql->GetOne("SELECT * FROM `#@__sys_task` WHERE id='$id' ");
include DedeInclude('templets/sys_task_edit.htm');