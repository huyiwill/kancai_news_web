<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ����ͼ�����
 *
 * @version        $Id: booklist.lib.php 1 9:29 2010��7��6��Z tianya $
 * @package        DedeCMS.Taglib
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ͼ���б����
 *
 * @access    public
 * @param     object  $ctag  ������ǩ
 * @param     object  $refObj  ���ö���
 * @param     int  $getcontent  �Ƿ��������
 * @return    string
 */
 
/*>>dede>>
<name>����ͼ��</name>
<type>ȫ�ֱ��</type>
<for>V55,V56,V57</for>
<description>����ͼ�����</description>
<demo>
{dede:booklist row='12' booktype='-1' orderby='lastpost' author='' keyword=''}
<a href='[field:bookurl /]'>[field:bookname /]</a><br />
{/dede:booklist}
</demo>
<attributes>
    <iterm>row:���ü�¼����</iterm> 
    <iterm>booktype:ͼ�����ͣ�0 ͼ�顢1 ������Ĭ��ȫ��</iterm>
    <iterm>orderby:�������ͣ�������������Ϊ commend ��ʾ�Ƽ�ͼ��</iterm>
    <iterm>author:����</iterm>
    <iterm>keyword:�ؼ���</iterm>
</attributes> 
>>dede>>*/

function lib_booklist(&$ctag, &$refObj, $getcontent=0)
{
    global $dsql, $envs, $cfg_dbprefix, $cfg_cmsurl;
    
    //���Դ���
    $attlist="row|12,booktype|-1,titlelen|30,orderby|lastpost,author|,keyword|";
    FillAttsDefault($ctag->CAttribute->Items,$attlist);
    extract($ctag->CAttribute->Items, EXTR_SKIP);

    if( !$dsql->IsTable("{$cfg_dbprefix}story_books") ) return 'û��װ����ģ��';
    $addquery = '';
    
    if(empty($innertext))
    {
        if($getcontent==0) $innertext = GetSysTemplets('booklist.htm');
        else $innertext = GetSysTemplets('bookcontentlist.htm');
    }
    
    //ͼ������
    if($booktype!=-1) {
        $addquery .= " AND b.booktype='{$booktype}' ";
    }
    
    //�Ƽ�
    if($orderby=='commend')
    {
        $addquery .= " AND b.iscommend=1 ";
        $orderby = 'lastpost';
    }

    //��������
    if(!empty($author))
    {
        $addquery .= " AND b.author LIKE '$author' ";
    }
    
    //�ؼ�������
    if(!empty($keyword))
    {
        $keywords = explode(',', $keyword);
        $keywords = array_unique($keywords);
        if(count($keywords) > 0) {
            $addquery .= " AND (";
        }
        foreach($keywords as $v) {
            $addquery .= " CONCAT(b.author,b.bookname,b.keywords) LIKE '%$v%' OR";
        }
        $addquery = preg_replace("# OR$#", "", $addquery);
        $addquery .= ")";
    }
    
    $clist = '';
    $query = "SELECT b.id,b.catid,b.ischeck,b.booktype,b.iscommend,b.click,b.bookname,b.lastpost,
         b.author,b.mid,b.litpic,b.pubdate,b.weekcc,b.monthcc,b.description,c.classname,c.classname as typename,c.booktype as catalogtype
         FROM `#@__story_books` b LEFT JOIN `#@__story_catalog` c ON c.id = b.catid
         WHERE b.postnum>0 AND b.ischeck>0 $addquery ORDER BY b.{$orderby} DESC LIMIT 0, $row";
    $dsql->SetQuery($query);
    $dsql->Execute();

    $ndtp = new DedeTagParse();
    $ndtp->SetNameSpace('field', '[', ']');
    $GLOBALS['autoindex'] = 0;
    while($row = $dsql->GetArray())
    {
        $GLOBALS['autoindex']++;
        $row['title'] = $row['bookname'];
        $ndtp->LoadString($innertext);

        //���ͼ�����µ�һ�������½�
        $row['contenttitle'] = '';
        $row['contentid'] = '';
        if($getcontent==1) {
            $nrow = $dsql->GetOne("SELECT id,title,chapterid FROM `#@__story_content` WHERE bookid='{$row['id']}' order by id desc ");
            $row['contenttitle'] = $nrow['title'];
            $row['contentid'] = $nrow['id'];
        }
        if($row['booktype']==1) {
            $row['contenturl'] = $cfg_cmspath.'/book/show-photo.php?id='.$row['contentid'];
        }
        else {
            $row['contenturl'] = $cfg_cmspath.'/book/story.php?id='.$row['contentid'];
        }

        //��̬��ַ
        $row['dmbookurl'] = $cfg_cmspath.'/book/book.php?id='.$row['id'];

        //��̬��ַ
        $row['bookurl'] = $row['url'] = GetBookUrl($row['id'],$row['bookname']);
        $row['catalogurl'] = $cfg_cmspath.'/book/list.php?id='.$row['catid'];
        $row['cataloglink'] = "<a href='{$row['catalogurl']}'>{$row['classname']}</a>";
        $row['booklink'] = "<a href='{$row['bookurl']}'>{$row['bookname']}</a>";
        $row['contentlink'] = "<a href='{$row['contenturl']}'>{$row['contenttitle']}</a>";
        $row['imglink'] = "<a href='{$row['bookurl']}'><img src='{$row['litpic']}' width='$imgwidth' height='$imgheight' border='0' /></a>";
        
        if($row['ischeck']==2) $row['ischeck']='���������';
        else $row['ischeck']='������...';

        if($row['booktype']==0) $row['booktypename']='С˵';
        else $row['booktypename']='����';

        if(is_array($ndtp->CTags))
        {
            foreach($ndtp->CTags as $tagid=>$ctag)
            {
                $tagname = $ctag->GetTagName();
                if(isset($row[$tagname])) $ndtp->Assign($tagid,$row[$tagname]);
                else $ndtp->Assign($tagid,'');
            }
        }
        $clist .= $ndtp->GetResult();
    }
    
    return $clist;
}