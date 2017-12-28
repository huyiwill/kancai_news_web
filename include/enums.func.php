<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * �����˵���
 *
 * @version        $Id: enums.func.php 2 13:19 2011-3-24 tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

// �������ڻ����ļ���д�뻺��
if(!file_exists(DEDEDATA.'/enums/system.php')) WriteEnumsCache();

/**
 *  ����ö�ٻ���
 *
 * @access    public
 * @param     string  $egroup  ������
 * @return    string
 */
function WriteEnumsCache($egroup='')
{
    global $dsql;
    $egroups = array();
    if($egroup=='') {
        $dsql->SetQuery("SELECT egroup FROM `#@__sys_enum` GROUP BY egroup ");
    }
    else {
        $dsql->SetQuery("SELECT egroup FROM `#@__sys_enum` WHERE egroup='$egroup' GROUP BY egroup ");
    }
    $dsql->Execute('enum');
    while($nrow = $dsql->GetArray('enum')) {
        $egroups[] = $nrow['egroup'];
    }
    foreach($egroups as $egroup)
    {
        $cachefile = DEDEDATA.'/enums/'.$egroup.'.php';
        $fp = fopen($cachefile,'w');
        fwrite($fp,'<'."?php\r\nglobal \$em_{$egroup}s;\r\n\$em_{$egroup}s = array();\r\n");
        $dsql->SetQuery("SELECT ename,evalue,issign FROM `#@__sys_enum` WHERE egroup='$egroup' ORDER BY disorder ASC, evalue ASC ");
        $dsql->Execute('enum');
        $issign = -1;
        $tenum = false; //����������ʶ
        while($nrow = $dsql->GetArray('enum'))
        {
            fwrite($fp,"\$em_{$egroup}s['{$nrow['evalue']}'] = '{$nrow['ename']}';\r\n");
            if($issign==-1) $issign = $nrow['issign'];
            if($nrow['issign']==2) $tenum = true;
        }
        if ($tenum) $dsql->ExecuteNoneQuery("UPDATE `#@__stepselect` SET `issign`=2 WHERE egroup='$egroup'; ");
        fwrite($fp,'?'.'>');
        fclose($fp);
        if(empty($issign)) WriteEnumsJs($egroup);
    }
    return '�ɹ���������ö�ٻ��棡';
}

/**
 *  ��ȡ�������������ݵĸ���������
 *
 * @access    public
 * @param     string  $v
 * @return    array
 */
function GetEnumsTypes($v)
{
    $rearr['top'] = $rearr['son'] = 0;
    if($v==0) return $rearr;
    if($v%500==0) {
        $rearr['top'] = $v;
    }
    else {
        $rearr['son'] = $v;
        $rearr['top'] = $v - ($v%500);
    }
    return $rearr;
}

/**
 *  ��ȡö�ٵ�select��
 *
 * @access    public
 * @param     string  $egroup  ������
 * @param     string  $evalue  ����ֵ
 * @param     string  $formid  ��ID
 * @param     string  $seltitle  ѡ�����
 * @return    string  �ɹ��󷵻�һ��ö�ٱ�
 */
function GetEnumsForm($egroup, $evalue=0, $formid='', $seltitle='')
{
    $cachefile = DEDEDATA.'/enums/'.$egroup.'.php';
    include($cachefile);
    if($formid=='')
    {
        $formid = $egroup;
    }
    $forms = "<select name='$formid' id='$formid' class='enumselect'>\r\n";
    $forms .= "\t<option value='0' selected='selected'>--��ѡ��--{$seltitle}</option>\r\n";
    foreach(${'em_'.$egroup.'s'} as $v=>$n)
    {
        $prefix = ($v > 500 && $v%500 != 0) ? '���� ' : '';
        if (preg_match("#\.#", $v)) $prefix = ' &nbsp;&nbsp;������ ';

        if($v==$evalue)
        {
            $forms .= "\t<option value='$v' selected='selected'>$prefix$n</option>\r\n";
        }
        else
        {
            $forms .= "\t<option value='$v'>$prefix$n</option>\r\n";
        }
    }
    $forms .= "</select>";
    return $forms;
}

/**
 *  ��ȡһ������
 *
 * @access    public
 * @param     string    $egroup   ������
 * @return    array
 */
function getTopData($egroup)
{
    $data = array();
    $cachefile = DEDEDATA.'/enums/'.$egroup.'.php';
    include($cachefile);
    foreach(${'em_'.$egroup.'s'} as $k=>$v)
    {
        if($k >= 500 && $k%500 == 0) {
            $data[$k] = $v;
        }
    }
    return $data;
}


/**
 *  ��ȡ���ݵ�JS����(��������)
 *
 * @access    public
 * @param     string    $egroup   ������
 * @return    string
 */
function GetEnumsJs($egroup)
{
    global ${'em_'.$egroup.'s'};
    include_once(DEDEDATA.'/enums/'.$egroup.'.php');
    $jsCode = "<!--\r\n";
    $jsCode .= "em_{$egroup}s=new Array();\r\n";
    foreach(${'em_'.$egroup.'s'} as $k => $v)
    {
        // JS�н�3����Ŀ��ŵ��ڶ���key��ȥ
        if (preg_match("#([0-9]{1,})\.([0-9]{1,})#", $k, $matchs))
        {
            $valKey = $matchs[1] + $matchs[2] / 1000;
            $jsCode .= "em_{$egroup}s[{$valKey}]='$v';\r\n";
        } else { 
            $jsCode .= "em_{$egroup}s[$k]='$v';\r\n";
        }
    }
    $jsCode .= "-->";
    return $jsCode;
}

/**
 *  д������JS����
 *
 * @access    public
 * @param     string    $egroup   ������
 * @return    string
 */
function WriteEnumsJs($egroup)
{
    $jsfile = DEDEDATA.'/enums/'.$egroup.'.js';
    $fp = fopen($jsfile, 'w');
    fwrite($fp, GetEnumsJs($egroup));
    fclose($fp);
}


/**
 *  ��ȡö�ٵ�ֵ
 *
 * @access    public
 * @param     string    $egroup   ������
 * @param     string    $evalue   ����ֵ
 * @return    string
 */
function GetEnumsValue($egroup, $evalue=0)
{
    include_once(DEDEDATA.'/enums/'.$egroup.'.php');
    if(isset(${'em_'.$egroup.'s'}[$evalue])) {
        return ${'em_'.$egroup.'s'}[$evalue];
    }
    else {
        return "����";
    }
}