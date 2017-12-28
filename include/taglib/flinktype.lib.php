<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
require_once(DEDEINC."/taglib/flink.lib.php");
/**
 * ��������
 *
 * @version        $Id: flinktype.lib.php 1 15:57 2011��2��18��Z niap $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2011, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/*>>dede>>
<name>������������</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>���ڻ�ȡ������������</description>
<demo>
{dede:flink row='24'/}
</demo>
<attributes>
    <iterm>row:������������</iterm>
    <iterm>titlelen:�������ֵĳ���</iterm>
</attributes> 
>>dede>>*/
 
function lib_flinktype(&$ctag,&$refObj)
{
    global $dsql;
    $attlist="row|24,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $totalrow = $row;
    $revalue = '';
  
    $equery = "SELECT * FROM #@__flinktype order by id asc limit 0,$totalrow";

    if(trim($ctag->GetInnerText())=='') $innertext = "<li>[field:typename /]</li>";
    else $innertext = $ctag->GetInnerText();
	if(!isset($type)) $type = '';
    $dtp = new DedeTagParse();
    $dtp->SetNameSpace("dede","{","}");
    $dtp->LoadString($innertext);
    
    $dsql->SetQuery($equery);
    $dsql->Execute();
    $rs = '';
    $row = array();
    while($dbrow=$dsql->GetObject())
    {
        $row[] = $dbrow;
    }
	$dedecms = false;
	$dedecms->id = 999;
	$dedecms->typename = '֯����';
	if($type == 'dedecms') $row[] = $dedecms;
	
    foreach ($row as $key => $value) {
        if (is_array($dtp->CTags))
        {
            $GLOBALS['envs']['flinkid'] = $value->id;
            foreach($dtp->CTags as $tagid=>$ctag)
            {
                $tagname = $ctag->GetName();
                if($tagname=="flink") $dtp->Assign($tagid, lib_flink($ctag, $refObj));
            }
        }
        $rs = $dtp->GetResult();
    	$rs = preg_replace("/\[field:id([\/\s]{0,})\]/isU", $value->id, $rs);
        $rs = preg_replace("/\[field:typename([\/\s]{0,})\]/isU", $value->typename, $rs);
        $revalue .= $rs;
    }
    
    return $revalue;
}