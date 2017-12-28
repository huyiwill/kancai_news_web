<?php
/**
 * @version        $Id: actionsearch_class.php 1 8:26 2010��7��12��Z tianya $
 * @package        DedeCMS.Administrator
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
class ActionSearch 
{
    var $keyword;
    var $asarray = array();
    var $result    = array();
    
    //��ʼ��ϵͳ
    function ActionSearch($keyword){
        $this->asarray = $this->GetSearchstr();
        $this->keyword = $keyword;
    }
    
    function GetSearchstr()
    {
        require_once(dirname(__FILE__)."/inc/inc_action_info.php");
        return is_array($actionSearch)? $actionSearch : array();
    }
    
    function search(){
        $this->searchkeyword();
        return $this->result;
    }
    
    /**
     *  ����������������йؼ���ƥ��
     *
     * @return    void
     */
    function searchkeyword(){
        $i = 0; //������������
        foreach ($this->asarray as $key=>$value) 
        {
            //�Զ�����Ŀ����ƥ��
            if(is_array($this->asarray[$key]['soniterm']))
            {
                foreach ($this->asarray[$key]['soniterm'] as $k=> $val) 
                {
                    //����Ȩ���ж�
                    if(TestPurview($val['purview']))
                    {
                        //����в���Ȩ��
                        if($this->_strpos($val['title'], $this->keyword) !== false || $this->_strpos($val['description'], $this->keyword)!== false)
                        {
                            //һ����Ŀƥ��
                            $this->result[$i]['toptitle'] = $this->redColorKeyword($this->asarray[$key]['toptitle']);
                            $this->result[$i]['title'] = $this->redColorKeyword($this->asarray[$key]['title']);
                            $this->result[$i]['description'] = $this->redColorKeyword($this->asarray[$key]['description']);
                            //������Ŀƥ��
                            $this->result[$i]['soniterm'][] = $this->redColorKeyword($val);
                        }
                    }
                }
            }
            $i++;
        }
    }

    /**
     *  �����ؼ���
     *
     * @access    public
     * @param     string  $text  �ؼ���
     * @return    string
     */
    function redColorKeyword($text){
        if(is_array($text))
        {
            foreach ($text as $key => $value) {
                if($key == 'title' || $key == 'description') 
               {
                    //����title,description���������滻
                    $text[$key] = str_replace($this->keyword,'<font color="red">'.$this->keyword.'</font>',$text[$key]);
               }
            }
        } else {
            $text = str_replace($this->keyword,'<font color="red">'.$this->keyword.'</font>',$text);
        }
        return $text;
    }
    
    function _strpos($string,$find) 
    {
        if (function_exists('stripos'))  return stripos($string,$find);
        return strpos($string,$find);
    }
}