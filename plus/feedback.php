<?php
/**
 *
 * ����
 *
 * @version        $Id: feedback.php 2 15:56 2012��10��30��Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
if($cfg_feedback_forbid=='Y') exit('ϵͳ�Ѿ���ֹ���۹��ܣ�');
require_once(DEDEINC."/filter.inc.php");
if(!isset($action))
{
    $action = '';
}
//���ݾɵ�JS����
if($action == 'good' || $action == 'bad')
{
    if(!empty($aid)) $id = $aid;
    require_once(dirname(__FILE__).'/digg_ajax.php');
    exit();
}

$cfg_formmember = isset($cfg_formmember) ? true : false;
$ischeck = $cfg_feedbackcheck=='Y' ? 0 : 1;
$aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
$fid = (isset($fid) && is_numeric($fid)) ? $fid : 0;
if(empty($aid) && empty($fid))
{
    ShowMsg('�ĵ�id����Ϊ��!','-1');
    exit();
}

include_once(DEDEINC."/memberlogin.class.php");
$cfg_ml = new MemberLogin();

if($action=='goodfb')
{
    AjaxHead();
    $fid = intval($fid);
    $dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET good = good+1 WHERE id='$fid' ");
    $row = $dsql->GetOne("SELECT good FROM `#@__feedback` WHERE id='$fid' ");
    echo "<a onclick=\"postBadGood('goodfb',{$aid})\">֧��</a>[{$row['good']}]";
    exit();
}
else if($action=='badfb')
{
    AjaxHead();
    $fid = intval($fid);
    $dsql->ExecuteNoneQuery("UPDATE `#@__feedback` SET bad = bad+1 WHERE id='$fid' ");
    $row = $dsql->GetOne("SELECT bad FROM `#@__feedback` WHERE id='$fid' ");
    echo "<a onclick=\"postBadGood('badfb',{$aid})\">����</a>[{$row['bad']}]";
    exit();
}
//�鿴����
/*
function __ViewFeedback(){ }
*/
//-----------------------------------
else if($action=='' || $action=='show')
{
    //��ȡ�ĵ���Ϣ
    $arcRow = GetOneArchive($aid);
    if(empty($arcRow['aid']))
    {
        ShowMsg('�޷��鿴δ֪�ĵ�������!','-1');
        exit();
    }
    extract($arcRow, EXTR_SKIP);
    include_once(DEDEINC.'/datalistcp.class.php');
    $dlist = new DataListCP();
    $dlist->pageSize = 20;

    if(empty($ftype) || ($ftype!='good' && $ftype!='bad' && $ftype!='feedback'))
    {
        $ftype = '';
    }
    $wquery = $ftype!='' ? " And ftype like '$ftype' " : '';
	helper('smiley');

    //���������б�
    $querystring = "SELECT fb.*,mb.userid,mb.face as mface,mb.spacesta,mb.scores,mb.sex FROM `#@__feedback` fb
                 LEFT JOIN `#@__member` mb on mb.mid = fb.mid
                 WHERE fb.aid='$aid' AND fb.ischeck='1' $wquery ORDER BY fb.id desc";
    $dlist->SetParameter('aid',$aid);
    $dlist->SetParameter('action','show');
    $dlist->SetTemplate(DEDETEMPLATE.'/plus/feedback_templet.htm');
    $dlist->SetSource($querystring);
    $dlist->Display();
    exit();
}

//��������
//------------------------------------
/*
function __Quote(){ }
*/
else if($action=='quote')
{
	$type = empty($type)? '' : 'ajax';
	if($type == 'ajax')
	{
		AjaxHead();
	}
    $row = $dsql->GetOne("SELECT * FROM `#@__feedback` WHERE id ='$fid'");
    require_once(DEDEINC.'/dedetemplate.class.php');
    $dtp = new DedeTemplate();
	$tplfile = $type == ''? DEDETEMPLATE.'/plus/feedback_quote.htm' : DEDETEMPLATE.'/plus/feedback_quote_ajax.htm';
	
    $dtp->LoadTemplate($tplfile);
    $dtp->Display();
    exit();
}
//��������
//------------------------------------
/*
function __SendFeedback(){ }
*/
else if($action=='send')
{
    //��ȡ�ĵ���Ϣ
    $arcRow = GetOneArchive($aid);
    if((empty($arcRow['aid']) || $arcRow['notpost']=='1') && empty($fid))
    {
        ShowMsg('�޷��Ը��ĵ���������!','-1');
        exit();
    }

    //�Ƿ����֤����ȷ��
    if(empty($isconfirm))
    {
        $isconfirm = '';
    }
    if($isconfirm!='yes' && $cfg_feedback_ck=='Y')
    {
        extract($arcRow, EXTR_SKIP);
        require_once(DEDEINC.'/dedetemplate.class.php');
        $dtp = new DedeTemplate();
        $dtp->LoadTemplate(DEDETEMPLATE.'/plus/feedback_confirm.htm');
        $dtp->Display();
        exit();
    }
    //�����֤��
    if(preg_match("/4/",$safe_gdopen)){
        $validate = isset($validate) ? strtolower(trim($validate)) : '';
        $svali = GetCkVdValue();
        if(strtolower($validate)!=$svali || $svali=='')
        {
            ResetVdValue();
            ShowMsg('��֤�����', '-1');
            exit();
        }
        
    }

    //����û���¼
    if(empty($notuser))
    {
        $notuser=0;
    }
	
	if($cfg_feedback_guest == 'N' && $cfg_ml->M_ID < 1)
	{
		ShowMsg('����Ա�������ο����ۣ�','-1');
		exit();
	}

    //������������
    if($notuser==1)
    {
        $username = $cfg_ml->M_ID > 0 ? '����' : '�ο�';
    }

    //�ѵ�¼���û�
    else if($cfg_ml->M_ID > 0)
    {
        $username = $cfg_ml->M_UserName;
    }

    //�û������֤
    else
    {
        if($username!='' && $pwd!='')
        {
            $rs = $cfg_ml->CheckUser($username,$pwd);
            if($rs==1)
            {
                $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET logintime='".time()."',loginip='".GetIP()."' WHERE mid='{$cfg_ml->M_ID}'; ");
            }
            else
            {
                $username = '�ο�';
            }
        }
        else
        {
            $username = '�ο�';
        }
    }
    $ip = GetIP();
    $dtime = time();
    
    //������ۼ��ʱ�䣻
    if(!empty($cfg_feedback_time))
    {
        //�����󷢱�����ʱ�䣬���δ��½�жϵ�ǰIP�������ʱ��
        if($cfg_ml->M_ID > 0)
        {
            $where = "WHERE `mid` = '$cfg_ml->M_ID'";
        }
        else
        {
            $where = "WHERE `ip` = '$ip'";
        }
        $row = $dsql->GetOne("SELECT dtime FROM `#@__feedback` $where ORDER BY `id` DESC ");
        if(is_array($row) && $dtime - $row['dtime'] < $cfg_feedback_time)
        {
            ResetVdValue();
            ShowMsg('����Ա���������ۼ��ʱ�䣬���Ե���Ϣһ�£�','-1');
            exit();
        }
    }

    if(empty($face))
    {
        $face = 0;
    }
    $face = intval($face);
    $typeid = (isset($typeid) && is_numeric($typeid)) ? intval($typeid) : 0;
    extract($arcRow, EXTR_SKIP);
    $msg = cn_substrR(TrimMsg($msg), 1000);
    $username = cn_substrR(HtmlReplace($username, 2), 20);
    if(empty($feedbacktype) || ($feedbacktype!='good' && $feedbacktype!='bad'))
    {
        $feedbacktype = 'feedback';
    }
    //������������
    if($comtype == 'comments')
    {
        $arctitle = addslashes($title);
		$typeid = intval($typeid);
		$ischeck = intval($ischeck);
		$feedbacktype = preg_replace("#[^0-9a-z]#i", "", $feedbacktype);
        if($msg!='')
        {
            $inquery = "INSERT INTO `#@__feedback`(`aid`,`typeid`,`username`,`arctitle`,`ip`,`ischeck`,`dtime`, `mid`,`bad`,`good`,`ftype`,`face`,`msg`)
                   VALUES ('$aid','$typeid','$username','$arctitle','$ip','$ischeck','$dtime', '{$cfg_ml->M_ID}','0','0','$feedbacktype','$face','$msg'); ";
            $rs = $dsql->ExecuteNoneQuery($inquery);
            if(!$rs)
            {
                ShowMsg(' �������۴���! ', '-1');
                //echo $dsql->GetError();
                exit();
            }
        }
    }
    //���ûظ�
    elseif ($comtype == 'reply')
    {
        $row = $dsql->GetOne("SELECT * FROM `#@__feedback` WHERE id ='$fid'");
        $arctitle = addslashes($row['arctitle']);
        $aid =$row['aid'];
        $msg = $quotemsg.$msg;
        $msg = HtmlReplace($msg, 2);
        $inquery = "INSERT INTO `#@__feedback`(`aid`,`typeid`,`username`,`arctitle`,`ip`,`ischeck`,`dtime`,`mid`,`bad`,`good`,`ftype`,`face`,`msg`)
                VALUES ('$aid','$typeid','$username','$arctitle','$ip','$ischeck','$dtime','{$cfg_ml->M_ID}','0','0','$feedbacktype','$face','$msg')";
        $dsql->ExecuteNoneQuery($inquery);
    }

    if($feedbacktype=='bad')
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores-{cfg_feedback_sub},badpost=badpost+1,lastpost='$dtime' WHERE id='$aid' ");
    }
    else if($feedbacktype=='good')
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores+{$cfg_feedback_add},goodpost=goodpost+1,lastpost='$dtime' WHERE id='$aid' ");
    }
    else
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET scores=scores+1,lastpost='$dtime' WHERE id='$aid' ");
    }
    if($cfg_ml->M_ID > 0)
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET scores=scores+{$cfg_sendfb_scores} WHERE mid='{$cfg_ml->M_ID}' ");
    }
    //ͳ���û�����������
    if($cfg_ml->M_ID > 0)
    {
        #api{{
        if(defined('UC_API') && @include_once DEDEROOT.'/api/uc.func.php')
        {
            //ͬ������
            uc_credit_note($cfg_ml->M_LoginID, $cfg_sendfb_scores);
            
            //�����¼�
            $arcRow = GetOneArchive($aid);
            $feed['icon'] = 'thread';
            $feed['title_template'] = '<b>{username} ����վ����������</b>';
            $feed['title_data'] = array('username' => $cfg_ml->M_UserName);
            $feed['body_template'] = '<b>{subject}</b><br>{message}';
            $url = !strstr($arcRow['arcurl'],'http://') ? ($cfg_basehost.$arcRow['arcurl']) : $arcRow['arcurl'];        
            $feed['body_data'] = array('subject' => "<a href=\"".$url."\">$arcRow[arctitle]</a>", 'message' => cn_substr(strip_tags(preg_replace("/\[.+?\]/is", '', $msg)), 150));
            $feed['images'][] = array('url' => $cfg_basehost.'/images/scores.gif', 'link'=> $cfg_basehost);
            uc_feed_note($cfg_ml->M_LoginID,$feed); unset($arcRow);
        }
        #/aip}}
    
        $row = $dsql->GetOne("SELECT COUNT(*) AS nums FROM `#@__feedback` WHERE `mid`='".$cfg_ml->M_ID."'");
        $dsql->ExecuteNoneQuery("UPDATE `#@__member_tj` SET `feedback`='$row[nums]' WHERE `mid`='".$cfg_ml->M_ID."'");
    }
    
    //��Ա��̬��¼
    $cfg_ml->RecordFeeds('feedback', $arctitle, $msg, $aid);
    
    $_SESSION['sedtime'] = time();
    if(empty($uid) && isset($cmtuser)) $uid = $cmtuser;
    $backurl = $cfg_formmember ? "index.php?uid={$uid}&action=viewarchives&aid={$aid}" : "feedback.php?aid={$aid}";
    if($ischeck==0)
    {
        ShowMsg('�ɹ��������ۣ�������˺�Ż���ʾ�������!', $backurl);
    }
    else
    {
        ShowMsg('�ɹ��������ۣ�����ת������ҳ��!', $backurl);
    }
    exit();
}