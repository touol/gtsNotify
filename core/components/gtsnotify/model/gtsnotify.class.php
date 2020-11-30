<?php

class gtsNotify
{
    /** @var modX $modx */
    public $modx;

    /** @var pdoFetch $pdoTools */
    public $pdoTools;

    /** @var array() $config */
    public $config = array();

    /** @var array $initialized */
    public $initialized = array();

    /** @var modError|null $error = */
    public $error = null;

    public $provider = null;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/gtsnotify/';
        $assetsUrl = MODX_ASSETS_URL . 'components/gtsnotify/';

        $this->config = array_merge($config, [
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'customPath' => $corePath . 'custom/',

            'connectorUrl' => $assetsUrl . 'connector.php',
            'actionUrl' => $assetsUrl . 'action.php',
            
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ]);

        $this->modx->addPackage('gtsnotify', $this->config['modelPath']);
        $this->modx->lexicon->load('gtsnotify:default');

        if($provider = $this->modx->getObject("gtsNotifyProvider",['active'=>1])){
            
            $this->config['ws_address'] = $provider->ws_address;
            $this->secret_key = $provider->secret_key;
            $this->config['host'] = $provider->host;

            if ($providerClass = $this->modx->loadClass($provider->class, MODX_CORE_PATH . $provider->path, false, true)) {
                $this->provider = new $providerClass($this->modx, []);
            }
        }

        if ($this->pdoTools = $this->modx->getService('pdoFetch')) {
            $this->pdoTools->setConfig($this->config);
        }

    }

    /**
     * Initializes component into different contexts.
     *
     * @param string $ctx The context to load. Defaults to web.
     * @param array $scriptProperties Properties for initialization.
     *
     * @return bool
     */
    public function initialize($ctx = 'web', $scriptProperties = array())
    {
        $this->config = array_merge($this->config, $scriptProperties);

        $this->config['pageId'] = $this->modx->resource->id;

        switch ($ctx) {
            case 'mgr':
                break;
            default:
                if (!defined('MODX_API_MODE') || !MODX_API_MODE) {

                    $config = $this->makePlaceholders($this->config);
                    if ($css = $this->modx->getOption('gtsnotify_frontend_css')) {
                        $this->modx->regClientCSS(str_replace($config['pl'], $config['vl'], $css));
                    }

                    $config_js = preg_replace(array('/^\n/', '/\t{5}/'), '', '
							gtsNotify = {};
							gtsNotifyConfig = ' . $this->modx->toJSON($this->config) . ';
					');


                    $this->modx->regClientStartupScript("<script type=\"text/javascript\">\n" . $config_js . "\n</script>", true);
                    if ($js = trim($this->modx->getOption('gtsnotify_frontend_js'))) {

                        if (!empty($js) && preg_match('/\.js/i', $js)) {
                            $this->modx->regClientScript(preg_replace(array('/^\n/', '/\t{7}/'), '', '
							<script type="text/javascript">
								if(typeof jQuery == "undefined") {
									document.write("<script src=\"' . $this->config['jsUrl'] . 'web/lib/jquery.min.js\" type=\"text/javascript\"><\/script>");
								}
							</script>
							'), true);
                            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));

                        }
                    }
                    if ($js = $this->provider->getJS()) {

                        if (!empty($js) && preg_match('/\.js/i', $js)) {
                            $this->modx->regClientScript(str_replace($config['pl'], $config['vl'], $js));

                        }
                    }
                    

                }

                break;
        }
        return true;
    }

    public function handleRequest($action, $data = array())
    {
        
        switch($action){
            case 'load_channel_notify': 
                return $this->load_channel_notify($data);
                break;
            case 'remove_channel_notify': //use
                //if(!$this->modx->user->hasSessionContext('mgr')) return $this->error("доступ запрешен!");
                return $this->remove_channel_notify_action($data);
                break;
            case 'send_notify':
                if(!$this->modx->user->hasSessionContext('mgr')) return $this->error("доступ запрешен!");
                return $this->send_notify($data);
                break;
            default:
                return $this->error("Метод $action в классе $class не найден!");
        }
    }
    public function new_client()
    {
        /*if(!$provider = $this->modx->getObject("gtsNotifyProvider",['active'=>1]))
            return $this->error("Не удалось получить провайдера!");
        
        if ($providerClass = $this->modx->loadClass($provider->class, MODX_CORE_PATH . $provider->path, false, true)) {
            $provider = new $providerClass($this->modx, []);
        }else{
            return $this->error("Не удалось получить провайдера!");
        }*/
        if($this->provider){
            $resp = $this->provider->new_client();
        }else{
            return $this->error('new_client no provider');
        }
        
        if($resp['success']) {
            $this->config['ws_id'] = $resp['data']['ws_id'];
        }
        return $resp;
    }
    
    public function remove_channel_notify_action($data)
    {
        //not use
        $channel = $data['name'];
        $notify_id = (int)$data['notify_id'];
        return $this->remove_channel_notify($notify_id,$channel);
    }
    
    public function remove_channel_notifys($notify_ids,$channel,$user_data = [], $user_id = false)
    {
        if($user_id === false) $user_id = $this->modx->user->id;

        if($channel = $this->modx->getObject('gtsNotifyChannel',['name'=>$channel,'active'=>1])){
            $notifys = $this->modx->getIterator("gtsNotifyNotify",[
                'id:IN'=>$notify_ids,
                ]);
            $purposes = $this->modx->getIterator("gtsNotifyNotifyPurpose",[
                'notify_id:IN'=>$notify_ids,
                'channel_id'=>$channel->id,
                'user_id'=>$user_id,
                ]);
            foreach($purposes as $p){
                $p->remove();
            }
            $channels = []; $c = $channel->toArray();
            $c['user_ids'][$user_id]['channel_count'] = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                'active'=>1,
                'channel_id'=>$channel->id,
                'user_id'=> $user_id,
            ]);
            if(isset($user_data[$user_id])) $c['user_ids'][$user_id]['user_data'] = $user_data[$user_id];
            $channels[$channel->name]=$c;

            foreach($notifys as $notify){
                $count_purposes = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                    'notify_id'=>$notify->id,
                    'channel_id'=>$channel->id,
                ]);
                if($count_purposes == 0){
                    $notify->remove();
                }
            }
            $resp = $this->provider->sendNotyfyUsers([$user_id],$channels, [], true);
            $count = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                'active'=>1,
                'channel_id'=>$channel->id,
                'user_id'=> $this->modx->user->id,
            ]);
            return $this->success('',['count'=>$count]); 
            
        }
        return $this->error("error!");
        
    }
    public function remove_channel_notify($notify_id,$channel,$user_id = false)
    {
        if($user_id === false) $user_id = $this->modx->user->id;

        if($notify = $this->modx->getObject('gtsNotifyNotify', $notify_id) 
            and $channel = $this->modx->getObject('gtsNotifyChannel',['name'=>$channel,'active'=>1])){
            $purposes = $this->modx->getIterator("gtsNotifyNotifyPurpose",[
                'notify_id'=>$notify_id,
                'channel_id'=>$channel->id,
                'user_id'=>$user_id,
                ]);
            foreach($purposes as $p){
                $p->remove();
            }
            $count_purposes = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                'notify_id'=>$notify_id,
                'channel_id'=>$channel->id,
            ]);
            
            $channels = []; $c = $channel->toArray();
            $c['user_ids'][$user_id]['channel_count'] = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                'active'=>1,
                'channel_id'=>$channel->id,
                'user_id'=> $user_id,
            ]);
            $channels[$channel->name]=$c;

            $resp = $this->provider->sendNotyfyUsers([$user_id],$channels, [], true);
            
            if($count_purposes == 0){
                $notify->remove();
            }
            $count = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                'active'=>1,
                'channel_id'=>$channel->id,
                'user_id'=> $this->modx->user->id,
            ]);
            
            return $this->success('',['count'=>$count]);
                
        }
        return $this->error("error!");
        
    }
    public function load_channel_notify($data)
    {
        $name = $data['name'];
        
        if($channel = $this->modx->getObject('gtsNotifyChannel',['name'=>$name,'active'=>1])){
            $default = array(
                'class' => 'gtsNotifyNotifyPurpose',
                'where' => [
                    'gtsNotifyNotifyPurpose.active'=>1,
                    'gtsNotifyNotifyPurpose.channel_id'=>$channel->id,
                    'gtsNotifyNotifyPurpose.user_id'=> $this->modx->user->id,
                ],
                'leftJoin' => [
                    'gtsNotifyNotify'=>[
                        'class'=>'gtsNotifyNotify',
                        'on'=>'gtsNotifyNotify.id = gtsNotifyNotifyPurpose.notify_id',
                    ]
                ],
                //'innerJoin' => $innerJoin,
                'select' => [
                    'gtsNotifyNotify'=>'*',
                    'gtsNotifyNotifyPurpose'=>'gtsNotifyNotifyPurpose.url as purpose_url',
                ],
                'sortby'=>['gtsNotifyNotify.time'=>'DESC'],
                'groupby' => 'gtsNotifyNotify.id',
                'return' => 'data',
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            if(count($rows) > 0){
                $output = [];
                foreach($rows as $row){
                    $content = $this->pdoTools->getChunk($channel->tpl,$row);
                    $output[] = $this->pdoTools->getChunk('tpl.gtsNotify.menu',['id'=>$row['id'],'url'=>$row['url'],'content'=>$content]);
                }
                return $this->success('',array('html'=>implode("\r\n",$output)));
            }else{
                return $this->error("error!");
            }
                
        }
        return $this->error("error!");
        
    }

    public function send_notify($data = [])
    {
        if($notify = $this->modx->getObject("gtsNotifyNotify",$data['trs_data'][0]['id'])){ 
            
            $Purposes = $this->modx->getIterator('gtsNotifyNotifyPurpose',['notify_id'=>$notify->id]);
            $users = []; $channels = [];
            foreach($Purposes as $p){
                $users[] = $p->user_id;
                $channels[] = $p->channel_id;
            }
            return $this->sendNotyfyUsers($users,$channels,'#',json_decode($notify->json,1),false,false);
        }
    }
    
    public function create_notify($data = [], $url = '')
    {
        if($notify = $this->modx->newObject("gtsNotifyNotify",[
            'json'=>json_encode($data),
            'time'=>date('Y-m-d H:i:s'),
            'url'=>$url,
        ])){ 
            $notify->save();
            return $notify;
        }
        return false;
    }

    public function sendNotyfyGroups($groups, $channels, $url, $data = array(),$send_only_channel_count = true, $save = true){
        
        if(is_string($groups) or (int)$groups > 0) $groups = explode(',',$users);
        if(!is_array($groups) or empty($groups)) $this->error("empty users!");
        $ids = []; $names = [];
        foreach($groups as $g){
            $g = trim($g);
            if((int)$g > 0){
                $ids[(int)$g] = (int)$g;
            }else{
                $names[] = $g;
            }
        }
        if(!empty($names)){
            $default = array(
                'class' => 'modUserGroup',
                'where' => [
                    'name:IN'=> $names,
                ],
                'select' => [
                    'modUserGroup'=>'id'
                ],
                'return' => 'data',
                'limit' => 0,
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            if(count($rows) > 0){
                foreach($rows as $row){
                    $ids[(int)$row['id']] = (int)$row['id'];
                }
            }
        }
        if(!empty($ids)){
            $default = array(
                'class' => 'modUserGroupMember',
                'where' => [
                    'user_group:IN'=> $ids,
                ],
                'select' => [
                    'modUserGroupMember'=>'member'
                ],
                'return' => 'data',
                'limit' => 0,
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            $users = [];
            if(count($rows) > 0){
                foreach($rows as $row){
                    $users[] = (int)$row['member'];
                }
            }
            return $this->sendNotyfyUsers($users, $channels, $url, $data, $send_only_channel_count, $save);
        }
        return $this->error('no users');
    }

    public function sendNotyfyUsers($users, $channels, $url, $data = array(),$send_only_channel_count = true, $save = true){
        if(empty($channels)) $this->error("empty channels!");
        if(is_string($channels) or (!is_array($channels) and (int)$channels > 0)) $channels = explode(',',$channels);
        if(!is_array($channels) or empty($channels)) $this->error("empty channels!");

        $channel_ids = []; $channel_names = [];
        foreach($channels as $channel){
            $channel = trim($channel);
            if((int)$channel > 0){
                $channel_ids[(int)$channel] = (int)$channel;
            }else{
                $channel_names[] = $channel;
            }
        }
        if(!empty($channel_names)){
            $default = array(
                'class' => 'gtsNotifyChannel',
                'where' => [
                    'name:IN'=> $channel_names,
                ],
                'select' => [
                    'gtsNotifyChannel'=>'id'
                ],
                'return' => 'data',
                'limit' => 0,
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            if(count($rows) > 0){
                foreach($rows as $row){
                    $channel_ids[(int)$row['id']] = (int)$row['id'];
                }
            }
        }
        if(empty($channel_ids)) return $this->error("empty channel_ids!");
        $default = array(
            'class' => 'gtsNotifyChannel',
            'where' => [
                'id:IN'=> $channel_ids,
            ],
            'select' => [
                'gtsNotifyChannel'=>'id, name, tpl'
            ],
            'return' => 'data',
            'limit' => 0,
        );
        
        $this->pdoTools->setConfig($default, false);
        $channels = $this->pdoTools->run();


        if(count($rows) > 0){
            foreach($rows as $row){
                $channel_ids[(int)$row['id']] = (int)$row['id'];
            }
        }

        if(is_string($users) or $users == 0 or (!is_array($users) and (int)$users > 0)) $users = explode(',',$users);
        if(!is_array($users) or empty($users)) $this->error("empty users!");
        $ids = []; $names = [];
        foreach($users as $user){
            $user = trim($user);
            if($user == 0 or (int)$user > 0){
                $ids[(int)$user] = (int)$user;
            }else{
                $names[] = $user;
            }
        }
        if(!empty($names)){
            $default = array(
                'class' => 'modUser',
                'where' => [
                    'username:IN'=> $names,
                ],
                'select' => [
                    'modUser'=>'id'
                ],
                'return' => 'data',
                'limit' => 0,
            );
            
            $this->pdoTools->setConfig($default, false);
            $rows = $this->pdoTools->run();
            if(count($rows) > 0){
                foreach($rows as $row){
                    $ids[(int)$row['id']] = (int)$row['id'];
                }
            }
        }
        if(empty($ids)) $this->error("empty user_ids!");
        $notify_id = 0;
        if($notify = $this->modx->newObject("gtsNotifyNotify")){
            $notify->fromArray([
                'json'=>json_encode($data),
                'time'=>date('Y-m-d H:i:s'),
                'url'=>$url,
            ]);
            if($save){
                if($notify->save()) $notify_id = $notify->id;
            }
        }
        foreach($channels as &$channel){
            foreach($ids as $user_id){
                if($notifyPurpose = $this->modx->newObject("gtsNotifyNotifyPurpose")){
                    $notifyPurpose->fromArray([
                        'notify_id'=>$notify->id,
                        'user_id'=>$user_id,
                        'channel_id'=>$channel['id'],
                    ]);
                    if($save) $notifyPurpose->save();
                }
                $channel['user_ids'][$user_id]['channel_count'] = $this->modx->getCount('gtsNotifyNotifyPurpose',[
                    'active'=>1,
                    'channel_id'=>$channel['id'],
                    'user_id'=> $user_id,
                ]);
            }

        }

        $channels0 = [];
        foreach($channels as $channel){
            $content = $this->pdoTools->getChunk($channel['tpl'], $data);
            //$channel['content'] = '<li><a href="'.$url.'" data-id="'.$row['id'].'">'.$content.'</a></li>';
            $channels0[$channel['name']] = $channel;
        }
        
        $resp = $this->provider->sendNotyfyUsers(array_keys($ids),$channels0, $data, $send_only_channel_count);
        $resp['data']['notify_id'] = $notify_id;
        return $resp;
    }
    /**
     * @return bool
     */
    public function loadServices()
    {
        $this->error = $this->modx->getService('error', 'error.modError', '', '');
        return true;
    }


    /**
     * Shorthand for the call of processor
     *
     * @access public
     *
     * @param string $action Path to processor
     * @param array $data Data to be transmitted to the processor
     *
     * @return mixed The result of the processor
     */
    public function runProcessor($action = '', $data = array())
    {
        if (empty($action)) {
            return false;
        }
        #$this->modx->error->reset();
        $processorsPath = !empty($this->config['processorsPath'])
            ? $this->config['processorsPath']
            : MODX_CORE_PATH . 'components/gtsnotify/processors/';

        return $this->modx->runProcessor($action, $data, array(
            'processors_path' => $processorsPath,
        ));
    }


    /**
     * Method loads custom classes from specified directory
     *
     * @var string $dir Directory for load classes
     *
     * @return void
     */
    public function loadCustomClasses($dir)
    {
        $files = scandir($this->config['customPath'] . $dir);
        foreach ($files as $file) {
            if (preg_match('/.*?\.class\.php$/i', $file)) {
                include_once($this->config['customPath'] . $dir . '/' . $file);
            }
        }
    }


    /**
     * Добавление ошибок
     * @param string $message
     * @param array $data
     */
    public function addError($message, $data = array())
    {
        $message = $this->modx->lexicon($message, $data);
        $this->error->addError($message);
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->modx->error->getErrors();
    }

    /**
     * Вернут true если были ошибки
     * @return boolean
     */
    public function hasError()
    {
        return $this->modx->error->hasError();
    }


    /**
     * Обработчик для событий
     * @param modSystemEvent $event
     * @param array $scriptProperties
     */
    public function loadHandlerEvent(modSystemEvent $event, $scriptProperties = array())
    {
        switch ($event->name) {
            case 'OnHandleRequest':
            case 'OnLoadWebDocument':
                break;
        }

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
    public function makePlaceholders($config)
    {
		$placeholders = [];
		foreach($config as $k=>$v){
			if(is_string($v)){
				$placeholders['pl'][] = "[[+$k]]";
				$placeholders['vl'][] = $v;
			}
		}
		return $placeholders;
	}
}