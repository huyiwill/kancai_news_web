<?php
/**
 * @version        $Id: index.php 1 13:41 2010��7��26��Z tianya $
 * @package        DedeCMS.Install
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
@set_time_limit(0);
//error_reporting(E_ALL);
error_reporting(E_ALL || ~E_NOTICE);

$verMsg = ' V5.7 GBKSP1';
$s_lang = 'gb2312';
$dfDbname = 'dedecmsv57gbksp1';
$errmsg = '';
$install_demo_name = 'dedev57demo.txt';
$insLockfile = dirname(__FILE__).'/install_lock.txt';
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';

define('DEDEINC',dirname(__FILE__).'/../include');
define('DEDEDATA',dirname(__FILE__).'/../data');
define('DEDEROOT',preg_replace("#[\\\\\/]install#", '', dirname(__FILE__)));
header("Content-Type: text/html; charset={$s_lang}");

require_once(DEDEROOT.'/install/install.inc.php');
require_once(DEDEINC.'/zip.class.php');

foreach(Array('_GET','_POST','_COOKIE') as $_request)
{
    foreach($$_request as $_k => $_v) ${$_k} = RunMagicQuotes($_v);
}

require_once(DEDEINC.'/common.func.php');

if(file_exists($insLockfile))
{
    exit(" ���������а�װ�������ȷ��Ҫ���°�װ�����ȴ�FTP��ɾ�� install/install_lock.txt��");
}

if(empty($step))
{
    $step = 1;
}
/*------------------------
ʹ��Э����
function _1_Agreement()
------------------------*/
if($step==1)
{
    include('./templates/step-1.html');
    exit();
}
/*------------------------
��������
function _2_TestEnv()
------------------------*/
else if($step==2)
{
    $phpv = phpversion();
    $sp_os = PHP_OS;
    $sp_gd = gdversion();
    $sp_server = $_SERVER['SERVER_SOFTWARE'];
    $sp_host = (empty($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_HOST'] : $_SERVER['REMOTE_ADDR']);
    $sp_name = $_SERVER['SERVER_NAME'];
    $sp_max_execution_time = ini_get('max_execution_time');
    $sp_allow_reference = (ini_get('allow_call_time_pass_reference') ? '<font color=green>[��]On</font>' : '<font color=red>[��]Off</font>');
    $sp_allow_url_fopen = (ini_get('allow_url_fopen') ? '<font color=green>[��]On</font>' : '<font color=red>[��]Off</font>');
    $sp_safe_mode = (ini_get('safe_mode') ? '<font color=red>[��]On</font>' : '<font color=green>[��]Off</font>');
    $sp_gd = ($sp_gd>0 ? '<font color=green>[��]On</font>' : '<font color=red>[��]Off</font>');
    $sp_mysql = (function_exists('mysql_connect') ? '<font color=green>[��]On</font>' : '<font color=red>[��]Off</font>');

    if($sp_mysql=='<font color=red>[��]Off</font>')
    $sp_mysql_err = TRUE;
    else
    $sp_mysql_err = FALSE;

    $sp_testdirs = array(
        '/',
        '/plus/*',
        '/52jscn_admin/*',
        '/data/*',
        '/html/*',
        '/install',
        '/special',
        '/uploads/*'
    );
    include('./templates/step-2.html');
    exit();
}
/*------------------------
���ò���
function _3_WriteSeting()
------------------------*/
else if($step==3)
{
    if(!empty($_SERVER['REQUEST_URI']))
    $scriptName = $_SERVER['REQUEST_URI'];
    else
    $scriptName = $_SERVER['PHP_SELF'];

    $basepath = preg_replace("#\/install(.*)$#i", '', $scriptName);

    if(!empty($_SERVER['HTTP_HOST']))
        $baseurl = 'http://'.$_SERVER['HTTP_HOST'];
    else
        $baseurl = "http://".$_SERVER['SERVER_NAME'];
    

    $rnd_cookieEncode = chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('a'),ord('z'))).chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('A'),ord('Z'))).chr(mt_rand(ord('a'),ord('z'))).mt_rand(1000,9999).chr(mt_rand(ord('A'),ord('Z')));

    if(file_get_contents($install_demo_name)) $isdemosign = 1;
    $module_local = DEDEDATA.'/module/';
    include('./templates/step-3.html');
    exit();
}
/*------------------------
��ͨ��װ
function _4_Setup()
------------------------*/
else if($step==4)
{
    $conn = mysql_connect($dbhost,$dbuser,$dbpwd) or die("<script>alert('���ݿ���������¼������Ч��\\n\\n�޷��������ݿ⣬�������趨��');history.go(-1);</script>");

    mysql_query("CREATE DATABASE IF NOT EXISTS `".$dbname."`;",$conn);
    
    mysql_select_db($dbname) or die("<script>alert('ѡ�����ݿ�ʧ�ܣ���������ûȨ�ޣ���Ԥ�ȴ���һ�����ݿ⣡');history.go(-1);</script>");

    //������ݿ�汾��Ϣ
    $rs = mysql_query("SELECT VERSION();",$conn);
    $row = mysql_fetch_array($rs);
    $mysqlVersions = explode('.',trim($row[0]));
    $mysqlVersion = $mysqlVersions[0].".".$mysqlVersions[1];

    mysql_query("SET NAMES '$dblang',character_set_client=binary,sql_mode='';",$conn);

    $fp = fopen(dirname(__FILE__)."/common.inc.php","r");
    $configStr1 = fread($fp,filesize(dirname(__FILE__)."/common.inc.php"));
    fclose($fp);

    $fp = fopen(dirname(__FILE__)."/config.cache.inc.php","r");
    $configStr2 = fread($fp,filesize(dirname(__FILE__)."/config.cache.inc.php"));
    fclose($fp);

    //common.inc.php
    $configStr1 = str_replace("~dbhost~",$dbhost,$configStr1);
    $configStr1 = str_replace("~dbname~",$dbname,$configStr1);
    $configStr1 = str_replace("~dbuser~",$dbuser,$configStr1);
    $configStr1 = str_replace("~dbpwd~",$dbpwd,$configStr1);
    $configStr1 = str_replace("~dbprefix~",$dbprefix,$configStr1);
    $configStr1 = str_replace("~dblang~",$dblang,$configStr1);

    @chmod(DEDEDATA,0777);
    $fp = fopen(DEDEDATA."/common.inc.php","w") or die("<script>alert('д������ʧ�ܣ�����../dataĿ¼�Ƿ��д�룡');history.go(-1);</script>");
    fwrite($fp,$configStr1);
    fclose($fp);

    //config.cache.inc.php
    $cmspath = trim(preg_replace("#\/{1,}#", '/', $cmspath));
    if($cmspath!='' && !preg_match("#^\/#", $cmspath)) $cmspath = '/'.$cmspath;

    if($cmspath=='') $indexUrl = '/';
    else $indexUrl = $cmspath;

    $configStr2 = str_replace("~baseurl~",$baseurl,$configStr2);
    $configStr2 = str_replace("~basepath~",$cmspath,$configStr2);
    $configStr2 = str_replace("~indexurl~",$indexUrl,$configStr2);
    $configStr2 = str_replace("~cookieEncode~",$cookieencode,$configStr2);
    $configStr2 = str_replace("~webname~",$webname,$configStr2);
    $configStr2 = str_replace("~adminmail~",$adminmail,$configStr2);

    $fp = fopen(DEDEDATA.'/config.cache.inc.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    $fp = fopen(DEDEDATA.'/config.cache.bak.php','w');
    fwrite($fp,$configStr2);
    fclose($fp);

    if($mysqlVersion >= 4.1)
    {
        $sql4tmp = "ENGINE=MyISAM DEFAULT CHARSET=".$dblang;
    }
  
    //�������ݱ�
  
    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dftables.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp,1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line."\n";
            $query = str_replace('#@__',$dbprefix,$query);
            if($mysqlVersion < 4.1)
            {
                $rs = mysql_query($query,$conn);
            } else {
                if(preg_match('#CREATE#i', $query))
                {
                    $rs = mysql_query(preg_replace("#TYPE=MyISAM#i",$sql4tmp,$query),$conn);
                }
                else
                {
                    $rs = mysql_query($query,$conn);
                }
            }
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);
    
    //����Ĭ������
    $query = '';
    $fp = fopen(dirname(__FILE__).'/sql-dfdata.txt','r');
    while(!feof($fp))
    {
        $line = rtrim(fgets($fp, 1024));
        if(preg_match("#;$#", $line))
        {
            $query .= $line;
            $query = str_replace('#@__',$dbprefix,$query);
            if($mysqlVersion < 4.1) $rs = mysql_query($query,$conn);
            else $rs = mysql_query(str_replace('#~lang~#',$dblang,$query),$conn);
            $query='';
        } else if(!preg_match("#^(\/\/|--)#", $line))
        {
            $query .= $line;
        }
    }
    fclose($fp);

    //��������
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$baseurl}' where varname='cfg_basehost';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$cmspath}' where varname='cfg_cmspath';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$indexUrl}' where varname='cfg_indexurl';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$cookieencode}' where varname='cfg_cookie_encode';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$webname}' where varname='cfg_webname';";
    mysql_query($cquery,$conn);
    $cquery = "Update `{$dbprefix}sysconfig` set value='{$adminmail}' where varname='cfg_adminemail';";
    mysql_query($cquery,$conn);
    
    //���ӹ���Ա�ʺ�
    $adminquery = "INSERT INTO `{$dbprefix}admin` VALUES (1, 10, '$adminuser', '".substr(md5($adminpwd),5,20)."', 'admin', '', '', 0, '".time()."', '127.0.0.1');";
    mysql_query($adminquery,$conn);
    
    //����ǰ̨��Ա�ʺ�
    $adminquery = "INSERT INTO `{$dbprefix}member` (`mid`,`userid`,`pwd`,`sex`,`rank`,`money`,`email`,
                   `scores` ,`matt` ,`face`,`jointime` ,`joinip` ,`logintime` ,`loginip` )
               VALUES ('1','$adminuser','".md5($adminpwd)."','��','100','0','','10000','10','','".time()."','','0',''); ";
    mysql_query($adminquery,$conn);

    $adminquery = "INSERT INTO `{$dbprefix}member_tj` (`mid`,`article`,`dg`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`)
                     VALUES ('1','0','0','0','0','0','0','0','0'); ";
    mysql_query($adminquery,$conn);
	

    //����װ�κο�ѡģ��
    if(!isset($modules) || !is_array($modules))
    {
        //������װ����
        $fp = fopen($insLockfile,'w');
        fwrite($fp,'ok');
        fclose($fp);
        include('./templates/step-5.html');
        exit();
    }
    else
    {
        $module = join(',',$modules);
        $fp = fopen($moduleCacheFile,'w');
        fwrite($fp,'<'.'?php'."\r\n");
        fwrite($fp,'$selModule = "'.$module.'"; '."\r\n");
        fwrite($fp,'?'.'>');
        //�������д�뻺���ļ����˳�ģ�鰲װ
        if(!$fp)
        {
            //������װ����
            $fp = fopen($insLockfile,'w');
            fwrite($fp,'ok');
            fclose($fp);
            $errmsg = "<font color='red'>�����޷�д��ģ�黺�棬��װ��ѡģ��ʧ�ܣ����¼����ģ�������װ��</font>";
            include('./templates/step-5.html');
            exit();
        }
        fclose($fp);
        include('./templates/step-4.html');
        exit();
    }
    exit();
}
/*------------------------
��װ��ѡģ��
function _5_SetupModule()
------------------------*/
else if($step==5)
{
    header("location:module-install.php");
    exit();
}
/*------------------------
������ݿ��Ƿ���Ч
function _10_TestDbPwd()
------------------------*/
else if($step==10)
{
    header("Pragma:no-cache\r\n");
    header("Cache-Control:no-cache\r\n");
    header("Expires:0\r\n");
    $conn = @mysql_connect($dbhost,$dbuser,$dbpwd);
    if($conn)
    {
		if(empty($dbname)){
			echo "<font color='green'>��Ϣ��ȷ</font>";
		}else{
			$info = mysql_select_db($dbname,$conn)?"<font color='red'>���ݿ��Ѿ����ڣ�ϵͳ���������ݿ�</font>":"<font color='green'>���ݿⲻ����,ϵͳ���Զ�����</font>";
			echo $info;
		}
    }
    else
    {
        echo "<font color='red'>���ݿ�����ʧ�ܣ�</font>";
    }
    @mysql_close($conn);
    exit();
}