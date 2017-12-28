<?php
/**
 * ���ͷ�ʽ����
 *
 * @version        $Id: shops_delivery.php 1 15:46 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('shops_Delivery');
require_once DEDEINC.'/datalistcp.class.php';

if(!isset($do)) $do ='';
if($do=='add')
{
    if( empty($dname) || (strlen($dname) > 100) )
    {
        ShowMsg("����д���ͷ�ʽ����!","-1");
        exit();
    }
    $price     = preg_replace("#[^.0-9]#", "", $price);
    if($price < 0.01)
    {
        $price = '0.00';
    }
    $des = cn_substrR($des,255);
    $InQuery = "INSERT INTO #@__shops_delivery(`dname`,`price`,`des`) VALUES ('$dname','$price','$des');";
    $result = $dsql->ExecuteNoneQuery($InQuery);
    if($result)
    {
        ShowMsg("�ɹ����һ�����ͷ�ʽ!","shops_delivery.php");
    }
    else
    {
        ShowMsg("������ͷ�ʽʱ����SQL����!","-1");
    }
    exit();
} else if ($do == 'del')
{
    $id = intval($id);
    $dsql->ExecuteNoneQuery("DELETE FROM #@__shops_delivery WHERE pid='$id'");
    ShowMsg("��ɾ����ǰ���ͷ�ʽ!","shops_delivery.php");
    exit();
} else if ($do == 'edit')
{
    foreach($pid as $id)
    {
        $id = intval($id);
        $row = $dsql->GetOne("SELECT pid,dname,price,des FROM #@__shops_delivery WHERE pid='$id' LIMIT 0,1");
        if(!is_array($row))
        {
            continue;
        }
        $dname = ${"m_dname".$id};
        $price = ${"m_price".$id};
        $des = ${"m_des".$id};
        if( empty($dname) || (strlen($dname) > 100) )
        {
            $dname = addslashes($row['dname']);
        }
        $price = preg_replace("#[^.0-9]#", "", $price);
        if(empty($price))
        {
            $price = $row['price'];
        }
        if(empty($des))
        {
            $des = addslashes($row['des']);
        }
        else
        {
            $des = cn_substrR($des,255);
        }
        $dsql->ExecuteNoneQuery("UPDATE #@__shops_delivery SET dname='$dname',price='$price',des='$des' WHERE pid='$id'");
    }
    ShowMsg("�ɹ��޸����ͷ�ʽ!","shops_delivery.php");
    exit();
}
$deliveryarr = array();
$dsql->SetQuery("SELECT pid,dname,price,des FROM #@__shops_delivery ORDER BY orders ASC");
$dsql->Execute();
while($row = $dsql->GetArray())
{
    $deliveryarr[] = $row;
}
$dlist = new DataListCP();
$dlist->pageSize = 25;              //�趨ÿҳ��ʾ��¼����Ĭ��25����

//�������˳���ܸ���
$dlist->SetTemplate(DEDEADMIN."/templets/shops_delivery.htm");      //����ģ��
$dlist->SetSource("SELECT `pid`,`dname`,`price`,`des` FROM #@__shops_delivery ORDER BY `orders` ASC");            //�趨��ѯSQL
$dlist->Display();                  //��ʾ