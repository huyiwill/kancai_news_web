<?php 
require_once(dirname(__FILE__)."/config.php");
CheckPurview('plus_Mail');
if(!isset($dopost)){
	$dopost = '';
}
if($dopost=="save"){
	$start = empty($start)? 1 : intval(preg_replace("/[\d]/",'', $start));
	$end = empty($end)? 0 : intval(preg_replace("/[\d]/",'', $end));
	if(!preg_match("/^[0-9a-z_]+$/i",$filename)){
		 ShowMsg("����д��ȷ���ļ���!","-1");
	   exit();
	}
	if($end!="0") $wheresql="where mid between $start and $end";
	else $wheresql="";
	
	$sql="SELECT email FROM  #@__member $wheresql";
	$db->Execute('me',$sql);
	while($row = $db->GetArray()){
		$mails[]=$row;
	}
	$email="";
	foreach($mails as $mail){
		$email.=$mail['email'].",";
	}
	
	$m_file = DEDEDATA."/mail/".$filename.".txt";
	
	if (file_exists($m_file)) {
    ShowMsg("���ļ��Ѿ����ڣ����»����ļ���!","-1");
	  exit();
	} else {
    $fp = fopen($m_file,'w');
		flock($fp,3);
		fwrite($fp,$email);
		fclose($fp);
		ShowMsg("��ȡ�ʼ��б�ɹ�!","-1");
		exit();
	}
}
require_once(DEDEADMIN."/templets/mail_getfile.htm");
?>