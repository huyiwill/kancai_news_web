<?php   if(!defined('DEDEMEMBER')) exit("dedecms");
/**
 * ���뺯��
 * 
 * @version        $Id: inc_pwd_functions.php 1 15:18 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ��֤�����ɺ���
 *
 * @param     int  $length  ��Ҫ���ɵĳ���
 * @param     int  $numeric  �Ƿ�Ϊ����
 * @return    string
 */
function random($length, $numeric = 0)
{
    PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
    if($numeric)
    {
        $hash = sprintf('%0'.$length.'d', mt_rand(0, pow(10, $length) - 1));
    }
    else
    {
        $hash = '';
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($chars) - 1;
        for($i = 0; $i < $length; $i++)
        {
            $hash .= $chars[mt_rand(0, $max)];
        }
    }
    return $hash;
}

/**
 *  �ʼ����ͺ���
 *
 * @param     string  $email  E-mail��ַ
 * @param     string  $mailtitle  E-mail����
 * @param     string  $mailbody  E-mail����
 * @param     string  $headers ͷ��Ϣ
 * @return    void
 */
function sendmail($email, $mailtitle, $mailbody, $headers)
{
    global $cfg_sendmail_bysmtp, $cfg_smtp_server, $cfg_smtp_port, $cfg_smtp_usermail, $cfg_smtp_user, $cfg_smtp_password, $cfg_adminemail;
    if($cfg_sendmail_bysmtp == 'Y')
    {
        $mailtype = 'TXT';
        require_once(DEDEINC.'/mail.class.php');
        $smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
        $smtp->debug = false;
        $smtp->sendmail($email,$cfg_webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
    } else {
        @mail($email, $mailtitle, $mailbody, $headers);
    }
}

/**
 *  �����ʼ���typeΪINSERT�½���֤�룬UPDATE�޸���֤�룻
 *
 * @param     int  $mid  ��ԱID
 * @param     int  $userid  �û�ID
 * @param     string  $mailto  ���͵�
 * @param     string  $type  ����
 * @param     string  $send  ���͵�
 * @return    string
 */
function newmail($mid, $userid, $mailto, $type, $send)
{
    global $db,$cfg_adminemail,$cfg_webname,$cfg_basehost,$cfg_memberurl;
    $mailtime = time();
    $randval = random(8);
    $mailtitle = $cfg_webname.":�����޸�";
    $mailto = $mailto;
    $headers = "From: ".$cfg_adminemail."\r\nReply-To: $cfg_adminemail";
    $mailbody = "�װ���".$userid."��\r\n���ã���л��ʹ��".$cfg_webname."����\r\n".$cfg_webname."Ӧ����Ҫ�������������룺��ע�������û��������룬����������Ϣ�Ƿ�й©����\r\n������ʱ��½����Ϊ��".$randval." ���������ڵ�½������ַȷ���޸ġ�\r\n".$cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&id=".$mid;
    if($type == 'INSERT')
    {
        $key = md5($randval);
        $sql = "INSERT INTO `#@__pwd_tmp` (`mid` ,`membername` ,`pwd` ,`mailtime`)VALUES ('$mid', '$userid',  '$key', '$mailtime');";
        if($db->ExecuteNoneQuery($sql))
        {
            if($send == 'Y')
            {
                sendmail($mailto,$mailtitle,$mailbody,$headers);
                return ShowMsg('EMAIL�޸���֤���Ѿ����͵�ԭ�������������', 'login.php','','5000');
            } else if ($send == 'N')
            {
                return ShowMsg('�Ժ���ת���޸�ҳ', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&amp;id=".$mid."&amp;key=".$randval);
            }
        }
        else
        {
            return ShowMsg('�Բ����޸�ʧ�ܣ�����ϵ����Ա', 'login.php');
        }
    }
    elseif($type == 'UPDATE')
    {
        $key = md5($randval);
        $sql = "UPDATE `#@__pwd_tmp` SET `pwd` = '$key',mailtime = '$mailtime'  WHERE `mid` ='$mid';";
        if($db->ExecuteNoneQuery($sql))
        {
            if($send == 'Y')
            {
                sendmail($mailto,$mailtitle,$mailbody,$headers);
                ShowMsg('EMAIL�޸���֤���Ѿ����͵�ԭ�������������', 'login.php');
            }
            elseif($send == 'N')
            {
                return ShowMsg('�Ժ���ת���޸�ҳ', $cfg_basehost.$cfg_memberurl."/resetpassword.php?dopost=getpasswd&amp;id=".$mid."&amp;key=".$randval);
            }
        }
        else
        {
            ShowMsg('�Բ����޸�ʧ�ܣ��������Ա��ϵ', 'login.php');
        }
    }
}

/**
 *  ��ѯ��Ա��Ϣmail�û����������ַ��userid�û���
 *
 * @param     string  $mail  �ʼ�
 * @param     string  $userid  �û�ID
 * @return    string
 */
function member($mail, $userid)
{
    global $db;
    $sql = "SELECT mid,email,safequestion FROM #@__member WHERE email='$mail' AND userid = '$userid'";
    $row = $db->GetOne($sql);
    if(!is_array($row)) return ShowMsg("�Բ����û�ID�������","-1");
    else return $row;
}

/**
 *  ��ѯ�Ƿ��͹���֤��
 *
 * @param     string  $mid  ��ԱID
 * @param     string  $userid  �û�����
 * @param     string  $mailto  �����ʼ���ַ
 * @param     string  $send  ΪY�����ʼ�,ΪN�������ʼ�Ĭ��ΪY
 * @return    string
 */
function sn($mid,$userid,$mailto, $send = 'Y')
{
    global $db;
    $tptim= (60*10);
    $dtime = time();
    $sql = "SELECT * FROM #@__pwd_tmp WHERE mid = '$mid'";
    $row = $db->GetOne($sql);
    if(!is_array($row))
    {
        //�������ʼ���
        newmail($mid,$userid,$mailto,'INSERT',$send);
    }
    //10���Ӻ�����ٴη�������֤�룻
    elseif($dtime - $tptim > $row['mailtime'])
    {
        newmail($mid,$userid,$mailto,'UPDATE',$send);
    }
    //���·����µ���֤��ȷ���ʼ���
    else
    {
        return ShowMsg('�Բ�����10���Ӻ�����������', 'login.php');
    }
}