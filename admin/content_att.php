<?php
/**
 * ��������
 *
 * @version        $Id: content_att.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_Att');
if(empty($dopost)) $dopost = '';

//�������
if($dopost=="save")
{
    $startID = 1;
    $endID = $idend;
    for(; $startID<=$endID; $startID++)
    {
        $att = ${'att_'.$startID};
        $attname = ${'attname_'.$startID};
        $sortid = ${'sortid_'.$startID};
        $query = "UPDATE `#@__arcatt` SET `attname`='$attname',`sortid`='$sortid' WHERE att='$att' ";
        $dsql->ExecuteNoneQuery($query);
    }
    echo "<script> alert('�ɹ������Զ��ĵ������Ա�'); </script>";
}

include DedeInclude('templets/content_att.htm');