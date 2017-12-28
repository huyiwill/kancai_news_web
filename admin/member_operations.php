<?php
/**
 * ��Ա������־��¼����
 *
 * @version        $Id: member_operations.php 1 11:24 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Operations');
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
require_once(DEDEINC.'/datalistcp.class.php');

if(empty($buyid)) $buyid = '';
$addsql = " WHERE buyid LIKE '%$buyid%' ";
if(isset($sta)) $addsql .= " AND sta='$sta' ";

$sql = "SELECT * FROM `#@__member_operation` $addsql ORDER BY aid DESC";
$dlist = new DataListCP();

//�趨ÿҳ��ʾ��¼����Ĭ��25����
$dlist->pageSize = 25;
$dlist->SetParameter("buyid",$buyid);
if(isset($sta)) $dlist->SetParameter("sta",$sta);

$dlist->dsql->SetQuery("SELECT * FROM #@__moneycard_type ");
$dlist->dsql->Execute('ts');
while($rw = $dlist->dsql->GetArray('ts'))
{
    $TypeNames[$rw['tid']] = $rw['pname'];
}
$tplfile = DEDEADMIN."/templets/member_operations.htm";

//�������˳���ܸ���
$dlist->SetTemplate($tplfile);      //����ģ��
$dlist->SetSource($sql);            //�趨��ѯSQL
$dlist->Display();                  //��ʾ

function GetMemberID($mid)
{
    global $dsql;
    if($mid==0)
    {
        return '0';
    }
    $row = $dsql->GetOne("SELECT userid FROM #@__member WHERE mid='$mid' ");
    if(is_array($row))
    {
        return "<a href='member_view.php?id={$mid}'>".$row['userid']."</a>";
    }
    else
    {
        return '0';
    }
}

function GetPType($tname)
{
    if($tname=='card') return '������';
    else if($tname=='archive') return '��������';
    else if($tname=='stc') return '�һ����';
    else return '��Ա����';
}

function GetSta($sta)
{
    if($sta==0)
    {
        return 'δ����';
    }
    else if($sta==1)
    {
        return '�Ѹ���';
    }
    else
    {
        return '�����';
    }
}