<?php
/**
 * @version        $Id: story_edit_photo_action.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_Edit');
include_once(DEDEINC. "/image.func.php");
include_once(DEDEINC. "/oxwindow.class.php");
require_once(DEDEADMIN. "/inc/inc_archives_functions.php");

if( empty($chapterid)
|| (!empty($addchapter) && !empty($chapternew)) )
{
    if(empty($chapternew))
    {
         ShowMsg("�����㷢��������ûѡ���½ڣ�ϵͳ�ܾ�������", "-1");
         exit();
    }
    $dsql = new DedeSql();
    $row = $dsql->GetOne("SELECT * FROM #@__story_chapter WHERE bookid='$bookid' ORDER BY chapnum DESC");
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
      ShowMsg("�����½�ʧ�ܣ�����ԭ��","-1");
        exit();
  }
}else
{
    $dsql = new DedeSql();
}

//��ø���Ŀ
$nrow = $dsql->GetOne("SELECT * FROM #@__story_catalog WHERE id='$catid' ");
$bcatid = $nrow['pid'];
$booktype = $nrow['booktype'];

if(empty($bcatid)) $bcatid = 0;
if(empty($booktype)) $booktype = 0;


$addtime = time();

//�����ϴ�������ͼ
if(!isset($isremote)) $isremote = 0;
$bigpic = UploadOneImage('imgfile',$imgurl,$isremote);

$adminID = $cuserLogin->getUserID();


//----------------------------------
$inQuery = "
   UPDATE `#@__story_content` SET `title`='$title',`bookname`='$bookname',
   `chapterid`='$chapterid',`sortid`='$sortid',`bigpic`='$bigpic'
  WHERE id='$cid'
";

if(!$dsql->ExecuteNoneQuery($inQuery)){
    ShowMsg("�����ݱ��浽���ݿ�ʱ�������飡".str_repolace("'","`", $dsql->GetError().$inQuery), "-1");
    $dsql->Close();
    exit();
}

$arcID = $cid;

//����HTML
//---------------------------------

//$artUrl = MakeArt($arcID,true);
if(empty($artcontentUrl))$artcontentUrl="";
if($artcontentUrl=="") $artcontentUrl = $cfg_mainsite.$cfg_cmspath."/book/show-photo.php?id=$arcID&bookid=$bookid&chapterid=$chapterid";

require_once(DEDEROOT. '/book/include/story.view.class.php');
$bv = new BookView($bookid, 'book');
$artUrl = $bv->MakeHtml();
$bv->Close();

//---------------------------------
//���سɹ���Ϣ
//----------------------------------
$msg = "
������ѡ����ĺ���������
<a href='story_photo_edit.php?cid={$cid}'><u>�����޸�</u></a>
&nbsp;&nbsp;
<a href='$artUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='$artcontentUrl' target='_blank'><u>Ԥ������</u></a>
&nbsp;&nbsp;
<a href='story_list_content.php?bookid={$bookid}'><u>������������</u></a>
&nbsp;&nbsp;
<a href='story_books.php'><u>��������ͼ��</u></a>
";

$wintitle = "�ɹ��޸����ݣ�";
$wecome_info = "���ع���::�޸���������";
$win = new OxWindow();
$win->AddTitle("�ɹ�����������");
$win->AddMsgItem($msg);
$winform = $win->GetWindow("hand", "&nbsp;", false);
$win->Display();
//ClearAllLink();
