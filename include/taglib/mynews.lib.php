<?php
/**
 * վ�����ŵ��ñ�ǩ
 *
 * @version        $Id:mynews.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
/*>>dede>>
<name>վ������</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>վ�����ŵ��ñ�ǩ</description>
<demo>
{dede:mynews row='' titlelen=''/}
</demo>
<attributes>
    <iterm>row:����վ��������</iterm> 
    <iterm>titlelen:���ű��ⳤ��</iterm>
</attributes> 
>>dede>>*/
 
function lib_mynews(&$ctag,&$refObj)
{
    global $dsql,$envs;
    //���Դ���
    $attlist="row|1,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    $innertext = trim($ctag->GetInnerText());
    if(empty($row)) $row=1;
    if(empty($titlelen)) $titlelen=30;
    if(empty($innertext)) $innertext = GetSysTemplets('mynews.htm');

    $idsql = '';
    if($envs['typeid'] > 0) $idsql = " WHERE typeid='".GetTopid($this->TypeID)."' ";
    $dsql->SetQuery("SELECT * FROM #@__mynews $idsql ORDER BY senddate DESC LIMIT 0,$row");
    $dsql->Execute();
    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field','[',']');
    $ctp->LoadSource($innertext);
    $revalue = '';
    while($row = $dsql->GetArray())
    {
        foreach($ctp->CTags as $tagid=>$ctag){
            @$ctp->Assign($tagid,$row[$ctag->GetName()]);
        }
        $revalue .= $ctp->GetResult();
    }
    return $revalue;
}