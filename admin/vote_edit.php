<?php
/**
 * ͶƱģ��༭
 *
 * @version        $Id: vote_edit.php 1 23:54 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('plus_ͶƱģ��');
require_once(DEDEINC."/dedetag.class.php");
if(empty($dopost)) $dopost="";

$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
$ENV_GOBACK_URL = empty($_COOKIE['ENV_GOBACK_URL']) ? "vote_main.php" : $_COOKIE['ENV_GOBACK_URL'];

if($dopost=="delete")
{
    if($dsql->ExecuteNoneQuery("DELETE FROM #@__vote WHERE aid='$aid'"))
    {
        if($dsql->ExecuteNoneQuery("DELETE FROM #@__vote_member WHERE voteid='$aid'"))
        {
            ShowMsg('�ɹ�ɾ��һ��ͶƱ!', $ENV_GOBACK_URL);
            exit;
        }
    }
    else
    {
        ShowMsg('ָ��ɾ��ͶƱ������!', $ENV_GOBACK_URL);
        exit;
    }
}
else if($dopost=="saveedit")
{
    $starttime = GetMkTime($starttime);
    $endtime = GetMkTime($endtime);
    $query = "UPDATE #@__vote SET votename='$votename',
        starttime='$starttime',
        endtime='$endtime',
        totalcount='$totalcount',
        ismore='$ismore',
        votenote='$votenote',
        isallow='$isallow',
        view='$view',
        spec='$spec',
        isenable='$isenable'
        WHERE aid='$aid'
        ";
    if($dsql->ExecuteNoneQuery($query))
    {
        $vt = new DedeVote($aid);
        $vote_file = DEDEDATA."/vote/vote_".$aid.".js";
        $vote_content = $vt->GetVoteForm();
        $vote_content = preg_replace(array("#/#","#([\r\n])[\s]+#"),array("\/"," "),$vote_content);        //ȡ�������еĿհ��ַ�������ת��
        $vote_content = 'document.write("'.$vote_content.'");';
        file_put_contents($vote_file,$vote_content);
        ShowMsg('�ɹ�����һ��ͶƱ!',$ENV_GOBACK_URL);
    }
    else
    {
        ShowMsg('����һ��ͶƱʧ��!',$ENV_GOBACK_URL);
    }
}
else
{
    $row = $dsql->GetOne("SELECT * FROM #@__vote WHERE aid='$aid'");
    if(!is_array($row))
    {
        ShowMsg('ָ��ͶƱ�����ڣ�', '-1');
        exit();
    }
    include DedeInclude('templets/vote_edit.htm');
}