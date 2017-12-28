<?php
/**
 * ��������
 * 
 * @version        $Id: resetpassword.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEMEMBER."/inc/inc_pwd_functions.php");
if(empty($dopost)) $dopost = "";
$id = isset($id)? intval($id) : 0;

if($dopost == "")
{
    include(dirname(__FILE__)."/templets/resetpassword.htm");
}
elseif($dopost == "getpwd")
{

    //��֤��֤��
    if(!isset($vdcode)) $vdcode = '';

    $svali = GetCkVdValue();
    if(strtolower($vdcode) != $svali || $svali=='')
    {
        ResetVdValue();
        ShowMsg("�Բ�����֤���������","-1");
        exit();
    }

    //��֤���䣬�û���
    if(empty($mail) && empty($userid))
    {
        showmsg('�Բ����������û���������', '-1');
        exit;
    } else if (!preg_match("#(.*)@(.*)\.(.*)#", $mail))
    {
        showmsg('�Բ�����������ȷ�������ʽ', '-1');
        exit;
    } else if (CheckUserID($userid, '', false) != 'ok')
    {
        ShowMsg("��������û��� {$userid} ���Ϸ���","-1");
        exit();
    }
    $member = member($mail, $userid);

    //���ʼ���ʽȡ�����룻
    if($type == 1)
    {
        //�ж�ϵͳ�ʼ������Ƿ���
        if($cfg_sendmail_bysmtp == "Y")
        {
            sn($member['mid'],$userid,$member['email']);
        }else
        {
            showmsg('�Բ����ʼ�������δ����������ϵ����Ա', 'login.php');
            exit();
        }

        //�԰�ȫ����ȡ�����룻
    } else if ($type == 2)
    {
        if($member['safequestion'] == 0)
        {
            showmsg('�Բ�������δ���ð�ȫ���룬��ͨ���ʼ���ʽ��������', 'login.php');
            exit;
        }
        require_once(dirname(__FILE__)."/templets/resetpassword3.htm");
    }
    exit();
}
else if($dopost == "safequestion")
{
    $mid = preg_replace("#[^0-9]#", "", $id);
    $sql = "SELECT safequestion,safeanswer,userid,email FROM #@__member WHERE mid = '$mid'";
    $row = $db->GetOne($sql);
    if(empty($safequestion)) $safequestion = '';

    if(empty($safeanswer)) $safeanswer = '';

    if($row['safequestion'] == $safequestion && $row['safeanswer'] == $safeanswer)
    {
        sn($mid, $row['userid'], $row['email'], 'N');
        exit();
    }
    else
    {
        ShowMsg("�Բ������İ�ȫ�����𰸻ش����","-1");
        exit();
    }

}
else if($dopost == "getpasswd")
{
    //�޸�����
    if(empty($id))
    {
        ShowMsg("�Բ����벻Ҫ�Ƿ��ύ","login.php");
        exit();
    }
    $mid = preg_replace("#[^0-9]#", "", $id);
    $row = $db->GetOne("SELECT * FROM #@__pwd_tmp WHERE mid = '$mid'");
    if(empty($row))
    {
        ShowMsg("�Բ����벻Ҫ�Ƿ��ύ","login.php");
        exit();
    }
    if(empty($setp))
    {
        $tptim= (60*60*24*3);
        $dtime = time();
        if($dtime - $tptim > $row['mailtime'])
        {
            $db->executenonequery("DELETE FROM `#@__pwd_tmp` WHERE `md` = '$id';");
            ShowMsg("�Բ�����ʱ�����޸������ѹ���","login.php");
            exit();
        }
        require_once(dirname(__FILE__)."/templets/resetpassword2.htm");
    }
    elseif($setp == 2)
    {
        if(isset($key)) $pwdtmp = $key;

        $sn = md5(trim($pwdtmp));
        if($row['pwd'] == $sn)
        {
            if($pwd != "")
            {
                if($pwd == $pwdok)
                {
                    $pwdok = md5($pwdok);
                    $sql = "DELETE FROM `#@__pwd_tmp` WHERE `mid` = '$id';";
                    $db->executenonequery($sql);
                    $sql = "UPDATE `#@__member` SET `pwd` = '$pwdok' WHERE `mid` = '$id';";
                    if($db->executenonequery($sql))
                    {
                        showmsg('��������ɹ������μ�������', 'login.php');
                        exit;
                    }
                }
            }
            showmsg('�Բ���������Ϊ�ջ���д��һ��', '-1');
            exit;
        }
        showmsg('�Բ�����ʱ�������', '-1');
        exit;
    }
}