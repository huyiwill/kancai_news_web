<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ��ͼ��
 *
 * @version        $Id: arc.partview.class.php 1 14:17 2010��7��7��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(DEDEINC.'/channelunit.class.php');
require_once(DEDEINC.'/typelink.class.php');
require_once(DEDEINC.'/ftp.class.php');

/**
 * ��ͼ��
 *
 * @package          PartView
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class PartView
{
    var $dsql;
    var $dtp;
    var $TypeID;
    var $Fields;
    var $TypeLink;
    var $pvCopy;
    var $refObj;
    var $ftp;
    var $remoteDir;

    /**
     *  php5���캯��
     *
     * @access    public
     * @param     int  $typeid  ��ĿID
     * @param     int  $needtypelink  �Ƿ���Ҫ��Ŀ����
     * @return    void
     */
    function __construct($typeid=0,$needtypelink=TRUE)
    {
        global $_sys_globals,$ftp;
        $this->TypeID = $typeid;
        $this->dsql = $GLOBALS['dsql'];
        $this->dtp = new DedeTagParse();
        $this->dtp->SetNameSpace("dede","{","}");
        $this->dtp->SetRefObj($this);
        $this->ftp = &$ftp;
        $this->remoteDir = '';

        if($needtypelink)
        {
            $this->TypeLink = new TypeLink($typeid);
            if(is_array($this->TypeLink->TypeInfos))
            {
                foreach($this->TypeLink->TypeInfos as $k=>$v)
                {
                    if(preg_match("/[^0-9]/", $k))
                    {
                        $this->Fields[$k] = $v;
                    }
                }
            }
            $_sys_globals['curfile'] = 'partview';
            $_sys_globals['typename'] = $this->Fields['typename'];

            //���û�������
            SetSysEnv($this->TypeID,$this->Fields['typename'],0,'','partview');
        }
        SetSysEnv($this->TypeID,'',0,'','partview');
        $this->Fields['typeid'] = $this->TypeID;

        //����һЩȫ�ֲ�����ֵ
        foreach($GLOBALS['PubFields'] as $k=>$v)
        {
            $this->Fields[$k] = $v;
        }
    }
    
    //php4���캯��
    function PartView($typeid=0,$needtypelink=TRUE)
    {
        $this->__construct($typeid,$needtypelink);
    }

    /**
     *  ����ָ������Ķ���
     *
     * @access    private
     * @param     object  $refObj  ���ö���
     * @return    void
     */
    function SetRefObj(&$refObj)
    {
        $this->dtp->SetRefObj($refObj);
        if(isset($refObj->TypeID))
        {
            $this->__construct($refObj->TypeID);
        }
    }

    /**
     *  ָ��typelink�������ǰ��ʵ��
     *
     * @access    public
     * @param     string  $typelink  ��Ŀ����
     * @return    string
     */
    function SetTypeLink(&$typelink)
    {
        $this->TypeLink = $typelink;
        if(is_array($this->TypeLink->TypeInfos))
        {
            foreach($this->TypeLink->TypeInfos as $k=>$v)
            {
                if(preg_match("/[^0-9]/", $k))
                {
                    $this->Fields[$k] = $v;
                }
            }
        }
    }

    /**
     *  ����Ҫ������ģ��
     *
     * @access    public
     * @param     string  $temp  ģ��
     * @param     string  $stype  ��������
     * @return    string
     */
    function SetTemplet($temp,$stype="file")
    {
        if($stype=="string")
        {
            $this->dtp->LoadSource($temp);
        }
        else
        {
            $this->dtp->LoadTemplet($temp);
        }
        if($this->TypeID > 0)
        {
            $this->Fields['position'] = $this->TypeLink->GetPositionLink(TRUE);
            $this->Fields['title'] = $this->TypeLink->GetPositionLink(false);
        }
        $this->ParseTemplet();
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
     * @return    string
     */
    function GetResult()
    {
        return $this->dtp->GetResult();
    }

    /**
     *  ������Ϊ�ļ�
     *
     * @access    public
     * @param     string  $filename  �ļ���
     * @param     string  $isremote  �Ƿ�Զ��
     * @return    string
     */
    function SaveToHtml($filename,$isremote=0)
    {
        global $cfg_remote_site;
        //�������Զ�̷�������Ҫ�����ж�
        if($cfg_remote_site=='Y' && $isremote == 1)
        {
            //����Զ���ļ�·��
            $remotefile = str_replace(DEDEROOT, '', $filename);
            $localfile = '..'.$remotefile;
            //����Զ���ļ���
            $remotedir = preg_replace('/[^\/]*\.js/', '', $remotefile);
            $this->ftp->rmkdir($remotedir);
            $this->ftp->upload($localfile, $remotefile, 'ascii');
        }
        $this->dtp->SaveTo($filename);
    }

    /**
     *  ����ģ����ı�ǩ
     *
     * @access    private
     * @return    void
     */
    function ParseTemplet()
    {
        $GLOBALS['envs']['typeid'] = $this->TypeID;
        if($this->TypeID>0)
        {
            $GLOBALS['envs']['topid'] = GetTopid($this->TypeID);
        }
        else 
        {
            $GLOBALS['envs']['topid'] = 0;
        }
        if(isset($this->TypeLink->TypeInfos['reid']))
        {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }
        if(isset($this->TypeLink->TypeInfos['channeltype']))
        {
          $GLOBALS['envs']['channelid'] = $this->TypeLink->TypeInfos['channeltype'];
        }
        MakeOneTag($this->dtp,$this); //����������� channelunit.func.php �ļ���
    }

    /**
     * ����޶�ģ�ͻ���Ŀ��һ��ָ���ĵ��б�
     * ����������ʹ���˻��棬���Ҵ���������֧�ֱַ�ģʽ�ģ�����ٶȸ��죬�����ܽ�����վ�����ݵ���
     * @param string $templets
     * @param int $typeid
     * @param int $row
     * @param int $col
     * @param int $titlelen
     * @param int $infolen
     * @param int $imgwidth
     * @param int $imgheight
     * @param string $listtype
     * @param string $orderby
     * @param string $keyword
     * @param string $innertext
     * @param int $tablewidth
     * @param int $arcid
     * @param string $idlist
     * @param int $channelid
     * @param string $limit
     * @param int $att
     * @param string $order
     * @param int $subday
     * @param int $autopartid
     * @param int $ismember
     * @param string $maintable
     * @param object $ctag
     * @return array
     */
    function GetArcList($templets='',$typeid=0,$row=10,$col=1,$titlelen=30,$infolen=160,
    $imgwidth=120,$imgheight=90,$listtype="all",$orderby="default",$keyword="",$innertext="",
    $tablewidth="100",$arcid=0,$idlist="",$channelid=0,$limit="",$att=0,$order='desc',$subday=0,
    $autopartid=-1,$ismember=0,$maintable='',$ctag='')
    {
        if(empty($autopartid))
        {
            $autopartid = -1;
        }
        if(empty($typeid))
        {
            $typeid=$this->TypeID;
        }
        if($autopartid!=-1)
        {
            $typeid = $this->GetAutoChannelID($autopartid,$typeid);
            if($typeid==0)
            {
                return "";
            }
        }

        if(!isset($GLOBALS['__SpGetArcList']))
        {
            require_once(dirname(__FILE__)."/inc/inc_fun_SpGetArcList.php");
        }
        return SpGetArcList($this->dsql,$templets,$typeid,$row,$col,$titlelen,$infolen,$imgwidth,$imgheight,
        $listtype,$orderby,$keyword,$innertext,$tablewidth,$arcid,$idlist,$channelid,$limit,$att,
        $order,$subday,$ismember,$maintable,$ctag);
    }

    //�ر���ռ�õ���Դ
    function Close()
    {
    }
}//End Class