<?php
/**
 * �ĵ��༭��֤
 * 
 * @version        $Id: archives_check_edit.php 1 13:52 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
if(!defined('DEDEMEMBER')) exit('dedecms');

require_once(DEDEINC."/image.func.php");
require_once(DEDEINC."/oxwindow.class.php");

$flag = '';
$typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
$userip = GetIP();
$svali = GetCkVdValue();
if(preg_match("/3/",$safe_gdopen)){
    if(strtolower($vdcode)!=$svali || $svali=='')
    {
        ResetVdValue();
        ShowMsg('��֤�����', '-1');
        exit();
    }
}
if($typeid==0)
{
    ShowMsg('��ָ���ĵ���������Ŀ��','-1');
    exit();
}
$query = "SELECT tp.ispart,tp.channeltype,tp.issend,ch.issend as cissend,ch.sendrank,ch.arcsta,ch.addtable,ch.fieldset,ch.usertype
         FROM `#@__arctype` tp LEFT JOIN `#@__channeltype` ch ON ch.id=tp.channeltype WHERE tp.id='$typeid' ";
$cInfos = $dsql->GetOne($query);
$addtable = $cInfos['addtable'];

//�����Ŀ�Ƿ���Ͷ��Ȩ��
if($cInfos['issend']!=1 || $cInfos['ispart']!=0|| $cInfos['channeltype']!=$channelid || $cInfos['cissend']!=1)
{
    ShowMsg("����ѡ�����Ŀ��֧��Ͷ�壡","-1");
    exit();
}

//�ĵ���Ĭ��״̬
if($cInfos['arcsta']==0)
{
    $ismake = 0;
    $arcrank = 0;
}
else if($cInfos['arcsta']==1)
{
    $ismake = -1;
    $arcrank = 0;
}
else
{
    $ismake = 0;
    $arcrank = -1;
}

//�Ա�������ݽ��д���
$title = cn_substrR(HtmlReplace($title,1),$cfg_title_maxlen);
$writer =  cn_substrR(HtmlReplace($writer,1),20);
if(empty($description)) $description = '';
$description = cn_substrR(HtmlReplace($description,1),250);
$keywords = cn_substrR(HtmlReplace($tags,1),30);
$mid = $cfg_ml->M_ID;
$isadmin = ($cfg_ml->fields['matt']==10 ? true : false);
if (empty($oldlitpic))
{
    $oldlitpic = '';
}

//�����ϴ�������ͼ
if($litpic != '')
{
    $litpic = MemberUploads('litpic', $oldlitpic, $mid, 'image', '', $cfg_ddimg_width, $cfg_ddimg_height, false, $isadmin);
    SaveUploadInfo($title, $litpic, 1);
}
else
{
    $litpic =$oldlitpic;
}