<?php
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEADMIN.'/inc/inc_archives_functions.php');
CheckPurview('plus_Mail');
//�ʼ����ͺ���
function sendmail($email, $mailtitle, $mailbody)
{
	global $cfg_sendmail_bysmtp, $cfg_smtp_server, $cfg_smtp_port, $cfg_smtp_usermail, $cfg_smtp_user, $cfg_smtp_password, $cfg_adminemail,$cfg_webname;
	if($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server))
	{
		$mailtype = 'HTML';
		require_once(DEDEINC.'/mail.class.php');
		$smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
		$smtp->debug = false;
		if(!$smtp->smtp_sockopen($cfg_smtp_server)){
		  ShowMsg('�ʼ�����ʧ��,����ϵ����Ա','-1');
	    exit();
		}
		$smtp->sendmail($email,$cfg_webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
	}else{
		@mail($email, $mailtitle, $mailbody, $headers);
	}
}

if(!isset($action)){
	$action = '';
}
if($action==""){
	$mfile = glob(DEDEDATA.'/mail/*.txt');
  $mnumber = count($mfile);
	$mailfiles = array();
	if($mnumber > 0){
		if(is_array($mfile)){ 
		  foreach( $mfile as $key=>$filename){
				$mailfiles[$key] = basename($filename);
			}
		}
	}
	unset($mfile);
	require_once(DEDEADMIN."/templets/mail_send.htm");
}
if($action=="post"){
	if($title==''){
		ShowMsg("����д��Ϣ����!","-1");
		exit();
	}
	if($message==''){
		ShowMsg("����д����!","-1");
		exit();
	}
  
  if($mode=="group"){
  	if(file_exists(DEDEDATA.'/mail/'.$mailfile)){
			$address = file(DEDEDATA.'/mail/'.$mailfile);
			$address=implode(",", $address);
		}else{
			ShowMsg($mailfile."������","-1");
		  exit();
		}    
  }elseif($mode=="more"){
  	$address=$address2;
  }
  if(!preg_match('/^(.+)@(.+)$/',$address)){
		ShowMsg("����д��ȷ���ʼ���ַ!","-1");
		exit();
  }
  
	$title = cn_substrR(HtmlReplace($title,1),60);
	$sendtime = time();
	$mailtitle = $title;
	$mailto = $address;
	$mailbody = stripslashes($message);
  $pattern="/\\".$cfg_medias_dir."/";
	$mailbody =preg_replace($pattern,$cfg_basehost.$cfg_medias_dir,$mailbody);
	$fromid=$cuserLogin->getUserID();
	$fromuid=$cuserLogin->getUserName();
  sendmail($mailto,$mailtitle,$mailbody);

	//$inquery = "INSERT INTO `#@__member_mail` (`fromid`,`fromuid`,`address`,`title`,`sendtime`,`message`)VALUES ('$fromid','$fromuid','$mailto','$mailtitle','$sendtime','$mailbody'); ";
      
	//$dsql->ExecuteNoneQuery($inquery);
	ShowMsg('�ʼ��ѳɹ�����','mail_send.php');
	exit();	
	
}
?>