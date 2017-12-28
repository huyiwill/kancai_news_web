<?php  if(!defined('DEDEINC')) exit('dedecms');
/**
 * ����С����
 *
 * @version        $Id: util.helper.php 4 19:20 2010��7��6��Z tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
 
define('T_NEW_LINE', -1);

if (!function_exists('token_get_all_nl')) 
{
    function token_get_all_nl($source)
    {
        $new_tokens = array();

        // Get the tokens
        $tokens = token_get_all($source);

        // Split newlines into their own tokens
        foreach ($tokens as $token)
        {
            $token_name = is_array($token) ? $token[0] : null;
            $token_data = is_array($token) ? $token[1] : $token;

            // Do not split encapsed strings or multiline comments
            if ($token_name == T_CONSTANT_ENCAPSED_STRING || substr($token_data, 0, 2) == '/*')
            {
                $new_tokens[] = array($token_name, $token_data);
                continue;
            }

            // Split the data up by newlines
            $split_data = preg_split('#(\r\n|\n)#', $token_data, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

            foreach ($split_data as $data)
            {
                if ($data == "\r\n" || $data == "\n")
                {
                    // This is a new line token
                    $new_tokens[] = array(T_NEW_LINE, $data);
                }
                else
                {
                    // Add the token under the original token name
                    $new_tokens[] = is_array($token) ? array($token_name, $data) : $data;
                }
            }
        }

        return $new_tokens;
    }
}
    
if (!function_exists('token_name_nl')) 
{
    function token_name_nl($token)
    {
        if ($token === T_NEW_LINE)
        {
            return 'T_NEW_LINE';
        }

        return token_name($token);
    }
}

/**
 *  ��õ�ǰ�Ľű���ַ
 *
 * @return    string
 */
if ( ! function_exists('GetCurUrl'))
{
    function GetCurUrl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        }
        else
        {
            $scriptName = $_SERVER["PHP_SELF"];
            if(empty($_SERVER["QUERY_STRING"]))
            {
                $nowurl = $scriptName;
            }
            else
            {
                $nowurl = $scriptName."?".$_SERVER["QUERY_STRING"];
            }
        }
        return $nowurl;
    }
}

/**
 *  ��ȡ�û���ʵ��ַ
 *
 * @return    string  �����û�ip
 */
if ( ! function_exists('GetIP'))
{
    function GetIP()
    {
        static $realip = NULL;
        if ($realip !== NULL)
        {
            return $realip;
        }
        if (isset($_SERVER))
        {
            if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            {
                $arr = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
                /* ȡX-Forwarded-For�е�x����unknown����ЧIP�ַ�? */
                foreach ($arr as $ip)
                {
                    $ip = trim($ip);
                    if ($ip != 'unknown')
                    {
                        $realip = $ip;
                        break;
                    }
                }
            }
            elseif (isset($_SERVER['HTTP_CLIENT_IP']))
            {
                $realip = $_SERVER['HTTP_CLIENT_IP'];
            }
            else
            {
                if (isset($_SERVER['REMOTE_ADDR']))
                {
                    $realip = $_SERVER['REMOTE_ADDR'];
                }
                else
                {
                    $realip = '0.0.0.0';
                }
            }
        }
        else
        {
            if (getenv('HTTP_X_FORWARDED_FOR'))
            {
                $realip = getenv('HTTP_X_FORWARDED_FOR');
            }
            elseif (getenv('HTTP_CLIENT_IP'))
            {
                $realip = getenv('HTTP_CLIENT_IP');
            }
            else
            {
                $realip = getenv('REMOTE_ADDR');
            }
        }
        preg_match("/[\d\.]{7,15}/", $realip, $onlineip);
        $realip = ! empty($onlineip[0]) ? $onlineip[0] : '0.0.0.0';
        return $realip;
    }
}

/**
 *  ��ȡ�༭��
 *
 * @param     string  $fname  ������
 * @param     string  $fvalue ���������Ĭ��ֵ,������Ĭ��ֵ
 * @param     string  $nheight �߶�
 * @param     string  $etype   �༭������
 * @param     string  $gtype   ��ȡ����
 * @param     string  $isfullpage   �Ƿ�ȫ��
 * @return    string
 */
if ( ! function_exists('GetEditor'))
{
    function GetEditor($fname, $fvalue, $nheight="350", $etype="Basic", $gtype="print", $isfullpage="FALSE",$bbcode=false)
    {
        if(!function_exists('SpGetEditor'))
        {
            require_once(DEDEINC."/inc/inc_fun_funAdmin.php");
        }
        return SpGetEditor($fname, $fvalue, $nheight, $etype, $gtype, $isfullpage, $bbcode);
    }
}

/**
 *  ��ȡģ��
 *
 * @param     string  $filename �ļ�����
 * @return    string
 */
if ( ! function_exists('GetTemplets'))
{
    function GetTemplets($filename)
    {
        if(file_exists($filename))
        {
            $fp = fopen($filename,"r");
            $rstr = fread($fp,filesize($filename));
            fclose($fp);
            return $rstr;
        }
        else
        {
            return '';
        }
    }
}

/**
 *  ��ȡϵͳģ��
 *
 * @param     $filename  ģ���ļ�
 * @return    string
 */
if ( ! function_exists('GetSysTemplets'))
{
    function GetSysTemplets($filename)
    {
        return GetTemplets($GLOBALS['cfg_basedir'].$GLOBALS['cfg_templets_dir'].'/system/'.$filename);
    }
}

/**
 *  ��ȡ������ʾ
 *
 * @return    void
 */
if ( ! function_exists('GetNewInfo'))
{
    function GetNewInfo()
    {
        if(!function_exists('SpGetNewInfo'))
        {
            require_once(DEDEINC."/inc/inc_fun_funAdmin.php");
        }
        return SpGetNewInfo();
    }
}

/**
 *  ����һ������ַ�
 *
 * @access    public
 * @param     string  $ddnum
 * @return    string
 */
if ( ! function_exists('dd2char'))
{
    function dd2char($ddnum)
    {
        $ddnum = strval($ddnum);
        $slen = strlen($ddnum);
        $okdd = '';
        $nn = '';
        for($i=0;$i<$slen;$i++)
        {
            if(isset($ddnum[$i+1]))
            {
                $n = $ddnum[$i].$ddnum[$i+1];
                if( ($n>96 && $n<123) || ($n>64 && $n<91) )
                {
                    $okdd .= chr($n);
                    $i++;
                }
                else
                {
                    $okdd .= $ddnum[$i];
                }
            }
            else
            {
                $okdd .= $ddnum[$i];
            }
        }
        return $okdd;
    }
}

/**
 *  json_encode���ݺ���
 *
 * @access    public
 * @param     string  $data
 * @return    string
 */
if (!function_exists('json_encode')) {
     function format_json_value(&$value)
    {
        if(is_bool($value)) {
            $value = $value?'TRUE':'FALSE';
        } else if (is_int($value)) {
            $value = intval($value);
        } else if (is_float($value)) {
            $value = floatval($value);
        } else if (defined($value) && $value === NULL) {
            $value = strval(constant($value));
        } else if (is_string($value)) {
            $value = '"'.addslashes($value).'"';
        }
        return $value;
    }

    function json_encode($data)
    {
        if(is_object($data)) {
            //����ת��������
            $data = get_object_vars($data);
        }else if(!is_array($data)) {
            // ��ͨ��ʽֱ�����
            return format_json_value($data);
        }
        // �ж��Ƿ��������
        if(empty($data) || is_numeric(implode('',array_keys($data)))) {
            $assoc  =  FALSE;
        }else {
            $assoc  =  TRUE;
        }
        // ��װ Json�ַ���
        $json = $assoc ? '{' : '[' ;
        foreach($data as $key=>$val) {
            if(!is_NULL($val)) {
                if($assoc) {
                    $json .= "\"$key\":".json_encode($val).",";
                }else {
                    $json .= json_encode($val).",";
                }
            }
        }
        if(strlen($json)>1) {// �����ж� ��ֹ������
            $json  = substr($json,0,-1);
        }
        $json .= $assoc ? '}' : ']' ;
        return $json;
    }
}

/**
 *  json_decode���ݺ���
 *
 * @access    public
 * @param     string  $json  json����
 * @param     string  $assoc  ���ò���Ϊ TRUE ʱ�������� array ���� object
 * @return    string
 */
if (!function_exists('json_decode')) {
    function json_decode($json, $assoc=FALSE)
    {
        // Ŀǰ��֧�ֶ�ά��������
        $begin  =  substr($json,0,1) ;
        if(!in_array($begin,array('{','[')))
            // ���Ƕ����������ֱ�ӷ���
            return $json;
        $parse = substr($json,1,-1);
        $data  = explode(',',$parse);
        if($flag = $begin =='{' ) {
            // ת����PHP����
            $result   = new stdClass();
            foreach($data as $val) {
                $item    = explode(':',$val);
                $key =  substr($item[0],1,-1);
                $result->$key = json_decode($item[1],$assoc);
            }
            if($assoc)
                $result   = get_object_vars($result);
        }else {
            // ת����PHP����
            $result   = array();
            foreach($data as $val)
                $result[]  =  json_decode($val,$assoc);
        }
        return $result;
    }
}
