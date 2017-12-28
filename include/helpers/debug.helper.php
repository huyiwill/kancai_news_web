<?php  if(!defined('DEDEINC')) exit('dedecms');
/**
 * ��֤С����
 *
 * @version        $Id: validate.helper.php 2 13:56 2010��7��5�� tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 * ������Ѻõı������,���ڵ���ʱ��ʹ��
 *
 * @param     mixed   $var       Ҫ����鿴������
 * @param     bool    $echo      �Ƿ�ֱ�����
 * @param     string  $label     ����˵����ǩ,�����,����ʾ"��ǩ��:"������ʽ
 * @param     bool    $strict    �Ƿ��ϸ����
 * @return    string
 */
if ( ! function_exists('Dump'))
{
    function Dump($var, $echo=true, $label=null, $strict=true)
    {
        $label = ($label===null) ? '' : rtrim($label) . ' ';
        if(!$strict) {
            if (ini_get('html_errors')) {
                $output = print_r($var, true);
                $output = "<pre>".$label.htmlspecialchars($output,ENT_QUOTES)."</pre>";
            } else {
                $output = $label . " : " . print_r($var, true);
            }
        }else {
            ob_start();
            var_dump($var);
            $output = ob_get_clean();
            if(!extension_loaded('xdebug')) {
                $output = preg_replace("/\]\=\>\n(\s+)/m", "] => ", $output);
                $output = '<pre>'. $label. htmlspecialchars($output, ENT_QUOTES). '</pre>';
            }
        }
        if ($echo) {
            echo($output);
            return null;
        }else
            return $output;
    }
}

/**
 *  ��ȡִ��ʱ��
 *  ����:$t1 = ExecTime();
 *       ��һ�����ݴ���֮��:
 *       $t2 = ExecTime();
 *  ���ǿ��Խ�2��ʱ��Ĳ�ֵ���:echo $t2-$t1;
 *
 *  @return    int
 */
if ( ! function_exists('ExecTime'))
{
    function ExecTime()
    {
        $time = explode(" ", microtime());
        $usec = (double)$time[0];
        $sec = (double)$time[1];
        return $sec + $usec;
    }
}
