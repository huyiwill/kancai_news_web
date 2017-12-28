<?php
/**
 * @version        $Id: story_add.php 1 9:02 2010��9��25��Z ��ɫ���� $
 * @package        DedeCMS.Module.Book
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(dirname(__FILE__). "/config.php");
CheckPurview('story_Catalog');
if(!isset($action)) $action = '';

$keywords = $writer = '';
//��ȡ������Ŀ
$dsql->SetQuery("SELECT id,classname,pid,rank,booktype FROM #@__story_catalog ORDER BY rank ASC");
$dsql->Execute();
$ranks = Array();
$btypes = Array();
$stypes = Array();
$booktypes = Array();
while($row = $dsql->GetArray())
{
    if($row['pid']==0)
    {
        $btypes[$row['id']] = $row['classname'];
    }
    else
    {
        $stypes[$row['pid']][$row['id']] = $row['classname'];
    }
    $ranks[$row['id']] = $row['rank'];
    if($row['booktype']=='0')
    {
        $booktypes[$row['id']] = 'С˵';
    }
    else
    {
        $booktypes[$row['id']] = '����';
    }
}
$lastid = $row['id'];
$msg = '';
require_once(dirname(__FILE__). "/templets/story_add.htm");
