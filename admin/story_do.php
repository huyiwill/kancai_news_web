<?php
/**
 * @version        $Id: story_do.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_Del');
require_once(DEDEINC. "/oxwindow.class.php");
if(empty($action))
{
    ShowMsg("��ûָ���κβ�����","-1");
    exit();
}

/*--------------------
function DelBook()
ɾ������ͼ��
-------------------*/
if($action=='delbook')
{
    $bids = explode(',', $bid);
    foreach($bids as $i => $bid)
    {
        if(intval($bid)<=0)
        {
            continue;
        }
        $row = $dsql->GetOne("SELECT booktype FROM #@__story_books WHERE bid='$bid' ");
        $dsql->ExecuteNoneQuery("DELETE FROM #@__story_books WHERE bid='$bid' ");
        $dsql->ExecuteNoneQuery("DELETE FROM #@__story_chapter  WHERE bookid='$bid' ");

        //ɾ��ͼƬ
        if(empty($row['booktype']))
        {
            $row['booktype'] = '';
        }
        if($row['booktype']==1)
        {
            $dsql->SetQuery("SELECT bigpic FROM #@__story_content WHERE bookid='$bid' ");
            $dsql->Execute();
            while($row = $dsql->GetArray())
            {
                $bigpic = $row['bigpic'];
                if( $bigpic!="" && !eregi('^http://',$bigpic) )
                {
                    @unlink($cfg_basedir.$bigpic);
                }
            }
        }
        $dsql->ExecuteNoneQuery("DELETE FROM #@__story_content WHERE bookid='$bid' ");
    }
    $i = $i+1;
    if(empty($ENV_GOBACK_URL))
    {
        $ENV_GOBACK_URL = 'story_books.php';
    }
    ShowMsg("�ɹ�ɾ�� {$i} ��ͼ�飡",$ENV_GOBACK_URL);
    exit();
}

/*--------------------
function DelStoryContent()
ɾ��ͼ������
-------------------*/
else if($action=='delcontent')
{

    $row = $dsql->GetOne("SELECT bigpic,chapterid,bookid FROM #@__story_content WHERE id='$cid' ");
    $chapterid = $row['chapterid'];
    $bookid = $row['bookid'];

    //���ͼƬ��Ϊ�գ���ɾ��ͼƬ
    if( $row['bigpic']!="" && !eregi('^http://',$row['bigpic']) )
    {
        @unlink($cfg_basedir.$row['bigpic']);
    }
    $dsql->ExecuteNoneQuery(" DELETE FROM #@__story_content WHERE id='$cid' ");

    //����ͼ���¼
    $row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content WHERE bookid='$bookid' ");
    $dsql->ExecuteNoneQuery("Update #@__story_books SET postnum='{$row['dd']}' WHERE bid='$bookid' ");

    //�����½ڼ�¼
    $row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content WHERE chapterid='$chapterid' ");
    $dsql->ExecuteNoneQuery("Update #@__story_chapter SET postnum='{$row['dd']}' WHERE id='$chapterid' ");
    ShowMsg("�ɹ�ɾ��ָ�����ݣ�",$ENV_GOBACK_URL);
    exit();
}

/*--------------------
function EditChapter()
�����½���Ϣ
-------------------*/
else if($action=='editChapter')
{

    require_once(DEDEINC."/charSET.func.php");
    //$chaptername = gb2utf8($chaptername);
    $dsql->ExecuteNoneQuery("Update #@__story_chapter SET chaptername='$chaptername',chapnum='$chapnum' WHERE id='$cid' ");
    AjaxHead();
    echo "<font color='red'>�ɹ������½ڣ�{$chaptername} �� [<a href=\"javascript:CloseLayer('editchapter')\">�ر���ʾ</a>]</font> <br /><br /> ��ʾ���޸��½����ƻ��½����ֱ��������޸ģ�Ȼ�����ұߵ� [����] �ᱣ�档 ";
    exit();
}

/*--------------------
function DelChapter()
ɾ���½���Ϣ
-------------------*/
else if($action=='delChapter')
{
    $row = $dsql->GetOne("SELECT c.bookid,b.booktype FROM #@__story_chapter c LEFT JOIN  #@__story_books b ON b.bid=c.bookid WHERE c.id='$cid' ");
    $bookid = $row['bookid'];
    $booktype = $row['booktype'];
    $dsql->ExecuteNoneQuery("DELETE FROM #@__story_chapter WHERE id='$cid' ");

    //ɾ��ͼƬ
    if($booktype==1)
    {
        $dsql->SetQuery("SELECT bigpic FROM #@__story_content WHERE bookid='$bookid' ");
        $dsql->Execute();
        while($row = $dsql->GetArray())
        {
            $bigpic = $row['bigpic'];
            if( $bigpic!="" && !eregi('^http://',$bigpic) )
            {
                @unlink($cfg_basedir.$bigpic);
            }
        }
    }
    $dsql->ExecuteNoneQuery("DELETE FROM #@__story_content WHERE chapterid='$cid' ");

    //����ͼ���¼
    $row = $dsql->GetOne("SELECT count(id) AS dd FROM #@__story_content WHERE bookid='$bookid' ");
    $dsql->ExecuteNoneQuery("UPDATE #@__story_books SET postnum='{$row['dd']}' WHERE bid='$bookid' ");
    ShowMsg("�ɹ�ɾ��ָ���½ڣ�",$ENV_GOBACK_URL);
    exit();
}

/*---------------
function EditChapterAll()
�����޸��½�
-------------------*/
else if($action=='upChapterSort')
{
    if(isSET($ids) && is_array($ids))
    {
        foreach($ids as $cid)
        {
            $chaptername = ${'chaptername_'.$cid};
            $chapnum= ${'chapnum_'.$cid};
            $dsql->ExecuteNoneQuery("UPDATE #@__story_chapter SET chaptername='$chaptername',chapnum='$chapnum' WHERE id='$cid' ");
        }
    }
    ShowMsg("�ɹ�����ָ���½���Ϣ��", $ENV_GOBACK_URL);
    exit();
}
