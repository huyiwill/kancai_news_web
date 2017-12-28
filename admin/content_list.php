<?php
/**
 * �����б�
 * content_s_list.php��content_i_list.php��content_select_list.php
 * ��ʹ�ñ��ļ���Ϊʵ�ʴ�����룬ֻ��ʹ�õ�ģ�岻ͬ��������ر䶯��ֻ��ı��ļ������ģ�弴��
 *
 * @version        $Id: content_list.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');
require_once(DEDEINC.'/typelink.class.php');
require_once(DEDEINC.'/datalistcp.class.php');
require_once(DEDEADMIN.'/inc/inc_list_functions.php');

$cid = isset($cid) ? intval($cid) : 0;
$channelid = isset($channelid) ? intval($channelid) : 0;
$mid = isset($mid) ? intval($mid) : 0;

if(!isset($keyword)) $keyword = '';
if(!isset($flag)) $flag = '';
if(!isset($arcrank)) $arcrank = '';
if(!isset($dopost)) $dopost = '';

//���Ȩ����ɣ���Ȩ��
CheckPurview('a_List,a_AccList,a_MyList');

//��Ŀ������
$userCatalogSql = '';
if(TestPurview('a_List'))
{
    ;
}
else if(TestPurview('a_AccList'))
{
    if($cid==0 && $cfg_admin_channel == 'array')
    {
        $admin_catalog = join(',', $admin_catalogs);
        $userCatalogSql = " arc.typeid IN($admin_catalog) ";
    }
    else
    {
        CheckCatalog($cid, '����Ȩ�����ָ����Ŀ�����ݣ�');
    }
    if(TestPurview('a_MyList')) $mid =  $cuserLogin->getUserID();

}

$adminid = $cuserLogin->getUserID();
$maintable = '#@__archives';
setcookie('ENV_GOBACK_URL', $dedeNowurl, time()+3600, '/');
$tl = new TypeLink($cid);

//----------------------------------------
//�ڲ�ָ�����������͹ؼ��ֵ������ֱ��ͳ��΢��
//----------------------------------------
if(empty($totalresult) && empty($keyword) && empty($orderby) && empty($flag))
{
    $tinyQuerys = array();
    
    if(!empty($userCatalogSql))
    {
        $tinyQuerys[] = str_replace('arc.', '', $userCatalogSql);
    }
    
    if(!empty($channelid) && empty($cid))
    {
        $tinyQuerys[] = " channel = '$channelid' ";
    }
    else
    {
        $tinyQuerys[] = " channel>0 ";
    }
    
    if(!empty($arcrank))
    {
        $tinyQuerys[] = " arcrank='$arcrank' ";
    }
    else
    {
        $tinyQuerys[] = " arcrank > -2 ";
    }
    
    if(!empty($mid))
    {
        $tinyQuerys[] = " mid='$mid' ";
    }
    
    if(!empty($cid))
    {
        $tinyQuerys[] = " typeid in(".GetSonIds($cid).") ";
    }
    
    if(count($tinyQuerys)>0)
    {
        $tinyQuery = "WHERE ".join(' AND ',$tinyQuerys);
    }
    // ���洦��
    $sql = "SELECT COUNT(*) AS dd FROM `#@__arctiny` $tinyQuery ";
    $cachekey = md5($sql);
    $arr = GetCache('listcache', $cachekey);
    if (empty($arr))
    {
        $arr = $dsql->GetOne($sql);
        SetCache('listcache', $cachekey, $arr);
    }
    $totalresult = $arr['dd'];
}

if($cid==0)
{
    if($channelid==0)
    {
        $positionname = '������Ŀ&gt;';
    }
    else
    {
        $row = $tl->dsql->GetOne("SELECT id,typename,maintable FROM `#@__channeltype` WHERE id='$channelid'");
        $positionname = $row['typename']." &gt; ";
        $maintable = $row['maintable'];
        $channelid = $row['id'];
    }
}
else
{
    $positionname = str_replace($cfg_list_symbol," &gt; ",$tl->GetPositionName())." &gt; ";
}

//��ѡ����ǵ���ģ����Ŀʱ��ֱ����ת������ģ�͹�����
if(empty($channelid) 
  && isset($tl->TypeInfos['channeltype']))
{
    $channelid = $tl->TypeInfos['channeltype'];
}
if($channelid < -1 )
{
    header("location:content_sg_list.php?cid=$cid&channelid=$channelid&keyword=$keyword");
    exit();
}


// ��Ŀ����800����Ҫ��������
$optHash = md5($cid.$admin_catalogs.$channelid);
$optCache = DEDEDATA."/tplcache/inc_option_$optHash.inc";

$typeCount = 0;
if (file_exists($cache1)) require_once($cache1);
else $cfg_Cs = array();
$typeCount = count($cfg_Cs);
if ( $typeCount > 800)
{
    if (file_exists($optCache))
    {
        $optionarr = file_get_contents($optCache);
    } else { 
        $optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
        file_put_contents($optCache, $optionarr);
    }
} else { 
    $optionarr = $tl->GetOptionArray($cid, $admin_catalogs, $channelid);
}

$whereSql = empty($channelid) ? " WHERE arc.channel > 0  AND arc.arcrank > -2 " : " WHERE arc.channel = '$channelid' AND arc.arcrank > -2 ";

$flagsArr = '';
$dsql->Execute('f', 'SELECT * FROM `#@__arcatt` ORDER BY sortid ASC');
while($frow = $dsql->GetArray('f'))
{
    $flagsArr .= ($frow['att']==$flag ? "<option value='{$frow['att']}' selected>{$frow['attname']}</option>\r\n" : "<option value='{$frow['att']}'>{$frow['attname']}</option>\r\n");
}


if(!empty($userCatalogSql))
{
    $whereSql .= " AND ".$userCatalogSql;
}
if(!empty($mid))
{
    $whereSql .= " AND arc.mid = '$mid' ";
}
if($keyword != '')
{
    $whereSql .= " AND ( CONCAT(arc.title,arc.writer) LIKE '%$keyword%') ";
}
if($flag != '')
{
    $whereSql .= " AND FIND_IN_SET('$flag', arc.flag) ";
}
if($cid != 0)
{
    $whereSql .= ' AND arc.typeid IN ('.GetSonIds($cid).')';
}
if($arcrank != '')
{
    $whereSql .= " AND arc.arcrank = '$arcrank' ";
    $CheckUserSend = "<input type='button' class='coolbg np' onClick=\"location='catalog_do.php?cid=".$cid."&dopost=listArchives&gurl=content_list.php';\" value='�����ĵ�' />";
}
else
{
    $CheckUserSend = "<input type='button' class='coolbg np' onClick=\"location='catalog_do.php?cid=".$cid."&dopost=listArchives&arcrank=-1&gurl=content_list.php';\" value='������' />";
}

$orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z0-9]#", "", $orderby);
$orderbyField = 'arc.'.$orderby;

$query = "SELECT arc.id,arc.typeid,arc.senddate,arc.flag,arc.ismake,
arc.channel,arc.arcrank,arc.click,arc.title,arc.color,arc.litpic,arc.pubdate,arc.mid
FROM `$maintable` arc
$whereSql
ORDER BY $orderbyField DESC";

if(empty($f) || !preg_match("#form#", $f)) $f = 'form1.arcid1';

//��ʼ��
$dlist = new DataListCP();
$dlist->pageSize = 30;

//GET����
$dlist->SetParameter('dopost', 'listArchives');
$dlist->SetParameter('keyword', $keyword);
if(!empty($mid)) $dlist->SetParameter('mid', $mid);
$dlist->SetParameter('cid', $cid);
$dlist->SetParameter('flag', $flag);
$dlist->SetParameter('orderby', $orderby);
$dlist->SetParameter('arcrank', $arcrank);
$dlist->SetParameter('channelid', $channelid);
$dlist->SetParameter('f', $f);

//ģ��
if(empty($s_tmplets)) $s_tmplets = 'templets/content_list.htm';
$dlist->SetTemplate(DEDEADMIN.'/'.$s_tmplets);

//��ѯ
$dlist->SetSource($query);

//��ʾ
$dlist->Display();
// echo $dlist->queryTime;
$dlist->Close();