<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * �Զ�����ǵ��ñ�ǩ
 *
 * @version        $Id: mytag.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>�Զ������</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>���ڻ�ȡ�Զ�����ǵ�����</description>
<demo>
{dede:mytag typeid='0' name=''/}
</demo>
<attributes>
    <iterm>name:������ƣ������Ǳ�������ԣ����� 2��3�ǿ�ѡ����</iterm> 
    <iterm>ismake:Ĭ���� no ��ʾ�趨�Ĵ�HTML���룬 yes ��ʾ������ǵĴ���</iterm>
    <iterm>typeid:��ʾ������Ŀ��ID��Ĭ��Ϊ 0 ����ʾ������Ŀͨ�õ���ʾ���ݣ����б���ĵ�ģ���У�typeidĬ��������б���ĵ��������Ŀ�ɣ�</iterm>
</attributes> 
>>dede>>*/
 
function lib_mytag(&$ctag, &$refObj)
{
    $attlist = "typeid|0,name|,ismake|no";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if(trim($ismake)=='') $ismake = 'no';
    $body = lib_GetMyTagT($refObj, $typeid, $name, '#@__mytag');
    //����
    if($ismake=='yes')
    {
        require_once(DEDEINC.'/arc.partview.class.php');
        $pvCopy = new PartView($typeid);
        $pvCopy->SetTemplet($body,"string");
        $body = $pvCopy->GetResult();
    }
    return $body;
}

function lib_GetMyTagT(&$refObj, $typeid,$tagname,$tablename)
{
    global $dsql;
    if($tagname=='') return '';
    if(trim($typeid)=='') $typeid=0;
    if( !empty($refObj->Fields['typeid']) && $typeid==0) $typeid = $refObj->Fields['typeid'];
    
    $typesql = $row = '';
    if($typeid > 0) $typesql = " And typeid IN(0,".GetTopids($typeid).") ";
    
    $row = $dsql->GetOne(" SELECT * FROM $tablename WHERE tagname LIKE '$tagname' $typesql ORDER BY typeid DESC ");
    if(!is_array($row)) return '';

    $nowtime = time();
    if($row['timeset']==1 
      && ($nowtime<$row['starttime'] || $nowtime>$row['endtime']) )
    {
        $body = $row['expbody'];
    }
    else
    {
        $body = $row['normbody'];
    }
    
    return $body;
}