<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 
 *
 * @version        $Id: arcpagelist.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
function lib_arcpagelist(&$ctag, &$refObj)
{
    global $dsql;
    $attlist = "tagid|,style|1";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);
    
    $row = $dsql->GetOne("SELECT * FROM #@__arcmulti WHERE tagid='$tagid'");
    if(is_array($row))
    {
      $ids = explode(',', $row['arcids']);
    
      $totalnum = count($ids);
      $pagestr = '<div id="page_'.$tagid.'">';
      if($row['pagesize'] < $totalnum)
      {
        $pagestr .= multipage($totalnum, 1, $row['pagesize'], $tagid);
      } else {
          $pagestr .= '��1ҳ';
      }
      $pagestr .= '</div>';
      return $pagestr;
    } else {
      $pagestr = '<div id="page_'.$tagid.'">';
      $pagestr .= 'û�м�������Ӧ��ҳ';
      $pagestr .= '</div>';
        return $pagestr;
    }
}

/**
 *  ��ҳ����
 *
 * @access    public
 * @param     string  $allItemTotal  ���м�¼
 * @param     string  $currPageNum  ��ǰҳ����
 * @param     string  $pageSize  ��ʾ����
 * @param     string  $tagid  ��ǩID
 * @return    string
 */
function multipage($allItemTotal, $currPageNum, $pageSize, $tagid='')
{
    if ($allItemTotal == 0) return "";

    //������ҳ��
    $pagesNum = ceil($allItemTotal/$pageSize);

    //��һҳ��ʾ
    $firstPage = ($currPageNum <= 1) ? $currPageNum ."</b>&lt;&lt;" : "<a href='javascript:multi(1,\"{$tagid}\")' title='��1ҳ'>1&lt;&lt;</a>";

    //���һҳ��ʾ
    $lastPage = ($currPageNum >= $pagesNum)? "&gt;&gt;". $currPageNum : "<a href='javascript:multi(". $pagesNum . ",\"{$tagid}\")' title='��". $pagesNum ."ҳ'>&gt;&gt;". $pagesNum ."</a>";

    //��һҳ��ʾ
    $prePage  = ($currPageNum <= 1) ? "��ҳ" : "<a href='javascript:multi(". ($currPageNum-1) . ",\"{$tagid}\")'  accesskey='p'  title='��һҳ'>[��һҳ]</a>";

    //��һҳ��ʾ
    $nextPage = ($currPageNum >= $pagesNum) ? "��ҳ" : "<a href='javascript:multi(". ($currPageNum+1) .",\"{$tagid}\")' title='��һҳ'>[��һҳ]</a>";

    //��ҳ��ʾ
    $listNums = "";
    for ($i=($currPageNum-4); $i<($currPageNum+9); $i++) {
        if ($i < 1 || $i > $pagesNum) continue;
        if ($i == $currPageNum) $listNums.= "<a href='javascript:void(0)' class='thislink'>".$i."</a>";
        else $listNums.= " <a href='javascript:multi(". $i .",\"{$tagid}\")' title='". $i ."'>". $i ."</a> ";
    }

    $returnUrl = $listNums;
    return $returnUrl;
}