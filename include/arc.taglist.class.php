<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * Tag�б���
 *
 * @version        $Id: arc.taglist.class.php 1 18:17 2010��7��7��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
require_once(DEDEINC.'/channelunit.class.php');
require_once(DEDEINC.'/typelink.class.php');

@set_time_limit(0);
/**
 * Tag�б���
 *
 * @package          TagList
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class TagList
{
    var $dsql;
    var $dtp;
    var $dtp2;
    var $TypeLink;
    var $PageNo;
    var $TotalPage;
    var $TotalResult;
    var $PageSize;
    var $ListType;
    var $Fields;
    var $Tag;
    var $Templet;
    var $TagInfos;
    var $TempletsFile;

    /**
     *  php5���캯��
     *
     * @access    public
     * @param     string  $keyword  �ؼ���
     * @param     string  $templet  ģ��
     * @return    void
     */
    function __construct($keyword, $templet)
    {
        global $dsql;
        $this->Templet = $templet;
        $this->Tag = $keyword;
        $this->dsql = $dsql;
        $this->dtp = new DedeTagParse();
        $this->dtp->SetRefObj($this);
        $this->dtp->SetNameSpace("dede","{","}");
        $this->dtp2 = new DedeTagParse();
        $this->dtp2->SetNameSpace("field","[","]");
        $this->TypeLink = new TypeLink(0);
        $this->Fields['tag'] = $keyword;
        $this->Fields['title'] = $keyword;
        $this->TempletsFile = '';

        //����һЩȫ�ֲ�����ֵ
        foreach($GLOBALS['PubFields'] as $k=>$v) $this->Fields[$k] = $v;

        //��ȡTag��Ϣ
        if($this->Tag!='')
        {
            $this->TagInfos = $this->dsql->GetOne("Select * From `#@__tagindex` where tag like '{$this->Tag}' ");
            if(!is_array($this->TagInfos))
            {
                $fullsearch = $GLOBALS['cfg_phpurl']."/search.php?keyword=".$this->Tag."&searchtype=titlekeyword";
                $msg = "ϵͳ�޴˱�ǩ�������Ѿ��Ƴ���<br /><br />�㻹���Գ���ͨ����������ȥ��������ؼ��֣�<a href='$fullsearch'>ǰ������&gt;&gt;</a>";
                ShowMsg($msg,"-1");
                exit();
            }
        }

        //��ʼ��ģ��
        $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$GLOBALS['cfg_df_style'].'/'.$this->Templet;
        if(!file_exists($tempfile)||!is_file($tempfile))
        {
            echo "ģ���ļ������ڣ��޷������ĵ���";
            exit();
        }
        $this->dtp->LoadTemplate($tempfile);
        $this->TempletsFile = preg_replace("#^".$GLOBALS['cfg_basedir']."#", '', $tempfile);

    }

    //php4���캯��
    function TagList($keyword,$templet)
    {
        $this->__construct($keyword,$templet);
    }

    //�ر������Դ
    function Close()
    {
        @$this->dsql->Close();
        @$this->TypeLink->Close();
    }

    /**
     *  ͳ���б���ļ�¼
     *
     * @access    private
     * @return    void
     */
    function CountRecord()
    {
        //ͳ�����ݿ��¼
        $this->TotalResult = -1;
        if(isset($GLOBALS['TotalResult']))
        {
            $this->TotalResult = $GLOBALS['TotalResult'];
        }
        if(isset($GLOBALS['PageNo']))
        {
            $this->PageNo = $GLOBALS['PageNo'];
        }
        else
        {
            $this->PageNo = 1;
        }
        if($this->TotalResult==-1)
        {
            $cquery = "SELECT COUNT(*) AS dd FROM `#@__taglist` WHERE tid = '{$this->TagInfos['id']}' AND arcrank >-1 ";
            $row = $this->dsql->GetOne($cquery);
            $this->TotalResult = $row['dd'];

            //����Tag��Ϣ
            $ntime = time();

            //����������ͼ�¼��
            $upquery = "UPDATE `#@__tagindex` SET total='{$row['dd']}',count=count+1,weekcc=weekcc+1,monthcc=monthcc+1 WHERE tag LIKE '{$this->Tag}' ";
            $this->dsql->ExecuteNoneQuery($upquery);
            $oneday = 24 * 3600;

            //��ͳ��
            if(ceil( ($ntime - $this->TagInfos['weekup'])/$oneday ) > 7)
            {
                $this->dsql->ExecuteNoneQuery("UPDATE `#@__tagindex` SET weekcc=0,weekup='{$ntime}' WHERE tag LIKE '{$this->Tag}' ");
            }

            //��ͳ��
            if(ceil( ($ntime - $this->TagInfos['monthup'])/$oneday ) > 30)
            {
                $this->dsql->ExecuteNoneQuery("UPDATE `#@__tagindex` SET monthcc=0,monthup='{$ntime}' WHERE tag LIKE '{$this->Tag}' ");
            }
        }
        $ctag = $this->dtp->GetTag("page");
        if(!is_object($ctag))
        {
            $ctag = $this->dtp->GetTag("list");
        }
        if(!is_object($ctag))
        {
            $this->PageSize = 25;
        }
        else
        {
            if($ctag->GetAtt("pagesize")!='')
            {
                $this->PageSize = $ctag->GetAtt("pagesize");
            }
            else
            {
                $this->PageSize = 25;
            }
        }
        $this->TotalPage = ceil($this->TotalResult/$this->PageSize);
    }

    /**
     *  ��ʾ�б�
     *
     * @access    public
     * @return    void
     */
    function Display()
    {
        if($this->Tag!='')
        {
            $this->CountRecord();
        }
        $this->ParseTempletsFirst();
        if($this->Tag!='')
        {
            $this->ParseDMFields($this->PageNo,0);
        }
        $this->Close();
        $this->dtp->Display();
    }

    /**
     *  ����ģ�壬�Թ̶��ı�ǽ��г�ʼ��ֵ
     *
     * @access    private
     * @return    void
     */
    function ParseTempletsFirst()
    {
        MakeOneTag($this->dtp,$this);
    }

    /**
     *  ����ģ�壬��������ı䶯���и�ֵ
     *
     * @access    public
     * @param     int  $PageNo  ҳ��
     * @param     int  $ismake  �Ƿ����
     * @return    string
     */
    function ParseDMFields($PageNo, $ismake=1)
    {
        foreach($this->dtp->CTags as $tagid=>$ctag){
            if($ctag->GetName()=="list")
            {
                $limitstart = ($this->PageNo-1) * $this->PageSize;
                if($limitstart<0)
                {
                    $limitstart = 0;
                }
                $row = $this->PageSize;
                if(trim($ctag->GetInnerText())=="")
                {
                    $InnerText = GetSysTemplets("list_fulllist.htm");
                }
                else
                {
                    $InnerText = trim($ctag->GetInnerText());
                }
                $this->dtp->Assign($tagid,
                $this->GetArcList(
                $limitstart,
                $row,
                $ctag->GetAtt("col"),
                $ctag->GetAtt("titlelen"),
                $ctag->GetAtt("infolen"),
                $ctag->GetAtt("imgwidth"),
                $ctag->GetAtt("imgheight"),
                $ctag->GetAtt("listtype"),
                $ctag->GetAtt("orderby"),
                $InnerText,
                $ctag->GetAtt("tablewidth"),
                $ismake,
                $ctag->GetAtt("orderway")
                )
                );
            }
            else if($ctag->GetName()=="pagelist")
            {
                $list_len = trim($ctag->GetAtt("listsize"));
                $ctag->GetAtt("listitem")=="" ? $listitem="info,index,pre,pageno,next,end,option" : $listitem=$ctag->GetAtt("listitem");
                if($list_len=="")
                {
                    $list_len = 3;
                }
                if($ismake==0)
                {
                    $this->dtp->Assign($tagid,$this->GetPageListDM($list_len,$listitem));
                }
                else
                {
                    $this->dtp->Assign($tagid,$this->GetPageListST($list_len,$listitem));
                }
            }
        }
    }

    /**
     *  ���һ�����е��ĵ��б�
     *
     * @access    public
     * @param     int  $limitstart  ���ƿ�ʼ  
     * @param     int  $row  ���� 
     * @param     int  $col  ����
     * @param     int  $titlelen  ���ⳤ��
     * @param     int  $infolen  ��������
     * @param     int  $imgwidth  ͼƬ���
     * @param     int  $imgheight  ͼƬ�߶�
     * @param     string  $listtype  �б�����
     * @param     string  $orderby  ����˳��
     * @param     string  $innertext  �ײ�ģ��
     * @param     string  $tablewidth  �����
     * @param     string  $ismake  �Ƿ����
     * @param     string  $orderWay  ����ʽ
     * @return    string
     */
    function GetArcList($limitstart=0,$row=10,$col=1,$titlelen=30,$infolen=250,
    $imgwidth=120,$imgheight=90,$listtype="all",$orderby="default",$innertext="",$tablewidth="100",$ismake=1,$orderWay='desc')
    {
        $getrow = ($row=='' ? 10 : $row);
        if($limitstart=='') $limitstart = 0;
        if($titlelen=='') $titlelen = 100;
        if($infolen=='') $infolen = 250;
        if($imgwidth=='') $imgwidth = 120;
        if($imgheight=='') $imgheight = 120;
        if($listtype=='') $listtype = 'all';
        $orderby = ($orderby=='' ? 'default' : strtolower($orderby) );
        if($orderWay=='') $orderWay = 'desc';
        $tablewidth = str_replace("%", "", $tablewidth);
        if($tablewidth=='') $tablewidth=100;
        if($col=='') $col=1;
        $colWidth = ceil(100/$col);
        $tablewidth = $tablewidth."%";
        $colWidth = $colWidth."%";
        $innertext = trim($innertext);
        if($innertext=='') $innertext = GetSysTemplets("list_fulllist.htm");
        $idlists = $ordersql = '';
        $this->dsql->SetQuery("SELECT aid FROM `#@__taglist` WHERE tid = '{$this->TagInfos['id']}' AND arcrank>-1 LIMIT $limitstart,$getrow");
        $this->dsql->Execute();
        while($row=$this->dsql->GetArray())
        {
            $idlists .= ($idlists=='' ? $row['aid'] : ','.$row['aid']);
        }
        if($idlists=='') return '';

        //����ͬ����趨SQL����
        $orwhere = " se.id IN($idlists) ";

        //����ʽ
        if($orderby=="sortrank")
        {
            $ordersql = "  ORDER BY se.sortrank $orderWay";
        }
        else
        {
            $ordersql=" ORDER BY se.id $orderWay";
        }
        $query = "SELECT se.*,tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,tp.ispart,tp.moresite,tp.siteurl,tp.sitepath
            FROM `#@__archives` se LEFT JOIN `#@__arctype` tp ON se.typeid=tp.id WHERE $orwhere $ordersql ";

        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $row = $this->PageSize / $col;
        $artlist = '';
        $this->dtp2->LoadSource($innertext);
        $GLOBALS['autoindex'] = 0;
        for($i=0; $i<$row; $i++)
        {
            if($col > 1)
            {
                $artlist .= "<div>\r\n";
            }
            for($j=0; $j<$col; $j++)
            {
                if($row = $this->dsql->GetArray("al"))
                {
                    $GLOBALS['autoindex']++;
                    $ids[$row['id']] = $row['id'];

                    //����һЩ�����ֶ�
                    $row['infos'] = cn_substr($row['description'],$infolen);
                    $row['id'] =  $row['id'];
                    $row['arcurl'] = GetFileUrl($row['id'],$row['typeid'],$row['senddate'],$row['title'],
                    $row['ismake'],
                    $row['arcrank'],$row['namerule'],$row['typedir'],$row['money'],
                    $row['filename'],$row['moresite'],$row['siteurl'],$row['sitepath']);
                    $row['typeurl'] = GetTypeUrl($row['typeid'],MfTypedir($row['typedir']),$row['isdefault'],$row['defaultname'],
                    $row['ispart'],$row['namerule2'],$row['moresite'],$row['siteurl'],$row['sitepath']);
                    if($row['litpic'] == '-' || $row['litpic'] == '')
                    {
                        $row['litpic'] = $GLOBALS['cfg_cmspath'].'/images/defaultpic.gif';
                    }
                    if(!preg_match("/^http:\/\//", $row['litpic']) && $GLOBALS['cfg_multi_site'] == 'Y')
                    {
                        $row['litpic'] = $GLOBALS['cfg_mainsite'].$row['litpic'];
                    }
                    $row['picname'] = $row['litpic'];
                    $row['stime'] = GetDateMK($row['pubdate']);
                    $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";
                    $row['image'] = "<img src='".$row['picname']."' border='0' width='$imgwidth' height='$imgheight' alt='".preg_replace("/['><]/", "", $row['title'])."'>";
                    $row['imglink'] = "<a href='".$row['filename']."'>".$row['image']."</a>";
                    $row['fulltitle'] = $row['title'];
                    $row['title'] = cn_substr($row['title'],$titlelen);
                    if($row['color']!='')
                    {
                        $row['title'] = "<font color='".$row['color']."'>".$row['title']."</font>";
                    }
                    if(preg_match('/c/', $row['flag']))
                    {
                        $row['title'] = "<b>".$row['title']."</b>";
                    }
                    $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";
                    $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];
                    $row['memberurl'] = $GLOBALS['cfg_memberurl'];
                    $row['templeturl'] = $GLOBALS['cfg_templeturl'];
                    if(is_array($this->dtp2->CTags))
                    {
                        foreach($this->dtp2->CTags as $k=>$ctag)
                        {
                            if($ctag->GetName()=='array')
                            {
                                //�����������飬��runphpģʽ������������
                                $this->dtp2->Assign($k,$row);
                            }
                            else
                            {
                                if(isset($row[$ctag->GetName()]))
                                {
                                    $this->dtp2->Assign($k,$row[$ctag->GetName()]);
                                }
                                else
                                {
                                    $this->dtp2->Assign($k,'');
                                }
                            }
                        }
                    }
                    $artlist .= $this->dtp2->GetResult();
                }//if hasRow

            }//Loop Col

            if($col>1)
            {
                $i += $col - 1;
                $artlist .= "    </div>\r\n";
            }
        }//Loop Line

        $this->dsql->FreeResult('al');
        return $artlist;
    }

    /**
     *  ��ȡ��̬�ķ�ҳ�б�
     *
     * @access    public
     * @param     int  $list_len  �б���
     * @param     string  $listitem  �б���ʽ
     * @return    string
     */
    function GetPageListDM($list_len,$listitem="info,index,end,pre,next,pageno")
    {
        $prepage="";
        $nextpage="";
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if($list_len == "" || preg_match("/[^0-9]/", $list_len))
        {
            $list_len = 3;
        }
        $totalpage = $this->TotalPage;
        if($totalpage <= 1 && $this->TotalResult > 0)
        {
            return "<span class=\"pageinfo\">��1ҳ/".$this->TotalResult."��</span>";
        }
        if($this->TotalResult == 0)
        {
            return "<span class=\"pageinfo\">��0ҳ/".$this->TotalResult."��</span>";
        }
        $maininfo = "<span class=\"pageinfo\">��{$totalpage}ҳ/".$this->TotalResult."��</span>\r\n";
        $purl = $this->GetCurUrl();
        $purl .= "?/".urlencode($this->Tag);

        //�����һҳ����һҳ������
        if($this->PageNo != 1)
        {
            $prepage.="<a href='".$purl."/$prepagenum/' class=\"prev\">��һҳ</a>\r\n";
            $indexpage="<a href='".$purl."/1/'>��ҳ</a>\r\n";
        }
        else
        {
            $indexpage="<a>��ҳ</a>\r\n";
        }
        if($this->PageNo!=$totalpage && $totalpage>1)
        {
            $nextpage.="<a href='".$purl."/$nextpagenum/' class=\"next\">��һҳ</a>\r\n";
            $endpage="<a href='".$purl."/$totalpage/'>ĩҳ</a>\r\n";
        }
        else
        {
            $endpage="<a>ĩҳ</a>\r\n";
        }

        //�����������
        $listdd="";
        $total_list = $list_len * 2 + 1;
        if($this->PageNo >= $total_list)
        {
            $j = $this->PageNo - $list_len;
            $total_list = $this->PageNo + $list_len;
            if($total_list > $totalpage)
            {
                $total_list = $totalpage;
            }
        }
        else
        {
            $j=1;
            if($total_list > $totalpage)
            {
                $total_list = $totalpage;
            }
        }
        for($j; $j<=$total_list; $j++)
        {
            if($j == $this->PageNo)
            {
                $listdd.= "<a class=\"on\">$j</a>\r\n";
            }
            else
            {
                $listdd.="<a href='".$purl."/$j/'>".$j."</a>\r\n";
            }
        }
        $plist  =  '';
        if(preg_match('/info/i', $listitem))
        {
            $plist .= $maininfo.' ';
        }
        if(preg_match('/index/i', $listitem))
        {
            $plist .= $indexpage.' ';
        }
        if(preg_match('/pre/i', $listitem))
        {
            $plist .= $prepage.' ';
        }
        if(preg_match('/pageno/i', $listitem))
        {
            $plist .= $listdd.' ';
        }
        if(preg_match('/next/i', $listitem))
        {
            $plist .= $nextpage.' ';
        }
        if(preg_match('/end/i', $listitem))
        {
            $plist .= $endpage.' ';
        }
        return $plist;
    }

    /**
     *  ���һ��ָ����Ƶ��������
     *
     * @access    private
     * @param     int  $typeid  ��ĿID
     * @param     string  $typedir  ��ĿĿ¼
     * @param     int  $isdefault  �Ƿ�ΪĬ��
     * @param     string  $defaultname  Ĭ������
     * @param     int  $ispart  ��Ŀ����
     * @param     string  $namerule2  ��Ŀ����
     * @param     string  $siteurl  վ���ַ
     * @return    string
     */
    function GetListUrl($typeid,$typedir,$isdefault,$defaultname,$ispart,$namerule2,$siteurl="")
    {
        return GetTypeUrl($typeid,MfTypedir($typedir),$isdefault,$defaultname,$ispart,$namerule2,$siteurl);
    }

    /**
     *  ���һ��ָ������������
     *
     * @access    private
     * @param     int  $aid  �ĵ�ID
     * @param     int  $typeid  ��ĿID
     * @param     int  $timetag  ʱ���
     * @param     string  $title  ����
     * @param     int  $ismake  �Ƿ����ɾ�̬
     * @param     int  $rank  ���Ȩ��
     * @param     string  $namerule  ��������
     * @param     string  $artdir  �ĵ�·��
     * @param     int  $money  ��Ҫ���
     * @param     string  $filename  �ļ�����
     * @return    string
     */
    function GetArcUrl($aid,$typeid,$timetag,$title,$ismake=0,$rank=0,$namerule="",$artdir="",$money=0,$filename='')
    {
        return GetFileUrl($aid,$typeid,$timetag,$title,$ismake,$rank,$namerule,$artdir,$money,$filename);
    }

    /**
     *  ��õ�ǰ��ҳ���ļ���url
     *
     * @access    private
     * @return    string
     */
    function GetCurUrl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $nowurl = $_SERVER["REQUEST_URI"];
            $nowurls = explode("?",$nowurl);
            $nowurl = $nowurls[0];
        }
        else
        {
            $nowurl = $_SERVER["PHP_SELF"];
        }
        return $nowurl;
    }
}//End Class