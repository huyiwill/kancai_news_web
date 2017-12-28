<?php   if(!defined('DEDEINC')) exit('Request Error!');
/**
 * ��Ա��¼��
 *
 * @version        $Id: userlogin.class.php 1 15:59 2010��7��5��Z tianya $
 * @package        DedeCMS.Libraries
 * @copyright      Copyright (c) 2007 - 2010, DesDev, Inc.
 * @license        http://help.dedecms.com/usersguide/license.html
 * @link           http://www.dedecms.com
 */

// ʹ�û�������
helper('cache');
/**
 *  ����û����ĺϷ���
 *
 * @access    public
 * @param     string  $uid  �û�UID
 * @param     string  $msgtitle  ��ʾ����
 * @param     string  $ckhas  ����Ƿ����
 * @return    string
 */
function CheckUserID($uid, $msgtitle='�û���', $ckhas=TRUE)
{
    global $cfg_mb_notallow,$cfg_mb_idmin,$cfg_md_idurl,$cfg_soft_lang,$dsql;
    if($cfg_mb_notallow != '')
    {
        $nas = explode(',', $cfg_mb_notallow);
        if(in_array($uid, $nas))
        {
            return $msgtitle.'Ϊϵͳ��ֹ�ı�ʶ��';
        }
    }
    if($cfg_md_idurl=='Y' && preg_match("/[^a-z0-9]/i",$uid))
    {
        return $msgtitle.'������Ӣ����ĸ��������ɣ�';
    }

    if($cfg_soft_lang=='utf-8')
    {
        $ck_uid = utf82gb($uid);
    }
    else
    {
        $ck_uid = $uid;
    }

    for($i=0; isset($ck_uid[$i]); $i++)
    {
        if(ord($ck_uid[$i]) > 0x80)
        {
            if(isset($ck_uid[$i+1]) && ord($ck_uid[$i+1])>0x40)
            {
                $i++;
            }
            else
            {
                return $msgtitle.'���ܺ������룬���������Ӣ����ĸ��������ϣ�';
            }
        }
        else
        {
            if(preg_match("/[^0-9a-z@\.-]/i",$ck_uid[$i]))
            {
                return $msgtitle.'���ܺ��� [@]��[.]��[-]�����������ţ�';
            }
        }
    }
    if($ckhas)
    {
        $row = $dsql->GetOne("SELECT * FROM `#@__member` WHERE userid LIKE '$uid' ");
        if(is_array($row)) return $msgtitle."�Ѿ����ڣ�";
    }
    return 'ok';
}

/**
 *  ����һ����Ϣ��¼
 *
 * @access    public
 * @param     string  $mid  �û�MID
 * @param     string  $mid  �û�ID
 * @param     string  $msg  ������Ϣ
 * @return    string
 */
function PutSnsMsg($mid, $userid, $msg)
{
    global $dsql;
    $msg = addslashes($msg);
    $query = "INSERT INTO `#@__member_snsmsg`(`mid`, `userid`, `sendtime`, `msg`) VALUES('$mid', '$userid', '".time()."', '$msg'); ";
    $rs = $dsql->ExecuteNoneQuery($query);
    return $rs;
}

/**
 *  ����û��Ƿ񱻽���
 *
 * @return    void
 */
function CheckNotAllow()
{
    global $dsql, $cfg_ml, $cfg_mb_spacesta;
    if(empty($cfg_ml->M_ID)) return ;
    if($cfg_ml->M_Spacesta == -2)
    {
        ShowMsg("���Ѿ������ԣ��������Ա��ϵ��", "-1");
        exit();
    }else if($cfg_ml->M_Spacesta == -10)
    {
        ShowMsg("ϵͳ�������ʼ���˻��ƣ��������ʺ���Ҫ��˺���ܷ���Ϣ��", "-1");
        exit();
    }
    else if($cfg_ml->M_Spacesta < 0)
    {
        ShowMsg('ϵͳ��������˻��ƣ��������ʺ���Ҫ����Ա��˺���ܷ���Ϣ��', '-1');
        exit();
    }
}

function FormatUsername($username)
{
    $username = str_replace("`","��",$username);
    $username = str_replace("'","��",$username);
    $username = str_replace("\"","��",$username);
    $username = str_replace(",","��",$username);
    $username = str_replace("(","��",$username);
    $username = str_replace(")","��",$username);
    return addslashes($username);
}

/**
 * ��վ��Ա��¼��
 *
 * @package          MemberLogin
 * @subpackage       DedeCMS.Libraries
 * @link             http://www.dedecms.com
 */
class MemberLogin
{
    var $M_ID;
    var $M_LoginID;
    var $M_MbType;
    var $M_Money;
    var $M_Scores;
    var $M_UserName;
    var $M_Rank;
     var $M_Face;
    var $M_LoginTime;
    var $M_KeepTime;
    var $M_Spacesta;
    var $fields;
    var $isAdmin;
    var $M_UpTime;
    var $M_ExpTime;
    var $M_HasDay;
    var $M_JoinTime;
    var $M_Honor = '';
    var $memberCache='memberlogin';

    //php5���캯��
    function __construct($kptime = -1, $cache=FALSE)
    {
        global $dsql;
        if($kptime==-1){
            $this->M_KeepTime = 3600 * 24 * 7;
        }else{
            $this->M_KeepTime = $kptime;
        }
        $formcache = FALSE;
        $this->M_ID = $this->GetNum(GetCookie("DedeUserID"));
        $this->M_LoginTime = GetCookie("DedeLoginTime");
        $this->fields = array();
        $this->isAdmin = FALSE;
        if(empty($this->M_ID))
        {
            $this->ResetUser();
        }else{
            $this->M_ID = intval($this->M_ID);
            
            if ($cache)
            {
                $this->fields = GetCache($this->memberCache, $this->M_ID);
                if( empty($this->fields) )
                {
                    $this->fields = $dsql->GetOne("Select * From `#@__member` where mid='{$this->M_ID}' ");
                } else {
                    $formcache = TRUE;
                }
            } else {
                $this->fields = $dsql->GetOne("Select * From `#@__member` where mid='{$this->M_ID}' ");
            }
                
            if(is_array($this->fields)){
                #api{{
                if(defined('UC_API') && @include_once DEDEROOT.'/uc_client/client.php')
                {
                    if($data = uc_get_user($this->fields['userid']))
                    {
                        if(uc_check_avatar($data[0]) && !strstr($this->fields['face'],UC_API))
                        {
                            $this->fields['face'] = UC_API.'/avatar.php?uid='.$data[0].'&size=middle';
                            $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET `face`='".$this->fields['face']."' WHERE `mid`='{$this->M_ID}'");
                        }
                    }
                }
                #/aip}}
            
                //���һСʱ����һ���û���¼ʱ��
                if(time() - $this->M_LoginTime > 3600)
                {
                    $dsql->ExecuteNoneQuery("update `#@__member` set logintime='".time()."',loginip='".GetIP()."' where mid='".$this->fields['mid']."';");
                    PutCookie("DedeLoginTime",time(),$this->M_KeepTime);
                }
                $this->M_LoginID = $this->fields['userid'];
                $this->M_MbType = $this->fields['mtype'];
                $this->M_Money = $this->fields['money'];
                $this->M_UserName = FormatUsername($this->fields['uname']);
                $this->M_Scores = $this->fields['scores'];
                $this->M_Face = $this->fields['face'];
                $this->M_Rank = $this->fields['rank'];
                $this->M_Spacesta = $this->fields['spacesta'];
                $sql = "Select titles From #@__scores where integral<={$this->fields['scores']} order by integral desc";
                $scrow = $dsql->GetOne($sql);
                $this->fields['honor'] = $scrow['titles'];
                $this->M_Honor = $this->fields['honor'];
                if($this->fields['matt']==10) $this->isAdmin = TRUE;
                $this->M_UpTime = $this->fields['uptime'];
                $this->M_ExpTime = $this->fields['exptime'];
                $this->M_JoinTime = MyDate('Y-m-d',$this->fields['jointime']);
                if($this->M_Rank>10 && $this->M_UpTime>0){
                    $this->M_HasDay = $this->Judgemember();
                }
                if( !$formcache )
                {
                    SetCache($this->memberCache, $this->M_ID, $this->fields, 1800);
                }
            }else{
                $this->ResetUser();
            }
        }
    }

    function MemberLogin($kptime = -1)
    {
        $this->__construct($kptime);
    }
    
    /**
     *  ɾ������,ÿ�ε�¼ʱ�����޸��û����ϵĵط������
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function DelCache($mid)
    {
        DelCache($this->memberCache, $mid);
    }
    
    /**
     *  �жϻ�Ա�Ƿ���
     *
     * @return    string
     */
    function Judgemember()
    {
        global $dsql,$cfg_mb_rank;
        $nowtime = time();
        $mhasDay = $this->M_ExpTime - ceil(($nowtime - $this->M_UpTime)/3600/24) + 1;
        if($mhasDay <= 0){
           $dsql->ExecuteNoneQuery("UPDATE `#@__member` SET uptime='0',exptime='0',
                                         rank='$cfg_mb_rank' WHERE mid='".$this->fields['mid']."';");
        }
        return $mhasDay;
    }

    /**
     *  �˳�cookie�ĻỰ
     *
     * @return    void
     */
    function ExitCookie()
    {
        $this->ResetUser();
    }

    /**
     *  ��֤�û��Ƿ��Ѿ���¼
     *
     * @return    bool
     */
    function IsLogin()
    {
        if($this->M_ID > 0) return TRUE;
        else return FALSE;
    }

    /**
     *  ����û��ϴ��ռ�
     *
     * @return    int
     */
    function GetUserSpace()
    {
        global $dsql;
        $uid = $this->M_ID;
        $row = $dsql->GetOne("SELECT sum(filesize) AS fs FROM `#@__uploads` WHERE mid='$uid'; ");
        return $row['fs'];
    }

    /**
     *  ����û��ռ���Ϣ
     *
     * @return    void
     */
    function CheckUserSpace()
    {
        global $cfg_mb_max;
        $uid = $this->M_ID;
        $hasuse = $this->GetUserSpace();
        $maxSize = $cfg_mb_max * 1024 * 1024;
        if($hasuse >= $maxSize)
        {
            ShowMsg('��Ŀռ��������������ϴ����ļ���','-1');
            exit();
        }
    }

    /**
     *  �����û���Ϣͳ�Ʊ�
     *
     * @access    public
     * @param     string  $field  �ֶ���Ϣ
     * @param     string  $uptype  ��������
     * @return    string
     */
    function UpdateUserTj($field, $uptype='add')
    {
        global $dsql;
        $mid = $this->M_ID;
        $arr = $dsql->GetOne("Select * `#@__member_tj` where mid='$mid' ");
        if(!is_array($arr))
        {
            $arr = array('article'=>0,'album'=>0,'archives'=>0,'homecount'=>0,'pagecount'=>0,'feedback'=>0,'friend'=>0,'stow'=>0);
        }
        extract($arr);
        if(isset($$field))
        {
            if($uptype=='add')
            {
                $$field++;
            }
            else if($$field > 0)
            {
                $$field--;
            }
        }
        $inquery = "INSERT INTO `#@__member_tj` (`mid`,`article`,`album`,`archives`,`homecount`,`pagecount`,`feedback`,`friend`,`stow`)
               VALUES ('$mid','$article','$album','$archives','$homecount','$pagecount','$feedback','$friend','$stow'); ";
        $dsql->ExecuteNoneQuery("Delete From `#@__member_tj` where mid='$mid' ");
        $dsql->ExecuteNoneQuery($inquery);
    }

    //
    /**
     *  �����û���Ϣ
     *
     * @return    void
     */
    function ResetUser()
    {
        $this->fields = '';
        $this->M_ID = 0;
        $this->M_LoginID = '';
        $this->M_Rank = 0;
        $this->M_Face = "";
        $this->M_Money = 0;
        $this->M_UserName = "";
        $this->M_LoginTime = 0;
        $this->M_MbType = '';
        $this->M_Scores = 0;
        $this->M_Spacesta = -2;
        $this->M_UpTime = 0;
        $this->M_ExpTime = 0;
        $this->M_JoinTime = 0;
        $this->M_HasDay = 0;
        DropCookie('DedeUserID');
        DropCookie('DedeLoginTime');
    }

    /**
     *  ��ȡ����ֵ
     *
     * @access    public
     * @param     string  $fnum  �������ֵ
     * @return    string
     */
    function GetNum($fnum){
        $fnum = preg_replace("/[^0-9\.]/", '', $fnum);
        return $fnum;
    }

    /**
     *  �û���¼
     *  �ѵ�¼����תΪָ������md5����
     *
     * @access    public
     * @param     string  $pwd  ��Ҫ���ܵ�����
     * @return    string
     */
    function GetEncodePwd($pwd)
    {
        global $cfg_mb_pwdtype;
        if(empty($cfg_mb_pwdtype)) $cfg_mb_pwdtype = '32';
        switch($cfg_mb_pwdtype)
        {
            case 'l16':
                return substr(md5($pwd), 0, 16);
            case 'r16':
                return substr(md5($pwd), 16, 16);
            case 'm16':
                return substr(md5($pwd), 8, 16);
            default:
                return md5($pwd);
        }
    }
    
    /**
     *  �����ݿ�����תΪ�ض�����
     *  ������ݿ����������ĵģ�������֧��
     *
     * @access    public
     * @param     string
     * @return    string
     */
    function GetShortPwd($dbpwd)
    {
        global $cfg_mb_pwdtype;
        if(empty($cfg_mb_pwdtype)) $cfg_mb_pwdtype = '32';
        $dbpwd = trim($dbpwd);
        if(strlen($dbpwd)==16)
        {
            return $dbpwd;
        }
        else
        {
            switch($cfg_mb_pwdtype)
            {
                case 'l16':
                    return substr($dbpwd, 0, 16);
                case 'r16':
                    return substr($dbpwd, 16, 16);
                case 'm16':
                    return substr($dbpwd, 8, 16);
                default:
                    return $dbpwd;
            }
        }
    }
    
    /**
     *  ����û��Ƿ�Ϸ�
     *
     * @access    public
     * @param     string  $loginuser  ��¼�û���
     * @param     string  $loginpwd  �û�����
     * @return    string
     */
    function CheckUser(&$loginuser, $loginpwd)
    {
        global $dsql;

        //����û����ĺϷ���
        $rs = CheckUserID($loginuser,'�û���',FALSE);

        //�û�������ȷʱ������֤����ԭ��¼��ͨ�����÷��ش�����ʾ��Ϣ
        if($rs!='ok')
        {
            $loginuser = $rs;
            return '0';
        }

        //matt=10 �ǹ���Ա������ǰ̨�ʺţ�Ϊ�˰�ȫ���������ʺ�ֻ�ܴӺ�̨��¼������ֱ�Ӵ�ǰ̨��¼
        $row = $dsql->GetOne("SELECT mid,matt,pwd,logintime FROM `#@__member` WHERE userid LIKE '$loginuser' ");
        if(is_array($row))
        {
            if($this->GetShortPwd($row['pwd']) != $this->GetEncodePwd($loginpwd))
            {
                return -1;
            }
            else
            {
                //����Ա�ʺŲ������ǰ̨��¼
                if($row['matt']==10) {
                    return -2;
                }
                else {
                    $this->PutLoginInfo($row['mid'], $row['logintime']);
                    return 1;
                }
            }
        }
        else
        {
            return 0;
        }
    }

    /**
     *  �����û�cookie
     *
     * @access    public
     * @param     string  $uid  �û�ID
     * @param     string  $logintime  ��¼����ʱ��
     * @return    void
     */
    function PutLoginInfo($uid, $logintime=0)
    {
        global $cfg_login_adds, $dsql;
        //��¼���ӻ���(��һ�ε�¼ʱ����������Сʱ)
        if(time() - $logintime > 7200 && $cfg_login_adds > 0)
        {
            $dsql->ExecuteNoneQuery("Update `#@__member` set `scores`=`scores`+{$cfg_login_adds} where mid='$uid' ");
        }
        $this->M_ID = $uid;
        $this->M_LoginTime = time();
        $loginip = GetIP();
        $inquery = "UPDATE `#@__member` SET loginip='$loginip',logintime='".$this->M_LoginTime."' WHERE mid='".$uid."'";
        $dsql->ExecuteNoneQuery($inquery);
        if($this->M_KeepTime > 0)
        {
            PutCookie('DedeUserID',$uid,$this->M_KeepTime);
            PutCookie('DedeLoginTime',$this->M_LoginTime,$this->M_KeepTime);
        }
        else
        {
            PutCookie('DedeUserID',$uid);
            PutCookie('DedeLoginTime',$this->M_LoginTime);
        }
    }

    /**
     *  ��û�ԱĿǰ��״̬
     *
     * @access    public
     * @param     string  $dsql  ���ݿ�����
     * @return    string
     */
    function GetSta($dsql)
    {
        $sta = '';
        if($this->M_Rank==0)
        {
            $sta .= "��Ŀǰ������ǣ���ͨ��Ա";
        }else{
            $row = $dsql->GetOne("Select membername From `#@__arcrank` where rank='".$this->M_Rank."'");
            $sta .= "��Ŀǰ������ǣ�".$row['membername'];
            $rs = $dsql->GetOne("Select id From `#@__admin` where userid='".$this->M_LoginID."'");
            if(!is_array($rs)){
                if($this->M_Rank>10 && $this->M_HasDay>0) $sta .= " ʣ������: <font color='red'>".$this->M_HasDay."</font>  �� ";
                elseif($this->M_Rank>10) $sta .= " <font color='red'>��Ա�����Ѿ�����</font> ";
          }
        }
        $sta .= " ӵ�н�ң�{$this->M_Money} ���� ���֣�{$this->M_Scores} �֡�";
        return $sta;
    }
    
    /**
     *  ��¼��Ա������־
     *
     * @access    public
     * @param     string  $type ��¼����
     * @param     string  $title ��¼����
     * @param     string  $note��¼����
     * @param     string  $aid�漰�������ݵ�id
     * @return    string
     */
    function RecordFeeds($type, $title, $note, $aid)
    {
        global $dsql,$cfg_mb_feedcheck;
        //ȷ���Ƿ���Ҫ��¼
        if (in_array($type,array('add','addsoft','feedback','addfriends','stow'))){
            $ntime = time();
            $title = htmlspecialchars(cn_substrR($title,255));
            if(in_array($type,array('add','addsoft','feedback','stow')))
            {
                $rcdtype = array('add'=>' �ɹ�������', 'addsoft'=>' �ɹ����������',
                                 'feedback'=>' ����������','stow'=>' �ղ���');
                //���ݷ�������
                $arcrul = " <a href='/plus/view.php?aid=".$aid."'>".$title."</a>";
                $title = htmlspecialchars($rcdtype[$type].$arcrul, ENT_QUOTES);
            } else if ($type == 'addfriends')
            {
                //��Ӻ��Ѵ���
                $arcrul = " <a href='/member/index.php?uid=".$aid."'>".$aid."</a>";
                $title = htmlspecialchars(' ��'. $arcrul."��Ϊ����", ENT_QUOTES);
            }
            $note = Html2Text($note);
            $aid = (isset($aid) && is_numeric($aid) ? $aid : 0);
            $ischeck = ($cfg_mb_feedcheck == 'Y')? 0 : 1;
            $query = "INSERT INTO `#@__member_feed` (`mid`, `userid`, `uname`, `type`, `aid`, `dtime`,`title`, `note`, `ischeck`) 
                        Values('$this->M_ID', '$this->M_LoginID', '$this->M_UserName', '$type', '$aid', '$ntime', '$title', '$note', '$ischeck'); ";
            $rs = $dsql->ExecuteNoneQuery($query);
            return $rs;
        } else {
            return FALSE;
        }
    }
}//End Class