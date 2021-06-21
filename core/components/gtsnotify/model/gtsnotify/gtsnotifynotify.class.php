<?php
class gtsNotifyNotify extends xPDOSimpleObject {
    
    public function addPurpose($user_id,$channels,$url = '')
    {
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
            if ($pdoTools = $this->xpdo->getService('pdoFetch')) {
                $pdoTools->setConfig($default, false);
                $rows = $pdoTools->run();
                if(count($rows) > 0){
                    foreach($rows as $row){
                        $channel_ids[(int)$row['id']] = (int)$row['id'];
                    }
                }
            }
        }
        if(empty($channel_ids)) return $this->error("empty channel_ids!");
        foreach($channel_ids as $channel){
            if($notifyPurpose = $this->xpdo->newObject("gtsNotifyNotifyPurpose")){
                $notifyPurpose->fromArray([
                    'notify_id'=>$this->id,
                    'user_id'=>$user_id,
                    'channel_id'=>$channel,
                    'url'=>$url,
                ]);
                $notifyPurpose->save();
            }
        }
        return $this->success();
    }
    
    public function addPurposeGroups($groups,$channels,$url = '')
    {
        $pdoTools = $this->xpdo->getService('pdoFetch');
        if(is_string($groups) or (int)$groups > 0) $groups = explode(',',$groups);
        if(!is_array($groups) or empty($groups)) $this->error("empty groups!");
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
            
            $pdoTools->setConfig($default, false);
            $rows = $pdoTools->run();
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
                'groupby'=>'member',
                'return' => 'data',
                'limit' => 0,
            );
            
            $pdoTools->setConfig($default, false);
            $rows = $pdoTools->run();
            //$this->xpdo->log(1,"addPurposeGroups ".print_r($rows,1));
            if(count($rows) > 0){
                foreach($rows as $row){
                    $this->addPurpose((int)$row['member'], $channels, $url);
                }
                return $this->success();
            }  
        }
        return $this->error('no users');
    }

    public function send($user_data = [], $send_only_channel_count = false)
    {
        if (!$pdoTools = $this->xpdo->getService('pdoFetch')) $this->error("empty pdoTools!");
        $data = json_decode($this->json,1);
        $data['notify_id'] = $this->id;

        $Purposes = $this->xpdo->getIterator('gtsNotifyNotifyPurpose',['notify_id'=>$this->id]);
        $users = []; $channel_ids = [];
        foreach($Purposes as $p){
            $users[] = $p->user_id;
            $channel_ids[] = $p->channel_id;
        }
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
        
        $pdoTools->setConfig($default, false);
        $channels = $pdoTools->run();
        $channels0 = [];
        foreach($users as $user_id){
            foreach($channels as &$channel){
                $content = $pdoTools->getChunk($channel['tpl'], $data);
                //$channel['content'] = '<li><a href="'.$url.'" data-id="'.$row['id'].'">'.$content.'</a></li>';
                $channel['user_ids'][$user_id]['channel_count'] = $this->xpdo->getCount('gtsNotifyNotifyPurpose',[
                    'active'=>1,
                    'channel_id'=>$channel['id'],
                    'user_id'=> $user_id,
                ]);
                if(isset($user_data[$user_id])) $channel['user_ids'][$user_id]['user_data'] = $user_data[$user_id];
            }
        }
        
            foreach($channels as $channel){
                $content = $pdoTools->getChunk($channel['tpl'], $data);
                unset($channel['id']);unset($channel['tpl']);
                $channels0[$channel['name']] = $channel;
            }
        
        if($provider = $this->xpdo->getObject("gtsNotifyProvider",['active'=>1])){
            if ($providerClass = $this->xpdo->loadClass($provider->class, MODX_CORE_PATH . $provider->path, false, true)) {
                $provider = new $providerClass($this->xpdo, []);
            }
        }
        $resp = $provider->sendNotyfyUsers($users, $channels0, $data, $send_only_channel_count);
        return $resp;
    }

    public function error($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->xpdo->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => false,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
    
    public function success($message = '', $data = array())
    {
        if(is_array($message)) $message = $this->xpdo->lexicon($message['lexicon'], $message['data']);
        $response = array(
            'success' => true,
            'message' => $message,
            'data' => $data,
        );

        return $response;
    }
}