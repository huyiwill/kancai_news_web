<?php
@set_time_limit(0);

require_once(dirname(__FILE__)."/config.php");
AjaxHead();
if(!function_exists('TestWriteable'))
{
	// ����Ƿ��д
	function TestWriteable($d, $c=TRUE)
	{
		$tfile = '_write_able.txt';
		$d = preg_replace("/\/$/", '', $d);
		$fp = @fopen($d.'/'.$tfile,'w');
		if(!$fp)
		{
			if( $c==false )
			{
				@chmod($d, 0777);
				return false;
			}
			else return TestWriteable($d, true);
		}
		else
		{
			fclose($fp);
			return @unlink($d.'/'.$tfile) ? true : false;
		}
	}
}

if(!function_exists('TestExecuteable'))
{
	// ����Ƿ��Ŀ¼��ִ��
	function TestExecuteable($d='.', $siteuRL='', $rootDir='') {
		$testStr = '<'.chr(0x3F).'p'.chr(hexdec(68)).chr(112)."\n\r";
		$filename = md5($d).'.php';
		$testStr .= 'function test(){ echo md5(\''.$d.'\');}'."\n\rtest();\n\r";
		$testStr .= chr(0x3F).'>';
		$reval = false;
		if(empty($rootDir)) $rootDir = DEDEROOT;
		if (TestWriteable($d)) 
		{
			@file_put_contents($d.'/'.$filename, $testStr);
			$remoteUrl = $siteuRL.'/'.str_replace($rootDir, '', str_replace("\\", '/',realpath($d))).'/'.$filename;
			$tempStr = @PostHost($remoteUrl);
			$reval = (md5($d) == trim($tempStr))? true : false;
			unlink($d.'/'.$filename);
			return $reval;
		} else
		{
			return -1;
		}
	}
}

if(!function_exists('PostHost'))
{
	function PostHost($host,$data='',$method='GET',$showagent=null,$port=null,$timeout=30){
		$parse = @parse_url($host);
		if (empty($parse)) return false;
		if ((int)$port>0) {
			$parse['port'] = $port;
		} elseif (!@$parse['port']) {
			$parse['port'] = '80';
		}
		$parse['host'] = str_replace(array('http://','https://'),array('','ssl://'),"$parse[scheme]://").$parse['host'];
		if (!$fp=@fsockopen($parse['host'],$parse['port'],$errnum,$errstr,$timeout)) {
			return false;
		}
		$method = strtoupper($method);
		$wlength = $wdata = $responseText = '';
		$parse['path'] = str_replace(array('\\','//'),'/',@$parse['path'])."?".@$parse['query'];
		if ($method=='GET') {
			$separator = @$parse['query'] ? '&' : '';
			substr($data,0,1)=='&' && $data = substr($data,1);
			$parse['path'] .= $separator.$data;
		} elseif ($method=='POST') {
			$wlength = "Content-length: ".strlen($data)."\r\n";
			$wdata = $data;
		}
		$write = "$method $parse[path] HTTP/1.0\r\nHost: $parse[host]\r\nContent-type: application/x-www-form-urlencoded\r\n{$wlength}Connection: close\r\n\r\n$wdata";
		@fwrite($fp,$write);
		while ($data = @fread($fp, 4096)) {
			$responseText .= $data;
		}
		@fclose($fp);
		empty($showagent) && $responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
		return $responseText;
	}
}

if(!function_exists('TestAdminPWD'))
{
	//���ؽ����-1��û�и���Ĭ�Ϲ���Ա����  -2��û�и���Ĭ�Ϲ���Ա�û��������� 0��û�з���Ĭ���˺�
	function TestAdminPWD() 
	{
		global $dsql;
		// ��ѯ��Ŀ��ȷ����Ŀ���ڵ�Ŀ¼
		$sql = "SELECT usertype,userid,pwd FROM #@__admin WHERE `userid`='admin'";
		$row = $dsql->GetOne($sql);
		if(is_array($row))
		{
			if($row['pwd'] == 'f297a57a5a743894a0e4')
			{
				return -2;
			} else {
				return -1;
			}
		} else {
			return 0;
		}
	}
}

if(!function_exists('IsWritable'))
{
	// ����Ƿ��д
	function IsWritable($pathfile) {
		$isDir = substr($pathfile,-1)=='/' ? true : false;
		if ($isDir) {
			if (is_dir($pathfile)) {
				mt_srand((double)microtime()*1000000);
				$pathfile = $pathfile.'dede_'.uniqid(mt_rand()).'.tmp';
			} elseif (@mkdir($pathfile)) {
				return IsWritable($pathfile);
			} else {
				return false;
			}
		}
		//@chmod($pathfile,0777);
		$fp = @fopen($pathfile,'ab');
		if ($fp===false) return false;
		fclose($fp);
		$isDir && @unlink($pathfile);
		return true;
	}
}

// ���Ȩ��
$safeMsg = array();
if(TestExecuteable(DEDEROOT.'/data',$cfg_basehost) || TestExecuteable(DEDEROOT.'/uploads',$cfg_basehost))
{
	$helpurl = "http://help.dedecms.com/install-use/server/2011/1109/2124.html";
	$safeMsg[] = 'Ŀǰdata��uploads��ִ��.phpȨ�ޣ��ǳ�Σ�գ���Ҫ����ȡ��Ŀ¼��ִ��Ȩ�ޣ�
	<a href="testenv.php" title="ȫ����"><img src="images/btn_fullscan.gif" /></a>
	<a href="'.$helpurl.'" style="color:blue;text-decoration:underline;" target="_blank">�鿴���ȡ��</a>';
}
$dirname = str_replace('index_body.php', '', strtolower($_SERVER['PHP_SELF']));
if(preg_match("#[\\|/]dede[\\|/]#", $dirname))
{
	$safeMsg[] = 'Ĭ�Ϲ���Ŀ¼Ϊdede����Ҫ��������������';
}
if(IsWritable(DEDEDATA.'/common.inc.php'))
{
	$safeMsg[] = 'ǿ�ҽ���data/common.inc.php�ļ���������Ϊ644��Linux/Unix����ֻ����NT����';
}
$rs = TestAdminPWD();
if($rs < 0)
{
	$linkurl = "<a href='sys_admin_user.php' style='color:blue;text-decoration:underline;'>�����޸�</a>";
	switch ($rs)
	{
		case -1:
			$msg = "û�и���Ĭ�Ϲ���Ա����admin���������޸�Ϊ���������˺ţ�{$linkurl}";
			break;
		case -2:
			$msg = "û�и���Ĭ�ϵĹ���Ա���ƺ����룬ǿ�ҽ��������и��ģ�{$linkurl}";
			break;
	}
	$safeMsg[] = $msg;
}

if(PostHost($cfg_basehost.'/data/admin/ver.txt') === @file_get_contents(DEDEDATA.'/admin/ver.txt'))
{
	$helpurl = 'http://help.dedecms.com/install-use/apply/2011/1110/2129.html';
	$safeMsg[] = '<font color="blue">ǿ�ҽ��齫dataĿ¼���Ƶ�Web��Ŀ¼���⣻</font><a href="'.$helpurl.'" style="color:blue;text-decoration:underline;" target="_blank">�鿴��ΰ�Ǩ</a>';
}
?>
<?php
if(count($safeMsg) > 0)
{
?>
<!--��ȫ�����ʾ -->
<div id="safemsg">
  <dl class="dbox" id="item1" style="margin-left:0.5%;margin-right:0.5%; width:97%">
    <dt class='lside'><span class='l'><?php echo $cfg_soft_enname; ?>��ȫ��ʾ</span></dt>
    <dd>
      <div id='safelist'>
        <table width="98%" border="0" cellspacing="1" cellpadding="0" style="color:red">
          <?php
  $i=1;
  foreach($safeMsg as $key => $val)
  {
  ?>
          <tr>
            <td><font color="black"><?php echo $i;?>.</font><?php echo $val;?></td>
          </tr>
          <?php
  	$i++;
  }
  ?>
        </table>
      </div>
    </dd>
  </dl>
</div>
<?php
}
?>