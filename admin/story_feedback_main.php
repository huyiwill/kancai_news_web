<?php
require_once(dirname(__FILE__). "/config.php");

//Ȩ�޼��
CheckPurview('sys_Feedback');
require_once(DEDEINC. "/datalistcp.class.php");
require_once(DEDEINC. "/typelink.class.php");
setcookie("ENV_GOBACK_URL", $dedeNowurl, time()+3600,"/");

function IsCheck($st){ return $st==1? "[�����]" : "<font color='red'>[δ���]</font>";}


if(!empty($job))
{
    $ids = preg_replace("#[^0-9,]#", '', $fid);
    if(empty($ids))
    {
        ShowMsg("��ûѡ���κ�ѡ�", $_COOKIE['ENV_GOBACK_URL'], 0, 500);
        exit;
    }
}
else
{
    $job = '';
}

//ɾ������
if( $job == 'del' )
{
        $query = "DELETE From `#@__bookfeedback` WHERE id in($ids) ";
        $dsql->ExecuteNoneQuery($query);
        ShowMsg("�ɹ�ɾ��ָ��������!",$_COOKIE['ENV_GOBACK_URL'],0,500);
        exit();
}
//ɾ����ͬIP����������
else if( $job == 'delall' )
{
        $dsql->SetQuery("SELECT ip FROM `#@__bookfeedback` WHERE id in ($ids) ");
        $dsql->Execute();
        $ips = '';
        while($row = $dsql->GetArray())
        {
            $ips .= ($ips=='' ? " ip = '{$row['ip']}' " : " OR ip = '{$row['ip']}' ");
        }
        if($ips!='')
        {
            $query = "DELETE FROM `#@__bookfeedback` WHERE $ips ";
            $dsql->ExecuteNoneQuery($query);
        }
        ShowMsg("�ɹ�ɾ��ָ����ͬIP����������!", $_COOKIE['ENV_GOBACK_URL'], 0, 500);
        exit();
}
//�������
else if($job=='check')
{
        $query = "UPDATE `#@__bookfeedback` SET ischeck=1 WHERE id in($ids) ";
        $dsql->ExecuteNoneQuery($query);
        ShowMsg("�ɹ����ָ������!", $_COOKIE['ENV_GOBACK_URL'], 0, 500);
        exit();
}
//�������
else
{
    $bgcolor = '';
    $typeid = isset($typeid) && is_numeric($typeid) ? $typeid : 0;
    $aid = isset($aid) && is_numeric($aid) ? $aid : 0;
    $keyword = !isset($keyword) ? '' : $keyword;
    $ip = !isset($ip) ? '' : $ip;
    
    $tl = new TypeLink($typeid);
    $openarray = $tl->GetOptionArray($typeid,$admin_catalogs,0);
    
    $addsql = ($typeid != 0  ? " And typeid in (".GetSonIds($typeid).")" : '');
    $addsql .= ($aid != 0  ? " And aid=$aid " : '');
    $addsql .= ($ip != ''  ? " And ip like '$ip' " : '');
    $querystring = "SELECT * FROM `#@__bookfeedback` WHERE msg like '%$keyword%' $addsql ORDER BY dtime DESC";
    
    $dlist = new DataListCP();
    $dlist->pageSize = 15;
    $dlist->SetParameter('aid', $aid);
    $dlist->SetParameter('ip', $ip);
    $dlist->SetParameter('typeid', $typeid);
    $dlist->SetParameter('keyword', $keyword);
    $dlist->SetTemplate(DEDEADMIN. '/templets/story_feedback_main.htm');
    $dlist->SetSource($querystring);
    $dlist->Display();
}
