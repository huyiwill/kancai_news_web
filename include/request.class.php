<?php
/**
 * �����ⲿ�����������
 *
 * ��ֹ���ļ�������ļ����� $_POST��$_GET��$_FILES������eval����(��request::myeval )
 * �Ա��ڶ���Ҫ�ڿ͹������з���
 *
 * @version        $Id: request.class.php 1 12:03 2010-10-28 tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
// REQUEST����,�����ж��Ƿ�����REQUEST��
define('DEDEREQUEST', TRUE);

//�� cls_request::item() ����
function Request($key, $df='')
{
    $GLOBALS['request'] = isset($GLOBALS['request'])? $GLOBALS['request'] : new Request;
    if (!$GLOBALS['request']->isinit)
    {
        $GLOBALS['request']->Init();
    }
    return $GLOBALS['request']->Item($key, $df);
}
class Request
{

    var $isinit = false;
    //�û���cookie
    var $cookies = array();

    //��GET��POST�ı����ϲ�һ�飬�൱�� _REQUEST
    var $forms = array();
    
    //_GET ����
    var $gets = array();

    //_POST ����
    var $posts = array();

    //�û�������ģʽ GET �� POST
    var $request_type = 'GET';

    //�ļ�����
    var $files = array();
    
    //�Ͻ�������ļ���
    var $filter_filename = '/\.(php|pl|sh|js)$/i';

   /**
    * ��ʼ���û�����
    * ���� post��get �����ݣ���ת�� selfforms ���飬 ��ɾ��ԭ������
    * ���� cookie �����ݣ���ת�� cookies ���飬����ɾ��ԭ������
    */
    function Init()
    {
        global $_POST,$_GET;
        //����post��get
        $formarr = array('p' => $_POST, 'g' => $_GET);
        foreach($formarr as $_k => $_r)
        {
            if( count($_r) > 0 )
            {
                foreach($_r as $k=>$v)
                {
                    if( preg_match('/^cfg_(.*?)/i', $k) )
                    {
                        continue;
                    }
                    $this->forms[$k] = $v;
                    if( $_k=='p' )
                    {
                        $this->posts[$k] = $v;
                    } else {
                        $this->gets[$k] = $v;
                    }
                }
            }
        }
        unset($_POST);
        unset($_GET);
        unset($_REQUEST);
        
        //����cookie
        if( count($_COOKIE) > 0 )
        {
            foreach($_COOKIE as $k=>$v)
            {
                if( preg_match('/^config/i', $k) )
                {
                    continue;
                }
                $this->cookies[$k] = $v;
            }
        }
        //unset($_POST, $_GET);
        
        //�ϴ����ļ�����
        if( isset($_FILES) && count($_FILES) > 0 )
        {
            $this->FilterFiles($_FILES);
        }
        $this->isinit = TRUE;
        
        //global����
        //self::$forms['_global'] = $GLOBALS;
    }

   /**
    * �� eval ������Ϊ myeval
    */
    function MyEval( $phpcode )
    {
        return eval( $phpcode );
    }

   /**
    * ���ָ����ֵ
    */
    function Item( $formname, $defaultvalue = '' )
    {
        return isset($this->forms[$formname]) ? $this->forms[$formname] :  $defaultvalue;
    }

   /**
    * ���ָ����ʱ�ļ���ֵ
    */
    function Upfile( $formname, $defaultvalue = '' )
    {
        return isset($this->files[$formname]['tmp_name']) ? $this->files[$formname]['tmp_name'] :  $defaultvalue;
    }

   /**
    * �����ļ����
    */
    function FilterFiles( &$files )
    {
        foreach($files as $k=>$v)
        {
            $this->files[$k] = $v;
        }
        unset($_FILES);
    }

   /**
    * �ƶ��ϴ����ļ�
    */
    function MoveUploadFile( $formname, $filename, $filetype = '' )
    {
        if( $this->IsUploadFile( $formname ) )
        {
            if( preg_match($this->filter_filename, $filename) )
            {
                return FALSE;
            }
            else
            {
                return move_uploaded_file($this->files[$formname]['tmp_name'], $filename);
            }
        }
    }

   /**
    * ����ļ�����չ��
    */
    function GetShortname( $formname )
    {
        $filetype = strtolower(isset($this->files[$formname]['type']) ? $this->files[$formname]['type'] : '');
        $shortname = '';
        switch($filetype)
        {
            case 'image/jpeg':
                $shortname = 'jpg';
                break;
            case 'image/pjpeg':
                $shortname = 'jpg';
                break;
            case 'image/gif':
                $shortname = 'gif';
                break;
            case 'image/png':
                $shortname = 'png';
                break;
            case 'image/xpng':
                $shortname = 'png';
                break;
            case 'image/wbmp':
                $shortname = 'bmp';
                break;
            default:
                $filename = isset($this->files[$formname]['name']) ? $this->files[$formname]['name'] : '';
                if( preg_match("/\./", $filename) )
                {
                    $fs = explode('.', $filename);
                    $shortname = strtolower($fs[ count($fs)-1 ]);
                }
                break;
        }
        return $shortname;
    }

   /**
    * ���ָ���ļ������ļ���ϸ��Ϣ
    */
    function GetFileInfo( $formname, $item = '' )
    {
        if( !isset( $this->files[$formname]['tmp_name'] ) )
        {
            return FALSE;
        }
        else
        {
            if($item=='')
            {
                return $this->files[$formname];
            }
            else
            {
                return (isset($this->files[$formname][$item]) ? $this->files[$formname][$item] : '');
            }
        }
    }

   /**
    * �ж��Ƿ�����ϴ����ļ�
    */
    function IsUploadFile( $formname )
    {
        if( !isset( $this->files[$formname]['tmp_name'] ) )
        {
            return FALSE;
        }
        else
        {
            return is_uploaded_file( $this->files[$formname]['tmp_name'] );
        }
    }
    
    /**
     * ����ļ���׺�Ƿ�Ϊָ��ֵ
     *
     * @param  string  $subfix
     * @return boolean
     */
     function CheckSubfix($formname, $subfix = 'csv')
    {
        if( $this->GetShortname( $formname ) != $subfix)
        {
            return FALSE;
        }
        return TRUE;
    }
}