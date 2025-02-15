<?php

/**
 * The home manager controller for syncRestApi.
 *
 */
class syncRestApiHomeManagerController extends modExtraManagerController
{
    /** @var syncRestApi $syncRestApi */
    public $syncRestApi;


    /**
     *
     */
    public function initialize()
    {
        $this->syncRestApi = $this->modx->getService('syncRestApi', 'syncRestApi', MODX_CORE_PATH . 'components/syncrestapi/model/');
        parent::initialize();
    }


    /**
     * @return array
     */
    public function getLanguageTopics()
    {
        return ['syncrestapi:default'];
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
        return $this->modx->lexicon('syncrestapi');
    }


    /**
     * @return void
     */
    public function loadCustomCssJs()
    {
        $this->addCss($this->syncRestApi->config['cssUrl'] . 'mgr/main.css');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/syncrestapi.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/misc/utils.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/misc/combo.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/widgets/items.grid.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/widgets/items.windows.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/widgets/home.panel.js');
        $this->addJavascript($this->syncRestApi->config['jsUrl'] . 'mgr/sections/home.js');

        $this->addHtml('<script type="text/javascript">
        syncRestApi.config = ' . json_encode($this->syncRestApi->config) . ';
        syncRestApi.config.connector_url = "' . $this->syncRestApi->config['connectorUrl'] . '";
        Ext.onReady(function() {MODx.load({ xtype: "syncrestapi-page-home"});});
        </script>');
    }


    /**
     * @return string
     */
    public function getTemplateFile()
    {
        $this->content .= '<div id="syncrestapi-panel-home-div"></div>';

        return '';
    }
}