<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * Ȧ�ӵ��ñ�ǩ
 *
 * @version        $Id: group.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>Ȧ�ӱ�ǩ</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>Ȧ�ӵ��ñ�ǩ</description>
<demo>
{dede:group row='6' orderby='threads' titlelen='30'}
 <li>
  <span><img style="visibility: inherit;" title="[field:groupname/]" src="[field:icon/]" /></span>
  <span><a href="[field:url/]" title="[field:groupname/]" target="_blank">[field:groupname/]</a></span>
 </li>
{/dede:group} 
</demo>
<attributes>
    <iterm>row:��������</iterm> 
    <iterm>orderby:����˳��Ĭ������������</iterm>
    <iterm>titlelen:Ȧ��������󳤶�</iterm>
</attributes> 
>>dede>>*/
 
function lib_group(&$ctag,&$refObj)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl;
    //���Դ���
    $attlist="row|6,orderby|threads,titlelen|30";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if( !$dsql->IsTable("{$cfg_dbprefix}groups") ) return 'û��װȦ��ģ��';
    
    if(!preg("#\/$#", $cfg_cmsurl)) $cfg_group_url = $cfg_cmsurl.'/group';
    else $cfg_group_url = $cfg_cmsurl.'group';
    
    $innertext = $ctag->GetInnerText();
    if(trim($innertext)=='') $innertext = GetSysTemplets("groups.htm");
    
    $list = '';
    $dsql->SetQuery("SELECT groupimg,groupid,groupname FROM `#@__groups` WHERE ishidden=0 ORDER BY $orderby DESC LIMIT 0,{$row}");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    while($rs = $dsql->GetArray())
    {
        $ctp->LoadSource($innertext);
        $rs['groupname'] = cn_substr($rs['groupname'], $titlelen);
        $rs['url'] = $cfg_group_url."/group.php?id={$rs['groupid']}";
        $rs['icon']  = $rs['groupimg'];
        foreach($ctp->CTags as $tagid=>$ctag)
        {
            if( !empty($rs[strtolower($ctag->GetName())]) ) {
                $ctp->Assign($tagid,$rs[$ctag->GetName()]);
            }
          }
          $list .= $ctp->GetResult();
    }
    return $list;
}