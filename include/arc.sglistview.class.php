<?php   if(!defined('DEDEINC'))exit('Request Error!');
/**
 * ����ģ���б���ͼ��
 *
 * @version        $Id: arc.sglistview.class.php 1 15:48 2010��7��7��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
@set_time_limit(0);
require_once(DEDEINC."/arc.partview.class.php");

/**
 * ����ģ���б���ͼ��
 *
 * @package          SgListView
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class SgListView
{
    var $dsql;
    var $dtp;
    var $dtp2;
    var $TypeID;
    var $TypeLink;
    var $PageNo;
    var $TotalPage;
    var $TotalResult;
    var $PageSize;
    var $ChannelUnit;
    var $ListType;
    var $Fields;
    var $PartView;
    var $addSql;
    var $IsError;
    var $CrossID;
    var $IsReplace;
    var $AddTable;
    var $ListFields;
    var $searchArr;
    var $sAddTable;
    
    /**
     *  php5���캯��
     *
     * @access    public
     * @param     int  $typeid  ��ĿID
     * @param     array  $searchArr  ��������
     * @return    void
     */
    function __construct($typeid,$searchArr=array())
    {
        global $dsql;
        $this->TypeID = $typeid;
        $this->dsql = $dsql;
        $this->CrossID = '';
        $this->IsReplace = false;
        $this->IsError = false;
        $this->dtp = new DedeTagParse();
        $this->dtp->SetRefObj($this);
        $this->sAddTable = false;
        $this->dtp->SetNameSpace("dede","{","}");
        $this->dtp2 = new DedeTagParse();
        $this->dtp2->SetNameSpace("field","[","]");
        $this->TypeLink = new TypeLink($typeid);
        $this->searchArr = $searchArr;
        if(!is_array($this->TypeLink->TypeInfos))
        {
            $this->IsError = true;
        }
        if(!$this->IsError)
        {
            $this->ChannelUnit = new ChannelUnit($this->TypeLink->TypeInfos['channeltype']);
            $this->Fields = $this->TypeLink->TypeInfos;
            $this->Fields['id'] = $typeid;
            $this->Fields['position'] = $this->TypeLink->GetPositionLink(true);
            $this->Fields['title'] = preg_replace("/[<>]/", " / ", $this->TypeLink->GetPositionLink(false));

            //��ø��ӱ���б��ֶ���Ϣ
            $this->AddTable = $this->ChannelUnit->ChannelInfos['addtable'];
            $listfield = trim($this->ChannelUnit->ChannelInfos['listfields']);

            $this->ListFields = explode(',', $listfield);

            //����һЩȫ�ֲ�����ֵ
            foreach($GLOBALS['PubFields'] as $k=>$v) $this->Fields[$k] = $v;
            $this->Fields['rsslink'] = $GLOBALS['cfg_cmsurl']."/data/rss/".$this->TypeID.".xml";

            //���û�������
            SetSysEnv($this->TypeID,$this->Fields['typename'],0,'','list');
            $this->Fields['typeid'] = $this->TypeID;

            //��ý�����ĿID
            if($this->TypeLink->TypeInfos['cross']>0 && $this->TypeLink->TypeInfos['ispart']==0)
            {
                $selquery = '';
                if($this->TypeLink->TypeInfos['cross']==1)
                {
                    $selquery = "SELECT id,topid FROM `#@__arctype` WHERE typename LIKE '{$this->Fields['typename']}' AND id<>'{$this->TypeID}' AND topid<>'{$this->TypeID}'  ";
                }
                else
                {
                    $this->Fields['crossid'] = preg_replace("/[^0-9,]/", '', trim($this->Fields['crossid']));
                    if($this->Fields['crossid']!='')
                    {
                        $selquery = "SELECT id,topid FROM `#@__arctype` WHERE id IN({$this->Fields['crossid']}) AND id<>{$this->TypeID} AND topid<>{$this->TypeID}  ";
                    }
                }
                if($selquery!='')
                {
                    $this->dsql->SetQuery($selquery);
                    $this->dsql->Execute();
                    while($arr = $this->dsql->GetArray())
                    {
                        $this->CrossID .= ($this->CrossID=='' ? $arr['id'] : ','.$arr['id']);
                    }
                }
            }

        }//!error

    }

    //php4���캯��
    function SgListView($typeid,$searchArr=array()){
        $this->__construct($typeid,$searchArr);
    }
    //�ر������Դ
    function Close()
    {

    }

    /**
     *  ͳ���б���ļ�¼
     *
     * @access    public
     * @return    void
     */
    function CountRecord()
    {
        global $cfg_list_son;

        //ͳ�����ݿ��¼
        $this->TotalResult = -1;
        if(isset($GLOBALS['TotalResult'])) $this->TotalResult = $GLOBALS['TotalResult'];
        if(isset($GLOBALS['PageNo'])) $this->PageNo = $GLOBALS['PageNo'];
        else $this->PageNo = 1;
        $this->addSql  = " arc.arcrank > -1 ";

        //��Ŀid����
        if(!empty($this->TypeID))
        {
            if($cfg_list_son=='N')
            {
                if($this->CrossID=='') $this->addSql .= " AND (arc.typeid='".$this->TypeID."') ";
                else $this->addSql .= " AND (arc.typeid IN({$this->CrossID},{$this->TypeID})) ";
            }
            else
            {
                if($this->CrossID=='') $this->addSql .= " AND (arc.typeid IN (".GetSonIds($this->TypeID,$this->Fields['channeltype']).") ) ";
                else $this->addSql .= " AND (arc.typeid IN (".GetSonIds($this->TypeID,$this->Fields['channeltype']).",{$this->CrossID}) ) ";
            }
        }

        $naddQuery = '';
        //��������Ϣ��������
        if(count($this->searchArr) > 0)
        {
            if(!empty($this->searchArr['nativeplace']))
            {
                if($this->searchArr['nativeplace'] % 500 ==0 )
                {
                    $naddQuery .= " AND arc.nativeplace >= '{$this->searchArr['nativeplace']}' AND arc.nativeplace < '".($this->searchArr['nativeplace']+500)."'";
                }
                else
                {
                    $naddQuery .= "AND arc.nativeplace = '{$this->searchArr['nativeplace']}'";
                }
            }
            if(!empty($this->searchArr['infotype']))
            {
                if($this->searchArr['infotype'] % 500 ==0 )
                {
                    $naddQuery .= " AND arc.infotype >= '{$this->searchArr['infotype']}' AND arc.infotype < '".($this->searchArr['infotype']+500)."'";
                }
                else
                {
                    $naddQuery .= "AND arc.infotype = '{$this->searchArr['infotype']}'";
                }
            }
            if(!empty($this->searchArr['keyword']))
            {
                $naddQuery .= "AND arc.title like '%{$this->searchArr['keyword']}%' ";
          }
        }

        if($naddQuery!='')
        {
            $this->sAddTable = true;
            $this->addSql .= $naddQuery;
        }

        if($this->TotalResult==-1)
        {
            if($this->sAddTable)
            {
                $cquery = "SELECT COUNT(*) AS dd FROM `{$this->AddTable}` arc WHERE ".$this->addSql;
            }
            else
            {
                $cquery = "SELECT COUNT(*) AS dd FROM `#@__arctiny` arc WHERE ".$this->addSql;
            }
            $row = $this->dsql->GetOne($cquery);
            if(is_array($row))
            {
                $this->TotalResult = $row['dd'];
            }
            else
            {
                $this->TotalResult = 0;
            }
        }
        //��ʼ���б�ģ�壬��ͳ��ҳ������
        $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$this->TypeLink->TypeInfos['templist'];
        $tempfile = str_replace("{tid}",$this->TypeID,$tempfile);
        $tempfile = str_replace("{cid}",$this->ChannelUnit->ChannelInfos['nid'],$tempfile);
        if(!file_exists($tempfile))
        {
            $tempfile = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir']."/".$GLOBALS['cfg_df_style']."/list_default_sg.htm";
        }
        if(!file_exists($tempfile)||!is_file($tempfile))
        {
            echo "ģ���ļ������ڣ��޷������ĵ���";
            exit();
        }
        $this->dtp->LoadTemplate($tempfile);
        $ctag = $this->dtp->GetTag("page");
        if(!is_object($ctag))
        {
            $ctag = $this->dtp->GetTag("list");
        }
        if(!is_object($ctag))
        {
            $this->PageSize = 20;
        }
        else
        {
            if($ctag->GetAtt('pagesize')!='')
            {
                $this->PageSize = $ctag->GetAtt('pagesize');
            }
            else
            {
                $this->PageSize = 20;
            }
        }
        $this->TotalPage = ceil($this->TotalResult/$this->PageSize);
    }

    /**
     *  �б���HTML
     *
     * @access    public
     * @param     string  $startpage  ��ʼҳ��
     * @param     string  $makepagesize  ���ɳߴ�
     * @return    string
     */
    function MakeHtml($startpage=1,$makepagesize=0)
    {
        if(empty($startpage))
        {
            $startpage = 1;
        }

        //��������ģ���ļ�
        if($this->TypeLink->TypeInfos['isdefault']==-1)
        {
            echo '�����Ŀ�Ƕ�̬��Ŀ��';
            return '';
        }

        //����ҳ��
        else if($this->TypeLink->TypeInfos['ispart']>0)
        {
            $reurl = $this->MakePartTemplets();
            return $reurl;
        }

        $this->CountRecord();
        //�������̶�ֵ�ı�Ǹ�ֵ
        $this->ParseTempletsFirst();
        $totalpage = ceil($this->TotalResult/$this->PageSize);
        if($totalpage==0)
        {
            $totalpage = 1;
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $murl = '';
        if($makepagesize > 0)
        {
            $endpage = $startpage+$makepagesize;
        }
        else
        {
            $endpage = ($totalpage+1);
        }
        if( $endpage >= $totalpage+1 )
        {
            $endpage = $totalpage+1;
        }
        if($endpage==1)
        {
            $endpage = 2;
        }
        for($this->PageNo=$startpage; $this->PageNo < $endpage; $this->PageNo++)
        {
            $this->ParseDMFields($this->PageNo,1);
            $makeFile = $this->GetMakeFileRule($this->Fields['id'],'list',$this->Fields['typedir'],'',$this->Fields['namerule2']);
            $makeFile = str_replace("{page}",$this->PageNo,$makeFile);
            $murl = $makeFile;
            if(!preg_match("/^\//",$makeFile))
            {
                $makeFile = "/".$makeFile;
            }
            $makeFile = $this->GetTruePath().$makeFile;
            $makeFile = preg_replace("/\/{1,}/", "/", $makeFile);
            $murl = $this->GetTrueUrl($murl);
            $this->dtp->SaveTo($makeFile);
        }
        if($startpage==1)
        {
            //����б����÷����ļ�����������ļ���һҳ
            if($this->TypeLink->TypeInfos['isdefault']==1
            && $this->TypeLink->TypeInfos['ispart']==0)
            {
                $onlyrule = $this->GetMakeFileRule($this->Fields['id'],"list",$this->Fields['typedir'],'',$this->Fields['namerule2']);
                $onlyrule = str_replace("{page}","1",$onlyrule);
                $list_1 = $this->GetTruePath().$onlyrule;
                $murl = MfTypedir($this->Fields['typedir']).'/'.$this->Fields['defaultname'];
                $indexname = $this->GetTruePath().$murl;
                copy($list_1,$indexname);
            }
        }
        return $murl;
    }

    /**
     *  ��ʾ�б�
     *
     * @access    public
     * @return    void
     */
    function Display()
    {
        if($this->TypeLink->TypeInfos['ispart']>0 && count($this->searchArr)==0 )
        {
            $this->DisplayPartTemplets();
            return ;
        }
        $this->CountRecord();
        /*
        if((empty($this->PageNo) || $this->PageNo==1) && $this->TypeLink->TypeInfos['ispart']==1)
        {
            $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
            $tempfile = str_replace("{tid}",$this->TypeID,$this->Fields['tempindex']);
            $tempfile = str_replace("{cid}",$this->ChannelUnit->ChannelInfos['nid'],$tempfile);
            $tempfile = $tmpdir."/".$tempfile;
            if(!file_exists($tempfile))
            {
                $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default_sg.htm";
            }
            $this->dtp->LoadTemplate($tempfile);
        }
        */
        $this->ParseTempletsFirst();
        $this->ParseDMFields($this->PageNo,0);
        $this->dtp->Display();
    }

    /**
     *  ��������ģ��ҳ��
     *
     * @access    public
     * @return    string
     */
    function MakePartTemplets()
    {
        $this->PartView = new PartView($this->TypeID,false);
        $this->PartView->SetTypeLink($this->TypeLink);
        $nmfa = 0;
        $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
        if($this->Fields['ispart']==1)
        {
            $tempfile = str_replace("{tid}",$this->TypeID,$this->Fields['tempindex']);
            $tempfile = str_replace("{cid}",$this->ChannelUnit->ChannelInfos['nid'],$tempfile);
            $tempfile = $tmpdir."/".$tempfile;
            if(!file_exists($tempfile))
            {
                $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default_sg.htm";
            }
            $this->PartView->SetTemplet($tempfile);
        }
        else if($this->Fields['ispart']==2)
        {
            //��ת��ַ
            return $this->Fields['typedir'];
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $makeUrl = $this->GetMakeFileRule($this->Fields['id'],"index",MfTypedir($this->Fields['typedir']),$this->Fields['defaultname'],$this->Fields['namerule2']);
        $makeUrl = preg_replace("/\/{1,}/", "/", $makeUrl);
        $makeFile = $this->GetTruePath().$makeUrl;
        if($nmfa==0)
        {
            $this->PartView->SaveToHtml($makeFile);
        }
        else
        {
            if(!file_exists($makeFile))
            {
                $this->PartView->SaveToHtml($makeFile);
            }
        }
        return $this->GetTrueUrl($makeUrl);
    }

    /**
     *  ��ʾ����ģ��ҳ��
     *
     * @access    public
     * @return    void
     */
    function DisplayPartTemplets()
    {
        $this->PartView = new PartView($this->TypeID,false);
        $this->PartView->SetTypeLink($this->TypeLink);
        $nmfa = 0;
        $tmpdir = $GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'];
        if($this->Fields['ispart']==1)
        {
            //����ģ��
            $tempfile = str_replace("{tid}",$this->TypeID,$this->Fields['tempindex']);
            $tempfile = str_replace("{cid}",$this->ChannelUnit->ChannelInfos['nid'],$tempfile);
            $tempfile = $tmpdir."/".$tempfile;
            if(!file_exists($tempfile))
            {
                $tempfile = $tmpdir."/".$GLOBALS['cfg_df_style']."/index_default_sg.htm";
            }
            $this->PartView->SetTemplet($tempfile);
        }
        else if($this->Fields['ispart']==2)
        {
            //��ת��ַ
            $gotourl = $this->Fields['typedir'];
            header("Location:$gotourl");
            exit();
        }
        CreateDir(MfTypedir($this->Fields['typedir']));
        $makeUrl = $this->GetMakeFileRule($this->Fields['id'],"index",MfTypedir($this->Fields['typedir']),$this->Fields['defaultname'],$this->Fields['namerule2']);
        $makeFile = $this->GetTruePath().$makeUrl;
        if($nmfa==0)
        {
            $this->PartView->Display();
        }
        else
        {
            if(!file_exists($makeFile))
            {
                $this->PartView->Display();
            }
            else
            {
                include($makeFile);
            }
        }
    }

    /**
     *  ���վ�����ʵ��·��
     *
     * @access    public
     * @return    string
     */
    function GetTruePath()
    {
        $truepath = $GLOBALS["cfg_basedir"];
        return $truepath;
    }

    /**
     *  �����ʵ����·��
     *
     * @access    public
     * @param     string  $nurl  ���ӵ�ַ
     * @return    string
     */
    function GetTrueUrl($nurl)
    {
        if(preg_match("/^http:\/\//", $nurl)) return $nurl;
        if($this->Fields['moresite']==1)
        {
            if($this->Fields['sitepath']!='')
            {
                $nurl = preg_replace("/^".$this->Fields['sitepath']."/", '', $nurl);
            }
            $nurl = $this->Fields['siteurl'].$nurl;
        }
        return $nurl;
    }

    /**
     *  ����ģ�壬�Թ̶��ı�ǽ��г�ʼ��ֵ
     *
     * @access    private
     * @return    void
     */
    function ParseTempletsFirst()
    {
        if(isset($this->TypeLink->TypeInfos['reid']))
        {
            $GLOBALS['envs']['reid'] = $this->TypeLink->TypeInfos['reid'];
        }
        $GLOBALS['envs']['channelid'] = $this->TypeLink->TypeInfos['channeltype'];
        $GLOBALS['envs']['typeid'] = $this->TypeID;
        $GLOBALS['envs']['cross'] = 1;
        MakeOneTag($this->dtp,$this);
    }

    /**
     *  ����ģ�壬��������ı䶯���и�ֵ
     *
     * @access    public
     * @param     int  $PageNo  ҳ��
     * @param     int  $ismake  �Ƿ����
     * @return    void
     */
    function ParseDMFields($PageNo, $ismake=1)
    {
        //�滻�ڶ�ҳ�������
        if(($PageNo>1 || strlen($this->Fields['content'])<10 ) && !$this->IsReplace)
        {
            $this->dtp->SourceString = str_replace('[cmsreplace]','display:none',$this->dtp->SourceString);
            $this->IsReplace = true;
        }
        foreach($this->dtp->CTags as $tagid=>$ctag)
        {
            if($ctag->GetName()=="list")
            {
                $limitstart = ($this->PageNo-1) * $this->PageSize;
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
                $ctag->GetAtt("listitem")=="" ? $listitem="index,pre,pageno,next,end,option" : $listitem=$ctag->GetAtt("listitem");
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
            else if($PageNo!=1 && $ctag->GetName()=='field' && $ctag->GetAtt('display')!='')
            {
                $this->dtp->Assign($tagid,'');
            }
        }
    }

    /**
     *  ���Ҫ�������ļ����ƹ���
     *
     * @access    public
     * @param     string  $typeid  ��ĿID
     * @param     string  $wname
     * @param     string  $typedir  ��ĿĿ¼
     * @param     string  $defaultname  Ĭ������
     * @param     string  $namerule2  ���ƹ���
     * @return    string
     */
    function GetMakeFileRule($typeid,$wname,$typedir,$defaultname,$namerule2)
    {
        $typedir = MfTypedir($typedir);
        if($wname=='index')
        {
            return $typedir.'/'.$defaultname;
        }
        else
        {
            $namerule2 = str_replace('{tid}',$typeid,$namerule2);
            $namerule2 = str_replace('{typedir}',$typedir,$namerule2);
            return $namerule2;
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
    function GetArcList($limitstart=0,$row=10,$col=1,$titlelen=30,$listtype="all",$orderby="default",$innertext="",$tablewidth="100",$ismake=1,$orderWay='desc')
    {
        global $cfg_list_son;
        $typeid=$this->TypeID;

        if($row=='') $row = 10;

        if($limitstart=='') $limitstart = 0;

        if($titlelen=='') $titlelen = 100;

        if($listtype=='') $listtype = "all";

        if($orderby=='') $orderby='id';
        else $orderby=strtolower($orderby);

        if($orderWay=='') $orderWay = 'desc';

        $tablewidth = str_replace("%", "", $tablewidth);
        if($tablewidth=='') $tablewidth=100;
        if($col=='') $col=1;
        $colWidth = ceil(100 / $col);
        $tablewidth = $tablewidth."%";
        $colWidth = $colWidth."%";

        $innertext = trim($innertext);
        if($innertext=='') $innertext = GetSysTemplets('list_sglist.htm');

        //����ʽ
        $ordersql = '';
        if($orderby=='senddate'||$orderby=='id')
        {
            $ordersql=" ORDER BY arc.aid $orderWay";
        }
        else if($orderby=='hot'||$orderby=='click')
        {
            $ordersql = " ORDER BY arc.click $orderWay";
        }
        else
        {
            $ordersql=" ORDER BY arc.aid $orderWay";
        }

        $addField = 'arc.'.join(',arc.',$this->ListFields);

        //�������Ĭ�ϵ�sortrank��id����ʹ�����ϲ�ѯ����������ʱ�ǳ�������
        if(preg_match('/hot|click/', $orderby) || $this->sAddTable)
        {
            $query = "SELECT tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,
            tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,arc.aid,arc.aid AS id,arc.typeid,
            $addField
            FROM `{$this->AddTable}` arc
            LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
            WHERE {$this->addSql} $ordersql LIMIT $limitstart,$row";
        }
        //��ͨ����ȴ�arctiny����ID��Ȼ��ID��ѯ���ٶȷǳ��죩
        else
        {
            $t1 = ExecTime();
            $ids = array();
            $nordersql = str_replace('.aid','.id',$ordersql);
            $query = "SELECT id From `#@__arctiny` arc WHERE {$this->addSql} $nordersql LIMIT $limitstart,$row ";

            $this->dsql->SetQuery($query);
            $this->dsql->Execute();
            while($arr=$this->dsql->GetArray())
            {
                $ids[] = $arr['id'];
            }
            $idstr = join(',',$ids);
            if($idstr=='')
            {
                return '';
            }
            else
            {
                $query = "SELECT tp.typedir,tp.typename,tp.isdefault,tp.defaultname,tp.namerule,tp.namerule2,
                tp.ispart,tp.moresite,tp.siteurl,tp.sitepath,arc.aid,arc.aid AS id,arc.typeid,
                       $addField
                       FROM `{$this->AddTable}` arc LEFT JOIN `#@__arctype` tp ON arc.typeid=tp.id
                       WHERE arc.aid IN($idstr) AND arc.arcrank >-1 $ordersql ";
            }
            $t2 = ExecTime();
            //echo $t2-$t1;
        }

        $this->dsql->SetQuery($query);
        $this->dsql->Execute('al');
        $t2 = ExecTime();

        //echo $t2-$t1;
        $artlist = '';
        $this->dtp2->LoadSource($innertext);
        $GLOBALS['autoindex'] = 0;
        for($i=0;$i<$row;$i++)
        {
            if($col>1)
            {
                $artlist .= "<div>\r\n";
            }
            for($j=0;$j<$col;$j++)
            {
                if($row = $this->dsql->GetArray("al"))
                {
                    $GLOBALS['autoindex']++;
                    $ids[$row['aid']] = $row['id']= $row['aid'];

                    //����һЩ�����ֶ�
                    $row['ismake'] = 1;
                    $row['money'] = 0;
                    $row['arcrank'] = 0;
                    $row['filename'] = '';
                    $row['filename'] = $row['arcurl'] = GetFileUrl($row['id'],$row['typeid'],$row['senddate'],$row['title'],$row['ismake'],
                                                       $row['arcrank'],$row['namerule'],$row['typedir'],$row['money'],$row['filename'],$row['moresite'],$row['siteurl'],$row['sitepath']);

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

                    $row['pubdate'] = $row['senddate'];

                    $row['stime'] = GetDateMK($row['pubdate']);

                    $row['typelink'] = "<a href='".$row['typeurl']."'>".$row['typename']."</a>";

                    $row['fulltitle'] = $row['title'];

                    $row['title'] = cn_substr($row['title'],$titlelen);

                    if(preg_match('/b/', $row['flag']))
                    {
                        $row['title'] = "<b>".$row['title']."</b>";
                    }

                    $row['textlink'] = "<a href='".$row['filename']."'>".$row['title']."</a>";

                    $row['plusurl'] = $row['phpurl'] = $GLOBALS['cfg_phpurl'];

                    $row['memberurl'] = $GLOBALS['cfg_memberurl'];

                    $row['templeturl'] = $GLOBALS['cfg_templeturl'];

                    //���븽�ӱ��������
                    foreach($row as $k=>$v) $row[strtolower($k)] = $v;

                    foreach($this->ChannelUnit->ChannelFields as $k=>$arr)
                    {
                         if(isset($row[$k]))
                         {
                              $row[$k] = $this->ChannelUnit->MakeField($k,$row[$k]);
                         }
                    }

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

        $t3 = ExecTime();

        //echo ($t3-$t2);
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
    function GetPageListST($list_len,$listitem="index,end,pre,next,pageno")
    {
        $prepage="";
        $nextpage="";
        $prepagenum = $this->PageNo-1;
        $nextpagenum = $this->PageNo+1;
        if($list_len=="" || preg_match("/[^0-9]/", $list_len))
        {
            $list_len=3;
        }
        $totalpage = ceil($this->TotalResult / $this->PageSize);
        if($totalpage <= 1 && $this->TotalResult > 0)
        {
            return "<span class=\"pageinfo\">�� <strong>1</strong>ҳ<strong>".$this->TotalResult."</strong>����¼</span>";
        }
        if($this->TotalResult == 0)
        {
            return "<span class=\"pageinfo\">�� <strong>0</strong>ҳ<strong>".$this->TotalResult."</strong>����¼</span>";
        }
        $purl = $this->GetCurUrl();
        $maininfo = "<span class=\"pageinfo\">�� <strong>{$totalpage}</strong>ҳ<strong>".$this->TotalResult."</strong>��</span>";
        $tnamerule = $this->GetMakeFileRule($this->Fields['id'], "list", $this->Fields['typedir'], $this->Fields['defaultname'], $this->Fields['namerule2']);
        $tnamerule = preg_replace("/^(.*)\//", '', $tnamerule);

        //�����һҳ����ҳ������
        if($this->PageNo != 1)
        {
            $prepage.="<li><a href='".str_replace("{page}", $prepagenum, $tnamerule)."'>��һҳ</a></li>\r\n";
            $indexpage="<li><a href='".str_replace("{page}", 1, $tnamerule)."'>��ҳ</a></li>\r\n";
        }
        else
        {
            $indexpage="<li>��ҳ</li>\r\n";
        }

        //��һҳ,δҳ������
        if($this->PageNo != $totalpage && $totalpage>1)
        {
            $nextpage.="<li><a href='".str_replace("{page}", $nextpagenum, $tnamerule)."'>��һҳ</a></li>\r\n";
            $endpage="<li><a href='".str_replace("{page}", $totalpage, $tnamerule)."'>ĩҳ</a></li>\r\n";
        }
        else
        {
            $endpage="<li>ĩҳ</li>";
        }

        //option����
        $optionlist = "";
        /*
        $optionlen = strlen($totalpage);
        $optionlen = $optionlen*10+18;
        $optionlist = "<li><select name='sldd' style='width:{$optionlen}px' onchange='location.href=this.options[this.selectedIndex].value;'>\r\n";
        for($mjj=1;$mjj<=$totalpage;$mjj++)
        {
            if($mjj==$this->PageNo)
            {
                $optionlist .= "<option value='".str_replace("{page}",$mjj,$tnamerule)."' selected>$mjj</option>\r\n";
            }
            else
            {
                $optionlist .= "<option value='".str_replace("{page}",$mjj,$tnamerule)."'>$mjj</option>\r\n";
            }
        }
        $optionlist .= "</select><li>";
        */

        //�����������
        $listdd = "";
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
        for($j; $j <= $total_list; $j++)
        {
            if($j == $this->PageNo)
            {
                $listdd.= "<li class=\"thisclass\">$j</li>\r\n";
            }
            else
            {
                $listdd.="<li><a href='".str_replace("{page}", $j, $tnamerule)."'>".$j."</a></li>\r\n";
            }
        }
        $plist = "";
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
        if(preg_match('/option/i', $listitem))
        {
            $plist .= $optionlist;
        }
        return $plist;
    }

    /**
     *  ��ȡ��̬�ķ�ҳ�б�
     *
     * @access    public
     * @param     int  $list_len  �б���
     * @param     string  $listitem  �б���ʽ
     * @return    string
     */
    function GetPageListDM($list_len,$listitem="index,end,pre,next,pageno")
    {
        global $nativeplace,$infotype,$keyword;
        if(empty($nativeplace)) $nativeplace = 0;
        if(empty($infotype)) $infotype = 0;
        if(empty($keyword)) $keyword = '';
        $prepage = $nextpage = '';
        $prepagenum = $this->PageNo - 1;
        $nextpagenum = $this->PageNo + 1;
        if($list_len=="" || preg_match("/[^0-9]/", $list_len))
        {
            $list_len=3;
        }
        $totalpage = ceil($this->TotalResult / $this->PageSize);
        if($totalpage<=1 && $this->TotalResult>0)
        {
            return "<span class=\"pageinfo\">��1ҳ/".$this->TotalResult."����¼</span>";
        }
        if($this->TotalResult == 0)
        {
            return "<span class=\"pageinfo\">��0ҳ/".$this->TotalResult."����¼</span>";
        }
        $purl = $this->GetCurUrl();
        $geturl = "tid=".$this->TypeID."&TotalResult=".$this->TotalResult."&nativeplace=$nativeplace&infotype=$infotype&keyword=".urlencode($keyword)."&";
        $hidenform = "<input type='hidden' name='tid' value='".$this->TypeID."' />\r\n";
        $hidenform = "<input type='hidden' name='nativeplace' value='$nativeplace' />\r\n";
        $hidenform = "<input type='hidden' name='infotype' value='$infotype' />\r\n";
        $hidenform = "<input type='hidden' name='keyword' value='$keyword' />\r\n";
        $hidenform .= "<input type='hidden' name='TotalResult' value='".$this->TotalResult."' />\r\n";
        $purl .= "?".$geturl;

        //�����һҳ����һҳ������
        if($this->PageNo != 1)
        {
            $prepage.="<li><a href='".$purl."PageNo=$prepagenum'>��һҳ</a></li>\r\n";
            $indexpage="<li><a href='".$purl."PageNo=1'>��ҳ</a></li>\r\n";
        }
        else
        {
            $indexpage="<li><a>��ҳ</a></li>\r\n";
        }
        if($this->PageNo!=$totalpage && $totalpage>1)
        {
            $nextpage.="<li><a href='".$purl."PageNo=$nextpagenum'>��һҳ</a></li>\r\n";
            $endpage="<li><a href='".$purl."PageNo=$totalpage'>ĩҳ</a></li>\r\n";
        }
        else
        {
            $endpage="<li><a>ĩҳ</a></li>";
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
        for($j; $j <= $total_list; $j++)
        {
            if($j == $this->PageNo)
            {
                $listdd.= "<li class=\"thisclass\"><a>$j</a></li>\r\n";
            }
            else
            {
                $listdd.="<li><a href='".$purl."PageNo=$j'>".$j."</a></li>\r\n";
            }
        }

        $plist = $indexpage.$prepage.$listdd.$nextpage.$endpage;
        return $plist;
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