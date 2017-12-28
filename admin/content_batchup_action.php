<?php
/**
 * ���ݴ�����
 *
 * @version        $Id: content_batch_up.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_ArcBatch');
require_once(DEDEINC."/typelink.class.php");
require_once(DEDEADMIN."/inc/inc_batchup.php");
@set_time_limit(0);

//typeid,startid,endid,seltime,starttime,endtime,action,newtypeid
//��������
//check del move makehtml
//��ȡID����
if(empty($startid)) $startid = 0;
if(empty($endid)) $endid = 0;
if(empty($seltime)) $seltime = 0;
if(empty($typeid)) $typeid = 0;
if(empty($userid)) $userid = '';

//����HTML����������ҳ�洦��
if($action=="makehtml")
{
    $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
    $jumpurl .= "&typeid=$typeid&pagesize=20&seltime=$seltime";
    $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
    header("Location: $jumpurl");
    exit();
}

$gwhere = " WHERE 1 ";
if($startid >0 ) $gwhere .= " AND id>= $startid ";
if($endid > $startid) $gwhere .= " AND id<= $endid ";
$idsql = '';

if($typeid!=0)
{
    $ids = GetSonIds($typeid);
    $gwhere .= " AND typeid IN($ids) ";
}
if($seltime==1)
{
    $t1 = GetMkTime($starttime);
    $t2 = GetMkTime($endtime);
    $gwhere .= " AND (senddate >= $t1 AND senddate <= $t2) ";
}
if(!empty($userid))
{
	$row = $dsql->GetOne("SELECT `mid` FROM #@__member WHERE `userid` LIKE '$userid'");
	if(is_array($row))
	{
		$gwhere .= " AND mid = {$row['mid']} ";
	}
}
//�������
if(!empty($heightdone)) $action=$heightdone;

//ָ�����
if($action=='check')
{
    if(empty($startid) || empty($endid) || $endid < $startid)
    {
        ShowMsg('�ò�������ָ����ʼID��','javascript:;');
        exit();
    }
    $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
    $jumpurl .= "&typeid=$typeid&pagesize=20&seltime=$seltime";
    $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
    $dsql->SetQuery("SELECT id,arcrank FROM `#@__arctiny` $gwhere");
    $dsql->Execute('c');
    while($row = $dsql->GetObject('c'))
    {
        if($row->arcrank==-1)
        {
            $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET arcrank=0 WHERE id='{$row->id}'");
            $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET arcrank=0 WHERE id='{$row->id}'");
        }
    }
    ShowMsg("������ݿ����˴���׼������HTML...",$jumpurl);
    exit();
}
//����ɾ��
else if($action=='del')
{
    if(empty($startid) || empty($endid) || $endid < $startid)
    {
        ShowMsg('�ò�������ָ����ʼID��','javascript:;');
        exit();
    }
    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('x');
    $tdd = 0;
    while($row = $dsql->GetObject('x'))
    {
        if(DelArc($row->id)) $tdd++;
    }
    ShowMsg("�ɹ�ɾ�� $tdd ����¼��","javascript:;");
    exit();
}
//ɾ���ձ����ĵ�
else if($action=='delnulltitle')
{
    $dsql->SetQuery("SELECT id FROM `#@__archives` WHERE trim(title)='' ");
    $dsql->Execute('x');
    $tdd = 0;
    while($row = $dsql->GetObject('x'))
    {
        if(DelArc($row->id)) $tdd++;
    }
    ShowMsg("�ɹ�ɾ�� $tdd ����¼��","javascript:;");
    exit();
}
//ɾ������������
else if($action=='delnullbody')
{
    $dsql->SetQuery("SELECT aid FROM `#@__addonarticle` WHERE LENGTH(body) < 10 ");
    $dsql->Execute('x');
    $tdd = 0;
    while($row = $dsql->GetObject('x'))
    {
        if(DelArc($row->aid)) $tdd++;
    }
    ShowMsg("�ɹ�ɾ�� $tdd ����¼��","javascript:;");
    exit();
}
//��������ͼ����
else if($action=='modddpic')
{
    $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET litpic='' WHERE trim(litpic)='litpic' ");
    ShowMsg("�ɹ���������ͼ����","javascript:;");
    exit();
}
//�����ƶ�
else if($action=='move')
{
    if(empty($typeid))
    {
        ShowMsg('�ò�������ָ����Ŀ��','javascript:;');
        exit();
    }
    $typeold = $dsql->GetOne("SELECT * FROM #@__arctype WHERE id='$typeid'; ");
    $typenew = $dsql->GetOne("SELECT * FROM #@__arctype WHERE id='$newtypeid'; ");
    if(!is_array($typenew))
    {
        ShowMsg("�޷�����ƶ���������Ŀ����Ϣ��������ɲ�����", "javascript:;");
        exit();
    }
    if($typenew['ispart']!=0)
    {
        ShowMsg("�㲻�ܰ������ƶ����������б����Ŀ��", "javascript:;");
        exit();
    }
    if($typenew['channeltype']!=$typeold['channeltype'])
    {
        ShowMsg("���ܰ������ƶ����������Ͳ�ͬ����Ŀ��","javascript:;");
        exit();
    }
    $gwhere .= " And channel='".$typenew['channeltype']."' And title like '%$keyword%'";

    $ch = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id={$typenew['channeltype']} ");
    $addtable = $ch['addtable'];

    $dsql->SetQuery("SELECT id FROM `#@__archives` $gwhere");
    $dsql->Execute('m');
    $tdd = 0;
    while($row = $dsql->GetObject('m'))
    {
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__arctiny` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        $rs = $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET typeid='$newtypeid' WHERE id='{$row->id}'");
        if($addtable!='')
        {
            $dsql->ExecuteNoneQuery("UPDATE `$addtable` SET typeid='$newtypeid' WHERE aid='{$row->id}' ");
        }
        if($rs) $tdd++;
        //DelArc($row->id,true); //2011.07.06������̳����������ʹ�������ĵ�ά�����ĵ����ƶ�������վ(by:֯�ε���)
    }

    if($tdd>0)
    {
        $jumpurl  = "makehtml_archives_action.php?endid=$endid&startid=$startid";
        $jumpurl .= "&typeid=$newtypeid&pagesize=20&seltime=$seltime";
        $jumpurl .= "&stime=".urlencode($starttime)."&etime=".urlencode($endtime);
        ShowMsg("�ɹ��ƶ� $tdd ����¼��׼����������HTML...",$jumpurl);
    }
    else
    {
        ShowMsg("��ɲ�����û�ƶ��κ�����...","javascript:;");
    }
}
//ɾ���ձ�������
else if($action=='delnulltitle')
{
    $dsql->SetQuery("SELECT id FROM #@__archives WHERE trim(title)='' ");
    $dsql->Execute('x');
    $tdd = 0;
    while($row = $dsql->GetObject('x'))
    {
        if(DelArc($row->id)) $tdd++;
    }
    ShowMsg("�ɹ�ɾ�� $tdd ����¼��","javascript:;");
    exit();
}
//��������ͼ����
else if($action=='modddpic')
{
    $dsql->ExecuteNoneQuery("UPDATE #@__archives SET litpic='' WHERE trim(litpic)='litpic' ");
    ShowMsg("�ɹ���������ͼ����","javascript:;");
    exit();
}
