<?php
/**
 * �����¼
 * 
 * @version        $Id: visit-history.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0,0);
$menutype = 'mydede';
if(!isset($dopost)) $dopost = '';

require_once(DEDEINC."/datalistcp.class.php");
$wsql = '';
if($dopost=='meview')
{
    $wsql = " v.vid='{$cfg_ml->M_ID}' ";
    $tname = "���������";
    $osql="ON v.mid=m.mid ";
}
else
{
    $wsql = " v.mid='{$cfg_ml->M_ID}' ";
    $tname = "��ע�ҵ���";
    $osql="ON v.vid=m.mid ";
}
$query = "SELECT v.*,m.sex,face FROM `#@__member_vhistory` AS v  LEFT JOIN `#@__member` AS m $osql WHERE $wsql ORDER BY vtime DESC";
$dlist = new DataListCP();
$dlist->pageSize = 20;
$dlist->SetParameter("dopost",$dopost);
$dlist->SetTemplate(DEDEMEMBER.'/templets/visit-history.htm');
$dlist->SetSource($query);
$dlist->Display();