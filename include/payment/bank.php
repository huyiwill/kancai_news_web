<?php
if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ���л��/ת�ʽӿ�
 */

/**
 * ��
 */
class bank
{
    /**
    * ���캯��
    *
    * @access  public
    * @param
    *
    * @return void
    */
    var $orderurl = '../member/shops_products.php';
    
    function bank()
    {
    }

    function __construct()
    {
      $this->bank();
    }
    
    /**
    * ���û��͵�ַ
    */
    
    function SetReturnUrl($returnurl='')
    {
        return "";
    }

    /**
    * �ύ����
    */
    function GetCode($order,$payment)
    {
        require_once DEDEINC.'/shopcar.class.php';
        $cart = new MemberShops();
        $cart->clearItem();
        $cart->MakeOrders();
        if($payment=="member") $button="������ <a href='/'>������ҳ</a> ��ȥ <a href='/member/operation.php'>��Ա����</a>";
        else $button="������ <a href='/'>������ҳ</a> ��ȥ <a href='{$this->orderurl}?oid=".$order."'>�鿴����</a>";
        return $button;
    }
    
}//End API