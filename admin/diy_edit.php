<?php
/**
 * �༭�Զ����
 *
 * @version        $Id: diy_add.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_Edit');
require_once(DEDEINC."/dedetag.class.php");
require_once(DEDEINC."/oxwindow.class.php");

if(empty($dopost)) $dopost="";
$diyid = (empty($diyid) ? 0 : intval($diyid));

/*----------------
function __SaveEdit()
-----------------*/
if($dopost=="save")
{
    $public = isset($public) && is_numeric($public) ? $public : 0;
    $name = htmlspecialchars($name);
    $query = "UPDATE `#@__diyforms` SET name = '$name', listtemplate='$listtemplate', viewtemplate='$viewtemplate', posttemplate='$posttemplate', public='$public' WHERE diyid='$diyid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("�ɹ�����һ���Զ������","diy_main.php");
    exit();
}
/*----------------
function __Delete()
-----------------*/
else if($dopost=="delete")
{
    @set_time_limit(0);
    CheckPurview('c_Del');
    $row = $dsql->GetOne("SELECT * FROM #@__diyforms WHERE diyid='$diyid'");
    if(empty($job)) $job = "";

    //ȷ����ʾ
    if($job=="")
    {
        $wintitle = "�Զ��������-ɾ���Զ����";
        $wecome_info = "<a href='diy_main.php'>�Զ��������</a>::ɾ���Զ����";
        $win = new OxWindow();
        $win->Init("diy_edit.php", "js/blank.js", "POST");
        $win->AddHidden("job", "yes");
        $win->AddHidden("dopost", $dopost);
        $win->AddHidden("diyid", $diyid);
        $win->AddTitle("����ɾ����������Զ������ص��ļ�������<br />��ȷʵҪɾ�� \"".$row['name']."\" ����Զ������");
        $winform = $win->GetWindow("ok");
        $win->Display();
        exit();
    }

    //����
    else if($job=="yes")
    {
        $row = $dsql->GetOne("SELECT `table` FROM `#@__diyforms` WHERE diyid='$diyid'",MYSQL_ASSOC);
        if(!is_array($row))
        {
            ShowMsg("����ָ�����Զ������Ϣ������!","-1");
            exit();
        }

        //ɾ����
        $dsql->ExecuteNoneQuery("DROP TABLE IF EXISTS `{$row['table']}`;");

        //ɾ��Ƶ��������Ϣ
        $dsql->ExecuteNoneQuery("DELETE FROM `#@__diyforms` WHERE diyid='$diyid'");
        ShowMsg("�ɹ�ɾ��һ���Զ������","diy_main.php");
        exit();
    }
}

/*----------------
function edit()
-----------------*/
$row = $dsql->GetOne("Select * From #@__diyforms where diyid='$diyid'");
include DEDEADMIN."/templets/diy_edit.htm";