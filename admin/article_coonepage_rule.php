<?php
/**
 * �ĵ�����ɼ�
 *
 * @version        $Id: article_coonepage_rule.php 1 14:12 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
require_once(dirname(__FILE__)."/config.php");
require_once(DEDEINC."/datalistcp.class.php");
setcookie("ENV_GOBACK_URL",$dedeNowurl,time()+3600,"/");
if(empty($action)) $action = '';

/*------
function _AddNote(){ }
-------*/
if($action == 'add')
{
    $row = $dsql->GetOne("SELECT * FROM `#@__co_onepage` WHERE url LIKE '$url' ");
    if(is_array($row))
    {
        echo "ϵͳ�Ѿ����������ַ����Ŀ��";
    } else {
        $query = " INSERT INTO `#@__co_onepage`(`url`,`title`,`issource`,`lang`,`rule`) Values('$url','$title','$issource','$lang','$rule'); ";
        $dsql->ExecuteNonequery($query);
        echo $dsql->GetError();
    }
}
/*------
function _DelNote(){ }
-------*/
else if($action == 'del')
{
    if(!preg_match("#,#", $ids))
    {
        $query = "DELETE FROM `#@__co_onepage` WHERE id='$ids' ";
    }
    else
    {
        $query = "DELETE FROM `#@__co_onepage` WHERE id IN($ids) ";
    }
    $dsql->ExecuteNonequery($query);
}

/*------
function _EditNote(){ }
-------*/
else if($action == 'editsave')
{
    $query = "UPDATE `#@__co_onepage` SET `url`='$url',`title`='$title',`issource`='$issource',`lang`='$lang',`rule`='$rule' WHERE id='$id' ";
    $dsql->ExecuteNonequery($query);
    echo $dsql->GetError();
}
/*------
function _EditNoteLoad(){ }
-------*/
else if($action == 'editload')
{
    $row = $dsql->GetOne("SELECT * FROM `#@__co_onepage` WHERE id='$id' ");
    AjaxHead();
?>
<form name='addform' action='article_coonepage_rule.php' method='post'>
<input type='hidden' name='id' value='<?php echo $id; ?>' />
<input type='hidden' name='action' value='editsave' />
<table width="430" border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td width="102" height="30">��վ���ƣ�</td>
    <td width="302"><input name="title" type="text" id="title" style="width:200px" value="<?php echo $row['title']; ?>" /></td>
    <td width="26" align="center"><a href="javascript:CloseEditNode()"><img src="images/close.gif" width="12" height="12" border="0" /></a></td>
  </tr>
  <tr>
    <td height="30">ԭ���ݱ��룺</td>
    <td colspan="2">
      <input type="radio" name="lang" value="gb2312" <?php echo ($row['lang']=='gb2312' ? ' checked="checked" ' : ''); ?> />
      GB2312/GBK
      <input type="radio" name="lang" value="utf-8" <?php echo ($row['lang']=='utf-8' ? ' checked="checked" ' : ''); ?> />
      UTF-8
    </td>
  </tr>
  <tr>
    <td height="30">����������Դ��</td>
    <td colspan="2">
        <input type="radio" name="issource" value="0" <?php echo ($row['issource']==0 ? ' checked="checked" ' : ''); ?> />
      ��
      <input name="issource" type="radio" value="1" <?php echo ($row['issource']==1 ? ' checked="checked" ' : ''); ?> />
      ��
    </td>
  </tr>
  <tr>
    <td height="30">��վ��ַ��</td>
    <td colspan="2">
        <input name="url" type="text" id="url" value="<?php echo $row['url']; ?>" style="width:200px" />
    </td>
  </tr>
  <tr>
    <td height="30">&nbsp;</td>
    <td colspan="2">
    ʹ�ò���http���κθ���Ŀ¼����ַ<br />
    �磺news.dedecms.com
    </td>
  </tr>
  <tr>
    <td height="30">�ɼ�����</td>
    <td colspan="2">������������ݣ���ʽ��ǰ��HTML{@body}����HMTL</td>
  </tr>
  <tr>
    <td height="90">&nbsp;</td>
    <td colspan="2"><textarea name="rule" style="width:300px;height:80px"><?php echo $row['rule']; ?></textarea></td>
  </tr>
  <tr>
    <td height="32">&nbsp;</td>
    <td colspan="2"><input class="nbt" type="submit" name="Submit" value="�������" />��
    <input type="reset" class="nbt" name="Submit2" value="����" /></td>
  </tr>
</table>
</form>
<?php
    exit();
} //loadedit
/*---------------
function _ShowLoad(){ }
-------------*/
$sql = "";
$sql = "SELECT id,url,title,lang,issource FROM `#@__co_onepage` ORDER BY id DESC";
$dlist = new DataListCP();
$dlist->SetTemplate(DEDEADMIN."/templets/article_coonepage_rule.htm");
$dlist->SetSource($sql);
$dlist->Display();
