<?php   if(!defined('DEDEINC')) exit('dedecms');
/**
 * ����Ա��̨��������
 *
 * @version        $Id:inc_fun_funAdmin.php 1 13:58 2010��7��5��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

/**
 *  ��ȡƴ����Ϣ
 *
 * @access    public
 * @param     string  $str  �ַ���
 * @param     int  $ishead  �Ƿ�Ϊ����ĸ
 * @param     int  $isclose  �������Ƿ��ͷ���Դ
 * @return    string
 */
function SpGetPinyin($str, $ishead=0, $isclose=1)
{
    global $pinyins;
    $restr = '';
    $str = trim($str);
    $slen = strlen($str);
    if($slen < 2)
    {
        return $str;
    }
    if(count($pinyins) == 0)
    {
        $fp = fopen(DEDEINC.'/data/pinyin.dat', 'r');
        while(!feof($fp))
        {
            $line = trim(fgets($fp));
            $pinyins[$line[0].$line[1]] = substr($line, 3, strlen($line)-3);
        }
        fclose($fp);
    }
    for($i=0; $i<$slen; $i++)
    {
        if(ord($str[$i])>0x80)
        {
            $c = $str[$i].$str[$i+1];
            $i++;
            if(isset($pinyins[$c]))
            {
                if($ishead==0)
                {
                    $restr .= $pinyins[$c];
                }
                else
                {
                    $restr .= $pinyins[$c][0];
                }
            }else
            {
                $restr .= "_";
            }
        }else if( preg_match("/[a-z0-9]/i", $str[$i]) )
        {
            $restr .= $str[$i];
        }
        else
        {
            $restr .= "_";
        }
    }
    if($isclose==0)
    {
        unset($pinyins);
    }
    return $restr;
}


/**
 *  ����Ŀ¼
 *
 * @access    public
 * @param     string  $spath Ŀ¼����
 * @return    string
 */
function SpCreateDir($spath)
{
    global $cfg_dir_purview,$cfg_basedir,$cfg_ftp_mkdir,$isSafeMode;
    if($spath=='')
    {
        return true;
    }
    $flink = false;
    $truepath = $cfg_basedir;
    $truepath = str_replace("\\","/",$truepath);
    $spaths = explode("/",$spath);
    $spath = "";
    foreach($spaths as $spath)
    {
        if($spath=="")
        {
            continue;
        }
        $spath = trim($spath);
        $truepath .= "/".$spath;
        if(!is_dir($truepath) || !is_writeable($truepath))
        {
            if(!is_dir($truepath))
            {
                $isok = MkdirAll($truepath,$cfg_dir_purview);
            }
            else
            {
                $isok = ChmodAll($truepath,$cfg_dir_purview);
            }
            if(!$isok)
            {
                echo "�������޸�Ŀ¼��".$truepath." ʧ�ܣ�<br>";
                CloseFtp();
                return false;
            }
        }
    }
    CloseFtp();
    return true;
}

function jsScript($js)
{
	$out = "<script type=\"text/javascript\">";
	$out .= "//<![CDATA[\n";
	$out .= $js;
	$out .= "\n//]]>";
	$out .= "</script>\n";

	return $out;
}

/**
 *  ��ȡ�༭��
 *
 * @access    public
 * @param     string  $fname ������
 * @param     string  $fvalue ��ֵ
 * @param     string  $nheight ���ݸ߶�
 * @param     string  $etype �༭������
 * @param     string  $gtype ��ȡֵ����
 * @param     string  $isfullpage �Ƿ�ȫ��
 * @return    string
 */
function SpGetEditor($fname,$fvalue,$nheight="350",$etype="Basic",$gtype="print",$isfullpage="false",$bbcode=false)
{
    global $cfg_ckeditor_initialized;
    if(!isset($GLOBALS['cfg_html_editor']))
    {
        $GLOBALS['cfg_html_editor']='fck';
    }
    if($gtype=="")
    {
        $gtype = "print";
    }
    if($GLOBALS['cfg_html_editor']=='fck')
    {
        require_once(DEDEINC.'/FCKeditor/fckeditor.php');
        $fck = new FCKeditor($fname);
        $fck->BasePath        = $GLOBALS['cfg_cmspath'].'/include/FCKeditor/' ;
        $fck->Width        = '100%' ;
        $fck->Height        = $nheight ;
        $fck->ToolbarSet    = $etype ;
        $fck->Config['FullPage'] = $isfullpage;
        if($GLOBALS['cfg_fck_xhtml']=='Y')
        {
            $fck->Config['EnableXHTML'] = 'true';
            $fck->Config['EnableSourceXHTML'] = 'true';
        }
        $fck->Value = $fvalue ;
        if($gtype=="print")
        {
            $fck->Create();
        }
        else
        {
            return $fck->CreateHtml();
        }
    }
    else if($GLOBALS['cfg_html_editor']=='ckeditor')
    {
        require_once(DEDEINC.'/ckeditor/ckeditor.php');
        $CKEditor = new CKEditor();
        $CKEditor->basePath = $GLOBALS['cfg_cmspath'].'/include/ckeditor/' ;
        $config = $events = array();
        $config['extraPlugins'] = 'dedepage,multipic,addon';
		if($bbcode)
		{
			$CKEditor->initialized = true;
			$config['extraPlugins'] .= ',bbcode';
			$config['fontSize_sizes'] = '30/30%;50/50%;100/100%;120/120%;150/150%;200/200%;300/300%';
			$config['disableObjectResizing'] = 'true';
			$config['smiley_path'] = $GLOBALS['cfg_cmspath'].'/images/smiley/';
			// ��ȡ������Ϣ
			require_once(DEDEDATA.'/smiley.data.php');
			$jsscript = array();
			foreach($GLOBALS['cfg_smileys'] as $key=>$val)
			{
				$config['smiley_images'][] = $val[0];
				$config['smiley_descriptions'][] = $val[3];
				$jsscript[] = '"'.$val[3].'":"'.$key.'"';
			}
			$jsscript = implode(',', $jsscript);
			echo jsScript('CKEDITOR.config.ubb_smiley = {'.$jsscript.'}');
		}

        $GLOBALS['tools'] = empty($toolbar[$etype])? $GLOBALS['tools'] : $toolbar[$etype] ;
        $config['toolbar'] = $GLOBALS['tools'];
        $config['height'] = $nheight;
        $config['skin'] = 'kama';
        $CKEditor->returnOutput = TRUE;
        $code = $CKEditor->editor($fname, $fvalue, $config, $events);
        if($gtype=="print")
        {
            echo $code;
        }
        else
        {
            return $code;
        }
    }
    else { 
        /*
        // ------------------------------------------------------------------------
        // ��ǰ�汾,��ʱȡ��dedehtml�༭����֧��
        // ------------------------------------------------------------------------
        require_once(DEDEINC.'/htmledit/dede_editor.php');
        $ded = new DedeEditor($fname);
        $ded->BasePath        = $GLOBALS['cfg_cmspath'].'/include/htmledit/' ;
        $ded->Width        = '100%' ;
        $ded->Height        = $nheight ;
        $ded->ToolbarSet = strtolower($etype);
        $ded->Value = $fvalue ;
        if($gtype=="print")
        {
            $ded->Create();
        }
        else
        {
            return $ded->CreateHtml();
        }
        */
    }
}

/**
 *  ��ȡ������Ϣ
 *
 * @return    void
 */
function SpGetNewInfo()
{
    global $cfg_version,$dsql;
    $nurl = $_SERVER['HTTP_HOST'];
    if( preg_match("#[a-z\-]{1,}\.[a-z]{2,}#i",$nurl) ) {
        $nurl = urlencode($nurl);
    }
    else {
        $nurl = "test";
    }
    $phpv = phpversion();
    $sp_os = PHP_OS;
    $mysql_ver = $dsql->GetVersion();
    $offUrl = "http://new"."ver.a"."pi.de"."decms.com/index.php?c=info57&version={$cfg_version}&formurl={$nurl}&phpver={$phpv}&os={$sp_os}&mysqlver={$mysql_ver}";
    return $offUrl;
}

?>