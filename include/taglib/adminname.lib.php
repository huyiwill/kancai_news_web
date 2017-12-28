<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ������α༭����
 *
 * @version        $Id: adminname.lib.php 2 8:48 2010��7��8��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

 /**
 *  ������α༭����
 *
 * @access    public
 * @param     object  $ctag  ������ǩ
 * @param     object  $refObj  ���ö���
 * @return    string  �ɹ��󷵻ؽ�����ı�ǩ����
 */
 
 /*>>dede>>
<name>���α༭</name> 
<type>������ģ��</type> 
<for>V55,V56,V57</for>
<description>������α༭����</description>
<demo>
{dede:adminname /}	
</demo>
<attributes>
</attributes> 
>>dede>>*/

function lib_adminname(&$ctag, &$refObj)
{
    global $dsql;
    if(empty($refObj->Fields['dutyadmin']))
    {
        $dutyadmin = $GLOBALS['cfg_df_dutyadmin'];
    }
    else
    {
        $row = $dsql->GetOne("SELECT uname FROM `#@__admin` WHERE id='{$refObj->Fields['dutyadmin']}' ");
        $dutyadmin = isset($row['uname']) ? $row['uname'] : $GLOBALS['cfg_df_dutyadmin'];
    }
    return $dutyadmin;
}