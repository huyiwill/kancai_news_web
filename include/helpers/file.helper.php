<?php  if(!defined('DEDEINC')) exit('dedecms');
/**
 * �ļ�����С����
 *
 * @version        $Id: file.helper.php 1 2010-07-05 11:43:09Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

$g_ftpLink = false;

/**
 *  ʹ��FTP���������ļ���Ŀ¼
 *
 * @param     string  $truepath  ��ʵĿ���ַ
 * @param     string  $mmode  ����ģʽ
 * @param     string  $isMkdir  �Ƿ񴴽�Ŀ¼
 * @return    bool
 */
if ( ! function_exists('FtpMkdir'))
{
    function FtpMkdir($truepath,$mmode,$isMkdir=true)
    {
        global $cfg_basedir,$cfg_ftp_root,$g_ftpLink;
        OpenFtp();
        $ftproot = preg_replace('/'.$cfg_ftp_root.'$/', '', $cfg_basedir);
        $mdir = preg_replace('/^'.$ftproot.'/', '', $truepath);
        if($isMkdir)
        {
            ftp_mkdir($g_ftpLink, $mdir);
        }
        return ftp_site($g_ftpLink, "chmod $mmode $mdir");
    }
}

/**
 *  �ı�Ŀ¼ģʽ
 *
 * @param     string  $truepath  ��ʵ��ַ
 * @param     string  $mmode   ģʽ
 * @return    bool
 */
if ( ! function_exists('FtpChmod'))
{
    function FtpChmod($truepath, $mmode)
    {
        return FtpMkdir($truepath, $mmode, false);
    }
}


/**
 *  ��FTP����,��֮ǰȷ���Ѿ����ú���FTP��ص�������Ϣ
 *
 * @return    void
 */
if ( ! function_exists('OpenFtp'))
{
    function OpenFtp()
    {
        global $cfg_basedir,$cfg_ftp_host,$cfg_ftp_port, $cfg_ftp_user,$cfg_ftp_pwd,$cfg_ftp_root,$g_ftpLink;
        if(!$g_ftpLink)
        {
            if($cfg_ftp_host=='')
            {
                echo "�������վ���PHP���ô������ƣ���������FTP����Ŀ¼������������ں�ָ̨��FTP��صı�����";
                exit();
            }
            $g_ftpLink = ftp_connect($cfg_ftp_host,$cfg_ftp_port);
            if(!$g_ftpLink)
            {
                echo "����FTPʧ�ܣ�";
                exit();
            }
            if(!ftp_login($g_ftpLink,$cfg_ftp_user,$cfg_ftp_pwd))
            {
                echo "��½FTPʧ�ܣ�";
                exit();
            }
        }
    }
}


/**
 *  �ر�FTP����
 *
 * @return    void
 */
if ( ! function_exists('CloseFtp'))
{
    function CloseFtp()
    {
        global $g_ftpLink;
        if($g_ftpLink)
        {
            @ftp_quit($g_ftpLink);
        }
    }
}


/**
 *  ��������Ŀ¼
 *
 * @param     string  $truepath  ��ʵ��ַ
 * @param     string  $mmode   ģʽ
 * @return    bool
 */
if ( ! function_exists('MkdirAll'))
{
    function MkdirAll($truepath,$mmode)
    {
        global $cfg_ftp_mkdir,$isSafeMode,$cfg_dir_purview;
        if( $isSafeMode || $cfg_ftp_mkdir=='Y' )
        {
            return FtpMkdir($truepath, $mmode);
        }
        else
        {
            if(!file_exists($truepath))
            {
                mkdir($truepath, $cfg_dir_purview);
                chmod($truepath, $cfg_dir_purview);
                return true;
            }
            else
            {
                return true;
            }
        }
    }
}

/**
 *  ��������ģʽ
 *
 * @access    public
 * @param     string  $truepath  �ļ�·��
 * @param     string  $mmode   ģʽ
 * @return    string
 */
if ( ! function_exists('ChmodAll'))
{
    function ChmodAll($truepath,$mmode)
    {
        global $cfg_ftp_mkdir,$isSafeMode;
        if( $isSafeMode || $cfg_ftp_mkdir=='Y' )
        {
            return FtpChmod($truepath, $mmode);
        }
        else
        {
            return chmod($truepath, '0'.$mmode);
        }
    }
}


/**
 *  ����Ŀ¼
 *
 * @param     string  $spath  �������ļ���
 * @return    bool
 */
if ( ! function_exists('CreateDir'))
{
    function CreateDir($spath)
    {
        if(!function_exists('SpCreateDir'))
        {
            require_once(DEDEINC.'/inc/inc_fun_funAdmin.php');
        }
        return SpCreateDir($spath);
    }
}

/**
 *  д�ļ�
 *
 * @access    public
 * @param     string  $file  �ļ���
 * @param     string  $content  ����
 * @param     int  $flag   ��ʶ
 * @return    string
 */
if ( ! function_exists('PutFile'))
{
    function PutFile($file, $content, $flag = 0)
    {
        $pathinfo = pathinfo ( $file );
        if (! empty ( $pathinfo ['dirname'] ))
        {
            if (file_exists ( $pathinfo ['dirname'] ) === FALSE)
            {
                if (@mkdir ( $pathinfo ['dirname'], 0777, TRUE ) === FALSE)
                {
                    return FALSE;
                }
            }
        }
        if ($flag === FILE_APPEND)
        {
            return @file_put_contents ( $file, $content, FILE_APPEND );
        }
        else
        {
            return @file_put_contents ( $file, $content, LOCK_EX );
        }
    }
}

/**
 *  �õݹ鷽ʽɾ��Ŀ¼
 *
 * @access    public
 * @param     string    $file   Ŀ¼�ļ�
 * @return    string
 */
if ( ! function_exists('RmRecurse'))
{
    function RmRecurse($file)
    {
        if (is_dir($file) && !is_link($file))
        {
            foreach(glob($file . '/*') as $sf)
            {
                if (!RmRecurse($sf))
                {
                    return false;
                }
            }
            return @rmdir($file);
        } else {
            return @unlink($file);
        }
    }
}


