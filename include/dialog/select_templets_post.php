<?php
/**
 * ģ�巢��
 *
 * @version        $Id: select_templets_post.php 1 9:43 2010��7��8��Z tianya $
 * @package        DedeCMS.Dialog
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
require_once(dirname(__FILE__)."/config.php");
$cfg_txttype = "htm|html|tpl|txt";
if(empty($uploadfile))
{
    $uploadfile = "";
}
if(!is_uploaded_file($uploadfile))
{
    ShowMsg("��û��ѡ���ϴ����ļ�!","-1");
    exit();
}
if(!preg_match("#^text#", $uploadfile_type))
{
    ShowMsg("���ϴ��Ĳ����ı����͸���!","-1");
    exit();
}
if(!preg_match("#\.(".$cfg_txttype.")#i", $uploadfile_name))
{
    ShowMsg("�����ϴ���ģ���ļ����Ͳ��ܱ�ʶ��ֻ����htm��html��tpl��txt��չ����","-1");
    exit();
}
if($filename!='')
{
    $filename = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $filename));
}
else
{
    $uploadfile_name = trim(preg_replace("#[ \r\n\t\*\%\\\/\?><\|\":]{1,}#", '', $uploadfile_name));
    $filename = $uploadfile_name;
    if($filename=='' || !preg_match("#\.(".$cfg_txttype.")#i", $filename))
    {
        ShowMsg("�����ϴ����ļ��������⣬�����ļ������Ƿ��ʺϣ�","-1");
        exit();
    }
}
$fullfilename = $cfg_basedir.$activepath."/".$filename;
move_uploaded_file($uploadfile,$fullfilename) or die("�ϴ��ļ��� $fullfilename ʧ�ܣ�");
@unlink($uploadfile);
ShowMsg("�ɹ��ϴ��ļ���","select_templets.php?comeback=".urlencode($filename)."&f=$f&activepath=".urlencode($activepath)."&d=".time());
exit();