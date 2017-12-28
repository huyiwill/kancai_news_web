<?php
/**
 * �����༭
 *
 * @version        $Id: media_edit.php 1 11:17 2010��7��19��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");

//Ȩ�޼��
CheckPurview('sys_Upload,sys_MyUpload');
if(empty($dopost)) $dopost = "";
$backurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "javascript:history.go(-1);";

/*---------------------------
function __del_file() //ɾ������
-----------------------------*/
if($dopost=='del')
{
    CheckPurview('sys_DelUpload');
    if(empty($ids))
    {
        $ids="";
    }
    if($ids=="")
    {
        $myrow = $dsql->GetOne("SELECT url FROM #@__uploads WHERE aid='".$aid."'");
        $truefile = $cfg_basedir.$myrow['url'];
        $rs = 0;
        if(!file_exists($truefile)||$myrow['url']=="")
        {
            $rs = 1;
        } else {
            $rs = @unlink($truefile);
            //�������Զ�̸�������Ҫͬ��ɾ���ļ�
            if($cfg_remote_site=='Y')
            {
                if($ftp->connect($ftpconfig) && $remoteuploads == 1)
                {
                    $remotefile = str_replace(DEDEROOT, '', $truefile);
                    $ftp->delete_file($remotefile);
                }
            }
        }
        if($rs==1)
        {
            $msg = "�ɹ�ɾ��һ��������";
            $dsql->ExecuteNoneQuery("DELETE FROM #@__uploads WHERE aid='".$aid."'");
        }
        ShowMsg($msg,$backurl);
        exit();
    } else {
        $ids = explode(',', $ids);
        $idquery = "";
        foreach($ids as $aid)
        {
            if($idquery=="")
            {
                $idquery .= " WHERE aid='$aid' ";
            }
            else
            {
                $idquery .= " OR aid='$aid' ";
            }
        }
        $dsql->SetQuery("SELECT aid,url FROM #@__uploads $idquery ");
        $dsql->Execute();
        
        //�������Զ�̸�������Ҫͬ��ɾ���ļ�
        if($cfg_remote_site=='Y' && $remoteuploads == 1)
        {
            $ftp->connect($ftpconfig);
        }
        while($myrow=$dsql->GetArray())
        {
            $truefile = $cfg_basedir.$myrow['url'];
            $rs = 0;
            if(!file_exists($truefile) || $myrow['url']=="")
            {
                $rs = 1;
            }
            else
            {
                $rs = @unlink($truefile);
                if($cfg_remote_site=='Y' && $remoteuploads == 1)
                {
                    $remotefile = str_replace(DEDEROOT, '', $truefile);
                    $ftp->delete_file($remotefile);
                }
            }
            if($rs==1)
            {
                $dsql->ExecuteNoneQuery("DELETE FROM #@__uploads WHERE aid='".$myrow['aid']."'");
            }
        }
        ShowMsg('�ɹ�ɾ��ѡ�����ļ���',$backurl);
        exit();
    }
}
/*--------------------------------
function __save_edit() //�������
-----------------------------------*/
else if($dopost=='save')
{
    if($aid=="") exit();

    //����Ƿ����޸�Ȩ��
    $myrow = $dsql->GetOne("SELECT * FROM #@__uploads WHERE aid='".$aid."'");
    if($myrow['mid']!=$cuserLogin->getUserID())
    {
        CheckPurview('sys_Upload');
    }

    //����ļ�����
    $addquery = "";
    if(is_uploaded_file($upfile))
    {
        if($mediatype==1)
        {
            $sparr = Array("image/pjpeg","image/jpeg","image/gif","image/png","image/xpng","image/wbmp");
            if(!in_array($upfile_type,$sparr))
            {
                ShowMsg("���ϴ��Ĳ���ͼƬ���͵��ļ���","javascript:history.go(-1);");
                exit();
            }
        }
        else if($mediatype==2)
        {
            $sparr = Array("application/x-shockwave-flash");
            if(!in_array($upfile_type,$sparr))
            {
                ShowMsg("���ϴ��Ĳ���Flash���͵��ļ���","javascript:history.go(-1);");
                exit();
            }
        }else if($mediatype==3)
        {
            if(!preg_match('#audio|media|video#i', $upfile_type))
            {
                ShowMsg("���ϴ���Ϊ����ȷ���͵�Ӱ���ļ���","javascript:history.go(-1);");
                exit();
            }
            if(!preg_match("#\.".$cfg_mediatype."#", $upfile_name))
            {
                ShowMsg("���ϴ���Ӱ���ļ���չ���޷���ʶ�������ϵͳ���õĲ�����","javascript:history.go(-1);");
                exit();
            }
        }else
        {
            if(!preg_match("#\.".$cfg_softtype."#", $upfile_name))
            {
                ShowMsg("���ϴ��ĸ�����չ���޷���ʶ�������ϵͳ���õĲ�����","javascript:history.go(-1);");
                exit();
            }
        }

        //�����ļ�
        $nowtime = time();
        $oldfile = $myrow['url'];
        $oldfiles = explode('/', $oldfile);
        $fullfilename = $cfg_basedir.$oldfile;
        $oldfile_path = preg_replace("#".$oldfiles[count($oldfiles)-1]."$#", "", $oldfile);
        if(!is_dir($cfg_basedir.$oldfile_path))
        {
            MkdirAll($cfg_basedir.$oldfile_path, 777);
            CloseFtp();
        }
        @move_uploaded_file($upfile, $fullfilename);
        if($mediatype==1)
        {
            require_once(DEDEINC."/image.func.php");
            if(in_array($upfile_type, $cfg_photo_typenames))
            {
                WaterImg($fullfilename, 'up');
            }
        }
        $filesize = $upfile_size;
        $imgw = 0;
        $imgh = 0;
        if($mediatype==1)
        {
            $info = "";
            $sizes[0] = 0; $sizes[1] = 0;
            $sizes = @getimagesize($fullfilename, $info);
            $imgw = $sizes[0];
            $imgh = $sizes[1];
        }
        if($imgw>0)
        {
            $addquery = ",width='$imgw',height='$imgh',filesize='$filesize' ";
        }
        else
        {
            $addquery = ",filesize='$filesize' ";
        }
    }
    else
    {
        $fileurl = $filename;
    }

    //д�����ݿ�
    $query = " UPDATE #@__uploads SET title='$title',mediatype='$mediatype',playtime='$playtime'";
    $query .= "$addquery WHERE aid='$aid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg('�ɹ�����һ�򸽼����ݣ�','media_edit.php?aid='.$aid);
    exit();
}

//��ȡ������Ϣ
$myrow = $dsql->GetOne("SELECT * FROM #@__uploads WHERE aid='".$aid."'");
if(!is_array($myrow))
{
    ShowMsg('�����Ҳ����˱�ŵĵ�����','javascript:;');
    exit();
}
include DedeInclude('templets/media_edit.htm');