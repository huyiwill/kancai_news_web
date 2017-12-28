<?php
/**
 * ϵͳ���ĺ�������ļ�
 * @version        $Id: common.func.php 4 16:39 2010��7��6��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
if(!defined('DEDEINC')) exit('dedecms');

/**
 *  ����С����,ϵͳĬ������С����
 *  ��/data/helper.inc.php�н���Ĭ��С���ֳ�ʼ��������
 *  ʹ��ʾ��:
 *      �ڿ�����,������Ҫ����һ��С���ֺ���,Ŀ¼��\include\helpers��
 *  ����,���Ǵ���һ��ʾ��Ϊtest.helper.php,�ļ�������������:
 *  <code>
 *  if ( ! function_exists('HelloDede'))
 *  {
 *      function HelloDede()
 *      {
 *          echo "Hello! Dede...";
 *      }
 *  }
 *  </code>
 *  �������ڿ�����ʹ�����С���ֵ�ʱ��ֱ��ʹ�ú���helper('test');��ʼ����
 *  Ȼ�����ļ��оͿ���ֱ��ʹ��:HelloDede();�����е���.
 *
 * @access    public
 * @param     mix   $helpers  С��������,����������,�����ǵ����ַ���
 * @return    void
 */
$_helpers = array();
function helper($helpers)
{
    //���������,����еݹ����
    if (is_array($helpers))
    {
        foreach($helpers as $dede)
        {
            helper($dede);
        }
        return;
    }

    if (isset($_helpers[$helpers]))
    {
        continue;
    }
    if (file_exists(DEDEINC.'/helpers/'.$helpers.'.helper.php'))
    { 
        include_once(DEDEINC.'/helpers/'.$helpers.'.helper.php');
        $_helpers[$helpers] = TRUE;
    }
    // �޷�����С����
    if ( ! isset($_helpers[$helpers]))
    {
        exit('Unable to load the requested file: helpers/'.$helpers.'.helper.php');                
    }
}

/**
 *  ���������ú���
 *
 * @access    public
 * @param     string  $ct    ������
 * @param     string  $ac    �����¼�
 * @param     string  $path  ָ������������Ŀ¼
 * @return    string
 */
function RunApp($ct, $ac = '',$directory = '')
{
    
    $ct = preg_replace("/[^0-9a-z_]/i", '', $ct);
    $ac = preg_replace("/[^0-9a-z_]/i", '', $ac);
        
    $ac = empty ( $ac ) ? $ac = 'index' : $ac;
	if(!empty($directory)) $path = DEDECONTROL.'/'.$directory. '/' . $ct . '.php';
	else $path = DEDECONTROL . '/' . $ct . '.php';
        
	if (file_exists ( $path ))
	{
		require $path;
	} else {
		 if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Controller false!");
        }
        //���������У��Ҳ������������������Ҫ��¼��־
        else
        {
            header ( "location:/404.html" );
            die ();
        }
	}
	$action = 'ac_'.$ac;
    $loaderr = FALSE;
    $instance = new $ct ( );
    if (method_exists ( $instance, $action ) === TRUE)
    {
        $instance->$action();
        unset($instance);
    } else $loaderr = TRUE;
        
    if ($loaderr)
    {
        if (DEBUG_LEVEL === TRUE)
        {
            trigger_error("Load Method false!");
        }
        //���������У��Ҳ������������������Ҫ��¼��־
        else
        {
            header ( "location:/404.html" );
            die ();
        }
    }
}

/**
 *  ����С����,�����û�����������helps������С����
 *
 * @access    public
 * @param     string
 * @return    string
 */
function helpers($helpers)
{
    helper($helpers);
}

//����php4��file_put_contents
if(!function_exists('file_put_contents'))
{
    function file_put_contents($n, $d)
    {
        $f=@fopen($n, "w");
        if (!$f)
        {
            return FALSE;
        }
        else
        {
            fwrite($f, $d);
            fclose($f);
            return TRUE;
        }
    }
}

/**
 *  ��ʾ������Ϣ
 *
 * @return    void
 */
function UpdateStat()
{
    include_once(DEDEINC."/inc/inc_stat.php");
    return SpUpdateStat();
}

$arrs1 = array(0x63,0x66,0x67,0x5f,0x70,0x6f,0x77,0x65,0x72,0x62,0x79);
$arrs2 = array(0x20,0x3c,0x61,0x20,0x68,0x72,0x65,0x66,0x3d,0x68,0x74,0x74,0x70,0x3a,0x2f,0x2f,
0x77,0x77,0x77,0x2e,0x64,0x65,0x64,0x65,0x63,0x6d,0x73,0x2e,0x63,0x6f,0x6d,0x20,0x74,0x61,0x72,
0x67,0x65,0x74,0x3d,0x27,0x5f,0x62,0x6c,0x61,0x6e,0x6b,0x27,0x3e,0x50,0x6f,0x77,0x65,0x72,0x20,
0x62,0x79,0x20,0x44,0x65,0x64,0x65,0x43,0x6d,0x73,0x3c,0x2f,0x61,0x3e);

/**
 *  ����Ϣ����,������ĳ������������Ѻõ���ʾ��Ϣ
 *
 * @param     string  $msg      ��Ϣ��ʾ��Ϣ
 * @param     string  $gourl    ��ת��ַ
 * @param     int     $onlymsg  ����ʾ��Ϣ
 * @param     int     $limittime  ����ʱ��
 * @return    void
 */
function ShowMsg($msg, $gourl, $onlymsg=0, $limittime=0)
{
    if(empty($GLOBALS['cfg_plus_dir'])) $GLOBALS['cfg_plus_dir'] = '..';

    $htmlhead  = "<html>\r\n<head>\r\n<title>DedeCMS��ʾ��Ϣ</title>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=gb2312\" />\r\n";
    $htmlhead .= "<base target='_self'/>\r\n<style>div{line-height:160%;}</style></head>\r\n<body leftmargin='0' topmargin='0' bgcolor='#FFFFFF'>".(isset($GLOBALS['ucsynlogin']) ? $GLOBALS['ucsynlogin'] : '')."\r\n<center>\r\n<script>\r\n";
    $htmlfoot  = "</script>\r\n</center>\r\n</body>\r\n</html>\r\n";

    $litime = ($limittime==0 ? 1000 : $limittime);
    $func = '';

    if($gourl=='-1')
    {
        if($limittime==0) $litime = 5000;
        $gourl = "javascript:history.go(-1);";
    }

    if($gourl=='' || $onlymsg==1)
    {
        $msg = "<script>alert(\"".str_replace("\"","��",$msg)."\");</script>";
    }
    else
    {
        //����ַΪ:close::objname ʱ, �رո���ܵ�id=objnameԪ��
        if(preg_match('/close::/',$gourl))
        {
            $tgobj = trim(preg_replace('/close::/', '', $gourl));
            $gourl = 'javascript:;';
            $func .= "window.parent.document.getElementById('{$tgobj}').style.display='none';\r\n";
        }
        
        $func .= "      var pgo=0;
      function JumpUrl(){
        if(pgo==0){ location='$gourl'; pgo=1; }
      }\r\n";
        $rmsg = $func;
        $rmsg .= "document.write(\"<br /><div style='width:450px;padding:0px;border:1px solid #DADADA;'>";
        $rmsg .= "<div style='padding:6px;font-size:12px;border-bottom:1px solid #DADADA;background:#DBEEBD url({$GLOBALS['cfg_plus_dir']}/img/wbg.gif)';'><b>DedeCMS ��ʾ��Ϣ��</b></div>\");\r\n";
        $rmsg .= "document.write(\"<div style='height:130px;font-size:10pt;background:#ffffff'><br />\");\r\n";
        $rmsg .= "document.write(\"".str_replace("\"","��",$msg)."\");\r\n";
        $rmsg .= "document.write(\"";
        
        if($onlymsg==0)
        {
            if( $gourl != 'javascript:;' && $gourl != '')
            {
                $rmsg .= "<br /><a href='{$gourl}'>�����������û��Ӧ����������...</a>";
                $rmsg .= "<br/></div>\");\r\n";
                $rmsg .= "setTimeout('JumpUrl()',$litime);";
            }
            else
            {
                $rmsg .= "<br/></div>\");\r\n";
            }
        }
        else
        {
            $rmsg .= "<br/><br/></div>\");\r\n";
        }
        $msg  = $htmlhead.$rmsg.$htmlfoot;
    }
    echo $msg;
}

/**
 *  ��ȡ��֤���sessionֵ
 *
 * @return    string
 */
function GetCkVdValue()
{
	@session_id($_COOKIE['PHPSESSID']);
    @session_start();
    return isset($_SESSION['securimage_code_value']) ? $_SESSION['securimage_code_value'] : '';
}

/**
 *  PHPĳЩ�汾��Bug��������ͬһ��������ͬʱ��session����ע��������˵��ú���ִ�б�����
 *
 * @return    void
 */
function ResetVdValue()
{
    @session_start();
    $_SESSION['securimage_code_value'] = '';
}


// �Զ��庯���ӿ�
// ������Ҫ�������ڵ��û���չ,v5.7֮�����ǽ���ʹ��С����helper������չ
if( file_exists(DEDEINC.'/extend.func.php') )
{
    require_once(DEDEINC.'/extend.func.php');
}