<?php
/**
 * ϵͳ����
 *
 * @version        $Id: sys_task.php 1 23:07 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Task');
if(empty($dopost)) $dopost = '';

//ɾ��
if($dopost=='del')
{
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_task` WHERE id='$id' ");
    ShowMsg("�ɹ�ɾ��һ������", "sys_task.php");
    exit();
}
include DedeInclude('templets/sys_task.htm');