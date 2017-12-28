<?php
/**
 * ���Ե���������
 *
 * @version        $Id: dedeatt.class.php 1 13:50 2010��7��6��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
// ------------------------------------------------------------------------
/**
 * ���Ե���������
 * function c____DedeAtt();
 *
 * @package          DedeAtt
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class DedeAtt
{
    var $Count = -1;
    var $Items = ""; //����Ԫ�صļ���

    /**
     *  //���ĳ������
     *
     * @access    public
     * @param     string    $str    ����
     * @return    string
     */
    function GetAtt($str)
    {
        if($str=="")
        {
            return "";
        }
        if(isset($this->Items[$str]))
        {
            return $this->Items[$str];
        }
        else
        {
            return "";
        }
    }

    //ͬ��
    function GetAttribute($str)
    {
        return $this->GetAtt($str);
    }

    /**
     *  �ж������Ƿ����
     *
     * @access    public
     * @param     string  $str  ��������
     * @return    string
     */
    function IsAttribute($str)
    {
        return isset($this->Items[$str]) ? TRUE : FALSE;
    }

    /**
     *  ��ñ������
     *
     * @access    public
     * @return    string
     */
    function GetTagName()
    {
        return $this->GetAtt("tagname");
    }

    /**
     *   ������Ը���
     *
     * @access    public
     * @return    int
     */
    function GetCount()
    {
        return $this->Count+1;
    }
}//End DedeAtt

/**
 * ���Խ�����
 * function c____DedeAttParse();
 *
 * @package          DedeAtt
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class DedeAttParse
{
    var $SourceString = "";
    var $SourceMaxSize = 1024;
    var $CAtt = ""; //���Ե�����������
    var $CharToLow = TRUE;

    /**
     *  �������Խ�����Դ�ַ���
     *
     * @access    public
     * @param     string  $str  ��Ҫ�������ַ���
     * @return    string
     */
    function SetSource($str="")
    {
        $this->CAtt = new DedeAtt();
        $strLen = 0;
        $this->SourceString = trim(preg_replace("/[ \t\r\n]{1,}/"," ",$str));
        $strLen = strlen($this->SourceString);
        if($strLen>0&&$strLen<=$this->SourceMaxSize)
        {
            $this->ParseAtt();
        }
    }

    /**
     *  ��������(˽�г�Ա������SetSource����)
     *
     * @access    private
     * @return    void
     */
    function ParseAtt()
    {
        $d = "";
        $tmpatt="";
        $tmpvalue="";
        $startdd=-1;
        $ddtag="";
        $notAttribute=TRUE;
        $strLen = strlen($this->SourceString);

        // �����ǻ��Tag������,��������Ƿ���Ҫ
        // ���������������,���ڽ�������Tagʱ����
        // �����в�Ӧ�ô���tagname�������
        for($i=0;$i<$strLen;$i++)
        {
            $d = substr($this->SourceString,$i,1);
            if($d==' ')
            {
                $this->CAtt->Count++;
                if($this->CharToLow)
                {
                    $this->CAtt->Items["tagname"]=strtolower(trim($tmpvalue));
                }
                else
                {
                    $this->CAtt->Items["tagname"]=trim($tmpvalue);
                }
                $tmpvalue = "";
                $notAttribute = FALSE;
                break;
            }
            else
            {
                $tmpvalue .= $d;
            }
        }

        //�����������б�����
        if($notAttribute)
        {
            $this->CAtt->Count++;
            $this->CAtt->Items["tagname"]= ($this->CharToLow ? strtolower(trim($tmpvalue)) : trim($tmpvalue));
        }

        //����ַ�����������ֵ������Դ�ַ���,����ø�����
        if(!$notAttribute)
        {
            for($i;$i<$strLen;$i++)
            {
                $d = substr($this->SourceString,$i,1);
                if($startdd==-1)
                {
                    if($d!="=")
                    {
                        $tmpatt .= $d;
                    }
                    else
                    {
                        if($this->CharToLow)
                        {
                            $tmpatt = strtolower(trim($tmpatt));
                        }
                        else
                        {
                            $tmpatt = trim($tmpatt);
                        }
                        $startdd=0;
                    }
                }
                else if($startdd==0)
                {
                    switch($d)
                    {
                        case ' ':
                            continue;
                            break;
                        case '\'':
                            $ddtag='\'';
                            $startdd=1;
                            break;
                        case '"':
                            $ddtag='"';
                            $startdd=1;
                            break;
                        default:
                            $tmpvalue.=$d;
                            $ddtag=' ';
                            $startdd=1;
                            break;
                    }
                }
                else if($startdd==1)
                {
                    if($d==$ddtag)
                    {
                        $this->CAtt->Count++;
                        $this->CAtt->Items[$tmpatt] = trim($tmpvalue);//strtolower(trim($tmpvalue));
                        $tmpatt = "";
                        $tmpvalue = "";
                        $startdd=-1;
                    }
                    else
                    {
                        $tmpvalue.=$d;
                    }
                }
            }
            if($tmpatt!="")
            {
                $this->CAtt->Count++;
                $this->CAtt->Items[$tmpatt]=trim($tmpvalue);//strtolower(trim($tmpvalue));
            }//������Խ���

        }//for

    }//has Attribute
}//End DedeAttParse