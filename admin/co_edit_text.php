<?php
/**
 * �ɼ�����༭-ר�Ҹ���ģʽ
 *
 * @version        $Id: co_edit_text.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require(dirname(__FILE__)."/config.php");
CheckPurview('co_EditNote');
if(empty($job)) $job='';

if($job=='')
{
    require_once(DEDEINC."/oxwindow.class.php");
    $wintitle = "���Ĳɼ�����";
    $wecome_info = "<a href='co_main.php'><u>�ɼ������</u></a>::���Ĳɼ����� - ר�Ҹ���ģʽ";
    $win = new OxWindow();
    $win->Init("co_edit_text.php", "js/blank.js", "POST");
    $win->AddHidden("job", "yes");
    $win->AddHidden("nid", $nid);
    $row = $dsql->GetOne("SELECT * FROM `#@__co_note` WHERE nid='$nid' ");
    $win->AddTitle("�����������Ϣ���ã�");
    $win->AddMsgItem("<textarea name='listconfig' style='width:100%;height:200px'>{$row['listconfig']}</textarea>");
    $win->AddTitle("�ֶ����ã�");
    $win->AddMsgItem("<textarea name='itemconfig' style='width:100%;height:300px'>{$row['itemconfig']}</textarea>");
    $winform = $win->GetWindow("ok");
    $win->Display();
    exit();
}
else
{
    CheckPurview('co_EditNote');
    $query = "UPDATE `#@__co_note` SET listconfig='$listconfig',itemconfig='$itemconfig' WHERE nid='$nid' ";
    $rs = $dsql->ExecuteNoneQuery($query);
    ShowMsg("�ɹ��޸�һ������!","co_main.php");
    exit();
}