<?php
/**
 * @version        $Id: story_edit_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_New');
require_once(DEDEINC. "/image.func.php");
require_once(DEDEINC. "/oxwindow.class.php");
require_once(DEDEADMIN. "/inc/inc_archives_functions.php");
if(!isset($iscommend)) $iscommend = 0;

if($catid==0)
{
    ShowMsg("��ָ��ͼ��������Ŀ��","-1");
    exit();
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];
$pubdate = GetMkTime($pubdate);
$lastpost=time();
$bookname = cn_substr($bookname,50);
if($keywords!="") $keywords = trim(cn_substr($keywords, 60));


//�����ϴ�������ͼ
if($litpic !="") $litpic = GetDDImage('litpic', $litpic, 0);

if($litpicname !="" && $litpic == "") $litpic = GetDDImage('litpic', $litpicname, 0);

$adminID = $cuserLogin->getUserID();

//�Զ�ժҪ
if($description=="" && $cfg_auot_description>0)
{
    $description = stripslashes(cn_substr(html2text($body), $cfg_auot_description));
    $description = addslashes($description);
}
$upQuery = "
Update `#@__story_books`
set catid='$catid',
bcatid='$bcatid',
iscommend='$iscommend',
click='$click',
freenum='$freenum',
arcrank='$arcrank',
bookname='$bookname',
author='$author',
litpic='$litpic',
pubdate='$pubdate',
lastpost='$lastpost',
description='$description',
body='$body',
keywords='$keywords',
status='$status',
ischeck='$ischeck'
where bid='$bookid' ";

if(!$dsql->ExecuteNoneQuery($upQuery))
{
    ShowMsg("�������ݿ�ʱ�������飡".$dsql->GetError(),"-1");
    $dsql->Close();
    exit();
}

//����HTML
require_once(DEDEROOT. '/book/include/story.view.class.php');
$bv = new BookView($bookid, 'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//���سɹ���Ϣ
$msg = "
������ѡ����ĺ���������
<a href='story_edit.php?bookid={$bookid}'><u>�����޸�</u></a>
&nbsp;&nbsp;
<a href='story_add.php?catid={$catid}'><u>������ͼ��</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>Ԥ��ͼ��</u></a>
&nbsp;&nbsp;
<a href='story_add_content.php?bookid={$bookid}'><u>����ͼ������</u></a>
&nbsp;&nbsp;
<a href='story_books.php'><u>����ͼ��</u></a>
";
$wintitle = "�ɹ��޸�ͼ�飡";
$wecome_info = "���ع���::�޸�ͼ��";
$win = new OxWindow();
$win->AddTitle("�ɹ��޸�һ��ͼ�飺");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand", "&nbsp;", false);
$win->Display();
