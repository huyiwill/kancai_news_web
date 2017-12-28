<?php
/**
 * ����Ŀ¼�����ļ�
 *
 * @version        $Id: config.php 1 14:31 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
define('DEDEADMIN', str_replace("\\", '/', dirname(__FILE__) ) );
require_once(DEDEADMIN.'/../include/common.inc.php');
require_once(DEDEINC.'/userlogin.class.php');
header('Cache-Control:private');
$dsql->safeCheck = FALSE;
$dsql->SetLongLink();
$cfg_admin_skin = 1; // ��̨������

if(file_exists(DEDEDATA.'/admin/skin.txt'))
{
	$skin = file_get_contents(DEDEDATA.'/admin/skin.txt');
	$cfg_admin_skin = !in_array($skin, array(1,2,3,4))? 1 : $skin;
}

//��õ�ǰ�ű����ƣ�������ϵͳ��������$_SERVER�����������и������ѡ��
$dedeNowurl = $s_scriptName = '';
$isUrlOpen = @ini_get('allow_url_fopen');
$dedeNowurl = GetCurUrl();
$dedeNowurls = explode('?', $dedeNowurl);
$s_scriptName = $dedeNowurls[0];
$cfg_remote_site = empty($cfg_remote_site)? 'N' : $cfg_remote_site;

//�����û���¼״̬
$cuserLogin = new userLogin();
if($cuserLogin->getUserID()==-1)
{
    header("location:login.php?gotopage=".urlencode($dedeNowurl));
    exit();
}

function XSSClean($val)
{
    if (is_array($val))
    {
        while (list($key) = each($val))
        {
            $val[$key] = XSSClean($val[$key]);
        }
        return $val;
    }
    return RemoveXss($val);
}

if($cfg_dede_log=='Y')
{
    $s_nologfile = '_main|_list';
    $s_needlogfile = 'sys_|file_';
    $s_method = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : '';
    $s_query = isset($dedeNowurls[1]) ? $dedeNowurls[1] : '';
    $s_scriptNames = explode('/', $s_scriptName);
    $s_scriptNames = $s_scriptNames[count($s_scriptNames)-1];
    $s_userip = GetIP();
    if( $s_method=='POST' || (!preg_match("#".$s_nologfile."#i", $s_scriptNames) && $s_query!='') || preg_match("#".$s_needlogfile."#i",$s_scriptNames) )
    {
        $inquery = "INSERT INTO `#@__log`(adminid,filename,method,query,cip,dtime)
             VALUES ('".$cuserLogin->getUserID()."','{$s_scriptNames}','{$s_method}','".addslashes($s_query)."','{$s_userip}','".time()."');";
        $dsql->ExecuteNoneQuery($inquery);
    }
}

//����Զ��վ���򴴽�FTP��
if($cfg_remote_site=='Y')
{
    require_once(DEDEINC.'/ftp.class.php');
    if(file_exists(DEDEDATA."/cache/inc_remote_config.php"))
    {
        require_once DEDEDATA."/cache/inc_remote_config.php";
    }
    if(empty($remoteuploads)) $remoteuploads = 0;
    if(empty($remoteupUrl)) $remoteupUrl = '';
    $config = array(
      'hostname' => $GLOBALS['cfg_ftp_host'],
      'username' => $GLOBALS['cfg_ftp_user'],
      'password' => $GLOBALS['cfg_ftp_pwd'],
      'debug' => 'TRUE'
    );
    $ftp = new FTP($config); 

    //��ʼ��FTP����
    if($remoteuploads==1){
        $ftpconfig = array(
            'hostname'=>$rmhost, 
            'port'=>$rmport,
            'username'=>$rmname,
            'password'=>$rmpwd
        );
    }
}

//�����桢����ԱƵ������
$cache1 = DEDEDATA.'/cache/inc_catalog_base.inc';
if(!file_exists($cache1)) UpDateCatCache();
$cacheFile = DEDEDATA.'/cache/admincat_'.$cuserLogin->userID.'.inc';
if(file_exists($cacheFile)) require_once($cacheFile);

//���·�����
require_once (DEDEDATA.'/admin/config_update.php');

if(strlen($cfg_cookie_encode)<=10)
{
    $chars='abcdefghigklmnopqrstuvwxwyABCDEFGHIGKLMNOPQRSTUVWXWY0123456789';
    $hash='';
    $length = rand(28,32);
    $max = strlen($chars) - 1;
    for($i = 0; $i < $length; $i++) {
        $hash .= $chars[mt_rand(0, $max)];
    }
	$dsql->ExecuteNoneQuery("UPDATE `#@__sysconfig` SET `value`='{$hash}' WHERE varname='cfg_cookie_encode' ");
	$configfile = DEDEDATA.'/config.cache.inc.php';
    if(!is_writeable($configfile))
    {
        echo "�����ļ�'{$configfile}'��֧��д�룬�޷��޸�ϵͳ���ò�����";
        exit();
    }
    $fp = fopen($configfile,'w');
    flock($fp,3);
    fwrite($fp,"<"."?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC ");
    $dsql->Execute();
    while($row = $dsql->GetArray())
    {
        if($row['type']=='number')
        {
            if($row['value']=='') $row['value'] = 0;
            fwrite($fp,"\${$row['varname']} = ".$row['value'].";\r\n");
        }
        else
        {
            fwrite($fp,"\${$row['varname']} = '".str_replace("'",'',$row['value'])."';\r\n");
        }
    }
    fwrite($fp,"?".">");
    fclose($fp);
}

/**
 *  ������Ŀ����
 *
 * @access    public
 * @return    void
 */
function UpDateCatCache()
{
    global $dsql, $cfg_multi_site, $cache1, $cacheFile, $cuserLogin;
    $cache2 = DEDEDATA.'/cache/channelsonlist.inc';
    $cache3 = DEDEDATA.'/cache/channeltoplist.inc';
    $dsql->SetQuery("SELECT id,reid,channeltype,issend,typename FROM `#@__arctype`");
    $dsql->Execute();
    $fp1 = fopen($cache1,'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$cfg_Cs;\r\n\$cfg_Cs=array();\r\n";
    fwrite($fp1,$fp1Header);
    while($row=$dsql->GetObject())
    {
        // ��typename��������
        $row->typename = base64_encode($row->typename);
        fwrite($fp1,"\$cfg_Cs[{$row->id}]=array({$row->reid},{$row->channeltype},{$row->issend},'{$row->typename}');\r\n");
    }
    fwrite($fp1, "{$phph}>");
    fclose($fp1);
    $cuserLogin->ReWriteAdminChannel();
    @unlink($cache2);
    @unlink($cache3);
}

// ���ѡ���
function ClearOptCache()
{
    $tplCache = DEDEDATA.'/tplcache/';
    $fileArray = glob($tplCache."inc_option_*.inc");
    if (count($fileArray) > 1)
    {
        foreach ($fileArray as $key => $value)
        {
            if (file_exists($value)) unlink($value);
            else continue;
        }
        return TRUE;
    }
    return FALSE;
}

/**
 *  ���»�Աģ�ͻ���
 *
 * @access    public
 * @return    void
 */
function UpDateMemberModCache()
{
    global $dsql;
    $cachefile = DEDEDATA.'/cache/member_model.inc';

    $dsql->SetQuery("SELECT * FROM `#@__member_model` WHERE state='1'");
    $dsql->Execute();
    $fp1 = fopen($cachefile,'w');
    $phph = '?';
    $fp1Header = "<{$phph}php\r\nglobal \$_MemberMod;\r\n\$_MemberMod=array();\r\n";
    fwrite($fp1,$fp1Header);
    while($row=$dsql->GetObject())
    {
        fwrite($fp1,"\$_MemberMod[{$row->id}]=array('{$row->name}','{$row->table}');\r\n");
    }
    fwrite($fp1,"{$phph}>");
    fclose($fp1);
}

/**
 *  ����ģ���ļ�
 *
 * @access    public
 * @param     string  $filename  �ļ�����
 * @param     bool  $isabs  �Ƿ�Ϊ����Ŀ¼
 * @return    string
 */
function DedeInclude($filename, $isabs=FALSE)
{
    return $isabs ? $filename : DEDEADMIN.'/'.$filename;
}

/**
 *  ��ȡ��ǰ�û���ftpվ��
 *
 * @access    public
 * @param     string  $current  ��ǰվ��
 * @param     string  $formname  ������
 * @return    string
 */
function GetFtp($current='', $formname='')
{
    global $dsql;
    $formname = empty($formname)? 'serviterm' : $formname;
    $cuserLogin = new userLogin();
    $row=$dsql->GetOne("SELECT servinfo FROM `#@__multiserv_config`");
    $row['servinfo']=trim($row['servinfo']);
    if(!empty($row['servinfo'])){
        $servinfos = explode("\n", $row['servinfo']);
        $select="";
        echo '<select name="'.$formname.'" size="1" id="serviterm">';
        $i=0;
        foreach($servinfos as $servinfo){
            $servinfo = trim($servinfo);
            list($servname,$servurl,$servport,$servuser,$servpwd,$userlist) = explode('|',$servinfo);
            $servname = trim($servname);
            $servurl = trim($servurl);
            $servport = trim($servport);
            $servuser = trim($servuser);
            $servpwd = trim($servpwd);
            $userlist = trim($userlist);   
            $checked = ($current == $i)? '  selected="selected"' : '';
            if(strstr($userlist,$cuserLogin->getUserName()))
            {
                $select.="<option value='".$servurl.",".$servuser.",".$servpwd."'{$checked}>".$servname."</option>";  
            }
            $i++;
        }
        echo  $select."</select>";
    }
}
helper('cache');
/**
 *  �����û�mid��ȡ�û�����
 *
 * @access    public
 * @param     int  $mid   �û�ID
 * @return    string
 */
if(!function_exists('GetMemberName')){
	function GetMemberName($mid=0)
	{
		global $dsql;
		$rs = GetCache('memberlogin', $mid);
		if( empty($rs) )
		{
			$rs = $dsql->GetOne("SELECT * FROM `#@__member` WHERE mid='{$mid}' ");
			SetCache('memberlogin', $mid, $rs, 1800);
		}
		return $rs['uname'];
	}
}
