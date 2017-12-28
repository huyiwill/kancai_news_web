<?php
/**
 * �ĵ��༭
 *
 * @version        $Id: article_edit.php 1 14:12 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('a_Edit,a_AccEdit,a_MyEdit');
require_once(DEDEINC."/customfields.func.php");
require_once(DEDEADMIN."/inc/inc_archives_functions.php");
if(file_exists(DEDEDATA.'/template.rand.php'))
{
    require_once(DEDEDATA.'/template.rand.php');
}
if(empty($dopost)) $dopost = '';


$aid = isset($aid) && is_numeric($aid) ? $aid : 0;
if($dopost!='save')
{
    require_once(DEDEADMIN."/inc/inc_catalog_options.php");
    require_once(DEDEINC."/dedetag.class.php");
    ClearMyAddon();

    //��ȡ�鵵��Ϣ
    $query = "SELECT ch.typename AS channelname,ar.membername AS rankname,arc.*
    FROM `#@__archives` arc
    LEFT JOIN `#@__channeltype` ch ON ch.id=arc.channel
    LEFT JOIN `#@__arcrank` ar ON ar.rank=arc.arcrank WHERE arc.id='$aid' ";
    $arcRow = $dsql->GetOne($query);
    if(!is_array($arcRow))
    {
        ShowMsg("��ȡ����������Ϣ����!","-1");
        exit();
    }
    $query = "SELECT * FROM `#@__channeltype` WHERE id='".$arcRow['channel']."'";
    $cInfos = $dsql->GetOne($query);
    if(!is_array($cInfos))
    {
        ShowMsg("��ȡƵ��������Ϣ����!","javascript:;");
        exit();
    }
    $addtable = $cInfos['addtable'];
    $addRow = $dsql->GetOne("SELECT * FROM `$addtable` WHERE aid='$aid'");
    if(!is_array($addRow))
    {
        ShowMsg("��ȡ������Ϣ����!","javascript:;");
        exit();
    }
    $channelid = $arcRow['channel'];
    $tags = GetTags($aid);
    include DedeInclude("templets/article_edit.htm");
    exit();
}
/*--------------------------------
function __save(){  }
-------------------------------*/
else if($dopost=='save')
{
    require_once(DEDEINC.'/image.func.php');
    require_once(DEDEINC.'/oxwindow.class.php');
    $flag = isset($flags) ? join(',',$flags) : '';
    $notpost = isset($notpost) && $notpost == 1 ? 1: 0;
    
    if(empty($typeid2)) $typeid2 = 0;
    if(!isset($autokey)) $autokey = 0;
    if(!isset($remote)) $remote = 0;
    if(!isset($dellink)) $dellink = 0;
    if(!isset($autolitpic)) $autolitpic = 0;
    
    if(empty($typeid))
    {
        ShowMsg("��ָ���ĵ�����Ŀ��", "-1");
        exit();
    }
    if(empty($channelid))
    {
        ShowMsg("�ĵ�Ϊ��ָ�������ͣ������㷢�����ݵı��Ƿ�Ϸ���", "-1");
        exit();
    }
    if(!CheckChannel($typeid, $channelid))
    {
        ShowMsg("����ѡ�����Ŀ�뵱ǰģ�Ͳ��������ѡ���ɫ��ѡ�", "-1");
        exit();
    }
    if(!TestPurview('a_Edit'))
    {
        if(TestPurview('a_AccEdit'))
        {
            CheckCatalog($typeid, "�Բ�����û�в�����Ŀ {$typeid} ���ĵ�Ȩ�ޣ�");
        }
        else
        {
            CheckArcAdmin($id, $cuserLogin->getUserID());
        }
    }


    //�Ա�������ݽ��д���
    $pubdate = GetMkTime($pubdate);
    $sortrank = AddDay($pubdate,$sortup);
    $ismake = $ishtml==0 ? -1 : 0;
    $autokey = 1;
    $title = htmlspecialchars(cn_substrR($title,$cfg_title_maxlen));
    $shorttitle = cn_substrR($shorttitle,36);
    $color =  cn_substrR($color,7);
    $writer =  cn_substrR($writer,20);
    $source = cn_substrR($source,30);
    $description = cn_substrR($description,250);
    $keywords = trim(cn_substrR($keywords,60));
    $filename = trim(cn_substrR($filename,40));
    $isremote  = (empty($isremote)? 0  : $isremote);
    $serviterm=empty($serviterm)? "" : $serviterm;
    if(!TestPurview('a_Check,a_AccCheck,a_MyCheck'))
    {
        $arcrank = -1;
    }
    $adminid = $cuserLogin->getUserID();

    //�����ϴ�������ͼ
    if(empty($ddisremote))
    {
        $ddisremote = 0;
    }
    $litpic = GetDDImage('none',$picname,$ddisremote);

    //����body�������
    $body = AnalyseHtmlBody($body,$description,$litpic,$keywords,'htmltext');

    //���������ӱ�����
    $inadd_f = '';
    $inadd_v = '';
    if(!empty($dede_addonfields))
    {
        $addonfields = explode(';',$dede_addonfields);
        $inadd_f = '';
        $inadd_v = '';
        if(is_array($addonfields))
        {
            foreach($addonfields as $v)
            {
                if($v=='')
                {
                    continue;
                }
                $vs = explode(',',$v);
                if($vs[1]=='htmltext'||$vs[1]=='textdata') //HTML�ı����⴦��
                {
                    ${$vs[0]} = AnalyseHtmlBody(${$vs[0]},$description,$litpic,$keywords,$vs[1]);
                }else
                {
                    if(!isset(${$vs[0]}))
                    {
                        ${$vs[0]} = '';
                    }
                    ${$vs[0]} = GetFieldValueA(${$vs[0]},$vs[1],$id);
                }
                $inadd_f .= ",`{$vs[0]}` = '".${$vs[0]}."'";
            }
        }
    }

    //����ͼƬ�ĵ����Զ�������
    if($litpic!='' && !preg_match("#p#", $flag))
    {
        $flag = ($flag=='' ? 'p' : $flag.',p');
    }
    if($redirecturl!='' && !preg_match("#j#", $flag))
    {
        $flag = ($flag=='' ? 'j' : $flag.',j');
    }

    //��ת��ַ���ĵ�ǿ��Ϊ��̬
    if(preg_match("#j#", $flag)) $ismake = -1;
    //�������ݿ��SQL���
    $query = "UPDATE #@__archives SET
    typeid='$typeid',
    typeid2='$typeid2',
    sortrank='$sortrank',
    flag='$flag',
    click='$click',
    ismake='$ismake',
    arcrank='$arcrank',
    money='$money',
    title='$title',
    color='$color',
    writer='$writer',
    source='$source',
    litpic='$litpic',
    pubdate='$pubdate',
    voteid='$voteid',
    notpost='$notpost',
    description='$description',
    keywords='$keywords',
    shorttitle='$shorttitle',
    filename='$filename',
    dutyadmin='$adminid',
    weight='$weight'
    WHERE id='$id'; ";

    if(!$dsql->ExecuteNoneQuery($query))
    {
        ShowMsg('�������ݿ�archives��ʱ��������',-1);
        exit();
    }
    
    $cts = $dsql->GetOne("SELECT addtable FROM `#@__channeltype` WHERE id='$channelid' ");
    $addtable = trim($cts['addtable']);
    if($addtable!='')
    {
        $useip = GetIP();
        $templet = empty($templet) ? '' : $templet;
        $iquery = "UPDATE `$addtable` SET typeid='$typeid',body='$body'{$inadd_f},redirecturl='$redirecturl',templet='$templet',userip='$useip' WHERE aid='$id'";
        if(!$dsql->ExecuteNoneQuery($iquery))
        {
            ShowMsg("���¸��ӱ� `$addtable`  ʱ��������ԭ��","javascript:;");
            exit();
        }
    }

    //����HTML
    UpIndexKey($id, $arcrank, $typeid, $sortrank, $tags);
    if($cfg_remote_site=='Y' && $isremote=="1")
    {    
        if($serviterm!=""){
            list($servurl, $servuser, $servpwd) = explode(',', $serviterm);
            $config=array( 'hostname' => $servurl, 'username' => $servuser, 
                                           'password' => $servpwd,'debug' => 'TRUE');
        } else {
            $config=array();
        }
        if(!$ftp->connect($config)) exit('Error:None FTP Connection!');
    }
    $artUrl = MakeArt($id,true,true,$isremote);
    if($artUrl=='')
    {
        $artUrl = $cfg_phpurl."/view.php?aid=$id";
    }
    ClearMyAddon($id, $title);
    
    //���سɹ���Ϣ
    $msg = "
    ������ѡ����ĺ���������
    <a href='article_add.php?cid=$typeid'><u>����������</u></a>
    &nbsp;&nbsp;
    <a href='archives_do.php?aid=".$id."&dopost=editArchives'><u>�鿴����</u></a>
    &nbsp;&nbsp;
    <a href='$artUrl' target='_blank'><u>�鿴����</u></a>
    &nbsp;&nbsp;
    <a href='catalog_do.php?cid=$typeid&dopost=listArchives'><u>��������</u></a>
    &nbsp;&nbsp;
    $backurl
    ";

    $wintitle = "�ɹ��������£�";
    $wecome_info = "���¹���::��������";
    $win = new OxWindow();
    $win->AddTitle("�ɹ��������£�");
    $win->AddMsgItem($msg);
    $winform = $win->GetWindow("hand","&nbsp;",false);
    $win->Display();
}