<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
/**
 * �������������ݱ�ǩ
 *
 * @version        $Id: loop.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>����ѭ��</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>�������������ݱ�ǩ</description>
<demo>
{dede:loop table='dede_archives' sort='' row='4' if=''}
<a href='[field:arcurl/]'>[field:title/]</a>
{/dede:loop}
</demo>
<attributes>
    <iterm>table:��ѯ����</iterm> 
    <iterm>sort:����������ֶ�</iterm>
    <iterm>row:���ؽ��������</iterm>
    <iterm>if:��ѯ������</iterm>
</attributes> 
>>dede>>*/
 
require_once(DEDEINC.'/dedevote.class.php');
function lib_loop(&$ctag,&$refObj)
{
    global $dsql;
    $attlist="table|,tablename|,row|8,sort|,if|,ifcase|,orderway|desc";//(2011.7.22 ����loop��ǩorderway���� by:֯�ε���)
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($ctag->GetInnertext());
    $revalue = '';
    if(!empty($table)) $tablename = $table;

    if($tablename==''||$innertext=='') return '';
    if($if!='') $ifcase = $if;

    if($sort!='') $sort = " ORDER BY $sort $orderway ";
    if($ifcase!='') $ifcase=" WHERE $ifcase ";
    $dsql->SetQuery("SELECT * FROM $tablename $ifcase $sort LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace("field","[","]");
    $ctp->LoadSource($innertext);
    $GLOBALS['autoindex'] = 0;
    while($row = $dsql->GetArray())
    {
        $GLOBALS['autoindex']++;
        foreach($ctp->CTags as $tagid=>$ctag)
        {
                if($ctag->GetName()=='array')
                {
                        $ctp->Assign($tagid, $row);
                }
                else
                {
                    if( !empty($row[$ctag->GetName()])) $ctp->Assign($tagid,$row[$ctag->GetName()]); 
                }
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}