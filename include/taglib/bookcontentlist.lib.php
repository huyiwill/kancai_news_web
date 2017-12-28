<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ����ͼ���������ݵ���
 *
 * @version        $Id: bookcontentlist.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>��������</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>����ͼ���������ݵ���</description>
<demo>
{dede:bookcontentlist row='12' booktype='-1' orderby='lastpost' author='' keyword=''}
<table width="100%" border="0" cellspacing="2" cellpadding="2">
<tr>
<td width='40%'>
[[field:cataloglink/]] [field:booklink/]</td>
<td width='40%'>[field:contentlink/]</td>
<td width='20%'>[field:lastpost function="GetDateMk(@me)"/]</td>
</tr>
</table>
{/dede:bookcontentlist} 
</demo>
<attributes>
    <iterm>row:���ü�¼����</iterm> 
    <iterm>booktype:ͼ�����ͣ�0 ͼ�顢1 ������Ĭ��ȫ��</iterm>
    <iterm>orderby:�������ͣ�������������Ϊ commend ��ʾ�Ƽ�ͼ��</iterm>
    <iterm>author:����</iterm>
    <iterm>keyword:�ؼ���</iterm>
</attributes> 
>>dede>>*/
 
require_once(DEDEINC.'/taglib/booklist.lib.php');

function lib_bookcontentlist(&$ctag, &$refObj)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl;

    $attlist="row|12,booktype|-1,titlelen|30,orderby|lastpost,author|,keyword|";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if( !$dsql->IsTable("{$cfg_dbprefix}story_books") ) return 'û��װ����ģ��';
    
    return lib_booklist($ctag, $refObj, 1);
    
}