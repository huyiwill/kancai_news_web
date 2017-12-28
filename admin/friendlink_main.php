<?php
/**
 * �������ӹ���
 *
 * @version        $Id: friendlink_main.php 1 8:48 2010��7��13��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/datalistcp.class.php');
setcookie('ENV_GOBACK_URL', $dedeNowurl, time()+3600, '/');

if(empty($keyword)) $keyword = '';
if(empty($ischeck)) {
    $ischeck = 0;
    $ischeckSql = '';
} else {
    if($ischeck==-1) $ischeckSql = " And ischeck < 1 ";
    else $ischeckSql = " And ischeck='$ischeck' ";
}

$selCheckArr = array(0=>'��������', -1=>'δ���', 1=>'��ҳ', 2=>'��ҳ');

$sql = "SELECT * FROM `#@__flink` WHERE  CONCAT(`url`,`webname`,`email`) LIKE '%$keyword%' $ischeckSql ORDER BY dtime desc";

$dlist = new DataListCP();
$dlist->SetParameter('keyword', $keyword);
$dlist->SetParameter('ischeck', $ischeck);
$dlist->SetTemplet(DEDEADMIN.'/templets/friendlink_main.htm');
$dlist->SetSource($sql);
$dlist->display();

function GetPic($pic)
{
    if($pic=='') return '��ͼ��';
    else return "<img src='$pic' width='88' height='31' border='0' />";
}

function GetSta($sta)
{
    if($sta==1) return '��ҳ';
    if($sta==2) return '��ҳ';
    else return 'δ���';
}