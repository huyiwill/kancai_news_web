<?php  if(!defined('DEDEINC')) exit('dedecms');
/**
 * ʱ���С����
 *
 * @version        $Id: time.helper.php 1 2010-07-05 11:43:09Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ���ظ������α�׼ʱ��
 *
 * @param     string  $format  �ַ�����ʽ
 * @param     string  $timest  ʱ���׼
 * @return    string
 */
if ( ! function_exists('MyDate'))
{
    function MyDate($format='Y-m-d H:i:s', $timest=0)
    {
        global $cfg_cli_time;
        $addtime = $cfg_cli_time * 3600;
        if(empty($format))
        {
            $format = 'Y-m-d H:i:s';
        }
        return gmdate ($format, $timest+$addtime);
    }
}


/**
 * ����ͨʱ��ת��ΪLinuxʱ���
 *
 * @param     string   $dtime  ��ͨʱ��
 * @return    string
 */
if ( ! function_exists('GetMkTime'))
{
    function GetMkTime($dtime)
    {
        if(!preg_match("/[^0-9]/", $dtime))
        {
            return $dtime;
        }
        $dtime = trim($dtime);
        $dt = Array(1970, 1, 1, 0, 0, 0);
        $dtime = preg_replace("/[\r\n\t]|��|��/", " ", $dtime);
        $dtime = str_replace("��", "-", $dtime);
        $dtime = str_replace("��", "-", $dtime);
        $dtime = str_replace("ʱ", ":", $dtime);
        $dtime = str_replace("��", ":", $dtime);
        $dtime = trim(preg_replace("/[ ]{1,}/", " ", $dtime));
        $ds = explode(" ", $dtime);
        $ymd = explode("-", $ds[0]);
        if(!isset($ymd[1]))
        {
            $ymd = explode(".", $ds[0]);
        }
        if(isset($ymd[0]))
        {
            $dt[0] = $ymd[0];
        }
        if(isset($ymd[1])) $dt[1] = $ymd[1];
        if(isset($ymd[2])) $dt[2] = $ymd[2];
        if(strlen($dt[0])==2) $dt[0] = '20'.$dt[0];
        if(isset($ds[1]))
        {
            $hms = explode(":", $ds[1]);
            if(isset($hms[0])) $dt[3] = $hms[0];
            if(isset($hms[1])) $dt[4] = $hms[1];
            if(isset($hms[2])) $dt[5] = $hms[2];
        }
        foreach($dt as $k=>$v)
        {
            $v = preg_replace("/^0{1,}/", '', trim($v));
            if($v=='')
            {
                $dt[$k] = 0;
            }
        }
        $mt = mktime($dt[3], $dt[4], $dt[5], $dt[1], $dt[2], $dt[0]);
        if(!empty($mt))
        {
              return $mt;
        }
        else
        {
              return time();
        }
    }
}


/**
 *  ��ȥʱ��
 *
 * @param     int  $ntime  ��ǰʱ��
 * @param     int  $ctime  ���ٵ�ʱ��
 * @return    int
 */
if ( ! function_exists('SubDay'))
{
    function SubDay($ntime, $ctime)
    {
        $dayst = 3600 * 24;
        $cday = ceil(($ntime-$ctime)/$dayst);
        return $cday;
    }
}


/**
 *  ��������
 *
 * @param     int  $ntime  ��ǰʱ��
 * @param     int  $aday   ��������
 * @return    int
 */
if ( ! function_exists('AddDay'))
{
    function AddDay($ntime, $aday)
    {
        $dayst = 3600 * 24;
        $oktime = $ntime + ($aday * $dayst);
        return $oktime;
    }
}


/**
 *  ���ظ�ʽ��(Y-m-d H:i:s)����ʱ��
 *
 * @param     int    $mktime  ʱ���
 * @return    string
 */
if ( ! function_exists('GetDateTimeMk'))
{
    function GetDateTimeMk($mktime)
    {
        return MyDate('Y-m-d H:i:s',$mktime);
    }
}

/**
 *  ���ظ�ʽ��(Y-m-d)������
 *
 * @param     int    $mktime  ʱ���
 * @return    string
 */
if ( ! function_exists('GetDateMk'))
{
    function GetDateMk($mktime)
    {
        if($mktime=="0") return "����";
        else return MyDate("Y-m-d", $mktime);
    }
}


/**
 *  ��ʱ��ת��Ϊ�������ڵľ�ȷʱ��
 *
 * @param     int   $seconds  ����
 * @return    string
 */
if ( ! function_exists('FloorTime'))
{
    function FloorTime($seconds)
    {
        $times = '';
        $days = floor(($seconds/86400)%30);
        $hours = floor(($seconds/3600)%24);
        $minutes = floor(($seconds/60)%60);
        $seconds = floor($seconds%60);
        if($seconds >= 1) $times .= $seconds.'��';
        if($minutes >= 1) $times = $minutes.'���� '.$times;
        if($hours >= 1) $times = $hours.'Сʱ '.$times;
        if($days >= 1)  $times = $days.'��';
        if($days > 30) return false;
        $times .= 'ǰ';
        return str_replace(" ", '', $times);
    }
}
