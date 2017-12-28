<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * Ȧ��������ñ�ǩ
 *
 * @version        $Id: groupthread.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>Ȧ������</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>Ȧ��������ñ�ǩ</description>
<demo>
{dede:groupthread gid='0' orderby='dateline' orderway='desc' row='12' titlelen='30'}
 <li>
  <a href='[field:groupurl/]' target="_blank">[[field:groupname function="cn_substr(@me,10)"/]]</a>
  <a href="[field:url/]" title="[field:subject/]" target="_blank">[field:subject/]</a>([field:lastpost function="GetDateMk('@me')"/])
 </li>
{/dede:groupthread} 
</demo>
<attributes>
    <iterm>gid:Ȧ��id��Ĭ��Ϊȫ��</iterm> 
    <iterm>orderby:����Ĭ��Ϊʱ��</iterm>
    <iterm>orderway:������Ĭ��������������ǰ</iterm>
    <iterm>row:��¼��</iterm>
    <iterm>titlelen:�������(subject)��󳤶�</iterm>
</attributes> 
>>dede>>*/
 
function lib_groupthread(&$ctag,&$refObj)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl;
    //���Դ���
    $attlist="gid|0,orderby|dateline,orderway|desc,row|12,titlelen|30";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    
    if( !$dsql->IsTable("{$cfg_dbprefix}groups") ) return 'û��װȦ��ģ��';

    if(!preg_match("#\/$#", $cfg_cmsurl)) $cfg_group_url = $cfg_cmsurl."/group";
    else $cfg_group_url = $cfg_cmsurl."group";
    
    $innertext = $ctag->GetInnerText();
    if(trim($innertext)=='') $innertext = GetSysTemplets('groupthreads.htm');
    
    $WhereSql = " WHERE t.closed=0 ";
    $orderby = 't.'.$orderby;
    if($gid > 0) $WhereSql .= " AND t.gid='$gid' ";
    
    $query = "SELECT t.subject,t.gid,t.tid,t.lastpost,g.groupname FROM `#@__group_threads` t 
             LEFT JOIN `#@__groups` g ON g.groupid=t.gid
             $WhereSql ORDER BY $orderby $orderway LIMIT 0,{$row}";
    
    $dsql->SetQuery($query);
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');
    if(!isset($list)) $list = '';
    while($rs = $dsql->GetArray())
    {
        $ctp->LoadSource($innertext);
        $rs['subject'] = cn_substr($rs['subject'], $titlelen);
        $rs['url'] = $cfg_group_url."/viewthread.php?id={$rs['gid']}&tid={$rs['tid']}";
        $rs['groupurl'] = $cfg_group_url."/group.php?id={$rs['gid']}";
        foreach($ctp->CTags as $tagid=>$ctag) {
            if(!empty($rs[strtolower($ctag->GetName())]))
            {
              $ctp->Assign($tagid, $rs[$ctag->GetName()]); 
            }
          }
          $list .= $ctp->GetResult();
    }
    return $list;
}