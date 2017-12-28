<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ��Ŀ��Ԫ,��Ҫ�û������̨����
 *
 * @version        $Id: typeunit.class.admin.php 1 15:21 2010��7��5��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
require_once(DEDEINC."/channelunit.func.php");

/**
 * ��Ŀ��Ԫ,��Ҫ�û������̨����
 *
 * @package          TypeUnit
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class TypeUnit
{
    var $dsql;
    var $artDir;
    var $baseDir;
    var $idCounter;
    var $idArrary;
    var $shortName;
    var $CatalogNums;

    //php5���캯��
    function __construct()
    {
        $this->idCounter = 0;
        $this->artDir = $GLOBALS['cfg_cmspath'].$GLOBALS['cfg_arcdir'];
        $this->baseDir = $GLOBALS['cfg_basedir'];
        $this->shortName = $GLOBALS['art_shortname'];
        $this->idArrary = '';
        $this->dsql = 0;
    }

    function TypeUnit()
    {
        $this->__construct();
    }

    //������
    function Close()
    {
    }

    //��ȡ������Ŀ���ĵ�ID��
    function UpdateCatalogNum()
    {
        $this->dsql->SetQuery("SELECT typeid,count(typeid) as dd FROM `#@__arctiny` WHERE arcrank <>-2 group by typeid");
        $this->dsql->Execute();
        while($row = $this->dsql->GetArray())
        {
            $this->CatalogNums[$row['typeid']] = $row['dd'];
        }
    }

    function GetTotalArc($tid)
    {
        if(!is_array($this->CatalogNums))
        {
            $this->UpdateCatalogNum();
        }
        if(!isset($this->CatalogNums[$tid]))
        {
            return 0;
        }
        else
        {
            $totalnum = 0;
            $ids = explode(',',GetSonIds($tid));
            foreach($ids as $tid)
            {
                if(isset($this->CatalogNums[$tid]))
                {
                    $totalnum += $this->CatalogNums[$tid];
                }
            }
            return $totalnum;
        }
    }

    /**
     *  �������з���,����Ŀ����ҳ(list_type)��ʹ��
     *
     * @access    public
     * @param     int   $channel  Ƶ��ID
     * @param     int   $nowdir  ��ǰ����ID
     * @return    string
     */
    function ListAllType($channel=0,$nowdir=0)
    {
        global $cfg_admin_channel, $admin_catalogs;
        $this->dsql = $GLOBALS['dsql'];
        
        //����û���Ȩ�޵Ķ�����Ŀ
        if($cfg_admin_channel=='array')
        {
            $admin_catalog = join(',', $admin_catalogs);
            $this->dsql->SetQuery("SELECT reid FROM `#@__arctype` WHERE id in($admin_catalog) group by reid ");
            $this->dsql->Execute();
            $topidstr = '';
            while($row = $this->dsql->GetObject())
            {
                if($row->reid==0) continue;
                $topidstr .= ($topidstr=='' ? $row->reid : ','.$row->reid);
            }
            $admin_catalog .= ','.$topidstr;
            $admin_catalogs = explode(',', $admin_catalog);
            $admin_catalogs = array_unique($admin_catalogs);
        }

        $this->dsql->SetQuery("SELECT id,typedir,typename,ispart,sortrank,ishidden FROM `#@__arctype` WHERE reid=0 order by sortrank");
        $this->dsql->Execute(0);
        while($row = $this->dsql->GetObject(0))
        {
            if( $cfg_admin_channel=='array' && !in_array($row->id, $admin_catalogs) )
            {
                continue;
            }
            $typeDir = $row->typedir;
            $typeName = $row->typename;
            $ispart = $row->ispart;
            $id = $row->id;
            $rank = $row->sortrank;
            if($row->ishidden=='1')
            {
                $nss = "<font color='red'>[��]</font>";
            }
            else
            {
                $nss = '';
            }
            echo "<table width='100%' border='0' cellspacing='0' cellpadding='2'>\r\n";
            //��ͨ�б�
            if($ispart==0)
            {
                echo "  <tr>\r\n";
                echo "  <td style='background-color:#FBFCE2;' width='2%' class='bline'><img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'></td>\r\n";
                echo "  <td style='background-color:#FBFCE2;' class='bline'><table width='98%' border='0' cellspacing='0' cellpadding='0'><tr><td width='50%'><input class='np' type='checkbox' name='tids[]' value='{$id}'><a href='catalog_do.php?cid=".$id."&dopost=listArchives' oncontextmenu=\"CommonMenu(event,this,$id,'".urlencode($typeName)."')\">{$nss}".$typeName."[ID:".$id."]</a>(�ĵ���".$this->GetTotalArc($id).")  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"><img src='images/write2.gif'/></a>";
                echo "    </td><td align='right'>";
                echo "<a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank'>Ԥ��</a>";
                echo "|<a href='catalog_do.php?cid={$id}&dopost=listArchives'>����</a>";
                echo "|<a href='catalog_add.php?id={$id}'>��������</a>";
                echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
            }
            //�������Ƶ��
            else if($ispart==1)
            {
                echo "  <tr >\r\n";
                echo "  <td style='background-color:#FBFCE2;'  width='2%' class='bline'><img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'></td>\r\n";
                echo "  <td style='background-color:#FBFCE2;' class='bline'><table width='98%' border='0' cellspacing='0' cellpadding='0'><tr><td width='50%'><input class='np' type='checkbox' name='tids[]' value='{$id}'><a href='catalog_do.php?cid=".$id."&dopost=listArchives' oncontextmenu=\"CommonMenuPart(event,this,$id,'".urlencode($typeName)."')\">{$nss}".$typeName."[ID:".$id."]</a>  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"> <img src='images/write2.gif'/> </a>";
                echo "    </td><td align='right'>";
                echo "<a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank'>Ԥ��</a>";
                echo "|<a href='catalog_do.php?cid={$id}&dopost=listArchives'>����</a>";
                echo "|<a href='catalog_add.php?id={$id}'>��������</a>";
                echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
            }
            //����ҳ��
            else if($ispart==2)
            {
                echo "  <tr height='24' bgcolor='#FBFCE2'>\r\n";
                echo "  <td width='2%' class='bline2'><img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'></td>\r\n";
                echo "  <td class='bline2'><table width='98%' border='0' cellspacing='0' cellpadding='0'><tr><td width='50%'><input class='np' type='checkbox' name='tids[]' value='{$id}'><a href='catalog_edit.php?id=".$id."' oncontextmenu=\"SingleMenu(event,this,$id,'".urlencode($typeName)."')\">{$nss}".$typeName."[ID:".$id."]</a>  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"><img src='images/write2.gif'/></a>";
                echo "    </td><td align='right'>";
                echo "<a href='{$typeDir}' target='_blank'>Ԥ��</a>";
                echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
            }
            echo "  <tr><td colspan='2' id='suns".$id."'>";
            $lastid = GetCookie('lastCid');
            if($channel==$id || $lastid==$id || isset($GLOBALS['exallct']) || $cfg_admin_channel=='array')
            {
                echo "    <table width='100%' border='0' cellspacing='0' cellpadding='0'>\r\n";
                $this->LogicListAllSunType($id,"��");
                echo "    </table>\r\n";
            }
            echo "</td></tr>\r\n</table>\r\n";
        }
    }

    /**
     *  �������Ŀ�ĵݹ����
     *
     * @access    public
     * @param     int  $id  ��ĿID
     * @param     string  $step  �㼶��־
     * @return    void
     */
    function LogicListAllSunType($id, $step)
    {
        global $cfg_admin_channel, $admin_catalogs;
        $fid = $id;
        $this->dsql->SetQuery("SELECT id,reid,typedir,typename,ispart,sortrank,ishidden FROM `#@__arctype` WHERE reid='".$id."' order by sortrank");
        $this->dsql->Execute($fid);
        if($this->dsql->GetTotalRow($fid)>0)
        {
            while($row = $this->dsql->GetObject($fid))
            {
                if($cfg_admin_channel=='array' && !in_array($row->id, $admin_catalogs) )
                {
                    continue;
                }
                $typeDir = $row->typedir;
                $typeName = $row->typename;
                $reid = $row->reid;
                $id = $row->id;
                $ispart = $row->ispart;
                if($step=="��")
                {
                    $stepdd = 2;
                }
                else
                {
                    $stepdd = 3;
                }
                $rank = $row->sortrank;
                if($row->ishidden=='1')
                {
                    $nss = "<font color='red'>[��]</font>";
                }
                else
                {
                    $nss = '';
                }

                //��ͨ�б�
                if($ispart==0)
                {
                    echo "<tr height='24' oncontextmenu=\"CommonMenu(event,this,$id,'".urlencode($typeName)."')\">\r\n";
                    echo "<td class='nbline'>";
                    echo "<table width='98%' border='0' cellspacing='0' cellpadding='0'>";
                    echo "<tr onMouseMove=\"javascript:this.bgColor='#FAFCE0';\" onMouseOut=\"javascript:this.bgColor='#FFFFFF';\"><td width='50%'>";
                    echo "<input class='np' type='checkbox' name='tids[]' value='{$id}'>$step <img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'> <a href='catalog_do.php?cid=".$id."&dopost=listArchives'>{$nss}".$typeName."[ID:".$id."]</a>(�ĵ���".$this->GetTotalArc($id).")  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"><img src='images/write2.gif'/></a>";
                    echo "</td><td align='right'>";
                    echo "<a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank'>Ԥ��</a>";
                    echo "|<a href='catalog_do.php?cid={$id}&dopost=listArchives'>����</a>";
                    echo "|<a href='catalog_add.php?id={$id}'>��������</a>";
                    echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                    echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                    echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                    echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
                }

                //����Ƶ��
                else if($ispart==1)
                {
                    echo " <tr height='24' oncontextmenu=\"CommonMenu(event,this,$id,'".urlencode($typeName)."')\">\r\n";
                    echo "<td class='nbline'><table width='98%' border='0' cellspacing='0' cellpadding='0'><tr onMouseMove=\"javascript:this.bgColor='#FAFCE0';\" onMouseOut=\"javascript:this.bgColor='#FFFFFF';\"><td width='50%'>";
                    echo "<input class='np' type='checkbox' name='tids[]' value='{$id}'>$step <img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'> <a href='catalog_do.php?cid=".$id."&dopost=listArchives'>{$nss}".$typeName."[ID:".$id."]</a>  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"><img src='images/write2.gif'/></a>";
                    echo "</td><td align='right'>";
                    echo "<a href='{$GLOBALS['cfg_phpurl']}/list.php?tid={$id}' target='_blank'>Ԥ��</a>";
                    echo "|<a href='catalog_do.php?cid={$id}&dopost=listArchives'>����</a>";
                    echo "|<a href='catalog_add.php?id={$id}'>��������</a>";
                    echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                    echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                    echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                    echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
                }

                //����ҳ��
                else if($ispart==2)
                {
                    echo "<tr height='24' oncontextmenu=\"SingleMenu(event,this,$id,'".urlencode($typeName)."')\">\r\n";
                    echo "<td class='bline2'><table width='98%' border='0' cellspacing='0' cellpadding='0'>";
                    echo "<tr onMouseMove=\"javascript:this.bgColor='#FAFCE0';\" onMouseOut=\"javascript:this.bgColor='#FFFFFF';\"><td width='50%'>";
                    echo "<input class='np' type='checkbox' name='tids[]' value='{$id}'>$step <img style='cursor:pointer' id='img".$id."' onClick=\"LoadSuns('suns".$id."',$id);\" src='images/dedeexplode.gif' width='11' height='11'> <a href='catalog_do.php?cid=".$id."&dopost=listArchives'>{$nss}".$typeName."[ID:".$id."]</a>  <a onclick=\"AlertMsg('��ݱ༭����','$id');\" href=\"javascript:;\"><img src='images/write2.gif'/></a>";
                    echo "</td><td align='right'>";
                    echo "<a href='{$typeDir}' target='_blank'>Ԥ��</a>";
                    echo "|<a href='catalog_edit.php?id={$id}'>����</a>";
                    echo "|<a href='catalog_do.php?dopost=moveCatalog&typeid={$id}'>�ƶ�</a>";
                    echo "|<a href='catalog_del.php?id={$id}&typeoldname=".urlencode($typeName)."'>ɾ��</a>";
                    echo "&nbsp; <input type='text' name='sortrank{$id}' value='{$rank}' style='width:25px;height:20px'></td></tr></table></td></tr>\r\n";
                }
                echo "  <tr><td id='suns".$id."' style='display:none'><table width='100%' border='0' cellspacing='0' cellpadding='0'>";
                $this->LogicListAllSunType($id,$step."��");
                echo "</table></td></tr>\r\n";
            }
        }
    }

    /**
     *  ������ĳ��Ŀ��ص��¼�Ŀ¼����ĿID�б�(ɾ����Ŀ������ʱ����)
     *
     * @access    public
     * @param     int   $id  ��ĿID
     * @param     int   $channel  Ƶ��ID
     * @return    array
     */
    function GetSunTypes($id, $channel=0)
    {
        $this->dsql = $GLOBALS['dsql'];
        $this->idArray[$this->idCounter]=$id;
        $this->idCounter++;
        $fid = $id;
        if($channel!=0)
        {
            $csql = " And channeltype=$channel ";
        }
        else
        {
            $csql = "";
        }
        $this->dsql->SetQuery("SELECT id FROM `#@__arctype` WHERE reid=$id $csql");
        $this->dsql->Execute("gs".$fid);

        //if($this->dsql->GetTotalRow("gs".$fid)!=0)
        //{
        while($row=$this->dsql->GetObject("gs".$fid))
        {
            $nid = $row->id;
            $this->GetSunTypes($nid,$channel);
        }
        //}
        return $this->idArray;
    }

    /**
     *  ɾ����Ŀ
     *
     * @access    public
     * @param     int   $id  ��ĿID
     * @param     bool   $isDelFile  �Ƿ�ɾ���ļ�
     * @return    string
     */
    function DelType($id, $isDelFile)
    {
        $this->idCounter = 0;
        $this->idArray = "";
        $this->GetSunTypes($id);
        $query = "
        SELECT #@__arctype.*,#@__channeltype.typename AS ctypename,
        #@__channeltype.addtable
        FROM `#@__arctype` LEFT JOIN #@__channeltype
        ON #@__channeltype.id=#@__arctype.channeltype
        WHERE #@__arctype.id='$id'
        ";
        $typeinfos = $this->dsql->GetOne($query);
        $topinfos = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__arctype` WHERE id='".$typeinfos['topid']."'");
        if(!is_array($typeinfos))
        {
            return FALSE;
        }
        $indir = $typeinfos['typedir'];
        $addtable = $typeinfos['addtable'];
        $ispart = $typeinfos['ispart'];
        $defaultname = $typeinfos['defaultname'];

        //ɾ�����ݿ������ؼ�¼
        foreach($this->idArray as $id)
        {
            $myrow = $this->dsql->GetOne("SELECT * FROM `#@__arctype` WHERE id='$id'");
            if($myrow['topid']>0)
            {
                $mytoprow = $this->dsql->GetOne("SELECT moresite,siteurl FROM `#@__arctype` WHERE id='".$myrow['topid']."'");
                if(is_array($mytoprow) && !empty($mytoprow))
                {
                    foreach($mytoprow as $k=>$v)
                    {
                        if(!preg_match("/[0-9]/",$k))
                        {
                            $myrow[$k] = $v;
                        }
                    }
                }
            }

            //ɾ��Ŀ¼��Ŀ¼��������ļ� ### ��ֹ�˴˹���
            //ɾ������ҳ��
            if($myrow['ispart']==2 && $myrow['typedir']=='')
            {
                if( is_file($this->baseDir.'/'.$myrow['defaultname']) )
                {
                    @unlink($this->baseDir.'/'.$myrow['defaultname']);
                }
            }

            //ɾ�����ݿ���Ϣ
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__arctype` WHERE id='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__arctiny` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__archives` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__spec` WHERE typeid='$id'");
            $this->dsql->ExecuteNoneQuery("DELETE FROM `#@__feedback` WHERE typeid='$id'");
            if($addtable!="")
            {
                $this->dsql->ExecuteNoneQuery("DELETE FROM $addtable WHERE typeid='$id'");
            }
        }

        //ɾ��Ŀ¼��Ŀ¼��������ļ� ### ��ֹ�˴˹���
        //ɾ������ҳ��
        if($ispart==2 && $indir=="")
        {
            if( is_file($this->baseDir."/".$defaultname) )
            {
                @unlink($this->baseDir."/".$defaultname);
            }
        }
        @reset($this->idArray);
        $this->idCounter = 0;
        return TRUE;
    }

    /**
     *  ɾ��ָ��Ŀ¼�������ļ�
     *
     * @access    public
     * @param     string  $indir  ָ��Ŀ¼
     * @return    int
     */
    function RmDirFile($indir)
    {
        if(!file_exists($indir)) return;
        $dh = dir($indir);
        while($file = $dh->read())
        {
            if($file == "." || $file == "..")
            {
                continue;
            }
            else if(is_file("$indir/$file"))
            {
                @unlink("$indir/$file");
            }
            else
            {
                $this->RmDirFile("$indir/$file");
            }
            if(is_dir("$indir/$file"))
            {
                @rmdir("$indir/$file");
            }
        }
        $dh->close();
        return(1);
    }
}//End Class