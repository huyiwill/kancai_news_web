<?php
/**
 * @version        $Id: story_add_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */


require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_New');
require_once(DEDEINC. "/image.func.php");
require_once(DEDEINC. "/oxwindow.class.php");
require_once(dirname(__FILE__). "/inc/inc_archives_functions.php");
if(!isset($iscommend))
{
    $iscommend = 0;
}
if($catid==0)
{
    ShowMsg("��ָ��ͼ��������Ŀ��", "-1");
    exit();
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];
$pubdate = GetMkTime($pubdate);
$bookname = cn_substr($bookname,50);
if($keywords!="") $keywords = trim(cn_substr($keywords,60));

if(empty($author))$author=$cuserLogin->getUserName();

//�����ϴ�������ͼ
if($litpic != ""){
    $litpic = GetDDImage('litpic',$litpicname,0);
}
$adminID = $cuserLogin->getUserID();

//�Զ�ժҪ
if($description=="" && $cfg_auot_description>0)
{
    $description = stripslashes(cn_substr(html2text($body),$cfg_auot_description));
    $description = addslashes($description);
}

$inQuery = "
INSERT INTO `#@__story_books`(`catid`,`bcatid`,`booktype`,`iscommend`,`click`,`freenum`,`bookname`,`author`,`mid`,`litpic`,`pubdate`,`lastpost`,`postnum`,`lastfeedback`,`feedbacknum`,`weekcc`,`monthcc`,`weekup`,`monthup`,`description`,`body`,`keywords`,`userip`,`senddate` ,`arcrank`,`goodpost`,`badpost`,`notpost`) VALUES ('$catid','$bcatid','$booktype', '$iscommend', '$click', '$freenum', '$bookname', '$author', '0', '$litpic', '$pubdate', '$pubdate', '0', '0', '0', '0', '0', '0', '0', '$description' , '$body' , '$keywords', '','".time()."','$arcrank','0','0','0')";
if(!$dsql->ExecuteNoneQuery($inQuery))
{
    ShowMsg("�����ݱ��浽���ݿ�ʱ�������飡","-1");
    exit();
}
$arcID = $dsql->GetLastID();

//����HTML
require_once(DEDEROOT. '/book/include/story.view.class.php');
$bv = new BookView($arcID, 'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//���سɹ���Ϣ
$msg = "
������ѡ����ĺ���������
<a href='./story_add.php?catid=$catid'><u>��������ͼ��</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>�鿴ͼ��</u></a>
&nbsp;&nbsp;
<a href='./story_add_content.php?bookid={$arcID}'><u>����ͼ������</u></a>
&nbsp;&nbsp;
<a href='./story_books.php'><u>����ͼ��</u></a>
";
$wintitle = "�ɹ�����ͼ�飡";
$wecome_info = "���ع���::����ͼ��";
$win = new OxWindow();
$win->AddTitle("�ɹ�����һ��ͼ�飺");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand",  "&nbsp;", false);
$win->Display();
