<?php  if(!defined('DEDEINC')) exit('dedecms');
/**
 * �ϴ�����С����
 *
 * @version        $Id: upload.helper.php 1 2010-07-05 11:43:09Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ����Ա�ϴ��ļ���ͨ�ú���
 *
 * @access    public
 * @param     string  $uploadname  �ϴ�����
 * @param     string  $ftype  �ļ�����
 * @param     string  $rnddd  ��׺����
 * @param     bool  $watermark  �Ƿ�ˮӡ
 * @param     string  $filetype  image��media��addon
 *      $file_type='' ����swfupload�ϴ����ļ��� ��Ϊû��filetype��������ָ����������Щ����֮����ͬ
 * @return    int   -1 ûѡ���ϴ��ļ���0 �ļ����Ͳ�����, -2 ����ʧ�ܣ������������ϴ�����ļ���
 */
if ( ! function_exists('AdminUpload'))
{
    function AdminUpload($uploadname, $ftype='image', $rnddd=0, $watermark=TRUE, $filetype='' )
    {
        global $dsql, $cuserLogin, $cfg_addon_savetype, $cfg_dir_purview;
        global $cfg_basedir, $cfg_image_dir, $cfg_soft_dir, $cfg_other_medias;
        global $cfg_imgtype, $cfg_softtype, $cfg_mediatype;
        if($watermark) include_once(DEDEINC.'/image.func.php');
        
        $file_tmp = isset($GLOBALS[$uploadname]) ? $GLOBALS[$uploadname] : '';
        if($file_tmp=='' || !is_uploaded_file($file_tmp) )
        {
            return -1;
        }
        
        $file_tmp = $GLOBALS[$uploadname];
        $file_size = filesize($file_tmp);
        $file_type = $filetype=='' ? strtolower(trim($GLOBALS[$uploadname.'_type'])) : $filetype;
        
        $file_name = isset($GLOBALS[$uploadname.'_name']) ? $GLOBALS[$uploadname.'_name'] : '';
        $file_snames = explode('.', $file_name);
        $file_sname = strtolower(trim($file_snames[count($file_snames)-1]));
        
        if($ftype=='image' || $ftype=='imagelit')
        {
            $filetype = '1';
            $sparr = Array('image/pjpeg', 'image/jpeg', 'image/gif', 'image/png', 'image/xpng', 'image/wbmp');
            if(!in_array($file_type, $sparr)) return 0;
            if($file_sname=='')
            {
                if($file_type=='image/gif') $file_sname = 'jpg';
                else if($file_type=='image/png' || $file_type=='image/xpng') $file_sname = 'png';
                else if($file_type=='image/wbmp') $file_sname = 'bmp';
                else $file_sname = 'jpg';
            }
            $filedir = $cfg_image_dir.'/'.MyDate($cfg_addon_savetype, time());
        }
        else if($ftype=='media')
        {
            $filetype = '3';
            if( !preg_match('/'.$cfg_mediatype.'/', $file_sname) ) return 0;
            $filedir = $cfg_other_medias.'/'.MyDate($cfg_addon_savetype, time());
        }
        else
        {
            $filetype = '4';
            $cfg_softtype .= '|'.$cfg_mediatype.'|'.$cfg_imgtype;
            $cfg_softtype = str_replace('||', '|', $cfg_softtype);
            if( !preg_match('/'.$cfg_softtype.'/', $file_sname) ) return 0;
            $filedir = $cfg_soft_dir.'/'.MyDate($cfg_addon_savetype, time());
        }
        if(!is_dir(DEDEROOT.$filedir))
        {
            MkdirAll($cfg_basedir.$filedir, $cfg_dir_purview);
            CloseFtp();
        }
        $filename = $cuserLogin->getUserID().'-'.dd2char(MyDate('ymdHis', time())).$rnddd;
        if($ftype=='imagelit') $filename .= '-L';
        if( file_exists($cfg_basedir.$filedir.'/'.$filename.'.'.$file_sname) )
        {
            for($i=50; $i <= 5000; $i++)
            {
                if( !file_exists($cfg_basedir.$filedir.'/'.$filename.'-'.$i.'.'.$file_sname) )
                {
                    $filename = $filename.'-'.$i;
                    break;
                }
            }
        }
        $fileurl = $filedir.'/'.$filename.'.'.$file_sname;
        $rs = move_uploaded_file($file_tmp, $cfg_basedir.$fileurl);
        if(!$rs) return -2;
        if($ftype=='image' && $watermark)
        {
            WaterImg($cfg_basedir.$fileurl, 'up');
        }
        
        //������Ϣ�����ݿ�
        $title = $filename.'.'.$file_sname;
        $inquery = "INSERT INTO `#@__uploads`(title,url,mediatype,width,height,playtime,filesize,uptime,mid)
            VALUES ('$title','$fileurl','$filetype','0','0','0','".filesize($cfg_basedir.$fileurl)."','".time()."','".$cuserLogin->getUserID()."'); ";
        $dsql->ExecuteNoneQuery($inquery);
        $fid = $dsql->GetLastID();
        AddMyAddon($fid, $fileurl);
        return $fileurl;
    }
}


//ǰ̨��Աͨ���ϴ�����
//$upname ���ļ��ϴ���ı����������Ǳ��ı���
//$handname �����û��ֹ�ָ����ַ����µ���ַ
if ( ! function_exists('MemberUploads'))
{
    function MemberUploads($upname,$handname,$userid=0,$utype='image',$exname='',$maxwidth=0,$maxheight=0,$water=false,$isadmin=false)
    {
        global $cfg_imgtype,$cfg_mb_addontype,$cfg_mediatype,$cfg_user_dir,$cfg_basedir,$cfg_dir_purview;
        
        //��Ϊ�ο�Ͷ�������£���� id Ϊ 0
        if(empty($userid) ) $userid = 0;
        if(!is_dir($cfg_basedir.$cfg_user_dir."/$userid"))
        {
                MkdirAll($cfg_basedir.$cfg_user_dir."/$userid", $cfg_dir_purview);
                CloseFtp();
        }
        //���ϴ��ļ�
        $allAllowType = str_replace('||', '|', $cfg_imgtype.'|'.$cfg_mediatype.'|'.$cfg_mb_addontype);
        if(!empty($GLOBALS[$upname]) && is_uploaded_file($GLOBALS[$upname]))
        {
            $nowtme = time();

            $GLOBALS[$upname.'_name'] = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#",'',$GLOBALS[$upname.'_name']));
            //Դ�ļ����ͼ��
            if($utype=='image')
            {
                if(!preg_match("/\.(".$cfg_imgtype.")$/", $GLOBALS[$upname.'_name']))
                {
                    ShowMsg("�����ϴ���ͼƬ���Ͳ�������б����ϴ�{$cfg_imgtype}���ͣ�",'-1');
                    exit();
                }
                $sparr = Array("image/pjpeg","image/jpeg","image/gif","image/png","image/xpng","image/wbmp");
                $imgfile_type = strtolower(trim($GLOBALS[$upname.'_type']));
                if(!in_array($imgfile_type, $sparr))
                {
                    ShowMsg('�ϴ���ͼƬ��ʽ������ʹ��JPEG��GIF��PNG��WBMP��ʽ������һ�֣�', '-1');
                    exit();
                }
            }
            else if($utype=='flash' && !preg_match("/\.swf$/", $GLOBALS[$upname.'_name']))
            {
                ShowMsg('�ϴ����ļ�����Ϊflash�ļ���', '-1');
                exit();
            }
            else if($utype=='media' && !preg_match("/\.(".$cfg_mediatype.")$/",$GLOBALS[$upname.'_name']))
            {
                ShowMsg('�����ϴ����ļ����ͱ���Ϊ��'.$cfg_mediatype, '-1');
                exit();
            }
            else if(!preg_match("/\.(".$allAllowType.")$/", $GLOBALS[$upname.'_name']))
            {
                ShowMsg("�����ϴ����ļ����Ͳ�������",'-1');
                exit();
            }
            //�ٴ��ϸ����ļ���չ���Ƿ����ϵͳ���������
            $fs = explode('.', $GLOBALS[$upname.'_name']);
            $sname = $fs[count($fs)-1];
            $alltypes = explode('|', $allAllowType);
            if(!in_array(strtolower($sname), $alltypes))
            {
                ShowMsg('�����ϴ����ļ����Ͳ�������', '-1');
                exit();
            }
            //ǿ�ƽ�ֹ���ļ�����
            if(preg_match("/(asp|php|pl|cgi|shtm|js)$/", $sname))
            {
                ShowMsg('���ϴ����ļ�Ϊϵͳ��ֹ�����ͣ�', '-1');
                exit();
            }
            if($exname=='')
            {
                $filename = $cfg_user_dir."/$userid/".dd2char($nowtme.'-'.mt_rand(1000,9999)).'.'.$sname;
            }
            else
            {
                $filename = $cfg_user_dir."/{$userid}/{$exname}.".$sname;
            }
            move_uploaded_file($GLOBALS[$upname], $cfg_basedir.$filename) or die("�ϴ��ļ��� {$filename} ʧ�ܣ�");
            @unlink($GLOBALS[$upname]);
            
            if(@filesize($cfg_basedir.$filename) > $GLOBALS['cfg_mb_upload_size'] * 1024)
            {
                @unlink($cfg_basedir.$filename);
                ShowMsg('���ϴ����ļ�����ϵͳ��С���ƣ�', '-1');
                exit();
            }
            
            //��ˮӡ����СͼƬ
            if($utype=='image')
            {
                include_once(DEDEINC.'/image.func.php');
                if($maxwidth>0 || $maxheight>0)
                {
                    ImageResize($cfg_basedir.$filename, $maxwidth, $maxheight);
                }
                else if($water)
                {
                    WaterImg($cfg_basedir.$filename);
                }
            }
            return $filename;
        }
        //û���ϴ��ļ�
        else
        {
            //ǿ�ƽ�ֹ���ļ�����
            if($handname=='')
            {
                return $handname;
            }
            else if(preg_match("/\.(asp|php|pl|cgi|shtm|js)$/", $handname))
            {
                exit('Not allow filename for not safe!');
            }
            else if( !preg_match("/\.(".$allAllowType.")$/", $handname) )
            {
                exit('Not allow filename for filetype!');
            }
            // 2011-4-10 �޸���Ա�����޸����ʱ�����(by:jason123j)
            else if( !preg_match('#^http:#', $handname) && !preg_match('#^'.$cfg_user_dir.'/'.$userid."#", $handname) && !$isadmin )
            {
                exit('Not allow filename for not userdir!');
            }
            return $handname;
        }
    }
}

