<?php
/**
 * ��ȡTAGS����
 *
 * @version        $Id: tag_test_action.php 1 23:07 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('sys_Keyword');
require_once(DEDEINC.'/datalistcp.class.php');
$timestamp = time();
if(empty($tag)) $tag = '';

if(empty($action))
{
    $orderby = empty($orderby) ? 'id' : preg_replace("#[^a-z]#i", '', $orderby);
    $orderway = isset($orderway) && $orderway == 'asc' ? 'asc' : 'desc';
    if(!empty($tag)) $where = " where tag like '%$tag%'";
    else $where = '';

    $neworderway = ($orderway == 'desc' ? 'asc' : 'desc');
    $query = "SELECT * FROM `#@__tagindex` $where ORDER BY $orderby $orderway";
    $dlist = new DataListCP();
    $tag = stripslashes($tag);
    $dlist->SetParameter("tag", $tag);
    $dlist->SetParameter("orderway", $orderway);
    $dlist->SetParameter("orderby", $orderby);
    $dlist->pageSize = 20;
    $dlist->SetTemplet(DEDEADMIN."/templets/tags_main.htm");
    $dlist->SetSource($query);
    $dlist->Display();
    exit();
}
/*
function update()
*/
else if($action == 'update')
{
    $tid = (empty($tid) ? 0 : intval($tid) );
    if(empty($tid))
    {
        ShowMsg('û��ѡ��Ҫɾ����tag!','-1');
        exit();
    }
    $query = "UPDATE `#@__tagindex` SET `count`='$count' WHERE id='$tid' ";
    $dsql->ExecuteNoneQuery($query);
    ShowMsg("�ɹ������ǩ�ĵ����Ϣ!", 'tags_main.php');
    exit();
}
/*
function delete()
*/
else if($action == 'delete')
{
    if(@is_array($ids))
    {
        $stringids = implode(',', $ids);
    }
    else if(!empty($ids))
    {
        $stringids = $ids;
    }
    else
    {
        ShowMsg('û��ѡ��Ҫɾ����tag','-1');
        exit();
    }
    $query = "DELETE FROM `#@__tagindex` WHERE id IN ($stringids)";
    if($dsql->ExecuteNoneQuery($query))
    {
        $query = "DELETE FROM `#@__taglist` WHERE tid IN ($stringids)";
        $dsql->ExecuteNoneQuery($query);
        ShowMsg("ɾ��tags[ $stringids ]�ɹ�", 'tags_main.php');
    }
    else
    {
        ShowMsg("ɾ��tags[ $stringids ]ʧ��", 'tags_main.php');
    }
    exit();
}
/*
function fetch()
*/
else if($action == 'fetch')
{
    $wheresql = '';
    $start = isset($start) && is_numeric($start) ? $start : 0;
    $where = array();
    if(isset($startaid) && is_numeric($startaid) && $startaid > 0)
    {
        $where[] = " id>$startaid ";
    }
    else
    {
        $startaid = 0;
    }
    if(isset($endaid) && is_numeric($endaid) && $endaid > 0)
    {
        $where[] = " id<$endaid ";
    }
    else
    {
        $endaid = 0;
    }
    if(!empty($where))
    {
        $wheresql = " WHERE arcrank>-1 AND ".implode(' AND ', $where);
    }
    $query = "SELECT id as aid,arcrank,typeid,keywords FROM `#@__archives` $wheresql LIMIT $start, 100";
    $dsql->SetQuery($query);
    $dsql->Execute();
    $complete = true;
    while($row = $dsql->GetArray())
    {
        $aid = $row['aid'];
        $typeid = $row['typeid'];
        $arcrank = $row['arcrank'];
        $row['keywords'] = trim($row['keywords']);
        if($row['keywords']!='' && !preg_match("#,#", $row['keywords']))
        {
            $keyarr = explode(' ', $row['keywords']);
        }
        else
        {
            $keyarr = explode(',', $row['keywords']);
        }
        foreach($keyarr as $keyword)
        {
            $keyword = trim($keyword);
            if($keyword != '' && strlen($keyword)<13 )
            {
                $keyword = addslashes($keyword);
                $row = $dsql->GetOne("SELECT id FROM `#@__tagindex` WHERE tag LIKE '$keyword'");
                if(is_array($row))
                {
                    $tid = $row['id'];
                    $query = "UPDATE `#@__tagindex` SET `total`=`total`+1 WHERE id='$tid' ";
                    $dsql->ExecuteNoneQuery($query);
                }
                else
                {
                    $query = " INSERT INTO `#@__tagindex`(`tag`,`count`,`total`,`weekcc`,`monthcc`,`weekup`,`monthup`,`addtime`) VALUES('$keyword','0','1','0','0','$timestamp','$timestamp','$timestamp');";
                    $dsql->ExecuteNoneQuery($query);
                    $tid = $dsql->GetLastID();
                }
                $query = "REPLACE INTO `#@__taglist`(`tid`,`aid`,`typeid`,`arcrank`,`tag`) VALUES ('$tid', '$aid', '$typeid','$arcrank','$keyword'); ";
                $dsql->ExecuteNoneQuery($query);
            }
        }
        $complete = FALSE;
    }
    if($complete)
    {
        ShowMsg("tags��ȡ���", 'tags_main.php');
        exit();
    }
    $start = $start + 100;
    $goto = "tags_main.php?action=fetch&startaid=$startaid&endaid=$endaid&start=$start";
    ShowMsg('������ȡtags ...', $goto, 0, 500);
    exit();
}