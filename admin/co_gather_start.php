<?php
/**
 * �ɼ�ָ���ڵ�
 *
 * @version        $Id: co_gather_start.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedecollection.class.php");
if(!empty($nid))
{
    $ntitle = '�ɼ�ָ���ڵ㣺';
    $nid = intval($nid);
    $co = new DedeCollection();
    $co->LoadNote($nid);
    $row = $dsql->GetOne("SELECT COUNT(aid) AS dd FROM `#@__co_htmls` WHERE nid='$nid'; ");
    if($row['dd']==0)
    {
        $unum = "û�м�¼�����û�вɼ�������ڵ㣡";
    }
    else
    {
        $unum = "���� {$row['dd']} ����ʷ������ַ��<a href='javascript:SubmitNew();'>[<u>����������ַ�����ɼ�</u>]</a>";
    }
} else {
    $nid = "";
    $row['dd'] = "";
    $ntitle = '���ʽ�ɼ���';
    $unum = "ûָ���ɼ��ڵ㣬��ʹ�ü�������ݲɼ�ģʽ��";
}
include DedeInclude('templets/co_gather_start.htm');