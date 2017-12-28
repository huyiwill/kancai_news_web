<?php
/**
 * @version        $Id: story_edit_content_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_Edit');
require_once(DEDEINC. "/oxwindow.class.php");
require_once(DEDEROOT. "/book/include/story.func.php");

if( empty($chapterid)
|| (!empty($addchapter) && !empty($chapternew)) )
{
    if(empty($chapternew))
    {
         ShowMsg("�����㷢��������ûѡ���½ڣ�ϵͳ�ܾ�������", "-1");
         exit();
    }
    $row = $dsql->GetOne("SELECT * From #@__story_chapter WHERE bookid='$bookid' ORDER BY chapnum DESC");
    if(is_array($row)) $nchapnum = $row['chapnum']+1;
    else $nchapnum = 1;
    $query = "INSERT INTO `#@__story_chapter`(`bookid`,`catid`,`chapnum`,`mid`,`chaptername`,`bookname`)
            VALUES ('$bookid', '$catid', '$nchapnum', '0', '$chapternew','$bookname');";
    $rs = $dsql->ExecuteNoneQuery($query);
    if($rs){
        $chapterid = $dsql->GetLastID();
    }
    else
  {
      ShowMsg("�����½�ʧ�ܣ�����ԭ��", "-1");
        exit();
  }
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];

if(empty($bcatid)) $bcatid = 0;
if(empty($booktype)) $booktype = 0;


$addtime = time();

$inQuery = "
   UPDATE `#@__story_content` SET `title`='$title',`bookname`='$bookname',
   `chapterid`='$chapterid',`sortid`='$sortid',`body`=''
  WHERE id='$cid'
";

if(!$dsql->ExecuteNoneQuery($inQuery)){
    ShowMsg("��������ʱ�������飡".str_repolace("'","`",$dsql->GetError().$inQuery),"-1");
    $dsql->Close();
    exit();
}

WriteBookText($cid,$body);
if(empty($artcontentUrl))$artcontentUrl="";
if($artcontentUrl=="") $artcontentUrl = $cfg_mainsite.$cfg_cmspath."/book/story.php?id={$cid}";

require_once(DEDEROOT. "/book/include/story.view.class.php");
$bv = new BookView($bookid,'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//---------------------------------
//���سɹ���Ϣ
//----------------------------------
$msg = "
������ѡ����ĺ���������
<a href='story_content_edit.php?cid={$cid}'><u>�����༭</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>Ԥ��С˵</u></a>
&nbsp;&nbsp;
<a href='$artcontentUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='story_list_content.php?bookid={$bookid}'><u>������������</u></a>
&nbsp;&nbsp;
<a href='story_books.php'><u>��������ͼ��</u></a>
";

$wintitle = "�ɹ��޸����£�";
$wecome_info = "���ع���::��������";
$win = new OxWindow();
$win->AddTitle("�ɹ��޸����£�");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand", "&nbsp;", false);
$win->Display();
//ClearAllLink();
