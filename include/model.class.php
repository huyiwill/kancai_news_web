<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ģ�ͻ���
 *
 * @version        $Id: model.class.php 1 13:46 2010-12-1 tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

class Model
{
    var $dsql;
    var $db;
    
    // ��������
    function Model()
    {
        global $dsql;
        if ($GLOBALS['cfg_mysql_type'] == 'mysqli')
        {
            $this->dsql = $this->db = isset($dsql)? $dsql : new DedeSqli(FALSE);
        } else {
            $this->dsql = $this->db = isset($dsql)? $dsql : new DedeSql(FALSE);
        }
            
    }
    
    // �ͷ���Դ
    function __destruct() 
    {
        $this->dsql->Close(TRUE);
    }
}