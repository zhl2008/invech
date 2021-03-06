<?php
namespace app\common\model;
use app\common\traits\model\Bank as BankTrait;
use app\common\traits\model\ApiToken;
use bong\service\auth\traits\Authenticatable;
use bong\service\auth\access\Authorizable;
use app\common\model\member\BasicTrait;//个人中心,后台
use app\common\model\member\RecordTrait;//流水记录
use app\common\model\member\BetTrait;
use app\common\model\member\PayTrait;
use app\common\model\member\MoneyTrait;
use app\common\model\member\AgentTrait;
use app\common\model\member\MessageTrait;
use app\common\model\member\BonusTrait;

class Member extends Base{

    use ApiToken,Authenticatable,Authorizable;
    use BankTrait;
    use BasicTrait,RecordTrait;
    use MoneyTrait,BetTrait,MessageTrait,AgentTrait,PayTrait;
    use BonusTrait;
    
    protected $table = 'gygy_members';
    protected $pk = 'uid';
    protected $createTime = 'created_at';
    protected $updateTime = 'updated_at';
    protected $autoWriteTimestamp = 'datetime';
    
    /*
    protected $field = [
        'uid','isDelete','enable','parentId','parents','admin','username','password',
        'coinPassword','type','subCount','sex' ,'nickname','name', 
        'regIP','created_at','updated_at','province' ,'city','address' ,'qq' ,
        'msn','mobile','email','idCard','grade' ,'score' ,'scoreTotal','coin',
        'fcoin','fanDian','fanDianBdw' ,'safepwd','safeEmail','regPath',
        'bank','withdraw_remain_amount',
    ];
    */
    
    protected $insert = ['coin' => 0];

    CONST AGENT_MODE_FLOW = 0;
    CONST AGENT_MODE_WIN = 1;
    
    //当status字段值是1的时候,获取器返回1对应的'正常';
    //先取字段值再调用获取器
	/*public function getStatusAttr($value)
    {
        $status = [-1=>'删除',0=>'禁用',1=>'正常',2=>'待审核'];
        return $status[$value];
    }*/

    public function setIdAttr($value){
        return $this->data['uid'] = $value;
    }

    public function getIdAttr($value)
    {
        return $this->data['uid'];
    }

    
    public static function getByIdOrName($username){
        $query = (new static)->db(true,false);
        if(is_numeric($username)){
            $query->where('uid',$username);
        }else{
            //$query->where('username|nickname','like','%'.$username.'%');
            $query->where('username',$username);
        }
        return $query->find();
    }


    //----------------后台------------------

    public static function getList(){

        $query = (new static())->getListBuild();

        return $query->paginate();
    }   

       //----api----
    protected $append = ["uid","is_agent","agent_id","agent_mode","username"];
    protected $visible = ["name","nickname","bank",'sex','birthday',"qq","mobile","email",'grade','score',
        'coin','fcoin','fanDian','loginTime','created_at','enable','updated_at'];  
    //-----------通用查询构造器------------------
    public function getListBuild($ignore = []){
        
        $query = $this->db(true,false);

        $query->order('uid desc');

        $params = get_where_params($ignore);

        if($params['username']??''){
             $query->where('username|nickname', 'like','%'.trim($params['username']).'%');
         }
        if($params['startTime']??''){
            $query->where('created_at', '>=',$params['startTime']);
        }
        if($params['endTime']??''){
            $query->where('created_at', '<=',$params['endTime']);
         }
        if(isset($params['is_agent']) && is_numeric($params['is_agent'])){
            $query->where('is_agent', $params['is_agent']);
        }
        if(isset($params['enable']) && is_numeric($params['enable'])){
            $query->where('enable', $params['enable']);
        }

        return $query; 
    }

}
