<?php
/**
 * ��ҳ�ĵ���ͬ��ʶ���ñ�ǩ
 *
 * @version        $Id: likepage.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>��ҳ�ĵ���ͬ��ʶ���ñ�ǩ</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>������ͬ��ʶ��ҳ�ĵ�</description>
<demo>
{dede:likepage likeid='' row=''/}
</demo>
<attributes>
    <iterm>row:��������</iterm> 
    <iterm>likeid:��ʶ��</iterm>
</attributes> 
>>dede>>*/
 
if(!defined('DEDEINC')) exit('Request Error!');
require_once(dirname(__FILE__).'/likesgpage.lib.php');

function lib_likepage(&$ctag,&$refObj)
{
    return lib_likesgpage($ctag, $refObj);
}
