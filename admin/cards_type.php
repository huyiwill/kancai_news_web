<?php
/**
 * �㿨����
 *
 * @version        $Id: cards_type.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__).'/config.php');
CheckPurview('member_Type');
if(empty($dopost)) $dopost = "";

//�������
if($dopost=="save")
{
    $startID = 1;
    $endID = $idend;
    for(;$startID<=$endID;$startID++)
    {
        $query = '';
        $tid = ${'ID_'.$startID};
        $pname =   ${'pname_'.$startID};
        $money =    ${'money_'.$startID};
        $num =   ${'num_'.$startID};
        if(isset(${'check_'.$startID}))
        {
            if($pname!='')
            {
                $query = "UPDATE #@__moneycard_type SET pname='$pname',money='$money',num='$num' WHERE tid='$tid'";
                $dsql->ExecuteNoneQuery($query);
                $query = "UPDATE #@__moneycard_record SET money='$money',num='$num' WHERE ctid='$tid' ; ";
                $dsql->ExecuteNoneQuery($query);
            }
        }
        else
        {
            $query = "DELETE FROM #@__moneycard_type WHERE tid='$tid' ";
            $dsql->ExecuteNoneQuery($query);
            $query = "DELETE FROM #@__moneycard_record WHERE ctid='$tid' AND isexp<>-1 ; ";
            $dsql->ExecuteNoneQuery($query);
        }
    }

    //�����¼�¼
    if(isset($check_new) && $pname_new!='')
    {
        $query = "INSERT INTO #@__moneycard_type(num,pname,money) VALUES('{$num_new}','{$pname_new}','{$money_new}');";
        $dsql->ExecuteNoneQuery($query);
    }
    header("Content-Type: text/html; charset={$cfg_soft_lang}");
    echo "<script> alert('�ɹ����µ㿨��Ʒ�����'); </script>";
}
require_once(DEDEADMIN."/templets/cards_type.htm");