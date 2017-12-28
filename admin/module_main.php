<?php
/**
 * ģ�����
 *
 * @version        $Id: module_main.php 1 14:17 2010��7��20��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
CheckPurview('sys_module');
require_once(dirname(__FILE__)."/../include/dedemodule.class.php");
require_once(dirname(__FILE__)."/../include/oxwindow.class.php");
if(empty($action)) $action = '';
require_once(DEDEDATA."/admin/config_update.php");
$mdir = DEDEDATA.'/module';
$mdurl = $updateHost.'dedecms/module_'.$cfg_soft_lang.'/modulelist.txt';

function TestWriteAble($d)
{
    $tfile = '_dedet.txt';
    $d = preg_replace("#\/$#", '', $d);
    $fp = @fopen($d.'/'.$tfile,'w');
    if(!$fp) return FALSE;
    else
    {
        fclose($fp);
        $rs = @unlink($d.'/'.$tfile);
        if($rs) return TRUE;
        else return FALSE;
    }
}

function ReWriteConfigAuto()
{
    global $dsql;
    $configfile = DEDEDATA.'/config.cache.inc.php';
    if(!is_writeable($configfile))
    {
        echo "�����ļ�'{$configfile}'��֧��д�룬�޷��޸�ϵͳ���ò�����";
        //ClearAllLink();
        exit();
    }
    $fp = fopen($configfile,'w');
    flock($fp,3);
    fwrite($fp,"<"."?php\r\n");
    $dsql->SetQuery("SELECT `varname`,`type`,`value`,`groupid` FROM `#@__sysconfig` ORDER BY aid ASC ");
    $dsql->Execute();
    while($row = $dsql->GetArray())
    {
        if($row['type']=='number') fwrite($fp,"\${$row['varname']} = ".$row['value'].";\r\n");
        else fwrite($fp,"\${$row['varname']} = '".str_replace("'",'',$row['value'])."';\r\n");
    }
    fwrite($fp,"?".">");
    fclose($fp);
}


function SendData($hash = '',$type = 1)
{
    if(!empty($hash)){
        global $cfg_basehost;
        $str = "basehost=".$cfg_basehost."&hash=".$hash."&type=".$type;
        $fp = fsockopen('www.dedecms.com',80,$errno,$errstr,30); 
        if(!$fp)
        {
            return FALSE;
        }else{ 
            fputs($fp, "POST http://www.dedecms.com/plugin.php HTTP/1.1\r\n"); 
            fputs($fp, "Host: www.dedecms.com\r\n"); 
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
            fputs($fp, "Content-length: ".strlen($str)."\r\n"); 
            fputs($fp, "Connection: close\r\n\r\n"); 
            fputs($fp, $str."\r\n\r\n"); 
            fclose($fp); 
        }
    }else{
        return FALSE; 
    }  
}
/*--------------
function ShowAll();
--------------*/
if($action=='')
{
    $types = array('soft'=>'ģ��','templets'=>'ģ��','plus'=>'С���','patch'=>'����');
    $dm = new DedeModule($mdir);
    if(empty($moduletype)) $moduletype = '';
	
	$modules_remote = $dm->GetModuleUrlList($moduletype,$mdurl);
	$modules = array();
	$modules = $dm->GetModuleList($moduletype);
	is_array($modules) || $modules = array();
	$modules = array_merge($modules,$modules_remote);
    require_once(dirname(__FILE__)."/templets/module_main.htm");
    $dm->Clear();
    exit();
}
/*--------------
function Setup();
--------------*/
else if($action=='setup')
{
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);

    if($infos['url']=='') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br /><font color="red">(���ģ������Ա�������ϵͳ�ı��벻һ�£����򿪷���ȷ�����ļ�����)</font>');

    $filelists = $dm->GetFileLists($hash);
    $filelist = '';
    $prvdirs = array();
    $incdir = array();
    foreach($filelists as $v)
    {
        if(empty($v['name'])) continue;
        if($v['type']=='dir')
        {
            $v['type'] = 'Ŀ¼';
            $incdir[] = $v['name'];
        }
        else
        {
            $v['type'] = '�ļ�';
        }
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    //�����Ҫ��Ŀ¼Ȩ��
    foreach($filelists as $v)
    {
        $prvdir = preg_replace("#\/([^\/]*)$#", '/', $v['name']);
        if(!preg_match("#^\.#", $prvdir)) $prvdir = './';
        $n = TRUE;
        foreach($incdir as $k=>$v)
        {
            if(preg_match("#^".$v."#i", $prvdir))
            {
                $n = FALSE;
                BREAK;
            }
        }
        if(!isset($prvdirs[$prvdir]) && $n && is_dir($prvdir))
        {
            $prvdirs[$prvdir][0] = 1;
            $prvdirs[$prvdir][1] = TestWriteAble($prvdir);
        }
    }
    $prvdir = "<table cellpadding='1' cellspacing='1' width='350' bgcolor='#cfcfcf' style='margin-top:5px;'>\r\n";
    $prvdir .= "<tr style='background:#FBFCE2'><th width='270'>Ŀ¼</td><th align='center'>��д</td></tr>\r\n";
    foreach($prvdirs as $k=>$v)
    {
        if($v) $cw = '��';
        else $cw = '<font color="red">��</font>';
        $prvdir .= "<tr bgcolor='#ffffff'><td >$k</td>";
        $prvdir .= "<td align='center' >$cw</td></tr>\r\n";
    }
    $prvdir .= "</table>";

    $win = new OxWindow();
    $win->Init("module_main.php","js/blank.js","post");
    $wecome_info = "ģ�����";
    $win->AddTitle("&nbsp;<a href='module_main.php'>ģ�����</a> &gt;&gt; ��װģ�飺 {$infos['name']}");
    $win->AddHidden("hash",$hash);
    $win->AddHidden("action",'setupstart');
    if(trim($infos['url'])=='') $infos['url'] = '��';
    $msg = "<style>.dtb{border-bottom:1px dotted #cccccc}</style>
    <table width='98%' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='20%' height='28' class='dtb'>ģ�����ƣ�</td>
    <td width='80%' class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>���ԣ�</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ļ���С��</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�Ŷ����ƣ�</td>
    <td class='dtb'>{$infos['team']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>����ʱ�䣺</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�������䣺</td>
    <td class='dtb'>{$infos['email']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ٷ���ַ��</td>
    <td class='dtb'>{$infos['url']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>ʹ��Э�飺</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank'>������...</a></td>
  </tr>
  <tr>
    <td height='30' class='dtb' bgcolor='#F9FCEF' colspan='2'>
    <b>ע�����</b>
    ��װʱ��ȷ���ļ��б����漰��Ŀ¼ǰ��д��Ȩ�ޣ����⡰��̨����Ŀ¼��������̨����Ŀ¼/templets��Ŀ¼Ҳ������ʱ���ÿ�д��Ȩ�ޡ�
    </td>
  </tr>
  <tr>
    <td height='30'><b>Ŀ¼Ȩ�޼�⣺</b><br /> ../ Ϊ��Ŀ¼ <br /> ./ ��ʾ��ǰĿ¼</td>
    <td>
    $prvdir
    </td>
  </tr>
  <tr>
    <td height='30'>ģ������������ļ��б�</td>
    <td></td>
  </tr>
  <tr>
    <td height='164' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:90%;height:200px'>{$filelist}</textarea>
    </td>
  </tr>
  <tr>
    <td height='28'>�����Ѵ����ļ���������</td>
    <td>
   <input name='isreplace' type='radio' value='1' checked='checked' />
    ����
   <input name='isreplace' type='radio' value='3' />
   ���ǣ���������
   <input type='radio' name='isreplace' value='0' />
   �������ļ�
   </td>
  </tr>
</table>
    ";
    $win->AddMsgItem("<div style='padding-left:10px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow("ok","");
    $win->Display();
    $dm->Clear();
    exit();
}
/*---------------
function SetupRun()
--------------*/
else if($action=='setupstart')
{
    if(!is_writeable($mdir))
    {
        ShowMsg("Ŀ¼ {$mdir} ��֧��д�룬�⽫���°�װ����û������������","-1");
        exit();
    }
    $dm = new DedeModule($mdir);

    $minfos = $dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);

    $menustring = addslashes($dm->GetSystemFile($hash,'menustring'));
    $indexurl = str_replace('**', '=', $indexurl);

    $query = "INSERT INTO `#@__sys_module`(`hashcode` , `modname` , `indexname` , `indexurl` , `ismember` , `menustring` )
                                    VALUES ('$hash' , '$name' , '$indexname' , '$indexurl' , '$ismember' , '$menustring' ) ";

    $rs = $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash' ");
    $rs = $dsql->ExecuteNoneQuery($query);
    if(!$rs)
    {
        ShowMsg('�������ݿ���Ϣʧ�ܣ��޷���ɰ�װ��'.$dsql->GetError(),'javascript:;');
        exit();
    }

    $dm->WriteFiles($hash,$isreplace);
    $filename = '';
    if(!isset($autosetup) || $autosetup==0) $filename = $dm->WriteSystemFile($hash, 'setup');
    if(!isset($autodel) || $autodel==0) $dm->WriteSystemFile($hash, 'uninstall');
    $dm->WriteSystemFile($hash,'readme');
    $dm->Clear();

    //��ģ��İ�װ����װ
    if(!isset($autosetup) || $autosetup==0)
    {
        include(DEDEDATA.'/module/'.$filename);
        exit();
    }
    //ϵͳ�Զ���װ
    else
    {
        $mysql_version = $dsql->GetVersion(TRUE);
        //Ĭ��ʹ��MySQL 4.1 ���°汾��SQL��䣬�Դ���4.1�汾�����滻���� TYPE=MyISAM ==> ENGINE=MyISAM DEFAULT CHARSET=#~lang~#
        $setupsql = $dm->GetSystemFile($hash, 'setupsql40');

        $setupsql = preg_replace("#ENGINE=MyISAM#i", 'TYPE=MyISAM', $setupsql);
        $sql41tmp = 'ENGINE=MyISAM DEFAULT CHARSET='.$cfg_db_language;
        
        if($mysql_version >= 4.1)
        {
            $setupsql = preg_replace("#TYPE=MyISAM#i", $sql41tmp, $setupsql);
        }

        //_ROOTURL_
        if($cfg_cmspath=='/') $cfg_cmspath = '';

        $rooturl = $cfg_basehost.$cfg_cmspath;
        
        $setupsql = preg_replace("#_ROOTURL_#i", $rooturl, $setupsql);
        $setupsql = preg_replace("#[\r\n]{1,}#", "\n", $setupsql);

        $sqls = @split(";[ \t]{0,}\n", $setupsql);
        foreach($sqls as $sql)
        {
            if(trim($sql)!='') $dsql->ExecuteNoneQuery($sql);
        }

        ReWriteConfigAuto();

        $rflwft = "<script language='javascript' type='text/javascript'>\r\n";
        $rflwft .= "if(window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;

        UpDateCatCache();
        SendData($hash);
        ShowMsg('ģ�鰲װ���...', 'module_main.php');
        exit();
    }
}
/*--------------
function DelModule();
--------------*/
else if($action=='del')
{
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);

    if($infos['url']=='') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang']==$cfg_soft_lang ? '' : '<br /><font color="red">(���ģ������Ա�������ϵͳ�ı��벻һ�£����򿪷���ȷ�����ļ�����)</font>');

    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "ģ�����";
    $win->AddTitle("<a href='module_main.php'>ģ�����</a> &gt;&gt; ɾ��ģ�飺 {$infos['name']}");
    $win->AddHidden('hash', $hash);
    $win->AddHidden('action', 'delok');
    $msg = "<style>.dtb{border-bottom:1px dotted #cccccc}</style>
    <table width='750' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='200' height='28' class='dtb'>ģ�����ƣ�</td>
    <td width='550' class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>���ԣ�</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td width='200' height='28' class='dtb'>�ļ���С��</td>
    <td width='550' class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�Ŷ����ƣ�</td>
    <td class='dtb'>{$infos['team']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>����ʱ�䣺</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�������䣺</td>
    <td class='dtb'>{$infos['email']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ٷ���ַ��</td>
    <td class='dtb'>{$infos['url']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>ʹ��Э�飺</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank'>������...</a></td>
  </tr>
  <tr>
    <td height='28' colspan='2'>
    ɾ��ģ���ɾ�����ģ��İ�װ���ļ���������Ѿ���װ����ִ��<a href='module_main.php?hash={$hash}&action=uninstall'><u>ж�س���</u></a>��ɾ����
   </td>
  </tr>
</table>
    ";
    $win->AddMsgItem("<div style='padding-left:10px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow("ok","");
    $win->Display();
    $dm->Clear();
    exit();
}
else if($action=='delok')
{
    $dm = new DedeModule($mdir);
    $modfile = $mdir."/".$dm->GetHashFile($hash);
    unlink($modfile) or die("ɾ���ļ� {$modfile} ʧ�ܣ�");
    ShowMsg("�ɹ�ɾ��һ��ģ���ļ���","module_main.php");
    exit();
}
/*--------------
function UnInstall();
--------------*/
else if($action=='uninstall')
{
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);

    if($infos['url']=='') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang']==$cfg_soft_lang ? '' : '<br /><font color="red">(���ģ������Ա�������ϵͳ�ı��벻һ�£����򿪷���ȷ�����ļ�����)</font>');

    $filelists = $dm->GetFileLists($hash);
    $filelist = '';
    foreach($filelists as $v)
    {
        if(empty($v['name'])) continue;
        if($v['type']=='dir') $v['type'] = 'Ŀ¼';
        else $v['type'] = '�ļ�';
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    $win = new OxWindow();
    $win->Init("module_main.php", "js/blank.js", "post");
    $wecome_info = "ģ�����";
    $win->AddTitle("<a href='module_main.php'>ģ�����</a> &gt;&gt; ж��ģ�飺 {$infos['name']}");
    $win->AddHidden("hash",$hash);
    $win->AddHidden("action",'uninstallok');
    $msg = "<style>.dtb{border-bottom:1px dotted #cccccc}</style>
    <table width='750' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='200' height='28' class='dtb'>ģ�����ƣ�</td>
    <td width='550' class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>���ԣ�</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td width='200' height='28' class='dtb'>�ļ���С��</td>
    <td width='550' class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�Ŷ����ƣ�</td>
    <td class='dtb'>{$infos['team']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>����ʱ�䣺</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�������䣺</td>
    <td class='dtb'>{$infos['email']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ٷ���ַ��</td>
    <td class='dtb'>{$infos['url']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>ʹ��Э�飺</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank'>������...</a></td>
  </tr>
  <tr>
    <td height='28'>ģ��������ļ���<br />(�ļ�·������ڵ�ǰĿ¼)</td><td>&nbsp;</td>
  </tr>
  <tr>
    <td height='164' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:90%;height:200px'>{$filelist}</textarea>
    </td>
  </tr>
  <tr>
    <td height='28'>����ģ����ļ���������</td>
    <td>
    <input type='radio' name='isreplace' value='0' checked='checked' />
    �ֹ�ɾ���ļ���������ж�س���
   <input name='isreplace' type='radio' value='2' />
    ɾ��ģ��������ļ�
   </td>
  </tr>
</table>
    ";
    $win->AddMsgItem("<div style='padding-left:10px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow("ok","");
    $win->Display();
    $dm->Clear();
    exit();
}
/*--------------
function UnInstallRun();
--------------*/
else if($action=='uninstallok')
{
    $dsql->ExecuteNoneQuery("DELETE FROM `#@__sys_module` WHERE hashcode LIKE '$hash' ");
    $dm = new DedeModule($mdir);

    $minfos = $dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);

    if(!isset($moduletype) || $moduletype != 'patch' )
    {
        $dm->DeleteFiles($hash, $isreplace);
    }
    @$dm->DelSystemFile($hash, 'readme');
    @$dm->DelSystemFile($hash, 'setup');
    $dm->Clear();
    if(!isset($autodel) || $autodel==0)
    {
        include(DEDEDATA."/module/{$hash}-uninstall.php");
        @unlink(DEDEDATA."/module/{$hash}-uninstall.php");
        exit();
    }
    else
    {
        @$dm->DelSystemFile($hash, 'uninstall');
        $delsql = $dm->GetSystemFile($hash, 'delsql');
        if(trim($delsql)!='')
        {
            $sqls = explode(';', $delsql);
            foreach($sqls as $sql)
            {
                if(trim($sql)!='') $dsql->ExecuteNoneQuery($sql);
            }
        }
        
        ReWriteConfigAuto();
        
        $rflwft = "<script language='javascript' type='text/javascript'>\r\n";
        $rflwft .= "if(window.navigator.userAgent.indexOf('MSIE')>=1) top.document.frames.menu.location = 'index_menu_module.php';\r\n";
        $rflwft .= "else top.document.getElementById('menufra').src = 'index_menu_module.php';\r\n";
        $rflwft .= "</script>";
        echo $rflwft;
        SendData($hash,2);
        ShowMsg('ģ��ж�����...','module_main.php');
        exit();
    }
}
/*--------------
function ShowReadme();
--------------*/
else if($action=='showreadme')
{
    $dm = new DedeModule($mdir);
    $msg = $dm->GetSystemFile($hash,'readme');
    $msg = preg_replace("/(.*)<body/isU","",$msg);
    $msg = preg_replace("/<\/body>(.*)/isU","",$msg);
    $dm->Clear();
    $win = new OxWindow();
    $win->Init("module_main.php","js/blank.js","post");
    $wecome_info = "ģ�����";
    $win->AddTitle("<a href='module_main.php'>ģ�����</a> &gt;&gt; ʹ��˵����");
    $win->AddMsgItem("<div style='padding-left:10px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow("hand");
    $win->Display();
    exit();
}
/*--------------
function ViewOne();
--------------*/
else if($action=='view')
{
    $dm = new DedeModule($mdir);
    $infos = $dm->GetModuleInfo($hash);

    if($infos['url']=='') $infos['url'] = '&nbsp;';
    $alertMsg = ($infos['lang'] == $cfg_soft_lang ? '' : '<br /><font color="red">(���ģ������Ա�������ϵͳ�ı��벻һ�£����򿪷���ȷ�����ļ�����)</font>');

    $filelists = $dm->GetFileLists($hash);
    $filelist = '';
    $setupinfo = '';
    foreach($filelists as $v)
    {
        if(empty($v['name'])) continue;
        if($v['type']=='dir') $v['type'] = 'Ŀ¼';
        else $v['type'] = '�ļ�';
        $filelist .= "{$v['type']}|{$v['name']}\r\n";
    }
    if(file_exists(DEDEDATA."/module/{$hash}-readme.php")) 
    {
        $setupinfo = "�Ѱ�װ <a href='module_main.php?action=uninstall&hash={$hash}'>ж��</a>";
    } else {
        $setupinfo = "δ��װ <a href='module_main.php?action=setup&hash={$hash}'>��װ</a>";
    }
    $win = new OxWindow();
    $win->Init("", "js/blank.js","");
    $wecome_info = "ģ�����";
    $win->AddTitle("<a href='module_main.php'>ģ�����</a> &gt;&gt; ģ�����飺 {$infos['name']}");
    $msg = "<style>.dtb{border-bottom:1px dotted #cccccc}</style>
    <table width='98%' border='0' cellspacing='0' cellpadding='0'>
  <tr>
    <td width='20%' height='28' class='dtb'>ģ�����ƣ�</td>
    <td width='80%' class='dtb'>{$infos['name']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>���ԣ�</td>
    <td class='dtb'>{$infos['lang']} {$alertMsg}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ļ���С��</td>
    <td class='dtb'>{$infos['filesize']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�Ƿ��Ѱ�װ��</td>
    <td class='dtb'>{$setupinfo}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�Ŷ����ƣ�</td>
    <td class='dtb'>{$infos['team']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>����ʱ�䣺</td>
    <td class='dtb'>{$infos['time']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�������䣺</td>
    <td class='dtb'>{$infos['email']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>�ٷ���ַ��</td>
    <td class='dtb'>{$infos['url']}</td>
  </tr>
  <tr>
    <td height='28' class='dtb'>ʹ��Э�飺</td>
    <td class='dtb'><a href='module_main.php?action=showreadme&hash={$hash}' target='_blank'>������...</a></td>
  </tr>
  <tr>
    <td height='28'>ģ��������ļ���<br />(�ļ�·������ڵ�ǰĿ¼)</td><td>&nbsp;</td>
  </tr>
  <tr>
    <td height='164' colspan='2'>
     <textarea name='filelists' id='filelists' style='width:90%;height:200px'>{$filelist}</textarea>
    </td>
  </tr>
</table>
    ";
    $win->AddMsgItem("<div style='padding-left:10px;line-height:150%'>$msg</div>");
    $winform = $win->GetWindow('hand', '');
    $win->Display();
    $dm->Clear();
    exit();
}
/*--------------
function Edit();
--------------*/
else if($action=='edit')
{
    $dm = new DedeModule($mdir);

    $minfos = $dm->GetModuleInfo($hash);
    extract($minfos, EXTR_SKIP);

    if(!isset($lang)) $lang = 'gb2312';
    if(!isset($moduletype)) $moduletype = 'soft';

    $menustring = $dm->GetSystemFile($hash, 'menustring');
    $setupsql40 = htmlspecialchars($dm->GetSystemFile($hash, 'setupsql40'));
    $readmetxt = $dm->GetSystemFile($hash, 'readme');
    $delsql = $dm->GetSystemFile($hash, 'delsql');
    $filelist = $dm->GetSystemFile($hash,'oldfilelist',false);
    $indexurl = str_replace('**', '=', $indexurl);
    $dm->Clear();
    
    require_once(dirname(__FILE__).'/templets/module_edit.htm');
    exit();
}
/*--------------
function Download();
--------------*/
else if($action=='download')
{
	$model_remote_url = $updateHost.'dedecms/module_'.$cfg_soft_lang.'/'.$hash.'.xml';
	$model_remote = file_get_contents($model_remote_url);
	file_put_contents($mdir.'/'.$hash.'.xml',$model_remote);
	echo "δ��װ <a href='module_main.php?action=setup&hash={$hash}'><u>��װ</u></a>";
}