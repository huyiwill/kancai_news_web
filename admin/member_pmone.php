<?php
/**
 * ��Ա����Ϣ,���͵�һ��
 *
 * @version        $Id: member_pmone.php 1 11:24 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Pm');
//����û����ĺϷ���
function CheckUserID($uid,$msgtitle='�û���',$ckhas=true)
{
    global $cfg_mb_notallow,$cfg_mb_idmin,$cfg_md_idurl,$cfg_soft_lang,$dsql;
    if($cfg_mb_notallow != '')
    {
        $nas = explode(',', $cfg_mb_notallow);
        if(in_array($uid, $nas))
        {
            return $msgtitle.'Ϊϵͳ��ֹ�ı�ʶ��';
        }
    }
    if($cfg_md_idurl=='Y' && preg_match("#[^a-z0-9]#i", $uid))
    {
        return $msgtitle.'������Ӣ����ĸ��������ɣ�';
    }

    if($cfg_soft_lang=='utf-8') $ck_uid = utf82gb($uid);
    else $ck_uid = $uid;
    
    for($i=0;isset($ck_uid[$i]);$i++)
    {
        if(ord($ck_uid[$i]) > 0x80)
        {
            if(isset($ck_uid[$i+1]) && ord($ck_uid[$i+1])>0x40)
            {
                $i++;
            }
            else
            {
                return $msgtitle.'���ܺ������룬���������Ӣ����ĸ��������ϣ�';
            }
        }
        else
        {
            if(preg_match("#[^0-9a-z@\.-]i#", $ck_uid[$i]))
            {
                return $msgtitle.'���ܺ��� [@]��[.]��[-]�����������ţ�';
            }
        }
    }
    if($ckhas)
    {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid' ");
        if(is_array($row)) return $msgtitle."�Ѿ����ڣ�";
    }
    return 'ok';
}

if(!isset($action)) $action = '';
if($action=="post")
{
    $floginid = $cuserLogin->getUserName();
    $fromid = $cuserLogin->getUserID();
    if($subject=='')
    {
        ShowMsg("����д��Ϣ����!","-1");
        exit();
    }
    $msg = CheckUserID($msgtoid,"�û���",false);
    if($msg!='ok')
    {
        ShowMsg($msg,"-1");
        exit();
    }
    $row = $dsql->GetOne("Select * From `#@__member` where userid like '$msgtoid' ");
    if(!is_array($row))
    {
        ShowMsg("��ָ�����û�������,���ܷ�����Ϣ!","-1");
        exit();
    }
    $subject = cn_substrR(HtmlReplace($subject,1),60);
    $message = cn_substrR(HtmlReplace($message,0),1024);
    $sendtime = $writetime = time();

    //�����ռ���(�ռ��˿ɹ���)
    $inquery = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
      VALUES ('$floginid','$fromid','{$row['mid']}','{$row['userid']}','inbox','$subject','$sendtime','$writetime','0','0','$message'); ";

    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg('�����ѳɹ�����','member_pmone.php');
    exit();
}
require_once(DEDEADMIN."/templets/member_pmone.htm");