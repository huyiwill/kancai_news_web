<?php
if(!defined('DEDEINC'))
{
    exit("Request Error!");
}
/**
 * ͶƱ��ǩ
 *
 * @version        $Id: vote.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>ͶƱ��ǩ</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>���ڻ�ȡһ��ͶƱ��</description>
<demo>
{dede:vote id='' lineheight='22' tablewidth='100%' titlebgcolor='#EDEDE2' titlebackground='' tablebgcolor='#FFFFFF'/}
{/dede}
</demo>
<attributes>
    <iterm>id:���֣���ǰͶƱID</iterm>
    <iterm>lineheight:���߶�</iterm>
    <iterm>tablewidth:�����</iterm>
    <iterm>titlebgcolor:ͶƱ���ⱳ��ɫ</iterm>
    <iterm>titlebackground:���ⱳ��ͼ</iterm>
    <iterm>tablebg:ͶƱ��񱳾�ɫ</iterm>
</attributes>
>>dede>>*/
 
require_once(DEDEINC.'/dedevote.class.php');
function lib_vote(&$ctag,&$refObj)
{
    global $dsql;
    $attlist="id|0,lineheight|24,tablewidth|100%,titlebgcolor|#EDEDE2,titlebackgroup|,tablebg|#FFFFFF";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if(empty($id)) $id=0;
    if($id==0)
    {
        $row = $dsql->GetOne("SELECT aid FROM `#@__vote` ORDER BY aid DESC LIMIT 0,1");
        if(!isset($row['aid'])) return '';
        else $id=$row['aid'];
    }
    $vt = new DedeVote($id);
    return $vt->GetVoteForm($lineheight,$tablewidth,$titlebgcolor,$titlebackgroup,$tablebg);
}