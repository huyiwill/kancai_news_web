<?php
/**
 * @version        $Id: story_add_photo_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_New');
require_once(DEDEINC. "/image.func.php");
require_once(DEDEINC. "/oxwindow.class.php");
require_once(DEDEADMIN. '/inc/inc_archives_functions.php');
if( empty($chapterid)
|| (!empty($addchapter) && !empty($chapternew)) )
{
    if(empty($chapternew))
    {
        ShowMsg("�����㷢��������ûѡ���½ڣ�ϵͳ�ܾ�������","-1");
        exit();
    }

    $row = $dsql->GetOne("SELECT * FROM #@__story_chapter WHERE bookid='$bookid' ORDER BY chapnum DESC");
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
        ShowMsg("�����½�ʧ�ܣ�����ԭ��", "-1");
        exit();
    }
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];
$addtime = time();

//�������һ������������˳����
$lrow = $dsql->GetOne("SELECT sortid FROM #@__story_content WHERE bookid='$bookid' AND chapterid='$chapterid' ORDER BY sortid DESC");
if(empty($lrow))
{
    $sortid = 1;
}
else
{
    $sortid = $lrow['sortid']+1;
}

//�����ϴ���ͼƬ
if(!isset($isremote))
{
    $isremote = 0;
}
//$bigpic = UploadOneImage('bigpic',$bigpicname,$ddisremote);

$adminID = $cuserLogin->getUserID();
$postnum = 0;
for($i=1;$i<=$photonum;$i++)
{
    $bigpic = UploadOneImage('imgfile'.$i,${'imgurl'.$i},$isremote);
    if($bigpic!='')
    {
        $titlen = ${'title'.$i};
        if(empty($titlen))
        {
            $titlen = ${'title'};
        }
        $inQuery = "
       INSERT INTO `#@__story_content`(`title`,`bookname`,`chapterid`,`catid`,`bcatid`,`booktype`,`bookid`,`sortid`,
      `mid`,`bigpic`,`body`,`addtime`)
     VALUES ('$titlen','$bookname', '$chapterid', '$catid','$bcatid','$booktype', '$bookid','$sortid', '0', '$bigpic' , '', '$addtime');";
        $rs = $dsql->ExecuteNoneQuery($inQuery);
        //if(!$rs) echo $inQuery."<hr>\r\n";
        if($rs)
        {
            $sortid++;
            $postnum++;
        }
    }
}
$arcID = $dsql->GetLastID();

//����ͼ���������
$row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content  WHERE bookid = '$bookid' ");
$dsql->ExecuteNoneQuery("UPDATE #@__story_books SET postnum='{$row['dd']}',lastpost='".time()."' WHERE bid='$bookid' ");

//�����½ڵ�������
$row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content  WHERE bookid = '$bookid' AND chapterid='$chapterid' ");
$dsql->ExecuteNoneQuery("UPDATE #@__story_chapter SET postnum='{$row['dd']}' WHERE id='$chapterid' ");
if(empty($arcID))
{
    ShowMsg("û�ɹ������κ�ͼƬ��������ϵͳ�����⣡","-1");
    exit();
}

//����HTML
//$artUrl = MakeArt($arcID,true);
if(empty($artcontentUrl)) $artcontentUrl="";

if($artcontentUrl=="") $artcontentUrl = $cfg_mainsite.$cfg_cmspath."/book/show-photo.php?id=$arcID&bookid=$bookid&chapterid=$chapterid";

require_once(DEDEROOT. '/book/include/story.view.class.php');
$bv = new BookView($bookid, 'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//���سɹ���Ϣ
$msg = "
������ѡ����ĺ���������
<a href='story_add_photo.php?bookid={$bookid}'><u>��������</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='$artcontentUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='story_list_content.php?bookid={$bookid}'><u>������������</u></a>
&nbsp;&nbsp;
<a href='story_books.php'><u>��������ͼ��</u></a>
";
$wintitle = "�ɹ�����ͼƬ��";
$wecome_info = "���ع���::����ͼƬ";
$win = new OxWindow();
$win->AddTitle("�ɹ�����ͼƬ��");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand", "&nbsp;", false);
$win->Display();
