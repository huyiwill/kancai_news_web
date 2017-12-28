<?php
/**
 * ����ģ�ͷ�����
 * 
 * @version        $Id: archives_sg_add.php 1 13:52 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
if(!defined('DEDEMEMBER')) exit("dedecms");

/**
 *  ��ȡѡ���б�
 *
 * @param     string  $selid  ��ǰѡ��ID
 * @param     string  $channeltype  Ƶ������
 * @return    string
 */
function GetOptionList($selid=0, $channeltype=0)
{
    global $OptionArrayList,$channels,$dsql;
    $dsql->SetQuery("SELECT id,typename FROM `#@__channeltype` ");
    $dsql->Execute();
    $channels = Array();
    while($row = $dsql->GetObject())
    {
        $channels[$row->id] = $row->typename;
    }
    $OptionArrayList = "";
    $query = "SELECT id,typename,ispart,channeltype,issend FROM `#@__arctype` WHERE ispart<2 AND reid=0 ORDER BY sortrank ASC ";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $selected = '';
    while($row=$dsql->GetObject())
    {
        if($selid==$row->id)
        {
            $selected = " selected='$selected'";
        }
        if($row->channeltype==$channeltype && $row->issend==1)
        {
            if($row->ispart==0)
            {
                $OptionArrayList .= "<option value='".$row->id."' class='option3'{$selected}>".$row->typename."</option>\r\n";
            }
            else if($row->ispart==1)
            {
                $OptionArrayList .= "<option value='".$row->id."' class='option2'{$selected}>".$row->typename."</option>\r\n";
            }
        }
        $selected = '';
        LogicGetOptionArray($row->id,"��",$channeltype,$selid);
    }
    return $OptionArrayList;
}

/**
 *  �߼��ݹ�
 *
 * @access    public
 * @param     int  $id
 * @param     string  $step
 * @param     string  $channeltype
 * @param     int  $selid
 * @return    string
 */
function LogicGetOptionArray($id,$step,$channeltype,$selid=0)
{
    global $OptionArrayList,$channels,$dsql;
    $selected = '';
    $dsql->SetQuery("Select id,typename,ispart,channeltype,issend From `#@__arctype` where reid='".$id."' And ispart<2 order by sortrank asc");
    $dsql->Execute($id);
    while($row=$dsql->GetObject($id))
    {
        if($selid==$row->id)
        {
            $selected = " selected='$selected'";
        }
        if($row->channeltype==$channeltype && $row->issend==1)
        {
            if($row->ispart==0)
            {
                $OptionArrayList .= "<option value='".$row->id."' class='option3'{$selected}>$step".$row->typename."</option>\r\n";
            }
            else if($row->ispart==1)
            {
                $OptionArrayList .= "<option value='".$row->id."' class='option2'{$selected}>$step".$row->typename."</option>\r\n";
            }
        }
        $selected = '';
        LogicGetOptionArray($row->id,$step."��",$channeltype,$selid);
    }
}

/**
 *  �Զ�������
 *
 * @param     int  $mid  ��ԱID
 * @param     int  $mtypeid  �Զ������ID
 * @param     int  $channelid  Ƶ��ID
 * @return    string
 */
function classification($mid, $mtypeid = 0, $channelid=1)
{
    global $dsql;
    $list = $selected = '';
    $quey = "SELECT * FROM `#@__mtypes` WHERE mid = '$mid' And channelid='$channelid' ;";
    $dsql->SetQuery($quey);
    $dsql->Execute();
    while ($row = $dsql->GetArray())
    {
        if($mtypeid != 0){
            if($mtypeid == $row['mtypeid'])
            {
                $selected = " selected";
            }
        }
        $list .= "<option value='".$row['mtypeid']."' class='option3'{$selected}>".$row['mtypename']."</option>\r\n";
        $selected = '';
    }
    return $list;
}