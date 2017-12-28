<?php
require_once(dirname(__FILE__)."/config.php");
CheckRank(0,0);
$menutype = 'mydede';
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
$dopost = isset($dopost) ? trim($dopost) : '';
$folder = isset($folder) ? trim($folder) : '';
$mid = $cfg_ml->M_ID;
if($dopost == '')
{
	if($cfg_mb_spacesta!="-10"){
		if($cfg_checkemail=='Y'){
			$row=$dsql->GetOne("SELECT email,checkmail FROM `#@__member` WHERE mid=$mid");
			if($row['checkmail']=="-1"){
				 $msg="�ʼ�������Ҫ���Ƚ���������֤��</br><a href='mail.php?dopost=sendmail'>���������֤</a>";
			   ShowMsg($msg,'-1');
		     exit();
			}
		}
	}
	$db->SetQuery("SELECT typeid FROM `#@__mail_order` WHERE mid=$mid");	
	$db->Execute();
	$typeid="";
	while($row = $db->GetArray())
	{
	   $typeid.=$row['typeid'].",";
	}
	if($folder=="drop"){
		$dsql->SetQuery("SELECT t.*,o.mid FROM `#@__mail_type` AS t LEFT JOIN `#@__mail_order` AS o ON t.id=o.typeid WHERE mid=$mid ORDER BY t.id asc");
	  $dsql->Execute();
	  while($arr = $dsql->GetArray())
	  {
	  	$rows[]=$arr;
	  }
	  $rows=empty($rows)? "" : $rows;
	  $tpl = new DedeTemplate();
	  $tpl->LoadTemplate(DEDEMEMBER.'/templets/mail_drop.htm');
	  $tpl->Display();
	}else{
		$typeid=explode(",",$typeid);
	  $dsql->SetQuery("SELECT * FROM `#@__mail_type` ORDER BY id asc");
	  $dsql->Execute();
	  $inputbox="";
	  while($row = $dsql->GetObject())
	  {
	  	if(in_array($row->id,$typeid)){
        $inputbox.="<li><input type='checkbox' name='mailtype[]' id='{$row->id}' value='{$row->id}' class='np' checked/> <label>{$row->typename}</label></li>\r\n";
    	}else{
    		$inputbox.="<li><input type='checkbox' name='mailtype[]' id='{$row->id}' value='{$row->id}' class='np' /> <label>{$row->typename}</label></li>\r\n";
	  	}
	  }
	  $tpl = new DedeTemplate();
	  $tpl->LoadTemplate(DEDEMEMBER.'/templets/mail.htm');
	  $tpl->Display();
	} 
}elseif($dopost == 'save' || $dopost == 'drop'){
	$mailtype=empty($mailtype)? "" : $mailtype;
	$dsql->ExecuteNoneQuery("DELETE FROM #@__mail_order WHERE mid=$mid");
	if($dopost == 'save' && $mailtype==""){
		ShowMsg("��ѡ�������ͣ�",'mail.php');
	  exit();
	}	
	if($dopost=="save") $msg="���ĳɹ���";
	elseif($dopost=="drop") $msg="�˶��ɹ���";
	if(is_array($mailtype)){
		foreach($mailtype as $type){
				$dsql->ExecuteNoneQuery("INSERT INTO #@__mail_order(`typeid` , `mid`)VALUES ('$type', '$mid')");
		}
	}	
	ShowMsg($msg,'mail.php');
	exit();
}elseif($dopost=='sendmail'){
	$userhash = md5($cfg_cookie_encode.'--'.$cfg_ml->fields['mid'].'--'.$cfg_ml->fields['email']);
  $url = $cfg_basehost.(empty($cfg_cmspath) ? '/' : $cfg_cmspath)."/member/mail.php?dopost=checkmail&mid={$cfg_ml->fields['mid']}&userhash={$userhash}&do=1";
  $url = eregi_replace('http://', '', $url);
  $url = 'http://'.eregi_replace('//', '/', $url);
  $mailtitle = "{$cfg_webname}--��Ա�ʼ���֤֪ͨ";
  $mailbody = '';
  $mailbody .= "�𾴵��û�[{$cfg_ml->fields['uname']}]�����ã�\r\n";
  $mailbody .= "��ӭʹ���ʼ����Ĺ��ܡ�\r\n";
  $mailbody .= "Ҫͨ����֤�����������������ӵ���ַ���������ַ��\r\n\r\n";
  $mailbody .= "{$url}\r\n\r\n";
 
	if($cfg_sendmail_bysmtp == 'Y' && !empty($cfg_smtp_server))
	{		
		$mailtype = 'TXT';
		require_once(DEDEINC.'/mail.class.php');
		$smtp = new smtp($cfg_smtp_server,$cfg_smtp_port,true,$cfg_smtp_usermail,$cfg_smtp_password);
		$smtp->debug = false;
		if(!$smtp->smtp_sockopen($cfg_smtp_server)){
		  ShowMsg('�ʼ�����ʧ��,����ϵ����Ա','index.php');
	    exit();
		}
		$smtp->sendmail($cfg_ml->fields['email'], $cfg_webname,$cfg_smtp_usermail, $mailtitle, $mailbody, $mailtype);
	}else{
		@mail($cfg_ml->fields['email'], $mailtitle, $mailbody);
	}
	if(empty($cfg_smtp_server)){
		ShowMsg('�ʼ�����ʧ��,����ϵ����Ա','index.php');
	  exit();
	}else{
		ShowMsg('�ɹ������ʼ������¼���������н��գ�', 'index.php');
		exit();	
	}
}else if($dopost=='checkmail'){
	$mid = intval($mid);
	if(empty($mid))
	{
		ShowMsg('���Ч�鴮���Ϸ���', '-1');
		exit();
	}
	$row = $dsql->GetOne("Select * From `#@__member` where mid='{$mid}' ");
	$needUserhash = md5($cfg_cookie_encode.'--'.$mid.'--'.$row['email']);
	if($needUserhash != $userhash)
	{
		ShowMsg('���Ч�鴮���Ϸ���', '-1');
		exit();
	}
	$dsql->ExecuteNoneQuery("Update `#@__member` set checkmail=0 where mid='{$mid}' ");
	ShowMsg('�����ɹ�,��ӭʹ���ʼ����ģ�', 'mail.php');
	exit();
}
?>
