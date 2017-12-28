<?php
/**
 * ����ѡ�����
 *
 * @version        $Id: stepselect_main.php 2 13:23 2011-3-24 tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('c_Stepselect');
require_once(DEDEINC."/datalistcp.class.php");
require_once(DEDEINC.'/enums.func.php');
/*-----------------
ǰ̨��ͼ
function __show() { }
------------------*/
$ENV_GOBACK_URL = (isset($ENV_GOBACK_URL) ? $ENV_GOBACK_URL : 'stepselect_main.php');
if(empty($action))
{
    setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
    if(!isset($egroup)) $egroup = '';
    if(!isset($topvalue)) $topvalue = 0;
    $etypes = array();
    $egroups = array();
    $dsql->Execute('me','SELECT * FROM `#@__stepselect` ORDER BY id DESC');
    while($arr = $dsql->GetArray())
    {
        $etypes[] = $arr;
        $egroups[$arr['egroup']] = $arr['itemname'];
    }

    if($egroup!='')
    {
        $orderby = 'ORDER BY disorder ASC, evalue ASC';
        if(!empty($topvalue))
        {
            // �ж��Ƿ�Ϊ1������
            if ($topvalue % 500 == 0)
            {
                $egroupsql = " WHERE egroup LIKE '$egroup' AND evalue>=$topvalue AND evalue < ".($topvalue + 500);
            } else { 
                $egroupsql = " WHERE (evalue LIKE '$topvalue.%%%' OR evalue=$topvalue) AND egroup LIKE '$egroup'";
            }
        }
        else
        {
            $egroupsql = " WHERE egroup LIKE '$egroup' ";
        }
        $sql = "SELECT * FROM `#@__sys_enum` $egroupsql $orderby";
    } else {
        $egroupsql = '';
        $sql = "SELECT * FROM `#@__stepselect` ORDER BY id DESC";
    }
    //echo $sql;exit;
    $dlist = new DataListCP();
    $dlist->SetParameter('egroup',$egroup);
    $dlist->SetParameter('topvalue',$topvalue);
    $dlist->SetTemplet(DEDEADMIN."/templets/stepselect_main.htm");
    $dlist->SetSource($sql);
    $dlist->display();
    exit();
}
else if($action=='edit' || $action=='addnew' || $action=='addenum' || $action=='view')
{
    AjaxHead();
    include('./templets/stepselect_showajax.htm');
    exit();
}
/*-----------------
ɾ�����ͻ�ö��ֵ
function __del() { }
------------------*/
else if($action=='del')
{
    $arr = $dsql->GetOne("SELECT * FROM `#@__stepselect` WHERE id='$id' ");
    if(!is_array($arr))
    {
        ShowMsg("�޷���ȡ������Ϣ�����������������", "stepselect_main.php?".ExecTime());
        exit();
    }
    if($arr['issystem']==1)
    {
        ShowMsg("ϵͳ���õ�ö�ٷ��಻��ɾ����", "stepselect_main.php?".ExecTime());
        exit();
    }
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__stepselect` WHERE id='$id'; ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE egroup='{$arr['egroup']}'; ");
    ShowMsg("�ɹ�ɾ��һ�����࣡", "stepselect_main.php?".ExecTime());
    exit();
}
else if($action=='delenumAllSel')
{
    if(isset($ids) && is_array($ids))
    {
        $id = join(',', $ids);

        $groups = array();
        $dsql->Execute('me', "SELECT egroup FROM `#@__sys_enum` WHERE id IN($id) GROUP BY egroup");
        while($row = $dsql->GetArray('me'))
        {
            $groups[] = $row['egroup'];
        }

        $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE id IN($id); ");

        //���»���
        foreach($groups as $egropu) 
        {
            WriteEnumsCache($egroup);
        }

        ShowMsg("�ɹ�ɾ��ѡ�е�ö�ٷ��࣡", $ENV_GOBACK_URL);
    }
    else
    {
        ShowMsg("��ûѡ���κη��࣡", "-1");
    }
    exit();
}
else if($action=='delenum')
{
    $row = $dsql->GetOne("SELECT egroup FROM `#@__sys_enum` WHERE id = '$id' ");
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE id='{$id}'; ");
    WriteEnumsCache($row['egroup']);
    ShowMsg("�ɹ�ɾ��һ��ö�٣�", $ENV_GOBACK_URL);
    exit();
}
/*-----------------
���������޸�
function __edit_save() { }
------------------*/
else if($action=='edit_save')
{
    if(preg_match("#[^0-9a-z_-]#i", $egroup))
    {
        ShowMsg("�����Ʋ�����ȫ���ַ���������ţ�","-1");
        exit();
    }
    $dsql->ExecuteNoneQuery("UPDATE `#@__stepselect` SET `itemname`='$itemname',`egroup`='$egroup' WHERE id='$id'; ");
    ShowMsg("�ɹ��޸�һ�����࣡", "stepselect_main.php?".ExecTime());
    exit();
}
/*-----------------
����������
function __addnew_save() { }
------------------*/
else if($action=='addnew_save')
{
    if(preg_match("#[^0-9a-z_-]#i", $egroup))
    {
        ShowMsg("�����Ʋ�����ȫ���ַ���������ţ�", "-1");
        exit();
    }
    $arr = $dsql->GetOne("SELECT * FROM `#@__stepselect` WHERE itemname LIKE '$itemname' OR egroup LIKE '$egroup' ");
    if(is_array($arr))
    {
        ShowMsg("��ָ����������ƻ��������Ѿ����ڣ�����ʹ�ã�","stepselect_main.php");
        exit();
    }
    $dsql->ExecuteNoneQuery("INSERT INTO `#@__stepselect`(`itemname`,`egroup`,`issign`,`issystem`) VALUES('$itemname','$egroup','0','0'); ");
    WriteEnumsCache($egroup);
    ShowMsg("�ɹ����һ�����࣡","stepselect_main.php?egroup=$egroup");
    exit();
}
/*---------
�Ѿɰ�ȫ��ʡ�б��滻��ǰ��������
function __exarea() { }
----------*/
else if($action=='exarea')
{
    $bigtypes = array();
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_enum` WHERE egroup='nativeplace'; ");
    $query = "SELECT * FROM `#@__area` WHERE reid =0 order by id asc";
    $dsql->Execute('me', $query);
    $n = 1;
    while($row = $dsql->GetArray())
    {
        $bigtypes[$row['id']] = $evalue = $disorder = $n * 500;
        $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`)
                                 VALUES('{$row['name']}','$evalue','nativeplace','$disorder','0'); ");
        $n++;                                
    }
    $stypes = array();
    foreach($bigtypes as $k=>$v)
    {
        $query = "SELECT * FROM `#@__area` WHERE reid=$k order by id asc";
        $dsql->Execute('me', $query);
        $n = 1;
        while($row = $dsql->GetArray())
        {
            $stypes[$row['id']] = $evalue = $disorder = $v + $n;
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`)
                                   VALUES('{$row['name']}','$evalue','nativeplace','$disorder','0'); ");
            $n++; 
        }
    }
    WriteEnumsCache('nativeplace');
    ShowMsg("�ɹ��������оɵĵ������ݣ�", "stepselect_main.php?egroup=nativeplace");
    exit();
}
/*--------------------
function __addenum_save() { }
���ڶ���ö�ٵ�˵����Ϊ�˽�ʡ��ѯ�ٶȣ�����ö����ͨ�������㷨���ɵģ�ԭ��Ϊ
�����ܱ� 500 �����Ķ���һ��ö�٣�(500 * n) + 1 < em < 500 * (n+1) Ϊ�¼�ö��
�磺1000 ���¼�ö�ٶ�Ӧ��ֵΪ 1001,1002,1003...1499
���� issign=1 �ģ���ʾ������ֻ��һ��ö�٣�����������㷨����
------------------------------------------------------------------------
�����㷨:
��������ö�������"-N"�Լ����ѡ��,����:
1001����ö�������3����Ŀ,��Ϊ1001-1,1001-2...
��ʱ����Ҫissign=2
---------------------*/
else if($action=='addenum_save')
{
    if(empty($ename) || empty($egroup)) 
    {
         Showmsg("������ƻ������Ʋ���Ϊ�գ�","-1");
         exit();
    }
    if($issign == 1 || $topvalue == 0)
    {
        $enames = explode(',', $ename);
        foreach($enames as $ename)
        {
            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup='$egroup' AND (evalue MOD 500)=0 ORDER BY disorder DESC ");
            if(!is_array($arr)) $disorder = $evalue = ($issign==1 ? 1 : 500);
            else $disorder = $evalue = $arr['disorder'] + ($issign==1 ? 1 : 500);
                
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`) 
                                    VALUES('$ename','$evalue','$egroup','$disorder','$issign'); "); 
        }
        WriteEnumsCache($egroup);                                                          
        ShowMsg("�ɹ����ö�ٷ��࣡".$dsql->GetError(), $ENV_GOBACK_URL);
        exit();
    } else if ($issign == 2 && $topvalue != 0)
    {
        $minid = $topvalue;
        $maxnum = 500; // �����������500��
        $enames = explode(',', $ename);
        foreach ($enames as $ename)
        {
            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup='$egroup' AND evalue LIKE '$topvalue.%%%' ORDER BY evalue DESC ");
            if(!is_array($arr))
            {
                $disorder = $minid;
                $evalue = $minid.'.001';
            }
            else
            {
                $disorder = $minid;
                preg_match("#([0-9]{1,})\.([0-9]{1,})#", $arr['evalue'], $matchs);
                $addvalue = $matchs[2] + 1;
                $addvalue = sprintf("%03d", $addvalue);
                $evalue = $matchs[1].'.'.$addvalue;
            }
            $sql = "INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`) 
                                    VALUES('$ename','$evalue','$egroup','$disorder','$issign'); ";
            // echo $sql;exit;
            $dsql->ExecuteNoneQuery($sql); 
        }
        // echo $minid;
        WriteEnumsCache($egroup);
        ShowMsg("�ɹ����ö�ٷ��࣡", $ENV_GOBACK_URL);
        exit();
    } else {
        $minid = $topvalue;
        $maxid = $topvalue + 500;
        $enames = explode(',', $ename);
        foreach($enames as $ename)
        {
            $arr = $dsql->GetOne("SELECT * FROM `#@__sys_enum` WHERE egroup='$egroup' AND evalue>$minid AND evalue<$maxid ORDER BY evalue DESC ");
            if(!is_array($arr))
            {
                $disorder = $evalue = $minid+1;
            }
            else
            {
                $disorder = $arr['disorder']+1;
                $evalue = $arr['evalue']+1;
            }
            $dsql->ExecuteNoneQuery("INSERT INTO `#@__sys_enum`(`ename`,`evalue`,`egroup`,`disorder`,`issign`) 
                          VALUES('$ename','$evalue','$egroup','$disorder','$issign'); ");
        }
        WriteEnumsCache($egroup);
        ShowMsg("�ɹ����ö�ٷ��࣡", $ENV_GOBACK_URL);
        exit();
    }
}
/*-----------------
�޸�ö�����ƺ�����
function __upenum() { }
------------------*/
else if($action=='upenum')
{
    $ename = trim(preg_replace("# ����(��){1,}#", '', $ename));
    $row = $dsql->GetOne("SELECT egroup FROM `#@__sys_enum` WHERE id = '$aid' ");
    WriteEnumsCache($row['egroup']);
    $dsql->ExecuteNoneQuery("UPDATE `#@__sys_enum` SET `ename`='$ename',`disorder`='$disorder' WHERE id='$aid'; ");
    ShowMsg("�ɹ��޸�һ��ö�٣�", $ENV_GOBACK_URL);
    exit();
}
/*-----------------
����ö�ٻ���
function __upallcache() { }
------------------*/
else if($action=='upallcache')
{
    if(!isset($egroup)) $egroup = '';
    WriteEnumsCache($egroup);
    ShowMsg("�ɸ���ö�ٻ��棡", $ENV_GOBACK_URL);
    exit();
}