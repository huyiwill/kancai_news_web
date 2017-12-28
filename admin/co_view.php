<?php
/**
 * @version        $Id: co_url.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/dedecollection.class.php");
$backurl = isset($_COOKIE['ENV_GOBACK_URL']) ? $_COOKIE['ENV_GOBACK_URL'] : "co_url.php";
if(empty($action)) $action='';
if($aid=='')
{
    ShowMsg('������Ч!','-1');
    exit();
}

//�������
if($action=="save")
{
    $result = '';
    for($i=0;$i < $endid;$i++)
    {
        $result .= "{dede:field name=\\'".${"noteid_$i"}."\\'}".${"value_$i"}."{/dede:field}\r\n";
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__co_htmls` SET result='$result' WHERE aid='$aid'; ");
    ShowMsg("�ɹ�����һ����¼��",$backurl);
    exit();
}
$dsql->SetSql("SELECT * FROM `#@__co_htmls` WHERE aid='$aid'");
$dsql->Execute();
$row = $dsql->GetObject();
$isdown = $row->isdown;
$nid = $row->nid;
$url = $row->url;
$dtime = $row->dtime;
$body = $row->result;
$litpic = $row->litpic;
$fields = array();
if($isdown == 0)
{
    $co = new DedeCollection();
    $co->LoadNote($nid);
    $co->DownUrl($aid, $url, $litpic);
    $co->dsql->SetSql("SELECT * FROM `#@__co_htmls` WHERE aid='$aid'");
    $co->dsql->Execute();
    $row = $co->dsql->GetObject();
    $isdown = $row->isdown;
    $nid = $row->nid;
    $url = $row->url;
    $dtime = $row->dtime;
    $body = $row->result;
    $litpic = $row->litpic;
}
$dtp = new DedeTagParse();
$dtp->SetNameSpace("dede", "{", "}");
$dtp->LoadString($body);
include DedeInclude('templets/co_view.htm');