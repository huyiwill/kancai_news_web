<?php
/**
 *  Ȧ�ӷ�������
 *
 * @version        $Id: group_store.php 1 15:34 2011-1-21 tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('group_Store');
require_once(DEDEINC.'/datalistcp.class.php');
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");

$id = isset($id) && is_numeric($id) ? $id : 0;
$action = isset($action) ? trim($action) : '';

if($action=="add")
{
    $storename = cn_substrR(HtmlReplace($storename, 2),20);
    $tops = preg_replace("#[^0-9]#","",$tops);
    $orders = preg_replace("#[^0-9]#","",$orders);
    if($tops < 1)
    {
        $tops = 0;
    }
    if($orders < 1)
    {
        $orders = 0;
    }
    if(empty($storename))
    {
        $msg = "����,����������Ϊ��!";
    }
    else
    {
        $db->ExecuteNoneQuery("INSERT INTO #@__store_groups(storename,tops,orders) VALUES('".$storename."','".$tops."','".$orders."');");
        $msg = "�ɹ���ӷ���";
    }
}
else if($action=="del"&&isset($id))
{
    $db->ExecuteNoneQuery("DELETE FROM #@__store_groups WHERE storeid='$id'");
    $msg = "ɾ�����ࣺ{$id} ��";
}
$btypes = array();
$db->SetQuery("SELECT * FROM #@__store_groups WHERE tops=0");
$db->Execute();
$options = '';
while($rs = $db->GetArray())
{
    array_push ($btypes,$rs);
}
foreach($btypes as $k=>$v)
{
    $options .= "<option value='".$v['storeid']."'>".$v['storename']."</option>\r\n";
}

/*
function LoadEdit();
*/

if($action=='editload')
{
    $row = $db->GetOne("Select * From #@__store_groups where storeid='$catid'");
    AjaxHead();
?>
<form name='editform' action='group_store.php' method='get'>
<input type='hidden' name='action' value='editsave' />
<input type='hidden' name='catid' value='<?php echo $catid; ?>' />
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
<td width="90" height="28">��Ŀ���ƣ�</td>
<td width="101"><input name="storename" type="text" id="storename" value="<?php echo $row['storename']; ?>" /></td>
<td width="20" align="right" valign="top"></td>
</tr>
<tr>
<td height="28">������Ŀ��</td>
<td colspan="2">
<select name="tops" id="tops">
<option value="0">������Ŀ</option>
<?php
foreach($btypes as $k=>$v)
{
    if($row['tops']==$v['storeid'])
    {
        echo "<option value='".$v['storeid']."' selected>".$v['storename']."</option>\r\n";
    }
    else
    {
        echo "<option value='".$v['storeid']."'>".$v['storename']."</option>\r\n";
    }
}
?>
</select>
</td>
</tr>
<tr>
<td height="28">���򼶱�</td>
<td colspan="2"><input name="orders" type="text" id="orders" size="5" value="<?php echo $row['orders']; ?>" />
����ֵС��ǰ��</td>
</tr>
<tr>
<td height="43">&nbsp;</td>
<td colspan="2"><input type="submit" name="Submit" value="�������"  class="np coolbg" style="width:80px"/></td>
</tr>
</table>
</form>
<?php

exit();
}
else if($action=='editsave')
{
    $db->ExecuteNoneQuery("UPDATE #@__store_groups SET storename='$storename',tops='$tops',orders='$orders' WHERE storeid='$catid'");
    $msg = "�ɹ��޸���Ŀ��{$catid} = {$storename} ��";
}
else if($action=='uprank')
{
    foreach($_POST as $rk=>$rv)
    {
        if(preg_match('#rank#i',$rk))
        {
            $catid = str_replace('rank_','',$rk);
            $db->ExecuteNoneQuery("UPDATE #@__store_groups SET orders='{$rv}' WHERE storeid='{$catid}'");
        }
    }
    $msg = "�ɹ��������� ��";
}

$sql = "SELECT storeid,storename,tops,orders FROM #@__store_groups WHERE tops=0 ORDER BY orders ASC";

$dl = new DataListCP();
$dl->pageSize = 20;

//�������˳���ܸ���
$dl->SetTemplate(DEDEADMIN."/templets/group_store.htm");      //����ģ��
$dl->SetSource($sql);            //�趨��ѯSQL
$dl->Display();                  //��ʾ

?>