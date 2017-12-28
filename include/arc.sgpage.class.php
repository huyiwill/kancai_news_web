<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ����ģ����ͼ��
 *
 * @version        $Id: arc.sgpage.class.php 1 15:48 2010��7��7��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(DEDEINC."/arc.partview.class.php");

/**
 * ����ģ���б���ͼ��
 *
 * @package          SgListView
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class sgpage
{
    var $dsql;
    var $dtp;
    var $TypeID;
    var $Fields;
    var $TypeLink;
    var $partView;

    /**
     *  php5���캯��
     *
     * @access    public
     * @param     int  $aid  ����ID
     * @return    string
     */
    function __construct($aid)
    {
        global $cfg_basedir,$cfg_templets_dir,$cfg_df_style,$envs;

        $this->dsql = $GLOBALS['dsql'];
        $this->dtp = new DedeTagParse();
        $this->dtp->refObj = $this;
        $this->dtp->SetNameSpace("dede","{","}");
        $this->Fields = $this->dsql->GetOne("SELECT * FROM `#@__sgpage` WHERE aid='$aid' ");
        $envs['aid'] = $this->Fields['aid'];

        //����һЩȫ�ֲ�����ֵ
        foreach($GLOBALS['PubFields'] as $k=>$v)
        {
            $this->Fields[$k] = $v;
        }
        if($this->Fields['ismake']==1)
        {
            $pv = new PartView();
            $pv->SetTemplet($this->Fields['body'],'string');
            $this->Fields['body'] = $pv->GetResult();
        }
        $tplfile = $cfg_basedir.str_replace('{style}',$cfg_templets_dir.'/'.$cfg_df_style,$this->Fields['template']);
        $this->dtp->LoadTemplate($tplfile);
        $this->ParseTemplet();
    }

    //php4���캯��
    function sgpage($aid)
    {
        $this->__construct($aid);
    }

    /**
     *  ��ʾ����
     *
     * @access    public
     * @return    void
     */
    function Display()
    {
        $this->dtp->Display();
    }

    /**
     *  ��ȡ����
     *
     * @access    public
     * @return    void
     */
    function GetResult()
    {
        return $this->dtp->GetResult();
    }

    /**
     *  ������Ϊ�ļ�
     *
     * @access    public
     * @return    void
     */
    function SaveToHtml()
    {
        $filename = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_cmspath'].'/'.$this->Fields['filename'];
        $filename = preg_replace("/\/{1,}/", '/', $filename);
        $this->dtp->SaveTo($filename);
    }

    /**
     *  ����ģ����ı�ǩ
     *
     * @access    public
     * @return    string
     */
    function ParseTemplet()
    {
        $GLOBALS['envs']['likeid'] = $this->Fields['likeid'];
        MakeOneTag($this->dtp,$this);
    }

    //�ر���ռ�õ���Դ
    function Close()
    {
    }
}//End Class