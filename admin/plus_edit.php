<?php
/**
 * ����༭
 *
 * @version        $Id: plus_edit.php 1 15:46 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_plus');
$aid = preg_replace("#[^0-9]#", "", $aid);
if($dopost=="show")
{
    $dsql->ExecuteNoneQuery("UPDATE #@__plus SET isshow=1 WHERE aid='$aid';");
    ShowMsg("�ɹ�����һ�����,��ˢ�µ����˵�!","plus_main.php");
    exit();
}
else if($dopost=="hide")
{
    $dsql->ExecuteNoneQuery("UPDATE #@__plus SET isshow=0 WHERE aid='$aid';");
    ShowMsg("�ɹ�����һ�����,��ˢ�µ����˵�!","plus_main.php");
    exit();
}
else if($dopost=="delete")
{
    if(empty($job)) $job="";
    if($job=="") //ȷ����ʾ
    {
        require_once(DEDEINC."/oxwindow.class.php");
        $wintitle = "ɾ�����";
        $wecome_info = "<a href='plus_main.php'>�������</a>::ɾ�����";
        $win = new OxWindow();
        $win->Init("plus_edit.php", "js/blank.js", "POST");
        $win->AddHidden("job", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("aid", $aid);
        $win->AddTitle("��ȷʵҪɾ��'".$title."'��������");
        $win->AddMsgItem("<font color='red'>���棺������ɾ������ɾ���˵��Ҫ�ɾ�ɾ������ģ�����ɾ����<br /><br /> <a href='module_main.php?moduletype=plus'>ģ�����&gt;&gt;</a> </font>");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    }
    else if($job=="yes") //����
    {
        $dsql->ExecuteNoneQuery("DELETE FROM #@__plus WHERE aid='$aid';");
        ShowMsg("�ɹ�ɾ��һ�����,��ˢ�µ����˵�!","plus_main.php");
        exit();
    }
}
else if($dopost=="saveedit") //�������
{
    $inquery = "UPDATE #@__plus SET plusname='$plusname',menustring='$menustring',filelist='$filelist' WHERE aid='$aid';";
    $dsql->ExecuteNoneQuery($inquery);
    ShowMsg("�ɹ����Ĳ��������!","plus_main.php");
    exit();
}
$row = $dsql->GetOne("SELECT * FROM #@__plus WHERE aid='$aid'");
include DedeInclude('templets/plus_edit.htm');