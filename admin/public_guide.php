<?php
/**
 * ����ĳƵ��ΪĬ�Ϸ���
 *
 * @version        $Id: public_guide.php 1 15:46 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
if(empty($action)) $action = '';

/*--------------------
function __SetDefault();
----------------------*/
if($action=='setdefault')
{
    CheckPurview('sys_Edit');
    $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isdefault=0 WHERE id<>'$cid'");
    if($cid != 0)
    {
        $dsql->ExecuteNoneQuery("UPDATE `#@__channeltype` SET isdefault=1 WHERE id='$cid'");
    }
    $win = new OxWindow();
    $win->Init();
    $win->mainTitle = "���ݷ�����";
    $wecome_info = "<a href='public_guide.php?action=edit'>���ݷ�����</a>";
    $win->AddTitle("<a href='public_guide.php?action=edit'>���ݷ�����</a> &gt;&gt; ����Ĭ�Ϸ�����");
    if($cid==0)
    {
        $msg = "
         �ɹ�ȡ��Ĭ�Ϸ�������
           <hr style='width:90%' size='1' />
           ��Ŀǰ��Ҫ���еĲ����� <a href='public_guide.php?action=edit'>���ط�����ҳ</a>
      ";
    }
    else
    {
        $msg = "
        �ɹ�����Ĭ�Ϸ��������Ժ��������ݷ�������彫ֱ����ת����ѡ������ݷ���ҳ��
        <hr style='width:90%' size='1' />
           ��Ŀǰ��Ҫ���еĲ����� <a href='public_guide.php'>ת��Ĭ�Ϸ�����</a> &nbsp; <a href='public_guide.php?action=edit'>���ط�����ҳ</a>
      ";
    }
    $win->AddMsgItem("<div style='padding-left:20px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow("hand");
    $win->Display();
    exit();
}

//����Ϊ�������������
/*--------------------
function __PageShow();
----------------------*/
$row = $dsql->GetOne("SELECT id,addcon FROM `#@__channeltype` WHERE isdefault='1' ");

//�Ѿ�������Ĭ�Ϸ�����
if(is_array($row) && $action!='edit')
{
    $addcon = $row['addcon'];
    if($addcon=='')
    {
        $addcon='archives_add.php';
    }
    $channelid = $row['id'];
    $cid = 0;
    require_once(DEDEADMIN.'/'.$addcon);
    exit();
}

//û������Ĭ�Ϸ�����
else
{
    $dsql->SetQuery("SELECT id,typename,mancon,isdefault,addtable FROM `#@__channeltype` WHERE id<>-1 And isshow=1 ");
    $dsql->Execute();
}
include DedeInclude('templets/public_guide.htm');

//��ȡƵ����Ŀ��
function GetCatalogs(&$dsql,$cid)
{
    $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__arctype` WHERE channeltype='$cid' ");
    return (!is_array($row) ? '0' : $row['dd']);
}