<?php
require(dirname(dirname(__DIR__)) .'/vendor/autoload.php');
use WebSocket\Client;

class gtsNotifyRu
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdoTools;

    public $client = null;
    public $provider = null;
    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;

        $this->config = array_merge([
            /*'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'customPath' => $corePath . 'custom/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',*/
        ], $config);
        //$gtsNotify = $modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/', []);

        $this->modx->addPackage('gtsnotify', MODX_CORE_PATH . 'components/gtsnotify/model/');
        if($provider = $this->modx->getObject("gtsNotifyProvider",['active'=>1])){
            $this->provider = $provider;
        }
        
        if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
            $this->pdoTools->setConfig($this->config);
        }
    }
    
    public function handleRequest($action, $data = array())
    {
        switch($action){
            case 'reg_client':
                return $this->reg_client($data);
                break;
            case 'delete_client':
                return $this->delete_client($data);
                break;
            default:
                return $this->error("Метод $action в классе $class не найден!");
        }
    }
    public function delete_client($data)
    {
        if($ws_client = $this->modx->getObject("gtsNotifyWSClient",['ws_id'=>$data['ws_id']])){
            if($ws_client->remove()) return $this->success('',array('ws_id'=>$data['ws_id']));
        }
        return $this->error("delete_client error!", $data);
    }

    public function reg_client($data)
    {
        if($ws_client = $this->modx->getObject("gtsNotifyWSClient",['ws_id'=>$data['ws_id']])){
            return $this->success('',array('ws_id'=>$ws_client->ws_id));
        }
        return $this->error("reg_client error!", $data);
    }
    
    public function new_client()
    {
        if($ws_client = $this->modx->newObject("gtsNotifyWSClient")){
            $ws_id = $this->generateCode();
            $ws_client->fromArray([
                'ws_id' => $ws_id,
                'user_id'=>$this->modx->user->id
            ]);
            if($ws_client->save()) return $this->success('',array('ws_id'=>$ws_client->ws_id));
        }
        return $this->error("reg_client error! $ws_id");
    }
    public function getJS()
    {
        return trim($this->modx->getOption('gtsnotify_gtsnotifyru_js'));
    }
    
    public function generateCode($length = 100){
		$chars = 'abcdefghijklmnopqrstuvwxyz1234567890';
		$numChars = strlen($chars);
		$string = '';
		for ($i = 0; $i < $length; $i++) {
		$string .= substr($chars, rand(1, $numChars) - 1, 1);
		}
		return $string;
    }
    public function sendNotyfyUsers($users = array(), $channels, $data = array(),$send_only_channel_count = true){
        
        $default = array(
            'class' => 'gtsNotifyWSClient',
            'where' => [
                'user_id:IN'=> $users,
            ],
            //'leftJoin' => $leftJoin,
            //'innerJoin' => $innerJoin,
            'select' => [
                'gtsNotifyWSClient'=>'ws_id,user_id'
            ],
            //'sortby'=>['time'=>'DESC'],
            //'groupby' => implode(', ', $groupby),
            'return' => 'data',
            'limit' => 0,
        );
        
        $this->pdoTools->setConfig($default, false);
        $rows = $this->pdoTools->run();
        if(is_array($rows) and count($rows) > 0){
            $ws_ids = [];
            foreach($rows as $row){
                $ws_ids[$row['user_id']][] = $row['ws_id'];
            }
            if(empty($ws_ids)) return $this->error("error no users ws_ids!");
            
            try{
                if($this->provider) $this->client = $this->getClient($this->provider->ws_address);
            }catch(Exception $e){
                $resp = $this->error('error client '.$e->getMessage());
            }

            $resp = $this->send([
                'type'=>'command',
                'command'=>'reg_server',
                'host'=>str_replace(['http://','https://','/',], '',$this->modx->getOption('site_url')),
                'secret_key'=>$this->provider->secret_key,
            ]);
            //$this->modx->log(1,"sendNotyfyUsers ".print_r($resp,1));
            if(!$resp['success']) return $resp;
            if($resp['data']['type'] != 'success') return $this->error('error server ', $resp);
            
            $channels0 = [];
            foreach($channels as $kc=>$channel){
                foreach($channel['user_ids'] as $user_id => $count){
                    foreach($ws_ids[$user_id] as $ws_id){
                        $channel['ws_ids'][$ws_id] = $count;
                    }
                }
                
                if($send_only_channel_count){
                    $channels0[$kc]['ws_ids'] = $channel['ws_ids'];
                }else{
                    unset($channel['user_ids']);
                    $channels0[$kc] = $channel;
                }
                    
            }

            $ws_ids0 = [];
            foreach($ws_ids as $u){
                foreach($u as $ws_id){
                    $ws_ids0[] = $ws_id; 
                }
            }
            $resp = $this->send([
                'type'=>'command',
                'command'=>'send_notify',
                'ws_ids'=>$ws_ids0,
                'data'=>$send_only_channel_count ? [] : $data,
                'channels'=>$channels0,
                'send_only_channel_count'=>$send_only_channel_count,
            ]);
            
            if(!$resp['success']) return $resp;
            if($resp['data']['type'] != 'success') return $this->error('error server ', $resp);
            
            return $this->success('Отправлено ws_ids: '.print_r($resp['data']['data'],1));
        }else{
            return $this->error("error no users!");
        }
                
        return $this->error("error!");
    }
    public function send($data = array()){
        
        if($this->client){
            if(is_array($data)){
                $data = json_encode($data);
            }
            try{   
                $this->client->send($data);
            }
            catch(Exception $e){
                return $this->error('send '. substr($e->getMessage(),0,10)); 
            }
            $t = 10000;
            while($t > 0){
                try{
                    $t--;
                    $result = $this->client->receive();  
                    $response = json_decode($result,1);
                    if($response['type'] == 'success') return $this->success($response['message'], $response);
                    if($response['type'] == 'error') return $this->error($response['message'], $response);
                    if($response['type'] == 'user_delete'){
                        if($ws_client = $this->modx->getObject('gtsNotifyWSClient',['ws_id'=>$response['ws_id']])){
                            $ws_client->remove();
                        }
                    }
                }
                catch(Exception $e){
                    return $this->error('error receive '. substr($e->getMessage(),0,10));
                }
            }
            if(!$result){
                $resp = $this->error('no response');
            }else{
                $response = json_decode($result,1);
                $resp = $this->success('response', $response);
            }
        }else{
            $resp = $this->error('no client');
        }
        return $resp;
    }
    
    public function getClient($host = ''){
        return new Client($host);
    }
    public function error($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
    
    public function success($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->modx->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
}