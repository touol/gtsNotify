<?php

class gtsNotifyChannelCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'gtsNotifyChannel';
    public $classKey = 'gtsNotifyChannel';
    public $languageTopics = ['gtsnotify'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('gtsnotify_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('gtsnotify_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'gtsNotifyChannelCreateProcessor';