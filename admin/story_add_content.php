<?php
/**
 * @version        $Id: story_add_content.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_New');
if(!isset($action)) $action = '';

if(empty($bookid))
{
    ShowMsg("��������","-1");
    exit();
}
$bookinfos = $dsql->GetOne("SELECT catid,bcatid,bookname,booktype FROM #@__story_books WHERE bid='$bookid' ");
if(empty($bookinfos['booktype'])) $bookinfos['booktype'] = '';

if($bookinfos['booktype']==1)
{
    header("location:story_add_photo.php?bookid={$bookid}");
    exit();
}

//��ȡ������Ŀ
$dsql->SetQuery("SELECT id,classname,pid,rank FROM #@__story_catalog ORDER BY rank ASC");
$dsql->Execute();
$ranks = Array();
$btypes = Array();
$stypes = Array();
while($row = $dsql->GetArray())
{
    if($row['pid']==0)
    {
        $btypes[$row['id']] = $row['classname'];
    }
    else
    {
        $stypes[$row['pid']][$row['id']] = $row['classname'];
    }
    $ranks[$row['id']] = $row['rank'];
}
$lastid = $row['id'];
$msg = '';
$dsql->SetQuery("SELECT id,chapnum,chaptername FROM #@__story_chapter WHERE bookid='$bookid' ORDER BY chapnum DESC");
$dsql->Execute();
$chapters = Array();
$chapnums = Array();
while($row = $dsql->GetArray())
{
    $chapters[$row['id']] = $row['chaptername'];
    $chapnums[$row['id']] = $row['chapnum'];
}
$catid = $bookinfos['catid'];
$bcatid = $bookinfos['bcatid'];
$bookname = $bookinfos['bookname'];
$booktype = $bookinfos['booktype'];
require_once(DEDEADMIN. '/templets/story_add_content.htm');
