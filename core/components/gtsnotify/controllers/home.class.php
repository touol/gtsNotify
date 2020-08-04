<?php

/**
 * The home manager controller for gtsNotify.
 *
 */
class gtsNotifyHomeManagerController extends modExtraManagerController
{
    /** @var gtsNotify $gtsNotify */
    public $gtsNotify;


    /**
     *
     */
    public function initialize()
    {
        $this->gtsNotify = $this->modx->getService('gtsNotify', 'gtsNotify', MODX_CORE_PATH . 'components/gtsnotify/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['gtsnotify:manager', 'gtsnotify:default'];
    }


    /**
     * @return bool
     */
    public function checkPermissions()
    {
        return true;
    }


    /**
     * @return null|string
     */
    public function getPageTitle()
    {
        return $this->modx->lexicon('gtsnotify');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->gtsNotify->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/gtsnotify.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/misc/default.grid.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/misc/default.window.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/widgets/items/grid.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/widgets/items/windows.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->gtsNotify->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addJavascript(MODX_MANAGER_URL . 'assets/modext/util/datetime.js');

        $this->gtsNotify->config['date_format'] = $this->modx->getOption('gtsnotify_date_format', null, '%d.%m.%y <span class="gray">%H:%M</span>');
        $this->gtsNotify->config['help_buttons'] = ($buttons = $this->getButtons()) ? $buttons : '';

        $this->addHtml('<script type="text/javascript">
        gtsNotify.config = ' . json_encode($this->gtsNotify->config) . ';
        gtsNotify.config.connector_url = "' . $this->gtsNotify->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "gtsnotify-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .=  '<div id="gtsnotify-panel-home-div"></div>';
        return '';
    }

    /**
     * @return string
     */
    public function getButtons()
    {
        $buttons = null;
        $name = 'gtsNotify';
        $path = "Extras/{$name}/_build/build.php";
        if (file_exists(MODX_BASE_PATH . $path)) {
            $site_url = $this->modx->getOption('site_url').$path;
            $buttons[] = [
                'url' => $site_url,
                'text' => $this->modx->lexicon('gtsnotify_button_install'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1&encryption_disabled=1',
                'text' => $this->modx->lexicon('gtsnotify_button_download'),
            ];
            $buttons[] = [
                'url' => $site_url.'?download=1',
                'text' => $this->modx->lexicon('gtsnotify_button_download_encryption'),
            ];
        }
        return $buttons;
    }
}