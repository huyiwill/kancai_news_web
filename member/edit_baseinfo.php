<?php
/**
 * @version        $Id: edit_baseinfo.php 1 8:38 2010��7��9��Z tianya $
 * @package        DedeCMS.Member
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckRank(0,0);
$menutype = 'config';
if(!isset($dopost)) $dopost = '';

$pwd2=(empty($pwd2))? "" : $pwd2;
$row=$dsql->GetOne("SELECT  * FROM `#@__member` WHERE mid='".$cfg_ml->M_ID."'");
$face = $row['face'];
if($dopost=='save')
{
    $svali = GetCkVdValue();

    if(strtolower($vdcode) != $svali || $svali=='')
    {
        ReSETVdValue();
        ShowMsg('��֤�����','-1');
        exit();
    }
    if(!is_array($row) || $row['pwd'] != md5($oldpwd))
    {
        ShowMsg('������ľ���������û��д���������޸����ϣ�','-1');
        exit();
    }
    if($userpwd != $userpwdok)
    {
        ShowMsg('����������������벻һ�£�','-1');
        exit();
    }
    if($userpwd=='')
    {
        $pwd = $row['pwd'];
    }
    else
    {
        $pwd = md5($userpwd);
        $pwd2 = substr(md5($userpwd),5,20);
    }
    $addupquery = '';
    
    #api{{
    if(defined('UC_API') && @include_once DEDEROOT.'/uc_client/client.php')
    {
        $emailnew = $email != $row['email'] ? $email : '';
        $ucresult = uc_user_edit($cfg_ml->M_LoginID, $oldpwd, $userpwd, $emailnew);        
    }
    #/aip}}
    
    //�޸İ�ȫ�����Email
    if($email != $row['email'] || ($newsafequestion != 0 && $newsafeanswer != ''))
    {
        if($row['safequestion']!=0 && ($row['safequestion'] != $safequestion || $row['safeanswer'] != $safeanswer))
        {
            ShowMsg('��ľɰ�ȫ���⼰�𰸲���ȷ�������޸�Email��ȫ���⣡','-1');
            exit();
        }

        //�޸�Email
        if($email != $row['email'])
        {
            if(!CheckEmail($email))
            {
                ShowMsg('Email��ʽ����ȷ��','-1');
                exit();
            }
            else
            {
                $addupquery .= ",email='$email'";
            }
        }

        //�޸İ�ȫ����
        if($newsafequestion != 0 && $newsafeanswer != '')
        {
            if(strlen($newsafeanswer) > 30)
            {
                ShowMsg('����°�ȫ����Ĵ�̫���ˣ��뱣����30�ֽ����ڣ�','-1');
                exit();
            }
            else
            {
			    $newsafequestion = HtmlReplace($newsafequestion,1);
			    $newsafeanswer = HtmlReplace($newsafeanswer,1);
                $addupquery .= ",safequestion='$newsafequestion',safeanswer='$newsafeanswer'";
            }
        }
    }

    //�޸�uname
    if($uname != $row['uname'])
    {
        $rs = CheckUserID($uname,'�ǳƻ�˾����',FALSE);
        if($rs!='ok')
        {
            ShowMsg($rs,'-1');
            exit();
        }
        $addupquery .= ",uname='$uname'";
    }
    
    //�Ա�
    if( !in_array($sex, array('��','Ů','����')) )
    {
        ShowMsg('��ѡ���������Ա�','-1');
        exit();    
    }
    
    $query1 = "UPDATE `#@__member` SET pwd='$pwd',sex='$sex'{$addupquery} where mid='".$cfg_ml->M_ID."' ";
    $dsql->ExecuteNoneQuery($query1);

    //����ǹ���Ա���޸����̨����
    if($cfg_ml->fields['matt']==10 && $pwd2!="")
    {
        $query2 = "UPDATE `#@__admin` SET pwd='$pwd2' where id='".$cfg_ml->M_ID."' ";
        $dsql->ExecuteNoneQuery($query2);
    }
    // �����Ա����
    $cfg_ml->DelCache($cfg_ml->M_ID);
    ShowMsg('�ɹ�������Ļ������ϣ�','edit_baseinfo.php',0,5000);
    exit();
}
include(DEDEMEMBER."/templets/edit_baseinfo.htm");