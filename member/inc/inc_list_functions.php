<?php  if(!defined('DEDEMEMBER')) exit("dedecms");
/**
 * ģ���б���
 * 
 * @version        $Id: inc_list_functions.php 1 13:52 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ����Ƿ��Ƽ��ı���
 *
 * @param     string  $iscommend  �Ƽ�
 * @return    string
 */
function IsCommendArchives($iscommend)
{
    $s = '';
    if(preg_match('/c/', $iscommend))
    {
        $s .= '�Ƽ�';
    }
    else if(preg_match('/h/', $iscommend))
    {
        $s .= ' ͷ��';
    }
    else if(preg_match('/p/', $iscommend))
    {
        $s .= ' ͼƬ';
    }
    else if(preg_match('/j/', $iscommend))
    {
        $s .= ' ��ת';
    }
    return $s;
}

/**
 *  ����Ƽ��ı���
 *
 * @param     string  $title  ����
 * @param     string  $iscommend  �Ƽ�
 * @return    string
 */
function GetCommendTitle($title, $iscommend)
{
    if(preg_match('/c/', $iscommend))
    {
        $title = "$title<font color='red'>(�Ƽ�)</font>";
    }
    return "$title";
}

$GLOBALS['RndTrunID'] = 1;
/**
 *  ������ɫ
 *
 * @param     string  $color1  ��ɫ1
 * @param     string  $color2  ��ɫ2
 * @return    string
 */
function GetColor($color1,$color2)
{
    $GLOBALS['RndTrunID']++;
    if($GLOBALS['RndTrunID']%2==0)
    {
        return $color1;
    }
    else
    {
        return $color2;
    }
}

/**
 *  ���ͼƬ�Ƿ����
 *
 * @param     string  $picname  ͼƬ��ַ
 * @return    string
 */
function CheckPic($picname)
{
    if($picname!="")
    {
        return $picname;
    }
    else
    {
        return "images/dfpic.gif";
    }
}

/**
 *  �ж������Ƿ�����HTML
 *
 * @param     int  $ismake  �Ƿ�����
 * @return    string
 */
function IsHtmlArchives($ismake)
{
    if($ismake==1)
    {
        return "������";
    }
    else if($ismake==-1)
    {
        return "����̬";
    }
    else
    {
        return "<font color='red'>δ����</font>";
    }
}

/**
 *  ������ݵ��޶���������
 *
 * @param     string  $arcrank  ��������
 * @return    string
 */
function GetRankName($arcrank)
{
    global $arcArray;
    if(!is_array($arcArray))
    {
        $dsql->SetQuery("SELECT * FROM #@__arcrank");
        $dsql->Execute();
        while($row = $dsql->GetObject())
        {
            $arcArray[$row->rank]=$row->membername;
        }
    }
    if(isset($arcArray[$arcrank]))
    {
        return $arcArray[$arcrank];
    }
    else
    {
        return "����";
    }
}

/**
 *  �ж������Ƿ�ΪͼƬ����
 *
 * @param     string  $picname  ͼƬ����
 * @return    string
 */
function IsPicArchives($picname)
{
    if($picname!="")
    {
        return "<font color='red'>(ͼ)</font>";
    }
    else
    {
        return "";
    }
}