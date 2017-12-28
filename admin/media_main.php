<?php
/**
 * ��������
 *
 * @version        $Id: media_main.php 1 11:17 2010��7��19��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC."/common.func.php");
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
if(empty($dopost)) $dopost = '';

//�ļ�ʽ������
if($dopost=='filemanager')
{
    if(file_exists('./file_manage_main.php'))
    {
        header("location:file_manage_main.php?activepath=$cfg_medias_dir");
    }
    else
    {
        ShowMsg("�Ҳ����ļ��������������Ѿ�ж��!","-1");
    }
    exit();
}

//���ݿ����
// ------------------------------------------------------------------------
if(empty($keyword)) $keyword = "";
$addsql = " WHERE (u.title LIKE '%$keyword%' OR u.url LIKE '%$keyword%') ";
if(empty($membertype))
{
    $membertype = 0;
}
if($membertype==1)
{
    $addsql .= " AND u.mid>0 ";
}
else if($membertype==2)
{
    $addsql .= " AND u.mid>0 ";
}

if(empty($mediatype))
{
    $mediatype = 0;
}
if($mediatype>1)
{
    $addsql .= " AND u.mediatype='$membertype' ";
}
$sql = "SELECT u.aid,u.title,u.url,u.mediatype,u.filesize,u.mid,u.uptime
,a.userid AS adminname,m.userid AS membername
FROM #@__uploads u
LEFT JOIN #@__admin a ON  a.id = u.mid
LEFT JOIN #@__member m ON m.mid = u.mid
$addsql ORDER BY u.aid DESC";
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetParameter("mediatype",$mediatype);
$dlist->SetParameter("keyword",$keyword);
$dlist->SetParameter("membertype",$membertype);
$dlist->SetTemplate(DEDEADMIN."/templets/media_main.htm");
$dlist->SetSource($sql);
$dlist->Display();

function MediaType($tid,$nurl)
{
    if($tid==1)
    {
        return "ͼƬ<a href=\"javascript:;\" onClick=\"ChangeImage('$nurl');\"><img src='../include/dialog/img/picviewnone.gif' name='picview' border='0' alt='Ԥ��'></a>";
    }
    else if($tid==2)
    {
        return "FLASH";
    }
    else if($tid==3)
    {
        return "��Ƶ/��Ƶ";
    }
    else
    {
        return "����/����";
    }
}

function GetFileSize($fs)
{
    $fs = $fs/1024;
    return trim(sprintf("%10.1f",$fs)." K");
}

function UploadAdmin($adminid,$mid)
{
    if($adminid!='') return $adminid;
    else return $mid;
}