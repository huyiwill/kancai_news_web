<?php
/**
 * @version        $Id: story_add_content_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
require_once(DEDEINC. "/oxwindow.class.php");
require_once(DEDEROOT. '/book/include/story.func.php');
if( empty($chapterid)
|| (!empty($addchapter) && !empty($chapternew)) )
{
    if(empty($chapternew))
    {
        ShowMsg("�����㷢��������ûѡ���½ڣ�ϵͳ�ܾ�������", "-1");
        exit();
    }
    $row = $dsql->GetOne("SELECT * FROM #@__story_chapter WHERE bookid='$bookid' ORDER BY chapnum desc");
    if(is_array($row))
    {
        $nchapnum = $row['chapnum']+1;
    }
    else
    {
        $nchapnum = 1;
    }
    $query = "INSERT INTO `#@__story_chapter`(`bookid`,`catid`,`chapnum`,`mid`,`chaptername`,`bookname`)
            VALUES ('$bookid', '$catid', '$nchapnum', '0', '$chapternew','$bookname');";
    $rs = $dsql->ExecuteNoneQuery($query);
    if($rs)
    {
        $chapterid = $dsql->GetLastID();
    }
    else
    {
        ShowMsg("�����½�ʧ�ܣ�����ԭ��","-1");
        exit();
    }
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];
if(empty($bcatid))
{
    $bcatid = 0;
}
if(empty($booktype))
{
    $booktype = 0;
}
$addtime = time();

//�����ϴ�������ͼ
//$litpic = GetDDImage('litpic',$litpicname,0);
$adminID = $cuserLogin->getUserID();

//�������һ��С˵������˳����
$lrow = $dsql->GetOne("SELECT sortid From #@__story_content WHERE bookid='$bookid' AND chapterid='$chapterid' ORDER BY sortid DESC");
if(empty($lrow))
{
    $sortid = 1;
}
else
{
    $sortid = $lrow['sortid']+1;
}
$inQuery = "
INSERT INTO `#@__story_content`(`title`,`bookname`,`chapterid`,`catid`,`bcatid`,`bookid`,`booktype`,`sortid`,
`mid`,`bigpic`,`body`,`addtime`)
VALUES ('$title','$bookname', '$chapterid', '$catid','$bcatid', '$bookid','$booktype','$sortid', '0', '' , '', '$addtime');";
if(!$dsql->ExecuteNoneQuery($inQuery))
{
    ShowMsg("�����ݱ��浽���ݿ�ʱ�������飡".$dsql->GetError().$inQuery,"-1");
    $dsql->Close();
    exit();
}
$arcID = $dsql->GetLastID();
WriteBookText($arcID,$body);

//����ͼ���������
$row = $dsql->GetOne("Select count(id) AS dd FROM #@__story_content  WHERE bookid = '$bookid' ");
$dsql->ExecuteNoneQuery("UPDATE #@__story_books SET postnum='{$row['dd']}',lastpost='".time()."' WHERE bid='$bookid' ");

//�����½ڵ�������
$row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content  WHERE bookid = '$bookid' AND chapterid='$chapterid' ");
$dsql->ExecuteNoneQuery("UPDATE #@__story_chapter SET postnum='{$row['dd']}' WHERE id='$chapterid' ");

//����HTML
//$artUrl = MakeArt($arcID,true);
if(empty($artcontentUrl)) $artcontentUrl = '';

if($artcontentUrl=="") $artcontentUrl = $cfg_cmspath."/book/story.php?id=$arcID";

require_once(DEDEROOT.'/book/include/story.view.class.php');
$bv = new BookView($bookid, 'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//���سɹ���Ϣ
$msg = "
������ѡ����ĺ���������
<a href='story_add_content.php?bookid={$bookid}'><u>��������</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>Ԥ��С˵</u></a>
&nbsp;&nbsp;
<a href='$artcontentUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='story_list_content.php?bookid={$bookid}'><u>������������</u></a>
&nbsp;&nbsp;
<a href='story_books.php'><u>��������ͼ��</u></a>
";
$wintitle = "�ɹ��������£�";
$wecome_info = "���ع���::��������";
$win = new OxWindow();
$win->AddTitle("�ɹ��������£�");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand","&nbsp;",false);
$win->Display();
//ClearAllLink();
