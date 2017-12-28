<?php   if(!defined('DEDEINC')) exit("Request Error!");
/**
 * ����С����,֧���ļ���memcache
 *
 * @version        $Id: cache.helper.php 1 10:46 2011-3-2 tianya $
 * @package        DedeCMS.Helpers
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */
/**
 *  ������
 *
 * @access    public
 * @param     string  $prefix  ǰ׺
 * @param     string  $key  ��
 * @param     string  $is_memcache  �Ƿ�Ϊmemcache����
 * @return    string
 */
if ( ! function_exists('GetCache'))
{
    function GetCache($prefix, $key, $is_memcache = TRUE)
    {
        global $cache_helper_config;
        $key = md5 ( $key );
        /* �������MC���� */
        if ($is_memcache === TRUE && ! empty ( $cache_helper_config['memcache'] ) && $cache_helper_config['memcache'] ['is_mc_enable'] === 'Y')
        {
            $mc_path = empty ( $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )] ) ? $cache_helper_config['memcache'] ['mc'] ['default'] : $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )];
            $mc_path = parse_url ( $mc_path );
            $key = ltrim ( $mc_path ['path'], '/' ) . '_' . $prefix . '_' . $key;
            if (empty ( $GLOBALS ['mc_' . $mc_path ['host']] ))
            {
                $GLOBALS ['mc_' . $mc_path ['host']] = new Memcache ( );
                $GLOBALS ['mc_' . $mc_path ['host']]->connect ( $mc_path ['host'], $mc_path ['port'] );
            }
            return $GLOBALS ['mc_' . $mc_path ['host']]->get ( $key );
        }
        $key = substr ( $key, 0, 2 ) . '/' . substr ( $key, 2, 2 ) . '/' . substr ( $key, 4, 2 ) . '/' . $key;
        $result = @file_get_contents ( DEDEDATA . "/cache/$prefix/$key.php" );
        
        if ($result === false)
        {
            return false;
        }
        $result = str_replace("<?php exit('dedecms');?>\n\r", "", $result);
        $result = @unserialize ( $result );
        if($result ['timeout'] != 0 && $result ['timeout'] < time ())
        {
              return false;
        }
        return $result ['data'];
    }
}


/**
 *  д����
 *
 * @access    public
 * @param     string  $prefix  ǰ׺
 * @param     string  $key  ��
 * @param     string  $value  ֵ
 * @param     string  $timeout  ����ʱ��
 * @return    int
 */
if ( ! function_exists('SetCache'))
{
    function SetCache($prefix, $key, $value, $timeout = 3600, $is_memcache = TRUE)
    {
        global $cache_helper_config;
        $key = md5 ( $key );
        /* �������MC���� */
        if (! empty ( $cache_helper_config['memcache'] ) && $cache_helper_config['memcache'] ['is_mc_enable'] === 'Y' && $is_memcache === TRUE)
        {
            $mc_path = empty ( $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )] ) ? $cache_helper_config['memcache'] ['mc'] ['default'] : $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )];
            $mc_path = parse_url ( $mc_path );
            $key = ltrim ( $mc_path ['path'], '/' ) . '_' . $prefix . '_' . $key;
            if (empty ( $GLOBALS ['mc_' . $mc_path ['host']] ))
            {
                $GLOBALS ['mc_' . $mc_path ['host']] = new Memcache ( );
                $GLOBALS ['mc_' . $mc_path ['host']]->connect ( $mc_path ['host'], $mc_path ['port'] );
                //��������ѹ���ż�
                //$GLOBALS ['mc_' . $mc_path ['host']]->setCompressThreshold(2048, 0.2);
            }
            $result = $GLOBALS ['mc_' . $mc_path ['host']]->set ( $key, $value, MEMCACHE_COMPRESSED, $timeout );
            return $result;
        }
        $key = substr ( $key, 0, 2 ) . '/' . substr ( $key, 2, 2 ) . '/' . substr ( $key, 4, 2 ) . '/' . $key;
        $tmp ['data'] = $value;
        $tmp ['timeout'] = $timeout != 0 ? time () + ( int ) $timeout : 0;
        $cache_data = "<?php exit('dedecms');?>\n\r".@serialize ( $tmp );
        return @PutFile ( DEDEDATA . "/cache/$prefix/$key.php",  $cache_data);
    }
}


/**
 *  ɾ������
 *
 * @access    public
 * @param     string  $prefix  ǰ׺
 * @param     string  $key  ��
 * @param     string  $is_memcache  �Ƿ�Ϊmemcache����
 * @return    string
 */
if ( ! function_exists('DelCache'))
{
    /* ɾ���� */
    function DelCache($prefix, $key, $is_memcache = TRUE)
    {
        global $cache_helper_config;
        $key = md5 ( $key );
        /* �������MC���� */
        if (! empty ( $cache_helper_config['memcache'] ) && $cache_helper_config['memcache'] ['is_mc_enable'] === TRUE && $is_memcache === TRUE)
        {
            $mc_path = empty ( $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )] ) ? $cache_helper_config['memcache'] ['mc'] ['default'] : $cache_helper_config['memcache'] ['mc'] [substr ( $key, 0, 1 )];
            $mc_path = parse_url ( $mc_path );
            $key = ltrim ( $mc_path ['path'], '/' ) . '_' . $prefix . '_' . $key;
            if (empty ( $GLOBALS ['mc_' . $mc_path ['host']] ))
            {
                $GLOBALS ['mc_' . $mc_path ['host']] = new Memcache ( );
                $GLOBALS ['mc_' . $mc_path ['host']]->connect ( $mc_path ['host'], $mc_path ['port'] );
            }
            return $GLOBALS ['mc_' . $mc_path ['host']]->delete ( $key );
        }
        $key = substr ( $key, 0, 2 ) . '/' . substr ( $key, 2, 2 ) . '/' . substr ( $key, 4, 2 ) . '/' . $key;
        return @unlink ( DEDEDATA . "/cache/$prefix/$key.php" );
    }
}