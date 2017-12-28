<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ͼ������غ���
 *
 * @version        $Id: image.func.php 1 15:59 2010��7��5��Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
include(DEDEDATA.'/mark/inc_photowatermark_config.php');
//����û�ϵͳ֧�ֵ�ͼƬ��ʽ
global $cfg_photo_type,$cfg_photo_typenames,$cfg_photo_support;
$cfg_photo_type['gif'] = FALSE;
$cfg_photo_type['jpeg'] = FALSE;
$cfg_photo_type['png'] = FALSE;
$cfg_photo_type['wbmp'] = FALSE;
$cfg_photo_typenames = Array();
$cfg_photo_support = '';
if(function_exists("imagecreatefromgif") && function_exists("imagegif"))
{
    $cfg_photo_type["gif"] = TRUE;
    $cfg_photo_typenames[] = "image/gif";
    $cfg_photo_support .= "GIF ";
}
if(function_exists("imagecreatefromjpeg") && function_exists("imagejpeg"))
{
    $cfg_photo_type["jpeg"] = TRUE;
    $cfg_photo_typenames[] = "image/pjpeg";
    $cfg_photo_typenames[] = "image/jpeg";
    $cfg_photo_support .= "JPEG ";
}
if(function_exists("imagecreatefrompng") && function_exists("imagepng"))
{
    $cfg_photo_type["png"] = TRUE;
    $cfg_photo_typenames[] = "image/png";
    $cfg_photo_typenames[] = "image/xpng";
    $cfg_photo_support .= "PNG ";
}
if(function_exists("imagecreatefromwbmp") && function_exists("imagewbmp"))
{
    $cfg_photo_type["wbmp"] = TRUE;
    $cfg_photo_typenames[] = "image/wbmp";
    $cfg_photo_support .= "WBMP ";
}

// ����ͼ����С����
helper('image');