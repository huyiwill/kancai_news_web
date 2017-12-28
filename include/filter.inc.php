<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ���˺��Ĵ����ļ�
 *
 * @version        $Id: filter.inc.php 1 15:59 2010��7��5��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ���˲��������
 *
 * @access    public
 * @param     string  $fk ���˼�
 * @param     string  $svar ����ֵ
 * @return    string
 */
$magic_quotes_gpc = ini_get('magic_quotes_gpc');
function _FilterAll($fk, &$svar)
{
    global $cfg_notallowstr,$cfg_replacestr;
    if( is_array($svar) )
    {
        foreach($svar as $_k => $_v)
        {
            $svar[$_k] = _FilterAll($fk,$_v);
        }
    }
    else
    {
        if($cfg_notallowstr!='' && preg_match("#".$cfg_notallowstr."#i", $svar))
        {
            ShowMsg(" $fk has not allow words!",'-1');
            exit();
        }
        if($cfg_replacestr!='')
        {
            $svar = preg_replace('/'.$cfg_replacestr.'/i', "***", $svar);
        }
    }
    if (!$magic_quotes_gpc) {
        $svar = addslashes($svar);
    }
    return $svar;
}

/* ��_GET,_POST,_COOKIE���й��� */
foreach(Array('_GET','_POST','_COOKIE') as $_request)
{
    foreach($$_request as $_k => $_v)
    {
        ${$_k} = _FilterAll($_k,$_v);
    }
}