<?php
/**
 * ��ǩ���Բ���
 *
 * @version        $Id: tag_test_action.php 1 23:07 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('temp_Test');
require_once(DEDEINC."/arc.partview.class.php");
if(empty($partcode))
{
    ShowMsg('��������','javascript:;');
    exit;
}
$partcode = stripslashes($partcode);

if(empty($typeid)) $typeid = 0;
if(empty($showsource)) $showsource = "";

if($typeid>0) $pv = new PartView($typeid);
else $pv = new PartView();

$pv->SetTemplet($partcode, "string");
if( $showsource == "" || $showsource == "yes" )
{
    echo "ģ�����:";
    echo "<span style='color:red;'><pre>".htmlspecialchars($partcode)."</pre></span>";
    echo "���:<hr size='1' width='100%'>";
}
$pv->Display();