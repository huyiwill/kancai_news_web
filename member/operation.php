<?php 
/**
 * ����
 * 
 * @version        $Id: search.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
CheckRank(0,0);
$menutype = 'mydede';
$menutype_son = 'op';
setcookie("ENV_GOBACK_URL",GetCurUrl(),time()+3600,"/");
if(!isset($dopost)) $dopost = '';

/**
 *  ��ȡ״̬
 *
 * @param     string  $sta  ״̬ID
 * @return    string
 */
function GetSta($sta){
    if($sta==0) return 'δ����';
    else if($sta==1) return '�Ѹ���';
    else return '�����';
}

if($dopost=='')
{
    $sql = "SELECT * FROM `#@__member_operation` WHERE mid='".$cfg_ml->M_ID."' AND product<>'archive' ORDER BY aid DESC";
    $dlist = new DataListCP();
    $dlist->pageSize = 20;
    $dlist->SetTemplate(DEDEMEMBER."/templets/operation.htm");    
    $dlist->SetSource($sql);
    $dlist->Display(); 
}
else if($dopost=='del')
{
    $ids = preg_replace("#[^0-9,]#", "", $ids);
    $query = "DELETE FROM `#@__member_operation` WHERE aid IN($ids) AND mid='{$cfg_ml->M_ID}'";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("�ɹ�ɾ��ָ���Ľ��׼�¼!","operation.php");
    exit();
}
