<?php

class syncRestApiItemCreateProcessor extends modObjectCreateProcessor
{
    public $objectType = 'syncRestApiItem';
    public $classKey = 'syncRestApiItem';
    public $languageTopics = ['syncrestapi'];
    //public $permission = 'create';


    /**
     * @return bool
     */
    public function beforeSet()
    {
        $name = trim($this->getProperty('name'));
        if (empty($name)) {
            $this->modx->error->addField('name', $this->modx->lexicon('syncrestapi_item_err_name'));
        } elseif ($this->modx->getCount($this->classKey, ['name' => $name])) {
            $this->modx->error->addField('name', $this->modx->lexicon('syncrestapi_item_err_ae'));
        }

        return parent::beforeSet();
    }

}

return 'syncRestApiItemCreateProcessor';