<?php
/**
 *
 * ��������
 *
 * @version        $Id: flink.php 1 15:38 2010��7��8��Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
if(empty($dopost)) $dopost = '';


if($dopost=='save')
{
    $validate = isset($validate) ? strtolower(trim($validate)) : '';
    $svali = GetCkVdValue();
    if($validate=='' || $validate!=$svali)
    {
        ShowMsg('��֤�벻��ȷ!','-1');
        exit();
    }
    $msg = htmlspecialchars($msg);
    $email = htmlspecialchars($email);
    $webname = htmlspecialchars($webname);
    $url = htmlspecialchars($url);
    $logo = htmlspecialchars($logo);
    $typeid = intval($typeid);
    $dtime = time();
    $query = "INSERT INTO `#@__flink`(sortrank,url,webname,logo,msg,email,typeid,dtime,ischeck)
                    VALUES('50','$url','$webname','$logo','$msg','$email','$typeid','$dtime','0')";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg('�ɹ�����һ�����ӣ�����Ҫ��˺������ʾ!','-1',1);
}

//��ʾģ��(��PHP�ļ�)
include_once(DEDETEMPLATE.'/plus/flink-list.htm');