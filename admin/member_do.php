<?php
/**
 * ��Ա�������
 *
 * @version        $Id: member_do.php 1 13:47 2010��7��19��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
if(empty($dopost)) $dopost = '';
if(empty($fmdo)) $fmdo = '';
$ENV_GOBACK_URL = isset($_COOKIE['ENV_GOBACK_URL']) ? 'member_main.php' : '';

/*----------------
function __DelMember()
ɾ����Ա
----------------*/
if($dopost=="delmember")
{
    CheckPurview('member_Del');
    if($fmdo=='yes')
    {
        $id = preg_replace("#[^0-9]#", '', $id);
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode),0,24);
        if($safecodeok!=$safecode)
        {
            ShowMsg("����д��ȷ�İ�ȫ��֤����","member_do.php?id={$id}&dopost=delmember");
            exit();
        }
        if(!empty($id))
        {
            //ɾ���û���Ϣ
            $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='$id' LIMIT 1 ");
            $rs = 0;
            if($row['matt'] == 10)
            {
                $nrow = $dsql->GetOne("SELECT * FROM `#@__admin` WHERE id='$id' LIMIT 1 ");
                //�Ѿ�ɾ�������Ĺ���Ա�ʺ�
                if(!is_array($nrow)) $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid='$id' LIMIT 1");
            }
            else
            {
                $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid='$id' LIMIT 1");
            }
            if($rs > 0)
            {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_space` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_company` WHERE mid='$id' LIMIT 1");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_person` WHERE mid='$id' LIMIT 1");

                //ɾ���û��������
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_guestbook` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid='$id' Or fromid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid='$id' Or fid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_vhistory` WHERE mid='$id' Or vid='$id' ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid='$id' ");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid='$id'");
                #api{{
                if(defined('UC_API') && @include_once DEDEROOT.'/uc_client/client.php')    {
            $infofromuc=uc_get_user($row['userid']);
          uc_user_delete($infofromuc[0]);
        }
                #/aip}}
            }
            else
            {
                ShowMsg("�޷�ɾ���˻�Ա����������Ա��<b>[����Ա]</b>��<br />������ɾ�����<b>[����Ա]</b>����ɾ�����ʺţ�", $ENV_GOBACK_URL, 0, 5000);
                exit();
            }
        }
        ShowMsg("�ɹ�ɾ��һ����Ա��",$ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000,99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode),0,24);
    $wintitle = "��Ա����-ɾ����Ա";
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>��Ա����</a>::ɾ����Ա";
    $win = new OxWindow();
    $win->Init("member_do.php","js/blank.js","POST");
    $win->AddHidden("fmdo","yes");
    $win->AddHidden("dopost",$dopost);
    $win->AddHidden("id",$id);
    $win->AddHidden("randcode",$randcode);
    $win->AddHidden("safecode",$safecode);
    $win->AddTitle("��ȷʵҪɾ��(ID:".$id.")�����Ա?");
    $win->AddMsgItem("��ȫ��֤����<input name='safecode' type='text' id='safecode' size='16' style='width:200px' />&nbsp;(���Ʊ����룺 <font color='red'>$safecode</font> )","30");
    $winform = $win->GetWindow("ok");
    $win->Display();
}else if($dopost=="delmembers"){
    CheckPurview('member_Del');
    if($fmdo=='yes')
    {
        $safecodeok = substr(md5($cfg_cookie_encode.$randcode),0,24);
        if($safecodeok!=$safecode)
        {
            ShowMsg("����д��ȷ�İ�ȫ��֤����","member_do.php?id={$id}&dopost=delmembers");
            exit();
        }
        if(!empty($id))
        {
            //ɾ���û���Ϣ
            
            $rs = $dsql->ExecuteNoneQuery2("DELETE FROM `#@__member` WHERE mid IN (".str_replace("`",",",$id).") And matt<>10 ");    
            if($rs > 0)
            {
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_tj` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_space` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_company` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_person` WHERE mid IN (".str_replace("`",",",$id).") ");

                //ɾ���û��������
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_flink` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_guestbook` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_operation` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_pms` WHERE toid IN (".str_replace("`",",",$id).") Or fromid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_friends` WHERE mid IN (".str_replace("`",",",$id).") Or fid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_vhistory` WHERE mid IN (".str_replace("`",",",$id).") Or vid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE mid IN (".str_replace("`",",",$id).") ");
                $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET mid='0' WHERE mid IN (".str_replace("`",",",$id).")");
            }
            else
            {
                ShowMsg("�޷�ɾ���˻�Ա����������Ա�ǹ���Ա������ID��<br />������ɾ���������Ա����ɾ�����ʺţ�",$ENV_GOBACK_URL,0,3000);
                exit();
            }
        }
        ShowMsg("�ɹ�ɾ����Щ��Ա��",$ENV_GOBACK_URL);
        exit();
    }
    $randcode = mt_rand(10000, 99999);
    $safecode = substr(md5($cfg_cookie_encode.$randcode), 0, 24);
    $wintitle = "��Ա����-ɾ����Ա";
    $wecome_info = "<a href='".$ENV_GOBACK_URL."'>��Ա����</a>::ɾ����Ա";
    $win = new OxWindow();
    $win->Init("member_do.php", "js/blank.js", "POST");
    $win->AddHidden("fmdo", "yes");
    $win->AddHidden("dopost", $dopost);
    $win->AddHidden("id",$id);
    $win->AddHidden("randcode", $randcode);
    $win->AddHidden("safecode", $safecode);
    $win->AddTitle("��ȷʵҪɾ��(ID:".$id.")�����Ա?");
    $win->AddMsgItem(" ��ȫ��֤����<input name='safecode' type='text' id='safecode' size='16' style='width:200px' /> (���Ʊ����룺 <font color='red'>$safecode</font>)","30");
    $winform = $win->GetWindow("ok");
    $win->Display();
}
/*----------------
function __Recommend()
�Ƽ���Ա
----------------*/
else if ($dopost=="recommend")
{
    CheckPurview('member_Edit');
    $id = preg_replace("#[^0-9]#", "", $id);
    if($matt==0)
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=1 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg("�ɹ�����һ����Ա�Ƽ���",$ENV_GOBACK_URL);
        exit();
    }
    else
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET matt=0 WHERE mid='$id' AND matt<>10 LIMIT 1");
        ShowMsg("�ɹ�ȡ��һ����Ա�Ƽ���",$ENV_GOBACK_URL);
        exit();
    }
}
/*----------------
function __EditUser()
���Ļ�Ա
----------------*/
else if ($dopost=='edituser')
{
    CheckPurview('member_Edit');
    if(!isset($_POST['id'])) exit('Request Error!');
    $pwdsql = empty($pwd) ? '' : ",pwd='".md5($pwd)."'";
    if(empty($sex)) $sex = '��';
    $uptime=GetMkTime($uptime);
    
    if($matt==10 && $oldmatt!=10)
    {
        ShowMsg("�Բ���Ϊ��ȫ�������֧��ֱ�Ӱ�ǰ̨��ԱתΪ����Ĳ�����", "-1");
        exit();
    }    
    $query = "UPDATE `#@__member` SET
            email = '$email',
            uname = '$uname',
            sex = '$sex',
            matt = '$matt',
            money = '$money',
            scores = '$scores',
            rank = '$rank',
            spacesta='$spacesta',
            uptime='$uptime',
            exptime='$exptime'
            $pwdsql
            WHERE mid='$id' AND matt<>10 ";
    $rs = $dsql->ExecuteNoneQuery2($query);
    if($rs==0)
    {
        $query = "UPDATE `#@__member` SET
            email = '$email',
            uname = '$uname',
            sex = '$sex',
            money = '$money',
            scores = '$scores',
            rank = '$rank',
            spacesta='$spacesta',
            uptime='$uptime',
            exptime='$exptime'
            $pwdsql
            WHERE mid='$id' ";
            $rs = $dsql->ExecuteNoneQuery2($query);
    }
    
    #api{{
    if(defined('UC_API') && @include_once DEDEROOT.'/api/uc.func.php')
    {
        $row = $dsql->GetOne("SELECT `scores`,`userid` FROM `#@__member` WHERE `mid`='$id' AND `matt`<>10");
        $amount = $scores-$row['scores'];
        uc_credit_note($row['userid'],$amount);
    }
    #/aip}}
    
    ShowMsg('�ɹ����Ļ�Ա���ϣ�', 'member_view.php?id='.$id);
    exit();
}
/*--------------
function __LoginCP()
��¼��Ա�Ŀ������
----------*/
else if ($dopost=="memberlogin")
{
    CheckPurview('member_Edit');
    PutCookie('DedeUserID',$id,1800);
    PutCookie('DedeLoginTime',time(),1800);
    if(empty($jumpurl)) header("location:../member/index.php");
    else header("location:$jumpurl");
} else if ($dopost == "deoperations")
{
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if(is_array($nid))
    {
        foreach ($nid as $var)
        {
            $query = "DELETE FROM `#@__member_operation` WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
        }
        ShowMsg("ɾ���ɹ���","member_operations.php");
        exit();
    }
} else if ($dopost == "upoperations")
{
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if(is_array($nid))
    {
        foreach ($nid as $var)
        {
            $query = "UPDATE `#@__member_operation` SET sta = '1' WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
            ShowMsg("���óɹ���","member_operations.php");
            exit();
        }
    }
} else if($dopost == "okoperations")
{
    $nid = preg_replace('#[^0-9,]#', '', preg_replace('#`#', ',', $nid));
    $nid = explode(',', $nid);
    if(is_array($nid))
    {
        foreach ($nid as $var)
        {
            $query = "UPDATE `#@__member_operation` SET sta = '2' WHERE aid = '$var'";
            $dsql->ExecuteNoneQuery($query);
            ShowMsg("���óɹ���","member_operations.php");
            exit();
        }
    }
}