<?php
/**
 *
 * �Զ����ǩjs���÷�ʽ
 *
 * @version        $Id: mytag_js.php 1 20:55 2010��7��8��Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/../include/common.inc.php');
require_once(DEDEINC.'/arc.partview.class.php');

if(isset($arcID)) $aid = $arcID;
$arcID = $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
if($aid==0) die(" document.write('Request Error!'); ");

$cacheFile = DEDEDATA.'/cache/mytag-'.$aid.'.htm';
if( isset($nocache) || !file_exists($cacheFile) || time() - filemtime($cacheFile) > $cfg_puccache_time )
{
    $pv = new PartView();
    $row = $pv->dsql->GetOne(" SELECT * FROM `#@__mytag` WHERE aid='$aid' ");
    if(!is_array($row))
    {
        $myvalues = "<!--\r\ndocument.write('Not found input!');\r\n-->";
    }
    else
    {
        $tagbody = '';
        if($row['timeset']==0)
        {
            $tagbody = $row['normbody'];
        }
        else
        {
            $ntime = time();
            if($ntime>$row['endtime'] || $ntime < $row['starttime']) {
                $tagbody = $row['expbody'];
            }
            else {
                $tagbody = $row['normbody'];
            }
        }
        $pv->SetTemplet($tagbody, 'string');
        $myvalues  = $pv->GetResult();
        $myvalues = str_replace('"','\"',$myvalues);
        $myvalues = str_replace("\r","\\r",$myvalues);
        $myvalues = str_replace("\n","\\n",$myvalues);
        $myvalues =  "<!--\r\ndocument.write(\"{$myvalues}\");\r\n-->\r\n";
        file_put_contents($cacheFile, $myvalues);
        /* ʹ�� file_put_contents�滻���д������ִ��Ч��
        $fp = fopen($cacheFile, 'w');
        fwrite($fp, $myvalues);
        fclose($fp);
        */
    }
}
include $cacheFile;