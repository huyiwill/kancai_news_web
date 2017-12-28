<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ��ȡ��ǰƵ�����¼���Ŀ�������б��ǩ
 *
 * @version        $Id: channelartlist.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/*>>dede>>
<name>Ƶ���ĵ�</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>��ȡ��ǰƵ�����¼���Ŀ�������б��ǩ</description>
<demo>
{dede:channelartlist row=6}
<dl>
 <dt><a href='{dede:field name='typeurl'/}'>{dede:field name='typename'/}</a></dt>
 <dd>
 {dede:arclist titlelen='42' row='10'}    <ul class='autod'> 
     <li><a href="[field:arcurl /]">[field:title /]</a></li>
      <li>([field:pubdate function="MyDate('m-d',@me)"/])</li>
    </ul>
{/dede:arclist}
</dl>
{/dede:channelartlist}
</demo>
<attributes>
    <iterm>typeid:Ƶ��ID</iterm> 
    <iterm>row:��ȡ����Ŀ����ֵ</iterm>
</attributes> 
>>dede>>*/
 
require_once(DEDEINC.'/arc.partview.class.php');

function lib_channelartlist(&$ctag,&$refObj)
{
    global $dsql,$envs,$_sys_globals;

    //���������ԡ�innertext
    $attlist = 'typeid|0,row|20,cacheid|';
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $innertext = trim($ctag->GetInnerText());
    $artlist = '';
    //��ȡ�̶��Ļ����
    $cacheid = trim($cacheid);
    if($cacheid !='') {
        $artlist = GetCacheBlock($cacheid);
        if($artlist!='') return $artlist;
    }
    
    if(empty($typeid))
    {
        $typeid = ( !empty($refObj->TypeLink->TypeInfos['id']) ?  $refObj->TypeLink->TypeInfos['id'] : 0 );
    }
    
    if($innertext=='') $innertext = GetSysTemplets('part_channelartlist.htm');
    $totalnum = $row;
    if(empty($totalnum)) $totalnum = 20;

    //������ID��������Ϣ
    $typeids = array();
    if($typeid==0 || $typeid=='top') {
        $tpsql = " reid=0 AND ispart<>2 AND ishidden<>1 AND channeltype>0 ";
    }
    else
    {
        if(!preg_match('#,#', $typeid)) {
            $tpsql = " reid='$typeid' AND ispart<>2 AND ishidden<>1 ";
        }
        else {
            $tpsql = " id IN($typeid) AND ispart<>2 AND ishidden<>1 ";
        }
    }
    $dsql->SetQuery("SELECT id,typename,typedir,isdefault,ispart,defaultname,namerule2,moresite,siteurl,sitepath 
                                            FROM `#@__arctype` WHERE $tpsql ORDER BY sortrank ASC LIMIT $totalnum");
    $dsql->Execute();
    while($row = $dsql->GetArray()) {
        $typeids[] = $row;
    }

    if(!isset($typeids[0])) return '';

    $GLOBALS['itemindex'] = 0;
    $GLOBALS['itemparity'] = 1;
    for($i=0;isset($typeids[$i]);$i++)
    {
        $GLOBALS['itemindex']++;
        $pv = new PartView($typeids[$i]['id']);
        $pv->Fields['typeurl'] = GetOneTypeUrlA($typeids[$i]);
        $pv->SetTemplet($innertext,'string');
        $artlist .= $pv->GetResult();
        $GLOBALS['itemparity'] = ($GLOBALS['itemparity']==1 ? 2 : 1);
    }
    //ע�������������Է�ֹ���������б�ʹ��
    $GLOBALS['envs']['typeid'] = $_sys_globals['typeid'];
    $GLOBALS['envs']['reid'] = '';
    if($cacheid !='') {
        WriteCacheBlock($cacheid, $artlist);
    }
    return $artlist;
}