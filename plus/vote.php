<?php
/**
 *
 * ͶƱ
 *
 * @version        $Id: vote.php 1 20:54 2010��7��8��Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__)."/../include/common.inc.php");
require(DEDEINC."/dedevote.class.php");
require(DEDEINC."/memberlogin.class.php");
require(DEDEINC."/userlogin.class.php");

$member = new MemberLogin;
$memberID = $member->M_LoginID;
$time = time();
$content = $memberID.'|'.$time;
$file = DEDEDATA.'/cache/vote_'.$aid.'_'.$member->M_ID.'.inc';//��Ż�ԱͶƱ��¼�Ļ����ļ�

$loginurl = $cfg_basehost."/member";
$ENV_GOBACK_URL = empty($_SERVER['HTTP_REFERER']) ? '':$_SERVER['HTTP_REFERER'];

if(empty($dopost)) $dopost = '';

$aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if($aid==0) die(" Request Error! ");

if($aid==0)
{
    ShowMsg("ûָ��ͶƱ��Ŀ��ID��","-1");
    exit();
}
$vo = new DedeVote($aid);
$rsmsg = '';


$row = $dsql->GetOne("SELECT * FROM #@__vote WHERE aid='$aid'");
//�ж��Ƿ������οͽ���ͶƱ
if($row['range'] == 1)
{
    if(!$member->IsLogin())
    {
        ShowMsg('���ȵ�¼�ٽ���ͶƱ',$loginurl);
        exit();
    }
}

if($dopost=='send')
{
    
    if(!empty($voteitem))
    {
        $rsmsg = "<br />&nbsp;�㷽�ŵ�ͶƱ״̬��".$vo->SaveVote($voteitem)."<br />";
    }
    else
    {
        $rsmsg = "<br />&nbsp;��ղ�ûѡ���κ�ͶƱ��Ŀ��<br />";
    }
    
    if($row['isenable'] == 1)
    {
        ShowMsg('��ͶƱ��δ����,��ʱ���ܽ���ͶƱ',$ENV_GOBACK_URL);
        exit();
    }
}

$voname = $vo->VoteInfos['votename'];
$totalcount = $vo->VoteInfos['totalcount'];
$starttime = GetDateMk($vo->VoteInfos['starttime']);
$endtime = GetDateMk($vo->VoteInfos['endtime']);
$votelist = $vo->GetVoteResult("98%",30,"30%");





//�ж��Ƿ������鿴
$admin = new userLogin;
if($dopost == 'view')
{
    if($row['view'] == 1 && empty($admin->userName))
    {
        ShowMsg('��ͶƱ�����鿴���',$ENV_GOBACK_URL);
        exit();
    }
}
//��ʾģ��(��PHP�ļ�)
include(DEDETEMPLATE.'/plus/vote.htm');