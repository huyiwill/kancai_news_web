<?php
/**
 * ��Ա����
 *
 * @version        $Id: member_type.php 1 14:14 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('member_Type');
if(empty($dopost)) $dopost = "";

//�������
if($dopost=="save")
{
    $startID = 1;
    $endID = $idend;
    for( ;$startID <= $endID; $startID++)
    {
        $query = '';
        $aid = ${'ID_'.$startID};
        $pname =   ${'pname_'.$startID};
        $rank =    ${'rank_'.$startID};
        $money =   ${'money_'.$startID};
        $exptime = ${'exptime_'.$startID};
        if(isset(${'check_'.$startID}))
        {
            if($pname!='')
            {
                $query = "UPDATE #@__member_type SET pname='$pname',money='$money',rank='$rank',exptime='$exptime' WHERE aid='$aid'";
            }
        }
        else
        {
            $query = "DELETE FROM #@__member_type WHERE aid='$aid' ";
        }
        if($query!='')
        {
            $dsql->ExecuteNoneQuery($query);
        }
    }

    //�����¼�¼
    if(isset($check_new) && $pname_new!='')
    {
        $query = "INSERT INTO #@__member_type(rank,pname,money,exptime) VALUES('{$rank_new}','{$pname_new}','{$money_new}','{$exptime_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("Content-Type: text/html; charset={$cfg_soft_lang}");
    echo "<script> alert('�ɹ����»�Ա��Ʒ�����'); </script>";
}
$arcranks = array();
$dsql->SetQuery("SELECT * FROM #@__arcrank WHERE rank>10 ");
$dsql->Execute();
while($row=$dsql->GetArray())
{
    $arcranks[$row['rank']] = $row['membername'];
}

$times = array();
$times[7] = 'һ��';
$times[30] = 'һ����';
$times[90] = '������';
$times[183] = '����';
$times[366] = 'һ��';
$times[32767] = '����';

require_once(DEDEADMIN."/templets/member_type.htm");