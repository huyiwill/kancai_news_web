<?php
/**
 * ����Ϊ����Ա
 *
 * @version        $Id: member_toadmin.php 1 14:09 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Edit');
if(empty($dopost)) $dopost = '';
if(empty($fmdo)) $fmdo = '';

$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';
$row = array();
/*----------------
function __Toadmin()
����Ϊ����Ա
----------------*/
if($dopost == "toadmin")
{
    $pwd = trim($pwd);
    if($pwd!='' && preg_match("#[^0-9a-zA-Z_@!\.-]#", $pwd))
    {
        ShowMsg('���벻�Ϸ�����ʹ��[0-9a-zA-Z_@!.-]�ڵ��ַ���','-1', 0, 3000);
        exit();
    }
    $safecodeok = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    if($safecodeok != $safecode)
    {
        ShowMsg("����д��ȷ�İ�ȫ��֤����", "member_toadmin.php?id={$id}");
        exit();
    }
    $pwdm = '';
    if($pwd!='')
    {
        $inputpwd = ",pwd";
        $inputpwdv = ",'".substr(md5($pwd), 5, 20)."'";
        $pwdm = ",pwd='".md5($pwd)."'";
    }else{
        $row = $dsql->GetOne("SELECT * FROM #@__member WHERE mid='$id'");
        $password = $row['pwd'];
        $inputpwd = ",pwd";
        $pwd = substr($password, 5, 20);
        $inputpwdv = ",'".$pwd."'";
        $pwdm = ",pwd='".$password."'";
    }
    $typeids=(empty($typeids))? "" : $typeids;
    if($typeids=='')
    {
        ShowMsg("��Ϊ�ù���Աָ��������Ŀ��","member_toadmin.php?id={$id}");
        exit();
    }
    $typeid = join(',', $typeids);
    if($typeid=='0') $typeid = '';
    if($id!=1)
    {
        $query = "INSERT INTO `#@__admin`(id,usertype,userid$inputpwd,uname,typeid,tname,email)
                    VALUES('$id','$usertype','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    }
    else
    {
        $query = "INSERT INTO `#@__admin`(id,userid$inputpwd,uname,typeid,tname,email)
                    VALUES('$id','$userid'$inputpwdv,'$uname','$typeid','$tname','$email')";
    }
    $dsql->ExecuteNoneQuery($query);
    $query = "UPDATE `#@__member` SET rank='100',uname='$uname',matt='10',email='$email'$pwdm WHERE mid='$id'";
    $dsql->ExecuteNoneQuery($query);
    $row = $dsql->GetOne("SELECT * FROM #@__admintype WHERE rank='$usertype'");
    $floginid = $cuserLogin->getUserName();
    $fromid = $cuserLogin->getUserID();
    $subject = "��ϲ���Ѿ��ɹ�����Ϊ����Ա";
    $message = "�װ��Ļ�Ա{$userid},���Ѿ��ɹ�����Ϊ{$row['typename']},�������Ȩ����ͬ��վ��������Ա��ϵ��";
    $sendtime = $writetime = time();
    $inquery = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
      VALUES ('$floginid','$fromid','$id','$userid','inbox','$subject','$sendtime','$writetime','0','0','$message'); ";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("�ɹ�����һ���ʻ���","member_main.php");
    exit();
}    
$id = preg_replace("#[^0-9]#", "", $id);

//��ʾ�û���Ϣ
$randcode = mt_rand(10000, 99999);
$safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);    
$typeOptions = '';
$typeid=(empty($typeid))? '' : $typeid;
$typeids = explode(',', $typeid);
$dsql->SetQuery("SELECT id,typename FROM `#@__arctype` WHERE reid=0 AND (ispart=0 OR ispart=1)");
$dsql->Execute('op');
while($nrow = $dsql->GetObject('op'))
{
    $typeOptions .= "<option value='{$nrow->id}' class='btype'".(in_array($nrow->id, $typeids) ? ' selected' : '').">{$nrow->typename}</option>\r\n";
    $dsql->SetQuery("SELECT id,typename FROM #@__arctype WHERE reid={$nrow->id} AND (ispart=0 OR ispart=1)");
    $dsql->Execute('s');
    while($nrow = $dsql->GetObject('s'))
    {
        $typeOptions .= "<option value='{$nrow->id}' class='stype'".(in_array($nrow->id, $typeids) ? ' selected' : '').">��{$nrow->typename}</option>\r\n";
    }
}
$row = $dsql->GetOne("SELECT * FROM #@__member WHERE mid='$id'");
include DedeInclude('templets/member_toadmin.htm');