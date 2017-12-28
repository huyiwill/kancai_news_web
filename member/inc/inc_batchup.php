<?php   if(!defined('DEDEMEMBER')) exit("dedecms");
/**
 * �ĵ�����������
 * 
 * @version        $Id: inc_batchup.php 1 13:52 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(DEDEINC."/channelunit.func.php");

/**
 *  ɾ���ĵ�
 *
 * @access    public
 * @param     int  $aid  �ĵ�ID
 * @return    bool
 */
function DelArc($aid)
{
    global $dsql,$cfg_cookie_encode,$cfg_ml,$cfg_upload_switch,$cfg_medias_dir;
    $aid = intval($aid);

    //��ȡ�ĵ���Ϣ
    $arctitle = '';
    $arcurl = '';

    $arcQuery = "SELECT arc.*,ch.addtable,tp.typedir,tp.typename,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,ch.nid
          FROM `#@__archives` arc
          LEFT JOIN `#@__arctype` tp ON tp.id=arc.typeid
          LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel
        WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($arcQuery);
    if(!is_array($arcRow))
    {
        return false;
    }

    //ɾ�����ݿ������
    $dsql->ExecuteNoneQuery(" DELETE FROM `#@__arctiny` WHERE id='$aid' ");
    if($arcRow['addtable']!='')
    {
        //�ж�ɾ�����¸��������Ƿ�����
        if($cfg_upload_switch == 'Y')
        {
            //�ж��������ԣ�
            switch($arcRow['nid'])
            {
                case "image":
                    $nid = "imgurls";
                    break;
                case "article":
                    $nid = "body";
                    break;
                case "soft":
                    $nid = "softlinks";
                    break;
                case "shop":
                    $nid = "body";
                    break;
                default:
                    $nid = "";
                    break;
            }
            if($nid !="")
            {
                $row = $dsql->GetOne("SELECT $nid FROM ".$arcRow['addtable']." WHERE aid = '$aid'");
                $licp = $dsql->GetOne("SELECT litpic FROM `#@__archives` WHERE id = '$aid'");
                if($licp['litpic'] != "")
                {
                    $litpic = DEDEROOT.$licp['litpic'];
                    if(file_exists($litpic) && !is_dir($litpic))
                    {
                        @unlink($litpic);
                    }
                }
                $tmpname = '/(\\'.$cfg_medias_dir.'.+?)(\"| )/';

                //ȡ�����¸�����
                preg_match_all("$tmpname", $row["$nid"], $delname);

                //�Ƴ��ظ�������
                $delname = array_unique($delname['1']);
                foreach ($delname as $var)
                {
                    $dsql->ExecuteNoneQuery("DELETE FROM `#@__uploads` WHERE url='$var' AND mid = '$cfg_ml->M_ID'");
                    $upname = DEDEROOT.$var;
                    if(file_exists($upname) && !is_dir($upname)) @unlink($upname);
                }
            }
        }
        $dsql->ExecuteNoneQuery("DELETE FROM `".$arcRow['addtable']."` where aid='$aid' ");
    }
    $dsql->ExecuteNoneQuery(" DELETE FROM `#@__archives` where id='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__taglist ` where aid='$aid'");

    //ɾ��HTML
    if($arcRow['ismake']==-1||$arcRow['arcrank']!=0 ||$arcRow['typeid']==0||$arcRow['money']>0)
    {
        return TRUE;
    }
    $arcurl = GetFileUrl($arcRow['id'],$arcRow['typeid'],$arcRow['senddate'],$arcRow['title'],$arcRow['ismake'],
    $arcRow['arcrank'],$arcRow['namerule'],$arcRow['typedir'],$arcRow['money'],$arcRow['filename']);
    if(!preg_match("#\?#", $arcurl))
    {
        $htmlfile = GetTruePath().str_replace($GLOBALS['cfg_basehost'], '', $arcurl);
        if(file_exists($htmlfile) && !is_dir($htmlfile))
        {
            @unlink($htmlfile);
            $arcurls = explode(".", $htmlfile);
            $sname = $arcurls[count($arcurls)-1];
            $fname = preg_replace("#(\.$sname)$#", "", $htmlfile);
            for($i=2; $i<=100; $i++)
            {
                $htmlfile = $fname."_$i".".".$sname;
                if(file_exists($htmlfile) && !is_dir($htmlfile)) @unlink($htmlfile);
                else break;
            }
        }
    }

    //ɾ���ı��ļ�
    $filenameh = DEDEDATA."/textdata/".(ceil($aid/5000))."/{$aid}-".substr(md5($cfg_cookie_encode),0,16).".txt";
    if(is_file($filename)) @unlink($filename);
    return TRUE;
}

/**
 *  ɾ��������������ģ�͵�����
 *
 * @access    public
 * @param     int  $aid  �ĵ�ID
 * @return    string
 */
function DelArcSg($aid)
{
    global $dsql,$cfg_cookie_encode,$cfg_ml,$cfg_upload_switch,$cfg_medias_dir;
    $aid = intval($aid);

    //��ȡ�ĵ���Ϣ
    $arctitle = '';
    $arcurl = '';

    $arcQuery = "Select arc.id,arc.typeid,arc.senddate,arc.arcrank,ch.addtable,ch.nid,
        tp.typedir,tp.typename,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
          from `#@__arctiny` arc
          left join `#@__arctype` tp on tp.id=arc.typeid
          left join `#@__channeltype` ch on ch.id=arc.channel
        where arc.id='$aid' ";
    $arcRow = $dsql->GetOne($arcQuery);

    if(!is_array($arcRow))
    {
        return FALSE;
    }

    //ɾ�����ݿ������
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` where id='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `".$arcRow['addtable']."` where aid='$aid' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__member_stow` where aid='$aid'");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__taglist ` where aid='$aid'");

    //ɾ��HTML
    if($arcRow['arcrank']!=0 ||$arcRow['typeid']==0)
    {
        return TRUE;
    }
    $arcurl = GetFileUrl($arcRow['id'],$arcRow['typeid'],$arcRow['senddate'],'',1,
              $arcRow['arcrank'],$arcRow['namerule'],$arcRow['typedir'],0,'');
    if(!preg_match("#\?#", $arcurl))
    {
        $htmlfile = GetTruePath().str_replace($GLOBALS['cfg_basehost'],'',$arcurl);
        if(file_exists($htmlfile) && !is_dir($htmlfile))
        {
             @unlink($htmlfile);
             $arcurls = explode(".", $htmlfile);
             $sname = $arcurls[count($arcurls)-1];
             $fname = preg_replace("#(\.$sname)$#", "", $htmlfile);
             for($i=2;$i<=100;$i++)
             {
                   $htmlfile = $fname."_$i".".".$sname;
                   if(file_exists($htmlfile) && !is_dir($htmlfile)) @unlink($htmlfile);
                   else break;
             }
        }
    }
    //ɾ���ı��ļ�
    $filenameh = DEDEDATA."/textdata/".(ceil($aid/5000))."/{$aid}-".substr(md5($cfg_cookie_encode),0,16).".txt";
    return TRUE;
}

/**
 *  ��ȡ��ʵ·��
 *
 * @return    string
 */
function GetTruePath()
{
    $truepath = $GLOBALS["cfg_basedir"];
    return $truepath;
}