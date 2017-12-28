<?php
if(!defined('DEDEINC')) exit('Request Error!');
/**
 *�ױ��ӿ���
 */
class yeepay
{
    var $dsql;
    var $mid;
  
    # ҵ������
    # ֧�����󣬹̶�ֵ"Buy" 
    var $p0_Cmd = 'Buy';
  
    # �ͻ���ַ
    # Ϊ"1": ��Ҫ�û����ͻ���ַ�����ױ�֧��ϵͳ;Ϊ"0": ����Ҫ��Ĭ��Ϊ "0".
    var $p9_SAF = "0";
    
    # ���ص�ַ����������
    var $reqURL_onLine = "https://www.yeepay.com/app-merchant-proxy/node";
    //$reqURL_onLine = "http://tech.yeepay.com:8080/robot/debug.action";
    
    var $return_url='/plus/carbuyaction.php?dopost=return'; //���ش����ַ
        
    /**
    * ���캯��
    *
    * @access  public
    * @param
    *
    * @return void
    */
    function yeepay()
    {
        global $dsql;
        $this->dsql = $dsql;
    }

    function __construct()
    {
        $this->yeepay();
    }
    
    /**
     *  �趨�ӿڻ��͵�ַ
     *
     *  ����: $this->SetReturnUrl($cfg_basehost."/tuangou/control/index.php?ac=pay&orderid=".$p2_Order)
     *
     * @param     string  $returnurl  ���͵�ַ
     * @return    void
     */
    function SetReturnUrl($returnurl='')
    {
        if (!empty($returnurl))
        {
            $this->return_url = $returnurl;
        }
    }

    /**
    * ����֧������
    * @param   array   $order      ������Ϣ
    * @param   array   $payment    ֧����ʽ��Ϣ
    */
    function GetCode($order, $payment)
    {
        global $cfg_basehost,$cfg_cmspath;
        //���ڶ���Ŀ¼�Ĵ���
        if(!empty($cfg_cmspath)) $cfg_basehost = $cfg_basehost.'/'.$cfg_cmspath;

        #    �̼������û�������Ʒ��֧����Ϣ.
        ##�ױ�֧��ƽ̨ͳһʹ��GBK/GB2312���뷽ʽ,�������õ����ģ���ע��ת��
        
        #    �̻�������,ѡ��.
        ##����Ϊ""���ύ�Ķ����ű����������˻�������Ψһ;Ϊ""ʱ���ױ�֧�����Զ�����������̻�������.
        $p2_Order                    = trim($order['out_trade_no']);
        
        #    ֧�����,����.
        ##��λ:Ԫ����ȷ����.
        $p3_Amt                        = $order['price'];
        
        #    ���ױ���,�̶�ֵ"CNY".
        $p4_Cur                        = "CNY";
        
        #    ��Ʒ����
        ##����֧��ʱ��ʾ���ױ�֧���������Ķ�����Ʒ��Ϣ.
        $p5_Pid                        = trim($order['out_trade_no']);
        
        #    ��Ʒ����
        $p6_Pcat                    = 'cart';
        
        #    ��Ʒ����
        $p7_Pdesc                    = '';
        
        #    �̻�����֧���ɹ����ݵĵ�ַ,֧���ɹ����ױ�֧������õ�ַ�������γɹ�֪ͨ.
        //$p8_Url                        = $cfg_basehost."/plus/carbuyaction.php?dopost=return&code=".$payment['code'];  
        $p8_Url                        =   $cfg_basehost.$this->return_url.'&code='.$payment['code'];
        
        #    �̻���չ��Ϣ
        ##�̻�����������д1K ���ַ���,֧���ɹ�ʱ��ԭ������.                                                
        $pa_MP                        = 'member';
        
        #    Ӧ�����
        ##Ϊ"1": ��ҪӦ�����;Ϊ"0": ����ҪӦ�����.
        $pr_NeedResponse    = 1;
        
        #    ���б���
            ##Ĭ��Ϊ""�����ױ�֧������.��������ʾ�ױ�֧����ҳ�棬ֱ����ת�������С�������֧��������һ��ͨ��֧��ҳ�棬���ֶο����ո�¼:�����б����ò���ֵ.            
        $pd_FrpId                    = '';
        #����ǩ����������ǩ����
        $hmac = $this->getReqHmacString($payment['yp_account'],$payment['yp_key'],$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse);
        
        $button = '<form target="_blank" method="post" action="'.$this->reqURL_onLine.'">
                            <input type="hidden" value="'.$this->p0_Cmd.'" name="p0_Cmd">
                            <input type="hidden" value="'.$payment['yp_account'].'" name="p1_MerId">
                            <input type="hidden" value="'.$p2_Order.'" name="p2_Order">
                            <input type="hidden" value="'.$p3_Amt.'" name="p3_Amt">
                            <input type="hidden" value="'.$p4_Cur.'" name="p4_Cur">
                            <input type="hidden" value="'.$p5_Pid.'" name="p5_Pid">
                            <input type="hidden" value="'.$p6_Pcat.'" name="p6_Pcat">
                            <input type="hidden" value="'.$p7_Pdesc.'" name="p7_Pdesc">
                            <input type="hidden" value="'.$p8_Url.'" name="p8_Url">
                            <input type="hidden" value="'.$this->p9_SAF.'" name="p9_SAF">
                            <input type="hidden" value="'.$pa_MP.'" name="pa_MP">
                            <input type="hidden" value="'.$pd_FrpId.'" name="pd_FrpId">
                            <input type="hidden" value="'.$pr_NeedResponse.'" name="pr_NeedResponse"    >
                            <input type="hidden" value="'.$hmac.'" name="hmac">
                            <input type="submit" value="����ʹ��YeePay�ױ�֧��"></form>';

    /* ��չ��ﳵ */
        require_once DEDEINC.'/shopcar.class.php';
        $cart     = new MemberShops();
        $cart->clearItem();
        $cart->MakeOrders();
        return $button;
    }

    /**
    * ��Ӧ����
    */
    function respond()
    {

        /* ���������ļ� */
		$code = preg_replace( "#[^0-9a-z-]#i", "", $_REQUEST['code'] );
		require_once DEDEDATA.'/payment/'.$code.'.php';
        
        $p1_MerId = trim($payment['yp_account']);
        $merchantKey = trim($payment['yp_key']);
        
        #  �������ز���.
        $return = $this->getCallBackValue($r0_Cmd, $r1_Code, $r2_TrxId, $r3_Amt, $r4_Cur, $r5_Pid, $r6_Order, $r7_Uid, $r8_MP, $r9_BType, $hmac);
        
        #  �жϷ���ǩ���Ƿ���ȷ��True/False��
        $bRet = $this->CheckHmac($p1_MerId,$merchantKey,$r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac);

        #  У������ȷ.
        if($bRet)
        {
            if($r1_Code=="1")
            {
                /*�ж϶�������*/
                if(preg_match ("/S-P[0-9]+RN[0-9]/",$r6_Order)) 
                {
                    //��ȡ�û�mid
                    $row = $this->dsql->GetOne("SELECT * FROM #@__shops_orders WHERE oid = '{$r6_Order}'");
                    $this->mid = $row['userid'];
                    $ordertype="goods";
                } else if (preg_match ("/M[0-9]+T[0-9]+RN[0-9]/",$r6_Order)){
                    $row = $this->dsql->GetOne("SELECT * FROM #@__member_operation WHERE buyid = '{$r6_Order}'");
                    //��ȡ������Ϣ����鶩������Ч��
                    if(!is_array($row)||$row['sta']==2) return $msg = "���Ķ����Ѿ������벻Ҫ�ظ��ύ!";
                    $ordertype = "member";
                    $product =    $row['product'];
                    $pname= $row['pname'];
                    $pid=$row['pid'];
                    $this->mid = $row['mid'];
                } else {    
                    return $msg = "֧��ʧ�ܣ����Ķ�����������!";
                }


                #    ��Ҫ�ȽϷ��صĽ�����̼����ݿ��ж����Ľ���Ƿ���ȣ�ֻ����ȵ�����²���Ϊ�ǽ��׳ɹ�.
                #    ������Ҫ�Է��صĴ������������ƣ����м�¼�������Դ�����ֹ��ͬһ�������ظ��������������.                
                if($r9_BType == "1" || $r9_BType == "3"){
                    if($ordertype == "goods"){ 
                        if($this->success_db($r6_Order))  return $msg = "֧���ɹ�!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                        else  return $msg = "֧��ʧ��!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                    } else if ($ordertype=="member") {
                        $oldinf = $this->success_mem($r6_Order,$pname,$product,$pid);
                        return $msg = "<font color='red'>".$oldinf."</font><br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                    }
                } else if ( $r9_BType == "2" ){
                    #�����ҪӦ�����������д��,��success��ͷ,��Сд������.
                    echo "success";
                    if($ordertype=="goods"){ 
                        if($this->success_db($r6_Order))  return $msg = "֧���ɹ�!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                        else  return $msg = "֧��ʧ��!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                    } else if ($ordertype=="member") {
                        if($this->success_mem($r6_Order,$pname,$product,$pid))  return $msg = "֧���ɹ�!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                        else  return $msg = "֧��ʧ��!<br> <a href='/'>������ҳ</a> <a href='/member'>��Ա����</a>";
                    }
                }
            }
        } else {
            $this->log_result ("verify_failed");
            return $msg = "������Ϣ����!<br> <a href='/'>������ҳ</a> ";
        }
    }


    #ǩ����������ǩ����
    function getReqHmacString($p1_MerId,$merchantKey,$p2_Order,$p3_Amt,$p4_Cur,$p5_Pid,$p6_Pcat,$p7_Pdesc,$p8_Url,$pa_MP,$pd_FrpId,$pr_NeedResponse)
    {
        #����ǩ������һ�������ĵ��б�����ǩ��˳�����
        $sbOld = "";
        #����ҵ������
        $sbOld = $sbOld.$this->p0_Cmd;
        #�����̻����
        $sbOld = $sbOld.$p1_MerId;
        #�����̻�������
        $sbOld = $sbOld.$p2_Order;     
        #����֧�����
        $sbOld = $sbOld.$p3_Amt;
        #���뽻�ױ���
        $sbOld = $sbOld.$p4_Cur;
        #������Ʒ����
        $sbOld = $sbOld.$p5_Pid;
        #������Ʒ����
        $sbOld = $sbOld.$p6_Pcat;
        #������Ʒ����
        $sbOld = $sbOld.$p7_Pdesc;
        #�����̻�����֧���ɹ����ݵĵ�ַ
        $sbOld = $sbOld.$p8_Url;
        #�����ͻ���ַ��ʶ
        $sbOld = $sbOld.$this->p9_SAF;
        #�����̻���չ��Ϣ
        $sbOld = $sbOld.$pa_MP;
        #�������б���
        $sbOld = $sbOld.$pd_FrpId;
        #�����Ƿ���ҪӦ�����
        $sbOld = $sbOld.$pr_NeedResponse;
        
        return $this->HmacMd5($sbOld,$merchantKey);
    } 

    #    ȡ�÷��ش��е����в���
    function getCallBackValue(&$r0_Cmd,&$r1_Code,&$r2_TrxId,&$r3_Amt,&$r4_Cur,&$r5_Pid,&$r6_Order,&$r7_Uid,&$r8_MP,&$r9_BType,&$hmac)
    {  
        $r0_Cmd       = $_REQUEST['r0_Cmd'];
        $r1_Code      = $_REQUEST['r1_Code'];
        $r2_TrxId     = $_REQUEST['r2_TrxId'];
        $r3_Amt       = $_REQUEST['r3_Amt'];
        $r4_Cur       = $_REQUEST['r4_Cur'];
        $r5_Pid       = $_REQUEST['r5_Pid'];
        $r6_Order     = $_REQUEST['r6_Order'];
        $r7_Uid       = $_REQUEST['r7_Uid'];
        $r8_MP        = $_REQUEST['r8_MP'];
        $r9_BType     = $_REQUEST['r9_BType']; 
        $hmac         = $_REQUEST['hmac'];
        return NULL;
    }

    function CheckHmac($p1_MerId,$merchantKey,$r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType,$hmac)
    {
        if($hmac == $this->getCallbackHmacString($p1_MerId,$merchantKey,$r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType))
            return TRUE;
        else
            return FALSE;
    }

    function getCallbackHmacString($p1_MerId,$merchantKey,$r0_Cmd,$r1_Code,$r2_TrxId,$r3_Amt,$r4_Cur,$r5_Pid,$r6_Order,$r7_Uid,$r8_MP,$r9_BType)
    {
        #ȡ�ü���ǰ���ַ���
        $sbOld = "";
        #�����̼�ID
        $sbOld = $sbOld.$p1_MerId;
        #������Ϣ����
        $sbOld = $sbOld.$r0_Cmd;
        #����ҵ�񷵻���
        $sbOld = $sbOld.$r1_Code;
        #���뽻��ID
        $sbOld = $sbOld.$r2_TrxId;
        #���뽻�׽��
        $sbOld = $sbOld.$r3_Amt;
        #������ҵ�λ
        $sbOld = $sbOld.$r4_Cur;
        #�����ƷId
        $sbOld = $sbOld.$r5_Pid;
        #���붩��ID
        $sbOld = $sbOld.$r6_Order;
        #�����û�ID
        $sbOld = $sbOld.$r7_Uid;
        #�����̼���չ��Ϣ
        $sbOld = $sbOld.$r8_MP;
        #���뽻�׽����������
        $sbOld = $sbOld.$r9_BType;
        
        return $this->HmacMd5($sbOld,$merchantKey,'gbk');

    }

    function HmacMd5($data,$key,$lang='utf-8')
    {
        // RFC 2104 HMAC implementation for php.
        // Creates an md5 HMAC.
        // Eliminates the need to install mhash to compute a HMAC
        // Hacked by Lance Rushing(NOTE: Hacked means written)
        
        //��Ҫ���û���֧��iconv���������Ĳ���������������
        if($GLOBALS['cfg_soft_lang'] != 'utf-8' || $lang!='utf-8')
        {
            $key = gb2utf8($key);
            $data = gb2utf8($data);
        }
        $b = 64; // byte length for md5
        if (strlen($key) > $b) {
            $key = pack("H*",md5($key));
        }
        $key = str_pad($key, $b, chr(0x00));
        $ipad = str_pad('', $b, chr(0x36));
        $opad = str_pad('', $b, chr(0x5c));
        $k_ipad = $key ^ $ipad ;
        $k_opad = $key ^ $opad;
        
        return md5($k_opad . pack("H*",md5($k_ipad . $data)));
    }

    /*������Ʒ����*/
    function success_db($order_sn)
    {
        //��ȡ������Ϣ����鶩������Ч��
        $row = $this->dsql->GetOne("SELECT state FROM #@__shops_orders WHERE oid='$order_sn' ");
        if($row['state'] > 0)
        {
            return TRUE;
        }    
        /* �ı䶩��״̬_֧���ɹ� */
        $sql = "UPDATE `#@__shops_orders` SET `state`='1' WHERE `oid`='$order_sn' AND `userid`='".$this->mid."'";
        if($this->dsql->ExecuteNoneQuery($sql))
        {
            $this->log_result("verify_success,������:".$order_sn); //����֤��������ļ�
            return TRUE;
        } else {
            $this->log_result ("verify_failed,������:".$order_sn);//����֤��������ļ�
            return FALSE;
        }
    }

    /*����㿨����Ա����*/
    function success_mem($order_sn,$pname,$product,$pid)
    {
        //���½���״̬Ϊ�Ѹ���
        $sql = "UPDATE `#@__member_operation` SET `sta`='1' WHERE `buyid`='$order_sn' AND `mid`='".$this->mid."'";
        $this->dsql->ExecuteNoneQuery($sql);

        /* �ı�㿨����״̬_֧���ɹ� */
        if($product=="card")
        {
            $row = $this->dsql->GetOne("SELECT cardid FROM #@__moneycard_record WHERE ctid='$pid' AND isexp='0' ");;
            //����Ҳ���ĳ�����͵Ŀ���ֱ��Ϊ�û����ӽ��
            if(!is_array($row))
            {
                $nrow = $this->dsql->GetOne("SELECT num FROM #@__moneycard_type WHERE pname = '{$pname}'");
                $dnum = $nrow['num'];
                $sql1 = "UPDATE `#@__member` SET `money`=money+'{$nrow['num']}' WHERE `mid`='".$this->mid."'";
                $oldinf ="�Ѿ���ֵ��".$nrow['num']."��ҵ������ʺţ�";
            } else {
                $cardid = $row['cardid'];
                $sql1=" UPDATE #@__moneycard_record SET uid='".$this->mid."',isexp='1',utime='".time()."' WHERE cardid='$cardid' ";
                $oldinf='���ĳ�ֵ�����ǣ�<font color="green">'.$cardid.'</font>';
            }
            //���½���״̬Ϊ�ѹر�
            $sql2=" UPDATE #@__member_operation SET sta=2,oldinfo='$oldinf' WHERE buyid='$order_sn'";
            if($this->dsql->ExecuteNoneQuery($sql1) && $this->dsql->ExecuteNoneQuery($sql2))
            {
                $this->log_result("verify_success,������:".$order_sn); //����֤��������ļ�
                return $oldinf;
            } else {
                $this->log_result ("verify_failed,������:".$order_sn);//����֤��������ļ�
                return "֧��ʧ�ܣ�";
            }
        /* �ı��Ա����״̬_֧���ɹ� */
        } else if ( $product=="member" ){
            $row = $this->dsql->GetOne("SELECT rank,exptime FROM #@__member_type WHERE aid='$pid' ");
            $rank = $row['rank'];
            $exptime = $row['exptime'];
            /*����ԭ������ʣ�������*/
            $rs = $this->dsql->GetOne("SELECT uptime,exptime FROM #@__member WHERE mid='".$this->mid."'");
            if($rs['uptime']!=0 && $rs['exptime']!=0 ) 
            {
                $nowtime = time();
                $mhasDay = $rs['exptime'] - ceil(($nowtime - $rs['uptime'])/3600/24) + 1;
                $mhasDay=($mhasDay>0)? $mhasDay : 0;
            }
            //��ȡ��ԱĬ�ϼ���Ľ�Һͻ�����
            $memrank = $this->dsql->GetOne("SELECT money,scores FROM #@__arcrank WHERE rank='$rank'");
            //���»�Ա��Ϣ
            $sql1 =  " UPDATE #@__member SET rank='$rank',money=money+'{$memrank['money']}',
                       scores=scores+'{$memrank['scores']}',exptime='$exptime'+'$mhasDay',uptime='".time()."' 
                       WHERE mid='".$this->mid."'";
            //���½���״̬Ϊ�ѹر�
            $sql2=" UPDATE #@__member_operation SET sta='2',oldinfo='��Ա�����ɹ�!' WHERE buyid='$order_sn' ";
            if($this->dsql->ExecuteNoneQuery($sql1) && $this->dsql->ExecuteNoneQuery($sql2))
            {
                $this->log_result("verify_success,������:".$order_sn); //����֤��������ļ�
                return "��Ա�����ɹ���";
            } else {
                $this->log_result ("verify_failed,������:".$order_sn);//����֤��������ļ�
                return "��Ա����ʧ�ܣ�";
            }
        }    
    }

    function  log_result($word) {
        global $cfg_cmspath;
        $fp = fopen(dirname(__FILE__)."/../../data/payment/log.txt","a");
        flock($fp, LOCK_EX) ;
        fwrite($fp,$word.",ִ������:".strftime("%Y-%m-%d %H:%I:%S",time())."\r\n");
        flock($fp, LOCK_UN);
        fclose($fp);
    }

}//End API