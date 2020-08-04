<?php
class gtsNotifyItemRemoveProcessor extends modObjectRemoveProcessor
{
    public $objectType = 'gtsNotifyItem';
    public $classKey = 'gtsNotifyItem';
    public $languageTopics = ['gtsnotify:manager'];
    #public $permission = 'remove';

    /**
     * @return bool|null|string
     */
    public function initialize()
    {
        if (!$this->modx->hasPermission($this->permission)) {
            return $this->modx->lexicon('access_denied');
        }
        return parent::initialize();
    }
}

return 'gtsNotifyItemRemoveProcessor';