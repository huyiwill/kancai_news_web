<?php
require_once(dirname(__FILE__).'/../include/common.inc.php');
$moduleCacheFile = dirname(__FILE__).'/modules.tmp.inc';
include($moduleCacheFile);
$modules = split(',',$selModule);
$insLockfile = dirname(__FILE__).'/install_lock.txt';

if(file_exists($insLockfile)) exit("");

$module_autos=array(
    '606c658db048ea7328ffe1c7ae2a732f'=>array(
        'name'=>'changyan_autoreg',
        'title'=>'����ģ��'
    )
);
$logs = '';

foreach($module_autos as $hh=>$module_auto)
{
    if(!in_array($hh, $modules)) continue;
    $autofile = dirname(__FILE__).'/module_autos/'.$module_auto['name'].'.php';
    if(file_exists($autofile)) require_once($autofile);
    else continue;
    $clsname = ucfirst($module_auto['name']);
    $macls = new $clsname();
    if(!$macls->run()) $logs .= "��ʼ��{$module_auto['title']}����".$macls->errmsg."<br/>";
    else $logs .= "�ɹ���ʼ��{$module_auto['title']}<br/>";
}

$fp = fopen($insLockfile,'w');
fwrite($fp,'ok');
fclose($fp);
@unlink('./modules.tmp.inc');

echo <<<EOT
<link href="style.css" rel="stylesheet" type="text/css" />
<div class="over-link fs-14" style="padding:0px;">
    <a href="../index.php?upcache=1" target='_top'>������վ��ҳ</a>
    <a href="../dede" target='_top'>��¼��վ��̨</a>
</div>
EOT;
?>