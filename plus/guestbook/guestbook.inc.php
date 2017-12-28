<?php
/**
 * @version        $Id: guestbook.inc.php 1 10:06 2010-11-10 tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__).'/../../include/common.inc.php');
require_once(DEDEINC."/filter.inc.php");
if(empty($gotopagerank)) $gotopagerank='';
require_once(DEDEINC."/memberlogin.class.php");
$cfg_ml = new MemberLogin(-1);

//����Ϊ 0,��ʾ������Ҫ���
//�������Ϊ 1 ,�����Բ���Ҫ��˾�����ʾ
if($cfg_feedbackcheck=='Y') $needCheck = 0;
else $needCheck = 1;

//�Ƿ��ǻ�Ա�����Ա
if($cfg_ml->IsLogin())
{
    $g_isadmin = ($cfg_ml->fields['matt'] >= 10);
    $g_mid = $cfg_ml->M_ID;
    $g_name = $cfg_ml->M_UserName;
}
else
{
    $g_isadmin = FALSE;
    $g_mid = 0;
    $g_name = '';
}

function GetIsCheck($ischeck,$id)
{
    if($ischeck==0) return "<br><a href='guestbook.php?action=admin&job=check&id=$id' style='color:red'>[���]</a>";
    else return '';
}