<?php if(!defined('DEDEINC')) exit('Request Error!');
/**
 * �ʴ���ñ�ǩ
 *
 * @version        $Id: ask.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
function lib_ask(&$ctag,&$refObj)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl,$cfg_ask_directory,$cfg_ask_isdomain,$cfg_ask_domain;
    //���Դ���
    $attlist="row|6,qtype|new,tid|0,titlelen|24";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    
    if( !$dsql->IsTable("{$cfg_dbprefix}ask") ) return 'û��װ�ʴ�ģ��';
    
    //���ö�������
    if($cfg_ask_isdomain == 'Y')
    {
        $weburl = $cfg_ask_domain.'/';  
    }else{
        $weburl = $cfg_ask_directory.'/'; 
    }
    
    $innertext = $ctag->GetInnerText();
    if(trim($innertext)=='') $innertext = GetSysTemplets("asks.htm");
    
    $qtypeQuery = '';
    if($tid > 0) $tid = " (tid=$tid Or tid2='$tid') AND ";
    else $tid = '';
    //�Ƽ�����
    if($qtype=='commend') $qtypeQuery = " $tid digest=1 ORDER BY dateline DESC ";
    //�½������
    else if($qtype=='ok') $qtypeQuery = " $tid status=1 ORDER BY solvetime DESC ";
    //�߷�����
    else if($qtype=='high') $qtypeQuery = " $tid status=0 ORDER BY reward DESC ";
    //������
    else $qtypeQuery = " $tid status=0 ORDER BY disorder DESC, dateline DESC ";

    $ctp = new DedeTagParse();
    $ctp->SetNameSpace('field', '[', ']');

    $solvingask = '';
    $query = "SELECT id, tid, tidname, tid2, tid2name, title,dateline FROM `#@__ask` WHERE $qtypeQuery  limit 0, $row";
    $dsql->Execute('me',$query);
    
    while($rs = $dsql->GetArray('me'))
    {
        $rs['title'] = cn_substr($rs['title'], $titlelen);
        $ctp->LoadSource($innertext);
        if($rs['tid2name'] != '')
        {
            $rs['tid'] = $rs['tid2'];
            $rs['tidname'] = $rs['tid2name'];
        }
        $rs['url'] = $weburl."?ct=question&askaid=".$rs['id'];
        foreach($ctp->CTags as $tagid=>$ctag) {
            if(!empty($rs[strtolower($ctag->GetName())])) {
                $ctp->Assign($tagid,$rs[$ctag->GetName()]);
            }
        }
        $solvingask .= $ctp->GetResult();
    }
    return $solvingask;
}