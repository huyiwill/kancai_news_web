<?php
/**
 * �㿨����
 *
 * @version        $Id: cards_manage.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC.'/datalistcp.class.php');
$dopost=empty($dopost)? "" : $dopost;
if($dopost=="delete"){
    $ids = explode('`',$aids);
    $dquery = "";
    foreach($ids as $id){
        if($dquery=="") $dquery .= "aid='$id' ";
        else $dquery .= " OR aid='$id' ";
    }
    if($dquery!="") $dquery = " WHERE ".$dquery;
    $dsql->ExecuteNoneQuery("DELETE FROM #@__moneycard_record $dquery");
    ShowMsg("�ɹ�ɾ��ָ���ļ�¼��","cards_manage.php");
    exit();    
}else{
    $addsql = '';
    if(isset($isexp)) $addsql = " WHERE isexp='$isexp' ";
    
    $sql = "SELECT * FROM #@__moneycard_record $addsql ORDER BY aid DESC";
    $dlist = new DataListCP();
    $dlist->pageSize = 25; //�趨ÿҳ��ʾ��¼����Ĭ��25����
    if(isset($isexp)) $dlist->SetParameter("isexp",$isexp);

    $dlist->dsql->SetQuery("SELECT * FROM #@__moneycard_type ");
    $dlist->dsql->Execute('ts');
    while($rw = $dlist->dsql->GetArray('ts'))
    {
        $TypeNames[$rw['tid']] = $rw['pname'];
    }
    $tplfile = DEDEADMIN."/templets/cards_manmage.htm";
    
    //�������˳���ܸ���
    $dlist->SetTemplate($tplfile);      //����ģ��
    $dlist->SetSource($sql);            //�趨��ѯSQL
    $dlist->Display();                  //��ʾ
}

function GetMemberID($mid)
{
    global $dsql;
    if($mid==0) return '0';
    $row = $dsql->GetOne("SELECT userid FROM #@__member WHERE mid='$mid' ");
    if(is_array($row)) return "<a href='member_view.php?mid={$mid}'>".$row['userid']."</a>";
    else return '0';
}

function GetUseDate($time=0)
{
    if(!empty($time)) return GetDateMk($time);
    else return 'δʹ��';
}
function GetSta($sta)
{
    if($sta==1) return '���۳�';
    else if($sta==-1) return '��ʹ��';
    else return 'δʹ��';
}