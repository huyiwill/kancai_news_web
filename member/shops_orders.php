<?php 
/**
 * ��Ʒ����
 * 
 * @version        $Id: shops_orders.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
include_once DEDEINC.'/datalistcp.class.php';
$menutype = 'mydede';
$menutype_son = 'op';
if(!isset($dopost)) $dopost = '';

/**
 *  ��ȡ״̬
 *
 * @access    public
 * @param     string  $sta  ״̬ID
 * @param     string  $oid  ����ID
 * @return    string
 */
function GetSta($sta,$oid)
{
    global $dsql;
    $row = $dsql->GetOne("SELECT p.name FROM #@__shops_orders AS s LEFT JOIN #@__payment AS p ON s.paytype=p.id WHERE s.oid='$oid'");
    if($sta==0)
    {
        return  'δ����('.$row['name'].') < <a href="../plus/carbuyaction.php?dopost=memclickout&oid='.$oid.'" target="_blank">ȥ����</a>';
    } else if ($sta==1){
        return '�Ѹ���,�ȷ���';
    } else if ($sta==2){
        return '<a href="shops_products.php?do=ok&oid='.$oid.'">ȷ��</a>';
    } else {
        return '�����';
    }
}
if($dopost=='')
{
  $sql = "SELECT * FROM #@__shops_orders WHERE userid='".$cfg_ml->M_ID."' ORDER BY stime DESC";
  $dl = new DataListCP();
  $dl->pageSize = 20;
  //�������˳���ܸ���
  $dl->SetTemplate(dirname(__FILE__)."/templets/shops_orders.htm");      //����ģ��
  $dl->SetSource($sql);            //�趨��ѯSQL
  $dl->Display();                  //��ʾ
} else if ($dopost=='del')
{
    $ids = explode(',',$ids);
    if(isset($ids) && is_array($ids))
    {
        foreach($ids as $id)
        {
            $id = preg_replace("/^[a-z][0-9]$/","",$id);
            $query = "DELETE FROM `#@__shops_products` WHERE oid='$id' AND userid='{$cfg_ml->M_ID}'";
            $query2 = "DELETE FROM `#@__shops_orders` WHERE oid='$id' AND userid='{$cfg_ml->M_ID}'";
            $query3 = "DELETE FROM `#@__shops_userinfo` WHERE oid='$id' AND userid='{$cfg_ml->M_ID}'";
            $dsql->ExecuteNoneQuery($query);
            $dsql->ExecuteNoneQuery($query2);
            $dsql->ExecuteNoneQuery($query3);
        }
        ShowMsg("�ɹ�ɾ��ָ���Ľ��׼�¼!","shops_orders.php");
        exit();
    }
}