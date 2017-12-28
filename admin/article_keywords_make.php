<?php
/**
 * �ĵ��ؼ�������
 *
 * @version        $Id: article_keywords_make.php 1 8:26 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
@ob_start();
@set_time_limit(3600);
require_once(dirname(__FILE__).'/config.php');
CheckPurview('sys_Keyword');
if(empty($dopost)) $dopost = '';

//�����Ѵ��ڵĹؼ��֣�������Ĭ�ϵ�����ģ�ͣ�
if($dopost=='analyse')
{
    echo "���ڶ�ȡ�ؼ������ݿ�...<br/>\r\n";
    flush();
    $ws = $wserr = $wsnew = "";
    $dsql->SetQuery("SELECT * FROM `#@__keywords`");
    $dsql->Execute();
    while($row = $dsql->GetObject())
    {
        if($row->sta==1) $ws[$row->keyword] = 1;
        else $wserr[$row->keyword] = 1;
    }
    echo "��ɹؼ������ݿ�����룡<br/>\r\n";
    flush();
    echo "��ȡ�������ݿ⣬���Խ��õĹؼ��ֺ����ֽ��д���...<br/>\r\n";
    flush();
    $dsql->SetQuery("SELECT id,keywords FROM `#@__archives`");
    $dsql->Execute();
    while($row = $dsql->GetObject())
    {
        $keywords = explode(',',trim($row->keywords));
        $nerr = false;
        $mykey = '';
        if(is_array($keywords))
        {
            foreach($keywords as $v)
            {
                $v = trim($v);
                if($v=='')
                {
                    continue;
                }
                if(isset($ws[$v]))
                {
                    $mykey .= $v." ";
                }
                else if(isset($wsnew[$v]))
                {
                    $mykey .= $v.' ';
                    $wsnew[$v]++;
                }
                else if(isset($wserr[$v]))
                {
                    $nerr = true;
                }
                else
                {
                    $mykey .= $v." ";
                    $wsnew[$v] = 1;
                }
            }
        }
    }
    echo "��ɵ������ݿ�Ĵ���<br/>\r\n";
    flush();
    if(is_array($wsnew))
    {
        echo "�Թؼ��ֽ�������...<br/>\r\n";
        flush();
        arsort($wsnew);
        echo "�ѹؼ��ֱ��浽���ݿ�...<br/>\r\n";
        flush();
        foreach($wsnew as $k=>$v)
        {
            if(strlen($k)>20)
            {
                continue;
            }
            $dsql->SetQuery("INSERT INTO `#@__keywords`(keyword,rank,sta,rpurl) VALUES('".addslashes($k)."','$v','1','')");
            $dsql->Execute();
        }
        echo "��ɹؼ��ֵĵ��룡<br/>\r\n";
        flush();
        sleep(1);
    }
    else
    {
        echo "û�����κ��µĹؼ��֣�<br/>\r\n";
        flush();
        sleep(1);
    }
    ShowMsg('������в���������ת���ؼ����б�ҳ��','article_keywords_main.php');
    exit();
}
//�Զ���ȡ�ؼ��֣�������Ĭ�ϵ�����ģ�ͣ�
else if($dopost=='fetch')
{
    require_once(DEDEINC."/splitword.class.php");
    if(empty($startdd))
    {
        $startdd = 0;
    }
    if(empty($pagesize))
    {
        $pagesize = 20;
    }
    if(empty($totalnum))
    {
        $totalnum = 0;
    }

    //ͳ�Ƽ�¼����
    if($totalnum==0)
    {
        $row = $dsql->GetOne("SELECT COUNT(*) AS dd FROM `#@__archives` WHERE channel='1' ");
        $totalnum = $row['dd'];
    }

    //��ȡ��¼���������ؼ���
    if($totalnum > $startdd+$pagesize)
    {
        $limitSql = " LIMIT $startdd,$pagesize";
    }
    else if(($totalnum-$startdd)>0)
    {
        $limitSql = " LIMIT $startdd,".($totalnum - $startdd);
    }
    else
    {
        $limitSql = '';
    }
    $tjnum = $startdd;
    if($limitSql!='')
    {
        $fquery = "SELECT arc.id,arc.title,arc.keywords,addon.body FROM `#@__archives` arc
              LEFT JOIN `#@__addonarticle` addon ON addon.aid=arc.id WHERE arc.channel='1' $limitSql ";
        $dsql->SetQuery($fquery);
        $dsql->Execute();
        $sp = new SplitWord($cfg_soft_lang , $cfg_soft_lang );
        while($row=$dsql->GetObject())
        {
            if($row->keywords!='')
            {
                continue;
            }
            $tjnum++;
            $id = $row->id;
            $keywords = "";
            
            $sp->SetSource($row->title, $cfg_soft_lang , $cfg_soft_lang );
            $sp->SetResultType(2);
            $sp->StartAnalysis(TRUE);

            $titleindexs = $sp->GetFinallyIndex();
            
            $sp->SetSource(Html2Text($row->body), $cfg_soft_lang , $cfg_soft_lang );
            $sp->SetResultType(2);
            $sp->StartAnalysis(TRUE);
            $allindexs = $sp->GetFinallyIndex();
            if(is_array($allindexs) && is_array($titleindexs))
            {
                foreach($titleindexs as $k => $v)
                {
                    if(strlen($keywords)>=30)
                    {
                        break;
                    }
                    else
                    {
                        if(strlen($k) <= 2) continue;
                        $keywords .= $k.",";
                    }
                }
                foreach($allindexs as $k => $v)
                {
                    if(strlen($keywords)>=30)
                    {
                        break;
                    }
                    else if(!in_array($k,$titleindexs))
                    {
                        if(strlen($k) <= 2) continue;
                        $keywords .= $k.",";
                    }
                }
            }
            $keywords = addslashes($keywords);
            if($keywords=='')
            {
                $keywords = ',';
            }
            $dsql->ExecuteNoneQuery("UPDATE `#@__archives` SET keywords='$keywords' WHERE id='$id'");
        }
        unset($sp);
    }//end if limit

    //������ʾ��Ϣ
    if($totalnum>0) $tjlen = ceil( ($tjnum/$totalnum) * 100 );
    else $tjlen=100;

    $dvlen = $tjlen * 2;
    $tjsta = "<div style='width:200;height:15;border:1px solid #898989;text-align:left'><div style='width:$dvlen;height:15;background-color:#829D83'></div></div>";
    $tjsta .= "<br/>��ɴ����ĵ������ģ�$tjlen %��λ�ã�{$startdd}������ִ������...";

    if($tjnum < $totalnum)
    {
        $nurl = "article_keywords_make.php?dopost=fetch&totalnum=$totalnum&startdd=".($startdd+$pagesize)."&pagesize=$pagesize";
        ShowMsg($tjsta,$nurl,0,500);
    }
    else
    {
        ShowMsg("�����������","javascript:;");
    }
    exit();
}
include DedeInclude('templets/article_keywords_make.htm');
