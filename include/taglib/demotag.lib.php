<?php
if(!defined('DEDEINC')){
    exit("Request Error!");
}
/**
 * �����һ����ʾ��ǩ
 *
 * @version        $Id: demotag.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>��ʾ��ǩ</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>�����һ����ʾ��ǩ</description>
<demo>
{dede:demotag /}
</demo>
<attributes>
</attributes> 
>>dede>>*/
 
function lib_demotag(&$ctag,&$refObj)
{
    global $dsql,$envs;
    
    //���Դ���
    $attlist="row|12,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    $revalue = '';
    
    //�����д�Ĵ��룬������echo֮���﷨�������շ���ֵ����$revalue
    //------------------------------------------------------
    
    $revalue = 'Hello Word!';
    
    //------------------------------------------------------
    return $revalue;
}