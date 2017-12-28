<?php
/**
 *
 * ����
 *
 * @version        $Id: download.php 1 15:38 2010��7��8��Z tianya $
 * @package        DedeCMS.Site
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/../include/common.inc.php");
require_once(DEDEINC."/channelunit.class.php");
if(!isset($open)) $open = 0;

//��ȡ�����б�
if($open==0)
{
    $aid = (isset($aid) && is_numeric($aid)) ? $aid : 0;
    if($aid==0) exit(' Request Error! ');

    $arcRow = GetOneArchive($aid);
    if($arcRow['aid']=='')
    {
        ShowMsg('�޷���ȡδ֪�ĵ�����Ϣ!','-1');
        exit();
    }
    extract($arcRow, EXTR_SKIP);

    $cu = new ChannelUnit($arcRow['channel'],$aid);
    if(!is_array($cu->ChannelFields))
    {
        ShowMsg('��ȡ�ĵ���Ϣʧ�ܣ�','-1');
        exit();
    }

    $vname = '';
    foreach($cu->ChannelFields as $k=>$v)
    {
        if($v['type']=='softlinks'){ $vname=$k; break; }
    }
    $row = $dsql->GetOne("SELECT $vname FROM `".$cu->ChannelInfos['addtable']."` WHERE aid='$aid'");

    include_once(DEDEINC.'/taglib/channel/softlinks.lib.php');
    $ctag = '';
    $downlinks = ch_softlinks($row[$vname], $ctag, $cu, '', TRUE);

    require_once(DEDETEMPLATE.'/plus/download_links_templet.htm');
    exit();
}
/*------------------------
//�ṩ������û�����(��ģʽ)
function getSoft_old()
------------------------*/
else if($open==1)
{
    //�������ش���
    $id = isset($id) && is_numeric($id) ? $id : 0;
    $link = base64_decode(urldecode($link));
    $hash = md5($link);
    $rs = $dsql->ExecuteNoneQuery2("UPDATE `#@__downloads` SET downloads = downloads + 1 WHERE hash='$hash' ");
    if($rs <= 0)
    {
        $query = " INSERT INTO `#@__downloads`(`hash`,`id`,`downloads`) VALUES('$hash','$id',1); ";
        $dsql->ExecNoneQuery($query);
    }
    header("location:$link");
    exit();
}
/*------------------------
//�ṩ������û�����(��ģʽ)
function getSoft_new()
------------------------*/
else if($open==2)
{
    $id = intval($id);
    //��ø��ӱ���Ϣ
    $row = $dsql->GetOne("SELECT ch.addtable,arc.mid FROM `#@__arctiny` arc LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel WHERE arc.id='$id' ");
    if(empty($row['addtable']))
    {
        ShowMsg('�Ҳ�������Ҫ�������Դ��', 'javascript:;');
        exit();
    }
    $mid = $row['mid'];
    
    //��ȡ�����б�����Ȩ����Ϣ
    $row = $dsql->GetOne("SELECT softlinks,daccess,needmoney FROM `{$row['addtable']}` WHERE aid='$id' ");
    if(empty($row['softlinks']))
    {
        ShowMsg('�Ҳ�������Ҫ�������Դ��', 'javascript:;');
        exit();
    }
    $softconfig = $dsql->GetOne("SELECT * FROM `#@__softconfig` ");
    $needRank = $softconfig['dfrank'];
    $needMoney = $softconfig['dfywboy'];
    if($softconfig['argrange']==0)
    {
        $needRank = $row['daccess'];
        $needMoney = $row['needmoney'];
    }
    
    //���������б�
    require_once(DEDEINC.'/dedetag.class.php');
    $softUrl = '';
    $islocal = 0;
    $dtp = new DedeTagParse();
    $dtp->LoadSource($row['softlinks']);
    if( !is_array($dtp->CTags) )
    {
        $dtp->Clear();
        ShowMsg('�Ҳ�������Ҫ�������Դ��', 'javascript:;');
        exit();
    }
    foreach($dtp->CTags as $ctag)
    {
        if($ctag->GetName()=='link')
        {
            $link = trim($ctag->GetInnerText());
            $islocal = $ctag->GetAtt('islocal');
            //������������
            if(!isset($firstLink) && $islocal==1) $firstLink = $link;
            if($islocal==1 && $softconfig['islocal'] != 1) continue;
            
            //֧��http,Ѹ������,ftp,flashget
            if(!preg_match("#^http:\/\/|^thunder:\/\/|^ftp:\/\/|^flashget:\/\/#i", $link))
            {
                 $link = $cfg_mainsite.$link;
            }
            $dbhash = substr(md5($link), 0, 24);
            if($uhash==$dbhash) $softUrl = $link;
        }
    }
    $dtp->Clear();
    if($softUrl=='' && $softconfig['ismoresite']==1 
    && $softconfig['moresitedo']==1 && trim($softconfig['sites'])!='' && isset($firstLink))
    {
        $firstLink = preg_replace("#http:\/\/([^\/]*)\/#i", '/', $firstLink);
        $softconfig['sites'] = preg_replace("#[\r\n]{1,}#", "\n", $softconfig['sites']);
        $sites = explode("\n", trim($softconfig['sites']));
        foreach($sites as $site)
        {
            if(trim($site)=='') continue;
            list($link, $serverName) = explode('|', $site);
            $link = trim( preg_replace("#\/$#", "", $link) ).$firstLink;
            $dbhash = substr(md5($link), 0, 24);
            if($uhash == $dbhash) $softUrl = $link;
        }
    }
    if( $softUrl == '' )
    {
        ShowMsg('�Ҳ�������Ҫ�������Դ��', 'javascript:;');
        exit();
    }
    //-------------------------
    // ��ȡ�ĵ���Ϣ���ж�Ȩ��
    //-------------------------
    $arcRow = GetOneArchive($id);
    if($arcRow['aid']=='')
    {
        ShowMsg('�޷���ȡδ֪�ĵ�����Ϣ!','-1');
        exit();
    }
    extract($arcRow, EXTR_SKIP);

    //������Ҫ����Ȩ�޵����
    if($needRank>0 || $needMoney>0)
    {
        require_once(DEDEINC.'/memberlogin.class.php');
        $cfg_ml = new MemberLogin();
        $arclink = $arcurl;
        $arctitle = $title;
        $arcLinktitle = "<a href=\"{$arcurl}\"><u>".$arctitle."</u></a>";
        $pubdate = GetDateTimeMk($pubdate);
    
        //��Ա������
        if(($needRank>1 && $cfg_ml->M_Rank < $needRank && $mid != $cfg_ml->M_ID))
        {
            $dsql->Execute('me' , "SELECT * FROM `#@__arcrank` ");
            while($row = $dsql->GetObject('me'))
            {
                $memberTypes[$row->rank] = $row->membername;
            }
            $memberTypes[0] = "�ο�";
            $msgtitle = "��û��Ȩ�����������{$arctitle}��";
            $moremsg = "��������Ҫ <font color='red'>".$memberTypes[$needRank]."</font> �������أ���Ŀǰ�ǣ�<font color='red'>".$memberTypes[$cfg_ml->M_Rank]."</font> ��";
            include_once(DEDETEMPLATE.'/plus/view_msg.htm');
            exit();
        }

        //����Ϊ����������Զ��۵���
        //���������Ҫ��ң�����û��Ƿ���������ĵ�
        if($needMoney > 0  && $mid != $cfg_ml->M_ID)
        {
            $sql = "SELECT aid,money FROM `#@__member_operation` WHERE buyid='ARCHIVE".$id."' AND mid='".$cfg_ml->M_ID."'";
            $row = $dsql->GetOne($sql);
            //δ�����������
            if( !is_array($row) )
            {
                //û���㹻�Ľ��
                if( $needMoney > $cfg_ml->M_Money || $cfg_ml->M_Money=='')
                {
                    $msgtitle = "��û��Ȩ�����������{$arctitle}��";
                    $moremsg = "��������Ҫ <font color='red'>".$needMoney." ���</font> �������أ���Ŀǰӵ�н�ң�<font color='red'>".$cfg_ml->M_Money." ��</font> ��";
                    include_once(DEDETEMPLATE.'/plus/view_msg.htm');
                    exit(0);
                }
                //���㹻��ң���¼�û���Ϣ
                $inquery = "INSERT INTO `#@__member_operation`(mid,oldinfo,money,mtime,buyid,product,pname,sta)
                  VALUES ('".$cfg_ml->M_ID."','$arctitle','$needMoney','".time()."', 'ARCHIVE".$id."', 'archive','�������', 2); ";
                //��¼����
                if( !$dsql->ExecuteNoneQuery($inquery) )
                {
                    ShowMsg('��¼����ʧ��, �뷵��', '-1');
                    exit(0);
                }
                //�۳����
                $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET money = money - $needMoney WHERE mid='".$cfg_ml->M_ID."'");
            }
        }
    }
    //�������ش���
    $hash = md5($softUrl);
    $rs = $dsql->ExecuteNoneQuery2("UPDATE `#@__downloads` SET downloads = downloads+1 WHERE hash='$hash' ");
    if($rs <= 0)
    {
        $query = " INSERT INTO `#@__downloads`(`hash`, `id`, `downloads`) VALUES('$hash', '$id', 1); ";
        $dsql->ExecNoneQuery($query);
    }
    header("location:{$softUrl}");
    exit();
}//opentype=2