<?php
require_once (dirname(__FILE__) . "/../include/common.inc.php");
header("Content-Type: text/html; charset=gb2312");
//header("Content-type:text/vnd.wap.wml");
require_once(dirname(__FILE__) . "/../include/wap.inc.php");
if(empty($action)) $action = 'index';
$cfg_templets_dir = $cfg_basedir.$cfg_templets_dir;
$channellist = '';
$newartlist = '';
$channellistnext = '';

//顶级导航列表
$dsql->SetQuery("Select id,typename From `#@__arctype` where reid=0 And channeltype=1 And ishidden=0 And ispart<>2 order by sortrank");
$dsql->Execute();
while($row=$dsql->GetObject())
{
	$channellist .= "<li><a href='wap.php?action=list&amp;id={$row->id}'>{$row->typename}</a></li>";
}

//调用带有幻灯属性的文章列表
$dsql->SetQuery("select id,title,litpic from `#@__archives` where flag like '%f%' and arcrank=0 order by id desc limit 3");
$dsql->Execute();
while($row=$dsql->GetObject())
{
	$articles_flash .= "
	<li> <a href='wap.php?action=article&amp;id={$row->id}'> <img src='{$row->litpic}' alt=''><p><span>{$row->title}</span></p></a></li>";
}
//调用带有头条属性的文章列表
$dsql->SetQuery("select id,title,litpic,description,click,pubdate from `#@__archives` where flag like '%h%' and arcrank=0 order by id desc limit 5");
$dsql->Execute();
while($row=$dsql->GetObject())
{
	$row->description = mb_substr($row->description,0,30,'gb2312');
	$row->pubdate = date('Y-m-d H:i',$row->pubdate);
	$articles_head .= "
	<div class='ui-mod-picsummary ui-border-bottom-gray'>
                <a href='wap.php?action=article&amp;id={$row->id}'>
                    <img class='ui-pic' src='{$row->litpic}'>
                    <h3 class='ui-title'>{$row->title}</h3>
                    <div class='ui-summary'>{$row->description}...  <div class='ui-comment-count'>$row->pubdate</div>
                    </div>
                </a>
            </div>";
}
//调用带有推荐属性的文章列表
$dsql->SetQuery("select id,title,litpic,description,click,pubdate from `#@__archives` where flag like '%c%' and arcrank=0 order by id desc limit 6");
$dsql->Execute();
while($row=$dsql->GetObject())
{
	$row->description = mb_substr($row->description,0,30,'gb2312');
	$row->title = mb_substr($row->title,0,20,'gb2312');
	$row->pubdate = date('Y-m-d H:i',$row->pubdate);
	$articles_tuijan .= "
	<li><a href='wap.php?action=article&amp;id={$row->id}'>
			            <div>
			                <img alt='{$row->title}' src='{$row->litpic}' width='140' height='115'>
			    		  <h3 style='height:56px'>{$row->title}</h3>	
				    </div>
			        </a>
			    </li>";
}

//调用顶级栏目以及各栏目下的文章列表
$dsql->SetQuery("select id,typename from dede_arctype where topid=0 and ishidden = 0");  //顶级栏目
$dsql->Execute();
$typeids = array();
$typenames = array();
while($row=$dsql->GetObject())
{
	$typeids[] = $row->id;
	$typenames[] = $row->typename;
}
foreach($typeids as $k=>$typeid){
	$dsql->SetQuery("
	select distinct id,title,litpic,description,click,pubdate,typeid from dede_archives where typeid in (select id from dede_arctype where topid = {$typeid} or id={$typeid})order by id desc limit 10");  //顶级栏目
	$dsql->Execute();
	$i=0;
	while($arc_row=$dsql->GetObject())
	{
		$i++;
		$description = mb_substr($arc_row->description,0,30,'gb2312');
		$pubdate = date('Y-m-d H:i',$arc_row->pubdate);
		if($i==1){
		$channellistAndArticles.="
	                <div class='ui-section-block'>
            <div class='ui-catgory'>
                <h2 class='ui-category-title'>{$typenames[$k]}</h2>
            </div>

                        <div class='ui-mod-picsummary ui-border-bottom-gray'>
                <a href='wap.php?action=article&amp;id={$arc_row->id}'>
                    <img class='ui-pic' src='{$arc_row->litpic}'>
                    <h3 class='ui-title'>{$arc_row->title}</h3>
                    <div class='ui-summary'>{$description}...                        <div class='ui-comment-count'>{$pubdate}</div>
                    </div>
                </a>
            </div>
                        <div class='ui-mod-lists ui-cate-list'>
                <ul>";
		}else if($i>1 && $i<10){
			$channellistAndArticles.="
                                        <li> <a href='wap.php?action=article&amp;id={$arc_row->id}'>{$arc_row->title}</a> </li>";
		}else if($i==10){
			$channellistAndArticles.="
                                    </ul>
                <a href='wap.php?action=list&id={$typeid}' class='ui-more'>查看{$typenames[$k]}频道</a>
            </div>
                    </div>
	";
		}
	}
}

//当前时间
$curtime = strftime("%Y-%m-%d %H:%M:%S",time());
$cfg_webname = ConvertStr($cfg_webname);

//主页
/*------------
function __index();
------------*/
if($action=='index')
{
	//显示WML
	include($cfg_templets_dir."/wap/index.wml");
	$dsql->Close();
	echo $pageBody;
	exit();
}

/*------------
function __list();
------------*/
//列表
else if($action=='list')
{
	$needCode = 'gb2312';
	$id = ereg_replace("[^0-9]", '', $id);
	if(empty($id)) exit('Error!');
	require_once(dirname(__FILE__)."/../include/datalistcp.class.php");
	$row = $dsql->GetOne("Select id,typename,ishidden From `#@__arctype` where id='$id' ");
	if($row['ishidden']==1) exit();
	$typename = ConvertStr($row['typename']);
	//当前栏目下级分类
	$dsql->SetQuery("Select id,typename From `#@__arctype` where reid='$id' And channeltype=1 And ishidden=0 And ispart<>2 order by sortrank");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		$channellistnext .= "<a href='wap.php?action=list&id={$row->id}'>".ConvertStr($row->typename)."</a> ";
	}
	//栏目内容(分页输出)
	$sids = GetSonIds($id,1,true);
	$varlist = "cfg_webname,typename,channellist,channellistnext,cfg_templeturl";
	ConvertCharset($varlist);
	$dlist = new DataListCP();
	$dlist->SetTemplet($cfg_templets_dir."/wap/list.wml");
	$dlist->pageSize = 10;
	$dlist->SetParameter("action","list");
	$dlist->SetParameter("id",$id);
	$dlist->SetSource("Select id,title,litpic,pubdate,click,description From `#@__archives` where typeid in($sids) And arcrank=0 order by id desc");
	$dlist->Display();
	exit();
}

/*------------
function __search();
------------*/
//搜索页
else if($action=='search')
{
	$keyword = $_GET['q'];
	$needCode = 'gb2312';
	
	require_once(dirname(__FILE__)."/../include/datalistcp.class.php");
	
	//栏目内容(分页输出)
	$varlist = "cfg_webname,channellist,keyword";
	ConvertCharset($varlist);
	$dlist = new DataListCP();
	$dlist->SetTemplet($cfg_templets_dir."/wap/search.wml");
	$dlist->pageSize = 10;
	$dlist->SetParameter("action","search");
	$dlist->SetParameter("q",$keyword);

	$key = urldecode($_GET['q']);
	$dlist->SetSource("Select id,title,litpic,pubdate,click,description From `#@__archives` where title like '%{$key}%' and arcrank=0 order by id desc");
	$dlist->Display();
	exit();
}

//文档
/*------------
function __article();
------------*/
else if($action=='article')
{
	//文档信息
	$query = "
	  Select tp.typename,tp.ishidden,arc.typeid,arc.title,arc.arcrank,arc.pubdate,arc.writer,arc.click,addon.body From `#@__archives` arc 
	  left join `#@__arctype` tp on tp.id=arc.typeid
	  left join `#@__addonarticle` addon on addon.aid=arc.id
	  where arc.id='$id'
	";
	$row = $dsql->GetOne($query,MYSQL_ASSOC);
	foreach($row as $k=>$v) $$k = $v;
	unset($row);
	$pubdate = strftime("%y-%m-%d %H:%M:%S",$pubdate);
	if($arcrank!=0) exit();
	$title = ConvertStr($title);
	$body = html2wml($body);
	if($ishidden==1) exit();
	//当前栏目下级分类
	$dsql->SetQuery("Select id,typename From `#@__arctype` where reid='$typeid' And channeltype=1 And ishidden=0 order by sortrank");
	$dsql->Execute();
	while($row=$dsql->GetObject()){
		$channellistnext .= "<a href='wap.php?action=list&amp;id={$row->id}'>".ConvertStr($row->typename)."</a> ";
	}
	//猜你喜欢
	$dsql->SetQuery("Select id,title,litpic From `#@__archives` where typeid='$typeid' And arcrank = 0 order by RAND() LIMIT 0,6");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		$suiji .= "<li>
          <div CLASS=\"picBox\"><a HREF=\"wap.php?action=article&amp;id={$row->id}\" CLASS=\"pic\"><img SRC=\"".ConvertStr($row->litpic)."\" /></a></div>
          <a CLASS=\"text\" HREF=\"wap.php?action=article&amp;id={$row->id}\">".ConvertStr($row->title)."</a></li>";
	}
	//最新文章
	$dsql->SetQuery("Select id,title,litpic From `#@__archives` where typeid='$typeid' And arcrank = 0 order by id LIMIT 0,6");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		  $newnews.= "<li><a HREF='wap.php?action=article&amp;id={$row->id}' REL='bookmark'>".ConvertStr($row->title)."</a></li>";
	}
	//随机文章
	$dsql->SetQuery("Select id,title,litpic From `#@__archives` where typeid='$typeid' And arcrank = 0 order by click LIMIT 0,6");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		  $randnews.= "<li><a HREF='wap.php?action=article&amp;id={$row->id}' REL='bookmark'>".ConvertStr($row->title)."</a></li>";
	}
	//上一篇
	$dsql->SetQuery("Select id,title,litpic From `#@__archives` where typeid='$typeid' And `id`< $id order by id desc LIMIT 1");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		  $uppage.= "<div CLASS='nav-previous'><a HREF='wap.php?action=article&amp;id={$row->id}' REL='next'> &lt; 上一篇</a></div>";
	}
	//下一篇
	$dsql->SetQuery("Select id,title,litpic From `#@__archives` where typeid='$typeid' And `id`> $id order by id asc LIMIT 1");
	$dsql->Execute();
	while($row=$dsql->GetObject())
	{
		  $downpage.= "<div CLASS='nav-next'><a HREF='wap.php?action=article&amp;id={$row->id}' REL='prev'>下一篇 &gt; </a></div>";
	}
	
	//栏目内容(分页输出)
	include($cfg_templets_dir."/wap/article.wml");
	$dsql->Close();
	echo $pageBody;
	exit();
}

//错误
/*------------
function __error();
------------*/
else
{
	ConvertCharset($varlist);
	include($cfg_templets_dir."/wap/error.wml");
	$dsql->Close();
	ConvertCharset($varlist);
	echo $pageBody;
	exit();
}
?>
