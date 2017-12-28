<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ������
 *
 * @version        $Id: myad.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>����ǩ</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>��ȡ������</description>
<demo>
{dede:myad name=''/}
</demo>
<attributes>
    <iterm>typeid:Ͷ�ŷ�Χ,0Ϊȫվ</iterm> 
    <iterm>name:����ʶ</iterm>
</attributes> 
>>dede>>*/
 
require_once(DEDEINC.'/taglib/mytag.lib.php');

function lib_myad(&$ctag, &$refObj)
{
    $attlist = "typeid|0,name|";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $body = lib_GetMyTagT($refObj, $typeid, $name, '#@__myad');
    
    return $body;
}