<?php
/**
 * ��Ա����Ϣ
 * 
 * @version        $Id: pm.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0,0);
$menutype = 'mydede';
$menutype_son = 'pm';
$id = isset($id)? intval($id) : 0;
if($cfg_mb_lit=='Y')
{
    ShowMsg('����ϵͳ�����˾�����Ա�ռ䣬�㲻����������Ա������Ϣ������������������ԣ�','-1');
    exit();
}

#api{{
if(defined('UC_API') && @include_once DEDEROOT.'/uc_client/client.php')
{
    if($data = uc_get_user($cfg_ml->M_LoginID)) uc_pm_location($data[0]);
}
#/aip}}

if(!isset($dopost))
{
    $dopost = '';
}
//����û��Ƿ񱻽���
CheckNotAllow();
$state=(empty($state))? "" : $state;
/*--------------------
function __send(){  }
----------------------*/
if($dopost=='send')
{
    /** ���Ѽ�¼ **/
    $sql = "SELECT * FROM `#@__member_friends` WHERE  mid='{$cfg_ml->M_ID}' AND ftype!='-1'  ORDER BY addtime DESC LIMIT 20";
    $friends = array();
    $dsql->SetQuery($sql);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $friends[] = $row;
    }

    include_once(dirname(__FILE__).'/templets/pm-send.htm');
    exit();
}
/*-----------------------
function __read(){  }
----------------------*/
else if($dopost=='read')
{
    $sql = "SELECT * FROM `#@__member_friends` WHERE  mid='{$cfg_ml->M_ID}' AND ftype!='-1'  ORDER BY addtime DESC LIMIT 20";
    $friends = array();
    $dsql->SetQuery($sql);
    $dsql->Execute();
    while ($row = $dsql->GetArray()) {
        $friends[] = $row;
    }
    $row = $dsql->GetOne("SELECT * FROM `#@__member_pms` WHERE id='$id' AND (fromid='{$cfg_ml->M_ID}' OR toid='{$cfg_ml->M_ID}')");
    if(!is_array($row))
    {
        ShowMsg('�Բ�����ָ������Ϣ�����ڻ���ûȨ�޲鿴��','-1');
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_pms` SET hasview=1 WHERE id='$id' AND folder='inbox' AND toid='{$cfg_ml->M_ID}'");
    $dsql->ExecuteNoneQuery("UPDATE `#@__member_pms` SET hasview=1 WHERE folder='outbox' AND toid='{$cfg_ml->M_ID}'");
    include_once(dirname(__FILE__).'/templets/pm-read.htm');
    exit();
}
/*-----------------------
function __savesend(){  }
----------------------*/
else if($dopost=='savesend')
{
    $svali = GetCkVdValue();
    if(preg_match("/5/",$safe_gdopen)){
        if(strtolower($vdcode)!=$svali || $svali=='')
        {
            ResetVdValue();
            ShowMsg('��֤�����', '-1');
            exit();
        }
        
    }
    $faqkey = isset($faqkey) && is_numeric($faqkey) ? $faqkey : 0;
    if($safe_faq_msg == 1)
    {
        if($safefaqs[$faqkey]['answer'] != $safeanswer || $safeanswer=='')
        {
            ShowMsg('��֤����𰸴���', '-1');
            exit();
        }
    }
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
    $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$msgtoid' ");
    if(!is_array($row))
    {
        ShowMsg("��ָ�����û�������,���ܷ�����Ϣ!","-1");
        exit();
    }
    $subject = cn_substrR(HtmlReplace($subject,1),60);
    $message = cn_substrR(HtmlReplace($message,0),1024);
    $sendtime = $writetime = time();

    //�����ռ���(�ռ��˿ɹ���)
    $inquery1 = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
      VALUES ('{$cfg_ml->M_LoginID}','{$cfg_ml->M_ID}','{$row['mid']}','{$row['userid']}','inbox','$subject','$sendtime','$writetime','0','0','$message'); ";

    //�������Լ��ķ�����(�Լ��ɹ���)
    $inquery2 = "INSERT INTO `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
      VALUES ('{$cfg_ml->M_LoginID}','{$cfg_ml->M_ID}','{$row['mid']}','{$row['userid']}','outbox','$subject','$sendtime','$writetime','0','0','$message'); ";
    $dsql->ExecuteNoneQuery($inquery1);
    $dsql->ExecuteNoneQuery($inquery2);
    ShowMsg("�ɹ�����һ����Ϣ!","pm.php?dopost=outbox");
    exit();
}
/*-----------------------
function __del(){  }
----------------------*/
else if($dopost=='del')
{
    $ids = preg_replace("#[^0-9,]#", "", $ids);
    if($folder=='inbox')
    {
        $boxsql="SELECT * FROM `#@__member_pms` WHERE id IN($ids) AND folder LIKE 'inbox' AND toid='{$cfg_ml->M_ID}'";
        $dsql->SetQuery($boxsql);
        $dsql->Execute();
        $query='';
        while($row = $dsql->GetArray())
        {
            if($row && $row['isadmin']==1)
            {
                $query = "Update `#@__member_pms` set writetime='0' WHERE id='{$row['id']}' AND folder='inbox' AND toid='{$cfg_ml->M_ID}' AND isadmin='1';";
                $dsql->ExecuteNoneQuery($query);
            }
            else
            {
                $query = "DELETE FROM `#@__member_pms` WHERE id in($ids) AND toid='{$cfg_ml->M_ID}' AND folder LIKE 'inbox'";
            }
        }
    }
    else if($folder=='outbox')
    {
        $query = "Delete From `#@__member_pms` WHERE id in($ids) AND fromid='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' ";
    }
    else
    {
        $query = "Delete From `#@__member_pms` WHERE id in($ids) AND fromid='{$cfg_ml->M_ID}' Or toid='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' Or (folder LIKE 'inbox' AND hasview='0')";
    }
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("�ɹ�ɾ��ָ������Ϣ!","pm.php?folder=".$folder);
    exit();
}
/*-----------------------
function __man(){  }
----------------------*/
else
{
    if(!isset($folder))
    {
        $folder = 'inbox';
    }
    require_once(DEDEINC."/datalistcp.class.php");
    $wsql = '';
    if($folder=='outbox')
    {
        $wsql = " `fromid`='{$cfg_ml->M_ID}' AND folder LIKE 'outbox' ";
        $tname = "������";
    }
    elseif($folder=='inbox')
    {
        $query = "SELECT * FROM `#@__member_pms` WHERE folder LIKE 'outbox' AND isadmin='1'";
        $dsql->SetQuery($query);
        $dsql->Execute();
        while($row = $dsql->GetArray())
        {
            $row2 = $dsql->GetOne("SELECT * FROM `#@__member_pms` WHERE fromid = '$row[id]' AND toid='{$cfg_ml->M_ID}'");
            if(!is_array($row2))
            {
                $row3= "INSERT INTO
                `#@__member_pms` (`floginid`,`fromid`,`toid`,`tologinid`,`folder`,`subject`,`sendtime`,`writetime`,`hasview`,`isadmin`,`message`)
                VALUES ('admin','{$row['id']}','{$cfg_ml->M_ID}','{$cfg_ml->M_LoginID}','inbox','{$row['subject']}','{$row['sendtime']}','{$row['writetime']}','{$row['hasview']}','{$row['isadmin']}','{$row['message']}')";
                $dsql->ExecuteNoneQuery($row3);
            }
        }
        if($state=="1"){
            $wsql= " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!='' and hasview=1";
            $tname = "�ռ���";
        } else if ($state=="-1")
        {
            $wsql = "toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!='' and hasview=0";
            $tname = "�ռ���";
        } else {
            $wsql = " toid='{$cfg_ml->M_ID}' AND folder='inbox' AND writetime!=''";
            $tname = "�ռ���";
        }
    }
    else
    {
        $wsql = " `fromid` ='{$cfg_ml->M_ID}' AND folder LIKE 'outbox'";
        $tname = "�ѷ���Ϣ";
    }
    $query = "SELECT * FROM `#@__member_pms` WHERE $wsql ORDER BY sendtime DESC";
    $dlist = new DataListCP();
    $dlist->pageSize = 20;
    $dlist->SetParameter("dopost",$dopost);
    $dlist->SetTemplate(DEDEMEMBER.'/templets/pm-main.htm');
    $dlist->SetSource($query);
    $dlist->Display();
}