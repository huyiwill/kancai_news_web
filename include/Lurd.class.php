<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 *
 *  ��������ָ��һ�� MySQL �����ݱ�
 *  �Զ������г����޸ġ�ɾ�������ӵȲ���
 *
 *   2011-11-08 [����]��LURD�������������
 *		1.���ӵ�����¼������,ָ��$lurd->singleManage=TRUE,���ӵ�����¼�Ĺ���ҳ��;
 *		2.�޸�LURDĬ�Ϲ���ҳ�������;
 *		3.ģ��ҳ��֧��~self~ָ��ǰҳ��ַ;
 *		4.lurd֧��getģʽ,���ڵ�����¼�Ĺ���;
 *   2010-11-17 [�޸�]����request��Ĭ�����ݴ�������
 *   2010-11-17 [�޸�]�������UNIQUE���ֶη������������
 *   2010-11-17 [����]���Ӽ�������ƥ�䷽��
 *   2010-11-16 [����]������ֶδ���ע��,���б����title����ֱ����ʾע����Ϣ
 *   2010-11-14 [�޸�]���ݱ����ǰ׺,ģ�����ƽ�������,ͬʱ���Զ�Lurd�����趨ģ��
 *
 *
 * @version        $Id: lurd.class.php 7 14:07 2011/11/8 IT����ͼ,tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

require_once(DEDEINC.'/datalistcp.class.php');

/***********************************
 @ class lurd

***********************************/
class Lurd extends DataListCP
{
    // �̶�����
    var $dateTypes = array('DATE', 'DATETIME', 'TIMESTAMP', 'TIME', 'YEAR');
    var $floatTypes = array('FLOAT', 'DOUBLE', 'DECIMAL');
    var $intTypes = array('TINYINT', 'SMALLINT', 'MEDIUMINT', 'INT', 'BIGINT');
    var $charTypes = array('VARCHAR', 'CHAR', 'TINYTEXT');
    var $textTypes = array('TEXT', 'MEDIUMTEXT', 'LONGTEXT');
    var $binTypes = array('TINYBLOB', 'BLOB', 'MEDIUMBLOB', 'LONGBLOB', 'BINARY', 'VARBINARY');
    var $emTypes = array('ENUM', 'SET');
    // ��ͨ����
    var $tableName = '';
    var $templateDir = '';
    var $lurdTempdir = '';
    var $tplName = '';
    var $appName = '';
    var $primaryKey = '';
    var $autoField = '';
    var $orderQuery = '';
    // �����ֶμ���������
    var $fields = array(); 
    // �����ֵΪTRUE��ÿ�ζ���������ģ�壬���ڵ���ģʽ
    var $isDebug = FALSE;
    // �ı����ݰ�ȫ���� 
    // 0 Ϊ������ 1 Ϊ��ֹ����ȫHTML����(javascript��)��2��ȫ��ֹHTML���ݣ����滻���ݲ���ȫ�ַ������磺eval(��union��CONCAT(��--���ȣ�
    var $stringSafe = 1;
    // ����������ڴ�������Ĳ���
    // ����ͨ�� $lurd->AddLinkTable($tablelurd, linkfields, mylinkid, linkid); ����һ����
    var $linkTables = array();
    // ������ָ���ֶ�(�����һ������ֶκ�ǰһ������, ǰ�߻ᱻ�滻)
    var $addFields = array();
    // ��ѯ����
    var $searchParameters = array();
	
	// ������¼����,�޸�/ɾ��
	var $singleManage = TRUE;

    //���캯����ָ��Ҫ�����ı���
    function __construct($tablename, $templatedir='', $lurdtempdir='')
    {
        global $dsql;
        $prefix = "#@__";
        $this->tplName = str_replace($prefix, '', $tablename);
        $this->tableName = str_replace($prefix, $GLOBALS['cfg_dbprefix'], $tablename);
        $this->templateDir = $templatedir;
        $this->lurdTempdir = empty($lurdtempdir) ? $this->templateDir.'/lurd' : $lurdtempdir;
        parent::__construct();
        $this->AnalyseTable();
        $ct = isset($GLOBALS['ct'])? $GLOBALS['ct'] : request('ct', '');
        if(!empty($ct))
        {
            $this->SetParameter('ct', $ct);
        }
        $this->SetParameter('ac', 'list');
    }
    
    //������������ָ����ac(ac)������Ӧ������
    function ListenAll($listfield = '', $wherequery = '', $orderquery ='')
    {
        global $action;
        $action = !empty($action)? $action : request('action');
        switch($action)
        {
            case 'add' :
                $this->AddData();
                break;
            case 'edit':
                $this->EditData();
                break;
            case 'del':
                $this->DelData();
                break;
            case 'saveadd':
                $this->SaveAddData();
                break;
            case 'saveedit':
                $this->SaveEditData();
                break;
            default:
                $this->ListData($listfield, $wherequery, $orderquery);
                break;
        }
    }

    // ָ������
    // �����ݱ�û������������£�����ָ��һ���ֶ�Ϊ�����ֶΣ�����ϵͳ���������ݵ�md5ֵ��Ϊ����
    function AddPriKey($fieldname)
    {
        $this->primaryKey = $fieldname;
    }
    
    // ָ��Ӧ�õ�����
    function AddAppName($appname)
    {
        $this->appName = $appname;
    }
    
    //�������������SQL���� order by id desc
    function SetOrderQuery($query)
    {
        $this->orderQuery = $query;
    }
    
    //ǿ��ָ���ֶ�Ϊ��������
    function BindType($fieldname, $ftype, $format='')
    {
        //'type' =>'','length' =>'0','unsigned'=>FALSE,'autokey'=>FALSE,
        //'null'=>FALSE,default'=>'','em'=>'','format'=>'','listtemplate'=>'','edittemplate'=>''
        $typesArr = array_merge($this->dateTypes, $this->floatTypes, $this->intTypes, $this->charTypes, $this->textTypes, $this->binTypes, $this->emTypes);
        if( isset($this->fields[$fieldname]) && in_array(strtoupper($ftype), $typesArr) )
        {
            $this->fields[$fieldname]['type'] = $ftype;
            $this->fields[$fieldname]['format'] = $format;
        }
    }
    
    //ǿ��ָ����ģ�壨�б�list�ͱ༭edit������addģ�����ã�
    function BindTemplate($fieldname, $tmptype='list', $temp='')
    {
        if( isset($this->fields[$fieldname]) )
        {
            $this->fields[$fieldname][$tmptype.'template'] = $temp;
        }
    }
    
    // �г�����
    /*********************************
     * $listfield = '' ָ��Ҫ�г����ֶ�(Ĭ��Ϊ'*') ��ֻ����д��ǰ��
                                                  ���������ֶ��� AddLinkTable ָ���� ��˲���Ҫ ����.�ֶ��� ��ʽʶ��
     * $wherequery = '' ��ѯquery��������������������� ����.�ֶ��� ʶ���ֶ�
     * $orderquery = '' ����query��������������������� ����.�ֶ��� ʶ���ֶ�
     **********************************/
    function ListData($listfield = '*', $wherequery = '', $orderquery ='', $Suff = '_list.htm')
    {
        $listdd = '';
        if(trim($listfield)=='') $listfield = '*';
        
        $template = $this->templateDir.'/'.$this->tplName.$Suff;
        //�����б�ģ��
        if( !file_exists($template) || $this->isDebug )
        {
            $this->MakeListTemplate($listfield);
        }
        
        // ��ȡ��������
        if( $wherequery == '' )
        {
            $wherequery = $this->GetSearchQuery();
        }

        //�Ƿ������������ı�
        $islink = count($this->linkTables) > 0 ? TRUE : FALSE;
        //���������ֶ�(Select * From ����)
        $listfields = explode(',', $listfield);
        foreach($listfields as $v)
        {
                $v = trim($v);
                if( !isset($this->fields[$v]) && $v != '*' ) continue;
                if($islink) {
                    $listdd .= ($listdd=='' ? "{$this->tableName}.{$v}" : ", {$this->tableName}.{$v} ");
                }
                else {
                    if($v=='*') $listdd .= ' * ';
                    else $listdd .= ($listdd=='' ? "`$v`" : ",`$v`");
                }
        }
        if($listdd=='') $listdd = " * ";
        
        //����������ֶ�
        if($islink)
        {
            $joinQuery = '';
            foreach($this->linkTables as $k=>$linkTable)
            {
                $k++;
                $linkTableName = $linkTable[0]->tableName;
                $joinQuery .= " LEFT JOIN `{$linkTableName}` ON {$linkTableName}.{$linkTable[2]} = {$this->tableName}.{$linkTable[1]} ";
                foreach($this->addFields as $f=>$v)
                {
                    if($v['table'] != $linkTableName) continue;
                    $listdd .= ", {$linkTableName}.{$f} ";
                }
            }
            $query = "SELECT $listdd FROM `{$this->tableName}` $joinQuery $wherequery $orderquery ";
        }
        else
        {
            $query = "SELECT $listdd FROM `{$this->tableName}` $wherequery $orderquery ";
        }
        
        $this->SaveCurUrl();
        $this->SetTemplate($template);
        $this->SetSource($query);
        $this->Display();
    }
    
    //��¼�б���ַ��cookie�����㷵��ʱ����
    function SaveCurUrl()
    {
        setcookie('LURD_GOBACK_URL', $this->GetCurFullUrl(), time()+3600, '/');
    }
    
    function GetCurFullUrl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $scriptName = $_SERVER["REQUEST_URI"];
            $nowurl = $scriptName;
        }
        else
        {
            $scriptName = $_SERVER["PHP_SELF"];
            $nowurl = empty($_SERVER["QUERY_STRING"]) ? $scriptName : $scriptName."?".$_SERVER["QUERY_STRING"];
        }
        return $nowurl;
    }
    
    //����ȫ��ģ��
    function MakeAllTemplate($listfield = '')
    {
        $this->MakeListTemplate($listfield);
        $this->MakeAddEditTemplate('add');
        $this->MakeAddEditTemplate('edit');
    }
    
    //�����б�ģ��
    function MakeListTemplate($listfield = '')
    {
        $templateTemplate = $this->lurdTempdir.'/lurd-list.htm';
        $template = $this->templateDir.'/'.$this->tplName.'_list.htm';
        $tempstr = '';
        $fp = fopen($templateTemplate, 'r');
        while( !feof($fp) ) $tempstr .= fread($fp, 1024);
        fclose($fp);
        $tempItems = array('appname'=>'', 'totalitem'=>'', 'titleitem'=>'', 'fielditem'=>'');
        $tempItems['appname'] = empty($this->appName) ? "�������ݱ� ".$this->tableName : $this->appName;
        //����ѡ����
        $tempItems['totalitem'] = 1;
        $tempItems['self'] = $_SERVER["PHP_SELF"];
        $titleitem = "    <td class='nowrap'>ѡ��</td>\r\n";
        if( !preg_match("/,/", $this->primaryKey) )
        {
                $fielditem = "    <td class='nowrap'><input type=\"checkbox\" name=\"{$this->primaryKey}[]\" value=\"{dede:field name='{$this->primaryKey}' /}\" /></td>\r\n";
        }
        else
        {
                $prikeys = explode(',', $this->primaryKey);
                $prikeyValue = '<'.'?php echo md5("key"';
                foreach($prikeys as $v)
                {
                    $prikeyValue .= '.$fields["'.$v.'"]';
                }
                $prikeyValue .= '); ?'.'>';
                $fielditem = "    <td class='nowrap'><input type=\"checkbox\" name=\"primarykey[]\" value=\"{$prikeyValue}\" /></td>\r\n";
        }
        //ʹ���ֹ�ָ���г��ֶ�
        if(!empty($listfield) && $listfield != '*' )
        {
            $listfields = explode(',', $listfield);
            $tempItems['totalitem'] = count($listfields) + 1;
            foreach($listfields as $k)
            {
                $k = trim($k);
                if( !isset($this->fields[$k]) ) continue;
                $v = $this->fields[$k];
                $title = !empty($v['comment'])? $v['comment'] : $k;
                $titleitem .= "    <td class='nowrap'>$title</td>\r\n";
                if( !empty($v['listtemplate']) )
                {
                    $fielditem .= "    <td class='nowrap'>{$v['listtemplate']}</td>\r\n";
                }
                else
                {
                    $dofunc = $dtype = $fformat = '';
                    $dtype = !empty($v['type']) ? $v['type'] : 'check';
                    if(isset($v['format']))
                    {
                        $fformat = $v['format'];
                    }
                    if(isset($v['dofunc']))
                    {
                        $dofunc = $v['dofunc'];
                    }
                    if(isset($v['type']))
                    {
                        $this->fields[$k]['type'] = $v['type'];
                    }
                    if( in_array($v['type'], $this->floatTypes) ) 
                    {
                        $dtype = 'float';
                        $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatFloat(@me, '$fformat')\"" : '');
                    }
                    if( in_array($v['type'], $this->dateTypes) )
                    {
                        $dtype = 'date';
                        $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatDate(@me, '{$this->fields[$k]['type']}', '$fformat')\"" : '');
                    }
                    $fielditem .= "    <td class='nowrap'>{dede:field name='{$k}' $dofunc /}</td>\r\n";
                }
            }//End foreach
        }
        //�Զ�����
        else
        {
            foreach($this->fields as $k=>$v)
            {
                if(in_array($v['type'], $this->binTypes) )
                {
                    continue;
                }
                $tempItems['totalitem']++;
				$title = !empty($v['comment'])? $v['comment'] : $k;
                $titleitem .= "    <td class='nowrap'>$title</td>\r\n";
                $dofunc = $dtype = $fformat = '';
                if(isset($v['format']))
                {
                        $fformat = $v['format'];
                }
                if( in_array($v['type'], $this->floatTypes) ) 
                {
                    $dtype = 'float';
                    $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatFloat(@me, '$fformat')\"" : '');
                }
                if( in_array($v['type'], $this->dateTypes) )
                {
                    $dtype = 'date';
                    $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatDate(@me, '{$this->fields[$k]['type']}', '$fformat')\"" : '');
                }
                $fielditem .= "    <td class='nowrap'>{dede:field name='{$k}' $dofunc /}</td>\r\n";
            }
        }
		
        //�Ƿ������������ı�
        $islink = count($this->linkTables) > 0 ? TRUE : FALSE;
        //���ӱ���ֶ�
        if($islink)
        {
            foreach($this->addFields as $k=>$v)
            {
                if(in_array($v['type'], $this->binTypes) )
                {
                    continue;
                }
                $tempItems['totalitem']++;
                $titleitem .= "    <td class='nowrap'>$k</td>\r\n";
                $dofunc = $dtype = $fformat = '';
                if( in_array($v['type'], $this->floatTypes) ) 
                {
                    $dtype = 'float';
                    $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatFloat(@me, '$fformat')\"" : '');
                }
                if( in_array($v['type'], $this->dateTypes) )
                {
                    $dtype = 'date';
                    $dofunc = ($dofunc=='' ? "function=\"Lurd::FormatDate(@me, '{$this->fields[$k]['type']}', '$fformat')\"" : '');
                }
                $fielditem .= "    <td class='nowrap'>{dede:field name='{$k}' $dofunc /}</td>\r\n";
            }
        }
		
		if($this->singleManage)
		{
			$tempItems['totalitem']++;
			$titleitem .= "    <td class='nowrap'>����</td>\r\n";
			$currentUrl = $this->GetCurUrl();
			$fielditem .= "    <td class='nowrap'>  
			<a href=\"{$currentUrl}?{$this->primaryKey}={dede:field name='{$this->primaryKey}' /}&ac=edit&get=yes\">�޸�</a> 
			|  <a href=\"{$currentUrl}?{$this->primaryKey}={dede:field name='{$this->primaryKey}' /}&ac=del&get=yes\">ɾ��</a> </td>\r\n";
		}
		
        $tempItems['titleitem'] = $titleitem;
        $tempItems['fielditem'] = $fielditem;
        foreach($tempItems as $k => $v)
        {
            $tempstr = str_replace("~$k~", $v, $tempstr);
        }
        $fp = fopen($template, 'w');
        fwrite($fp, $tempstr);
        fclose($fp);
    }
    
    //���ɷ�����༭ģ��
    function MakeAddEditTemplate($getTemplets='add')
    {
        $templateTemplate = $this->lurdTempdir."/lurd-{$getTemplets}.htm";
        $template = $this->templateDir.'/'.$this->tplName."_{$getTemplets}.htm";
        $tempstr = '';
        $fp = fopen($templateTemplate, 'r');
        while( !feof($fp) ) $tempstr .= fread($fp, 1024);
        fclose($fp);
        $tempItems = array('appname'=>'', 'fields'=>'', 'primarykey'=>'');
        $tempItems['appname'] = empty($this->appName) ? "�� {$this->tableName} ".($getTemplets=='add' ? '�������' : '�༭����' ) : $this->appName;
        $tempItems['fields'] = '';
		$tempItems['self'] = $_SERVER["PHP_SELF"];
        if( !preg_match("/,/", $this->primaryKey) )
        {
            $tempItems['primarykey'] = "    <input type=\"hidden\" name=\"{$this->primaryKey}\" value=\"{dede:field name='{$this->primaryKey}' /}\" />\r\n";
        }
        else
        {
            $prikeys = explode(',', $this->primaryKey);
            $prikeyValue = '<'.'?php echo md5("key"';
            foreach($prikeys as $v)
            {
                $prikeyValue .= '.$fields["'.$v.'"]';
            }
            $prikeyValue .= '); ?'.'>';
            $tempItems['primarykey'] = "    <input type=\"hidden\" name=\"primarykey[]\" value=\"{$prikeyValue}\" />\r\n";
        }
        $fielditem = '';
        foreach($this->fields as $k=>$v)
        {
            $aeform = $dtype = $defaultvalue = $fformat = '';
			$title = !empty($v['comment'])? $v['comment'] : $k;
            //��ָ�����ֶ�ģ������²�ʹ���Զ�����
            if(isset($this->fields[$k][$getTemplets.'template']))
            {
                $fielditem .= $this->fields[$k][$getTemplets.'template'];
                continue;
            }
            //�ų��Զ�������
            if($k==$this->autoField)
            {
                continue;
            }
            //�༭ʱ���ų�����
            if($k==$this->primaryKey && $getTemplets=='edit')
            {
                continue;
            }
            //��ʽ��ѡ��(�༭ʱ��)
            if(isset($this->fields[$k]['format']))
            {
                $fformat = $this->fields[$k]['format'];
            }
            //����ֶ�Ĭ��ֵ���༭ʱ�����ݿ��ȡ��
            if($getTemplets=='edit')
            {
                if( in_array($this->fields[$k]['type'], $this->binTypes) ) $dfvalue = '';
                else $dfvalue = "{dede:field name='$k' /}";
            }
            else
            {
                $dfvalue = $this->fields[$k]['default'];
            }
            //С������
            if( in_array($this->fields[$k]['type'], $this->floatTypes) ) 
            {
                if($getTemplets=='edit')
                {
                    $dfvalue = "{dede:field name='$k' function=\"Lurd::FormatFloat(@me, '$fformat')\" /}";
                }
                else if($this->fields[$k]['default']=='')
                {
                    $dfvalue = 0;
                }
                $aeform  = "<input type='input' name='{$k}' class='txtnumber' value='$dfvalue' />";
            }
            //��������
            if( in_array($this->fields[$k]['type'], $this->intTypes) ) 
            {
                $aeform  = "<input type='input' name='{$k}' class='txtnumber' value='$dfvalue' />";
            }
            //ʱ������
            else if( in_array($this->fields[$k]['type'], $this->dateTypes))
            {
                if(empty($fformat)) $fformat = 'Y-m-d H:i:s';
                if($getTemplets=='edit')
                {
                    $dfvalue = "{dede:field name='$k' function=\"MyDate(@me, '$fformat')\" /}";
                }
                else if(empty($this->fields[$k]['default']))
                {
                    $dfvalue = "{dede:var.a function=\"MyDate(time(), '$fformat')\" /}";
                }
                $aeform  = "<input type='input' name='{$k}' class='txtdate' value='$dfvalue' />";
            }
            //���ı�����
            else if( in_array($this->fields[$k]['type'], $this->textTypes))
            {
                $aeform  = "<textarea name='$k' class='txtarea'>{$dfvalue}</textarea>";
            }
            //����������
            else if( in_array($this->fields[$k]['type'], $this->textTypes))
            {
                $aeform = "<input type='file' name='$k' size='45' />";
            }
            //SET����
            else if( $this->fields[$k]['type']=='SET' )
            {
                $ems = explode(',', $this->fields[$k]['em']);
                if($getTemplets=='edit')
                {
                    $aeform .= '<'.'?php $enumItems = explode(\',\', $fields[\''.$k.'\']); ?'.'>';
                }
                foreach($ems as $em)
                {
                    if($getTemplets=='add')
                    {
                        $aeform .= "<input type='checkbox' name='{$k}[]' value='$em' />$em \r\n";
                    }
                    else
                    {
                        $aeform .= "<input type='checkbox' name='{$k}[]' value='$em' {dede:if in_array('$em', \$enumItems)}checked{/dede:if} />$em \r\n";
                    }
                }
            }
            //ENUM����
            else if( $this->fields[$k]['type']=='ENUM' )
            {
                $ems = explode(',', $this->fields[$k]['em']);
                foreach($ems as $em)
                {
                    if($getTemplets=='edit') {
                        $aeform .= "<input type='radio' name='$k' value='$em' {dede:if \$fields['$k']}=='$em'}checked{/dede:if} />$em \r\n";
                    }
                    else {
                        $aeform .= "<input type='radio' name='$k' value='$em' />$em \r\n";
                    }
                }
            }
            else
            {
                $aeform  = "<input type='input' name='{$k}' class='txt' value='$dfvalue' />";
            }
            $fielditem .= "    <tr>\r\n<td height='28' align='center' bgcolor='#FFFFFF'>{$title}</td>\r\n<td bgcolor='#FFFFFF'>{$aeform}</td>\r\n</tr>\r\n";
        }
        $tempItems['fields'] = $fielditem;
        foreach($tempItems as $k=>$v)
        {
            $tempstr = str_replace("~$k~", $v, $tempstr);
        }
        $fp = fopen($template, 'w');
        fwrite($fp, $tempstr);
        fclose($fp);
    }

    // ��ȡ����
    function EditData()
    {
        $template = $this->templateDir.'/'.$this->tplName.'_edit.htm';
        //�����б�ģ��
        if( !file_exists($template) || $this->isDebug )
        {
            $this->MakeAddEditTemplate('edit');
        }
        $whereQuery = '';
        $GLOBALS[$this->primaryKey] = isset($GLOBALS[$this->primaryKey])? $GLOBALS[$this->primaryKey] : request($this->primaryKey);
        if(empty($GLOBALS['primarykey'][0]) && empty($GLOBALS[$this->primaryKey][0]))
        {
            ShowMsg('��ѡ��Ҫ�޸ĵļ�¼��', '-1');
            exit();
        }
        if(preg_match("/,/", $this->primaryKey))
        {
            $whereQuery = "WHERE md5(CONCAT('key', `".str_replace(',', '`,`', $this->primaryKey)."`) = '{$GLOBALS['primarykey'][0]}' ";
        }
        else
        {
			$pkey = (request('get') == 'yes')? request($this->primaryKey) : $GLOBALS[$this->primaryKey][0];
            $whereQuery = "WHERE `{$this->primaryKey}` = '".$GLOBALS[$this->primaryKey][0]."' ";
        }

        //�г�����
        $query = "SELECT * FROM `{$this->tableName}` $whereQuery ";
        $this->SetTemplate($template);
        $this->SetSource($query);
        $this->Display();
    }

    // ��������
    function AddData()
    {
        $template = $this->templateDir.'/'.$this->tplName.'_add.htm';
        //�����б�ģ��
        if( !file_exists($template) || $this->isDebug )
        {
            $this->MakeAddEditTemplate('add');
        }
        //���ɷ���������
        $this->SetTemplate($template);
        $this->Display();
        exit();
    }

    // ������������
    function SaveAddData($isturn=TRUE)
    {
        $allfield = $allvalue = '';
        foreach($this->fields as $k=>$v)
        {
            //�Զ�������������
            if($k==$this->autoField)
            {
                continue;
            }
            $allfield .= ($allfield=='' ? "`$k`" : ' , '."`$k`");
            $v = $this->GetData($k);
            $allvalue .= ($allvalue=='' ? "'$v'" : ' , '."'$v'");
        }
        $inQuery = "INSERT INTO `$this->tableName`($allfield) VALUES($allvalue); ";
        $rs = $this->dsql->ExecuteNoneQuery($inQuery);
        
        //ֻ���ؽ��
        if(!$isturn)
        {
            return $rs;
        }
        //��ȫ����ģʽ
        $gourl = !empty($_COOKIE['LURD_GOBACK_URL']) ? $_COOKIE['LURD_GOBACK_URL'] : '-1';
        if(!$rs)
        {
            $this->dsql->SaveErrorLog($inQuery);
            ShowMsg('��������ʧ�ܣ��������ݿ������־��', $gourl);
            exit();
        }
        else
        {
            ShowMsg('�ɹ�����һ�����ݣ�', $gourl);
            exit();
        }
    }

    // ����༭����
    function SaveEditData($isturn=TRUE)
    {
        $editfield = '';
        foreach($this->fields as $k=>$v)
        {
            //�Զ�������������
            $GLOBALS[$k] = isset($GLOBALS[$k])? $GLOBALS[$k] : $GLOBALS['request']->forms[$k];
            if($k==$this->autoField || !isset($GLOBALS[$k]))
            {
                continue;
            }
            $v = $this->GetData($k);
            $editfield .= ($editfield=='' ? " `$k`='$v' " : ",\n `$k`='$v' ");
        }
        //�������ֵ
        if(preg_match("#,#", $this->primaryKey))
        {
            $keyvalue = (isset($GLOBALS['primarykey']) ? $GLOBALS['primarykey'] : '');
        }
        else
        {
            $keyvalue = $this->GetData($this->primaryKey);
        }
        $keyvalue = preg_replace("#[^0-9a-z]#i", "", $keyvalue);
        if( !preg_match("#,#", $this->primaryKey) )
        {
            $inQuery = " UPDATE `$this->tableName` SET $editfield WHERE `{$this->primaryKey}`='{$keyvalue}' ";
        }
        else
        {
            $inQuery = " UPDATE `$this->tableName` SET $editfield WHERE md5('key', `".str_replace(',','`,`',$this->primaryKey)."`='{$keyvalue}' ";
        }
        $rs = $this->dsql->ExecuteNoneQuery($inQuery);
        //ֻ���ؽ��
        if(!$isturn)
        {
            return $rs;
        }
        //��ȫ����ģʽ
        $gourl = !empty($_COOKIE['LURD_GOBACK_URL']) ? $_COOKIE['LURD_GOBACK_URL'] : '-1';
        if(!$rs)
        {
            $this->dsql->SaveErrorLog($inQuery);
            ShowMsg('��������ʧ�ܣ��������ݿ������־��', $gourl);
            exit();
        }
        else
        {
            ShowMsg('�ɹ�����һ�����ݣ�', $gourl);
            exit();
        }
    }
	
    //��õ�ǰ��ַ
    function GetCurUrl()
    {
        if(!empty($_SERVER["REQUEST_URI"]))
        {
            $nowurl = $_SERVER["REQUEST_URI"];
            $nowurls = explode("?",$nowurl);
            $nowurl = $nowurls[0];
        }
        else
        {
            $nowurl = $_SERVER["PHP_SELF"];
        }
        return $nowurl;
    }

    // ɾ������
    function DelData($isturn=TRUE)
    {
        $GLOBALS[$this->primaryKey] = isset($GLOBALS[$this->primaryKey])? $GLOBALS[$this->primaryKey] : request($this->primaryKey);
        if(preg_match("#,#", $this->primaryKey))
        {
            $keyArr = (isset($GLOBALS['primarykey']) ? $GLOBALS['primarykey'] : '');
        }
        else
        {
            $keyArr = isset($GLOBALS[$this->primaryKey]) ? $GLOBALS[$this->primaryKey] : '';
        }
        if(!is_array($keyArr))
        {
            ShowMsg('ûָ��Ҫɾ���ļ�¼��', '-1');
            exit();
        }
        else
        {
            $isid = !preg_match("#,#", $this->primaryKey) ? TRUE : FALSE;
            $i = 0;
            foreach($keyArr as $v)
            {
                $v = preg_replace("#[^0-9a-z]#i", '', $v);
                if(empty($v)) continue;
                $i++;
                if($isid)
                {
                    $this->dsql->ExecuteNoneQuery("DELETE FROM `{$this->tableName}` WHERE `$this->primaryKey`='$v' ");
                }
                else
                {
                    $this->dsql->ExecuteNoneQuery("DELETE FROM `{$this->tableName}` WHERE md5('key', `".str_replace(',','`,`',$this->primaryKey)."`='$v' ");
                }
            }
            if($isturn)
            {
                $gourl = !empty($_COOKIE['LURD_GOBACK_URL']) ? $_COOKIE['LURD_GOBACK_URL'] : '-1';
                ShowMsg('�ɹ�ɾ��ָ���ļ�¼��', $gourl);
                exit();
            }
            else
            {
                return $i;
            }
        }
    }
    
    //����������(�������ݲ�ѯ�����)
    //$tablelurd Ŀ���lurd����, $linkfields select���ֶ�
    //$mylinkid ��ǰ������������ֶ�
    //$linkid Ŀ�������������ֶ�
    function AddLinkTable(&$tablelurd, $mylinkid, $linkid, $linkfields='*')
    {
        if(trim($linkfields)=='') $linkfields = '*';
        $this->linkTables[] = array($tablelurd, $mylinkid, $linkid, $linkfields);
        //��¼���ӱ���ֶ���Ϣ
        if($linkfields != '*')
        {
            $fs = explode(',', $linkfields);
            foreach($fs as $f)
            {
                $f = trim($f);
                if(isset($tablelurd->fields[$f]))
                {
                    $this->addFields[$f] = $tablelurd->fields[$f];
                    $this->addFields[$f]['table'] = $tablelurd->tableName;
                }
            }
        }
        else
        {
            foreach($tablelurd->fields as $k=>$v) 
            {
                $this->addFields[$k] = $v;
                $this->addFields[$k]['table'] = $tablelurd->tableName;
            }
        }
    }

    //������ṹ
    function AnalyseTable()
    {
        if($this->tableName == '')
        {
            exit(" No Input Table! ");
        }
        
        $this->dsql->Execute('ana', " SHOW CREATE TABLE `{$this->tableName}`; ");
        $row = $this->dsql->GetArray('ana', MYSQL_NUM);
        if(!is_array($row))
        {
            exit(" Analyse Table `$tablename` Error! ");
        }

        // ��ȥ�������е�ע��
        // $row[1] = preg_replace('#COMMENT \'(.*?)\'#i', '', $row[1]);
        // echo $row[1];exit;
        $flines = explode("\n", $row[1]);
        $parArray = array('date', 'float', 'int', 'char', 'text', 'bin', 'em');
        $prikeyTmp = '';
        for($i=1; $i < count($flines)-1; $i++ )
        {
            $line = trim($flines[$i]);
            $lines = explode(' ', str_replace('`', '', $line));

            if( $lines[0] == 'KEY' ) continue;
            if( $lines[0] == 'UNIQUE' ) continue;
            
            if( $lines[0] == 'PRIMARY' )
            {
                $this->primaryKey = preg_replace("/[\(\)]|,$/", '', $lines[count($lines)-1]);
                continue;
            }
            //�ֶ����ơ�����
            $this->fields[$lines[0]] = array('type' => '',  'length' => '', 'unsigned' => FALSE, 'autokey' => FALSE, 'null' => TRUE, 'default' => '', 'em' => '', 'comment' => '');
            $this->fields[$lines[0]]['type'] = strtoupper(preg_replace("/\(.*$|,/", '', $lines[1]));
            $this->fields[$lines[0]]['length'] = preg_replace("/^.*\(|\)/", '', $lines[1]);
            if(preg_match("#[^0-9]#", $this->fields[$lines[0]]['length']))
            {
                if($this->fields[$lines[0]]['type'] == 'SET'
                    || $this->fields[$lines[0]]['type'] == 'ENUM')
                {
                    $this->fields[$lines[0]]['em'] = preg_replace("/'/", '', $this->fields[$lines[0]]['length']);
                }
                $this->fields[$lines[0]]['length'] = 0;
            }
            
            //���ض����͵����ݼ���������
            foreach($parArray as $v)
            {
                $tmpstr = "if(in_array(\$this->fields[\$lines[0]]['type'], \$this->{$v}Types))
                {
                    \$this->{$v}Fields[] = \$lines[0];
                }";
                eval($tmpstr);
            }
            if( !in_array($this->fields[$lines[0]]['type'], $this->textTypes) 
                && !in_array($this->fields[$lines[0]]['type'], $this->binTypes) )
            {
                $prikeyTmp .= ($prikeyTmp=='' ? $lines[0] : ','.$lines[0]);
            }
            
            //������������
            // echo $line;exit;
            if(preg_match("#unsigned#i", $line))
            {
                $this->fields[$lines[0]]['unsigned'] = TRUE;
            }
            if(preg_match("#auto_increment#i", $line))
            {
                $this->fields[$lines[0]]['autokey'] = TRUE;
                $this->autoField = $lines[0];
            }
            if(preg_match("#NOT NULL#i", $line))
            {
                $this->fields[$lines[0]]['null'] = FALSE;
            }
            if(preg_match("#default#i", $line))
            {
                preg_match("#default '(.*?)'#i", $line, $dfmatchs);
                $this->fields[$lines[0]]['default'] = isset($dfmatchs[1])? $dfmatchs[1] : NULL;
            }
            if(preg_match("#comment#i", $line))
            {
                preg_match("#comment '(.*?)'#i", $line, $cmmatchs);
                $this->fields[$lines[0]]['comment'] = isset($cmmatchs[1])? $cmmatchs[1] : NULL;
            }
            
        }//end for
        if( $this->primaryKey=='' )
        {
            $this->primaryKey = $prikeyTmp;
        }
    }
    
    /**
    * ������������
    * @parem $fieldname �ֶ�����
    * @parem $fieldvalue ����� value ֵ�����Ⱦ���ת��
    * @parem $condition ���� >��<��=��<> ��like��%like%��%like��like%
    * @parem $linkmode AND �� OR
    */
    function AddSearchParameter($fieldname, $fieldvalue, $condition, $linkmode='AND')
    {
        $c = count($this->searchParameters);
        //����ָ���˶���ֶΣ�ʹ�� CONCAT �������ᣨͨ����like������
        if( preg_match('/,/', $fieldname) )
        {
            $fs = explode(',', $fieldname);
            $fieldname = "CONCAT(";
            $ft = '';
            foreach($fs as $f)
            {
                $f = trim($f);
                $ft .= ($ft=='' ? "`{$f}`" : ",`{$f}`");
            }
            $fieldname .= "{$ft}) ";
        }
        $this->searchParameters[$c]['field'] = $fieldname;
        $this->searchParameters[$c]['value'] = $fieldvalue;
        $this->searchParameters[$c]['condition'] = $condition;
        $this->searchParameters[$c]['mode'] = $linkmode;
    }

    // ��ȡ��������
    function GetSearchQuery()
    {
        $wquery = '';
        if( count( $this->searchParameters ) == 0 )
        {
            return '';
        }
        else
        {
            foreach($this->searchParameters as $k=>$v)
            {
                if( preg_match("/like/i", $v['condition']) )
                {
                    $v['value'] = preg_replace("/like/i", $v['value'], $v['condition']);
                }
                $v['condition'] = preg_replace("/%/", '', $v['condition']);
                if( $wquery=='' )
                {
                    if(!preg_match("/\./", $v['field'])) $v['field'] = "{$v['field']}";
                    $wquery .= "WHERE ".$v['field']." {$v['condition']} '{$v['value']}' ";
                }
                else
                {
                    if(!preg_match("/\./", $v['field'])) $v['field'] = "{$v['field']}";
                    $wquery .= $v['mode']." ".$v['field']." {$v['condition']} '{$v['value']}' ";
                }
            }
        }
        return $wquery ;
    }
    
    //�Ѵӱ����ݻ�������ֵ���д���
    function GetData($fname)
    {
        $reValue = '';
        $ftype = $this->fields[$fname]['type'];
        $GLOBALS[$fname] = isset($GLOBALS[$fname])? $GLOBALS[$fname] : @$GLOBALS['request']->forms[$fname];
        //�����Ƶ�������
        if( in_array($ftype, $this->binTypes) )
        {
            return $this->GetBinData($fname);
        }
        //û�������������Ĭ��ֵ
        else if( !isset($GLOBALS[$fname]) )
        {
            if( isset($this->fields[$fname]['default']) )
            {
                return $this->fields[$fname]['default'];
            }
            else
            {
                if(in_array($ftype, $this->intTypes) || in_array($ftype, $this->floatTypes)) {
                    return 0;
                }
                else if(in_array($ftype, $this->charTypes) || in_array($ftype, $this->textTypes)) {
                    return '';
                }
                else {
                    return 'NULL';
                }
            }
        }
        //��������
        else if( preg_match("#YEAR|INT#", $ftype) )
        {
            // $temp = isset($GLOBALS[$fname][0])? $GLOBALS[$fname][0] : 0;
            $negTag = is_int($GLOBALS[$fname]) && $GLOBALS[$fname]< 0 ? '-' : $GLOBALS[$fname];
            $reValue = preg_replace("#[^0-9]#", '', $GLOBALS[$fname]);
            $reValue = empty($reValue) ? 0 : intval($reValue);
            if($negTag=='-' && !$this->fields[$fname]['unsigned']
                 && $reValue != 0 && $ftype != 'YEAR')
            {
                $reValue = intval('-'.$reValue);
            }
        }
        //����С������
        else if(in_array($ftype, $this->floatTypes))
        {
            $negTag = $GLOBALS[$fname][0];
            $reValue = preg_replace("#[^0-9\.]|^\.#", '', $GLOBALS[$fname]);
            $reValue = empty($reValue) ? 0 : doubleval($reValue);
            if($negTag=='-' && !$this->fields[$fname]['unsigned'] && $reValue != 0)
            {
                $reValue = intval('-'.$reValue);
            }
        }
        //�ַ�������
        else if(in_array($ftype, $this->charTypes))
        {
            $reValue = cn_substrR($this->StringSafe($GLOBALS[$fname]), $this->fields[$fname]['length']);
        }
        //�ı�����
        else if(in_array($ftype, $this->textTypes))
        {
            $reValue = $this->StringSafe($GLOBALS[$fname]);
        }
        //SET����
        else if($ftype=='SET')
        {
            $sysSetArr = explode(',', $this->fields[$fname]['em']);
            if( !is_array($GLOBALS[$fname]) )
            {
                $setArr[] = $GLOBALS[$fname];
            }
            else
            {
                $setArr = $GLOBALS[$fname];
            }
            $reValues = array();
            foreach($setArr as $a)
            {
                if(in_array($a, $sysSetArr)) $reValues[] = $a;
            }
            $reValue = count($reValues)==0 ? 'NULL' : join(',', $reValues);
        }
        //ö������
        else if($ftype=='ENUM')
        {
            $sysEnumArr = explode(',', $this->fields[$fname]['em']);
            if(in_array($GLOBALS[$fname], $sysEnumArr)) $reValue = $GLOBALS[$fname];
            else $reValue = 'NULL';
        }
        //ʱ����������
        else if(in_array($ftype, $this->dateTypes))
        {
            if($ftype=='TIMESTAMP')
            {
                $reValue = GetMkTime($GLOBALS[$fname]);
            }
            else
            {
                $reValue = preg_replace("#[^0-9 :-]#", '', $GLOBALS[$fname]);
            }
        }
        return $reValue;
    }
    
    //��������ַ������а�ȫ����
    function StringSafe($str, $safestep=-1)
    {
        $safestep = ($safestep > -1) ? $safestep : $this->stringSafe;
        //����Σ�յ�HTML(Ĭ�ϼ���)
        if($safestep == 1)
        {
            $str = preg_replace("#script:#i", "���������", $str);
            $str = preg_replace("#<[\/]{0,1}(link|meta|ifr|fra|scr)[^>]*>#isU", '', $str);
            $str = preg_replace("#[\r\n\t ]{1,}#", ' ', $str);
            return $str;
        }
        //��ȫ��ֹHTML
        //��ת��һЩ����ȫ�ַ������磺eval(��union��CONCAT(��--���ȣ�
        else if($this->stringSafe == 2)
        {
            $str = addslashes(htmlspecialchars(stripslashes($str)));
            $str = preg_replace("#eval#i", '������', $str);
            $str = preg_replace("#union#i", '�������', $str);
            $str = preg_replace("#concat#i", '�������', $str);
            $str = preg_replace("#--#", '����', $str);
            $str = preg_replace("#[\r\n\t ]{1,}#", ' ', $str);
            return $str;
        }
        //������ȫ����
        else
        {
            return $str;
        }
    }
    //����������ļ�����
    //Ϊ�˰�ȫ������Զ��������ݱ���ʹ��base64��������
    function GetBinData($fname)
    {
        $lurdtmp = DEDEDATA.'/lurdtmp';
        if(!isset($_FILES[$fname]['tmp_name']) || !is_uploaded_file($_FILES[$fname]['tmp_name']))
        {
            return '';
        }
        else
        {
            $tmpfile = $lurdtmp.'/'.md5( time() . Sql::ExecTime() . mt_rand(1000, 5000) ).'.tmp';
            $rs = move_uploaded_file($_FILES[$fname]['tmp_name'], $tmpfile);
            if(!$rs) return '';
            $fp = fopen($tmpfile, 'r');
            $data = base64_encode(fread($fp, filesize($tmpfile)));
            fclose($fp);
            return $data;
        }
    }

    //��ʽ����������
    function FormatFloat($fvalue, $ftype='')
    {
        if($ftype=='') $ftype='%0.4f';
        return sprintf($ftype, $fvalue);
    }
    
    //���Ĭ��ʱ���ʽ
    function GetDateTimeDf($ftype)
    {
        if($ftype=='DATE') return 'Y-m-d';
        else if($ftype=='TIME') return 'H:i:s';
        else if($ftype=='YEAR') return 'Y';
        else return 'Y-m-d H:i:s';
    }
    
    //��ʽ������
    function FormatDate($fvalue, $ftype, $fformat='')
    {
        if($ftype=='INT' || $ftype='TIMESTAMP' ) return MyDate($fvalue, $fformat);
        else return $fvalue;
    }

}
?>