<?php
/**
 * �����վ������
 *
 * @version        $Id: article_allowurl_edit.php 1 11:36 2010��10��8��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/oxwindow.class.php");
CheckPurview('sys_Source');
if(empty($dopost)) $dopost = '';
if(empty($allurls)) $allsource = '';
else $allurls = stripslashes($allurls);

$m_file = DEDEDATA."/admin/allowurl.txt";

//����
if($dopost=='save')
{
    $fp = fopen($m_file,'w');
    flock($fp,3);
    fwrite($fp,$allurls);
    fclose($fp);
    echo "<script>alert('Save OK!');</script>";
}
//����
if(empty($allurls) && filesize($m_file)>0)
{
    $fp = fopen($m_file,'r');
    $allurls = fread($fp,filesize($m_file));
    fclose($fp);
}
$wintitle = "";
$wecome_info = "����ĳ�����";
$win = new OxWindow();
$win->Init('article_allowurl_edit.php','js/blank.js','POST');
$win->AddHidden('dopost','save');
$win->AddTitle("ÿ�б���һ�������ӣ�");
$win->AddMsgItem("<textarea name='allurls' id='allurls' style='width:100%;height:300px'>$allurls</textarea>");
$winform = $win->GetWindow('ok');
$win->Display();