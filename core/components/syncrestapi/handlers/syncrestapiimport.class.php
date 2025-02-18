<?php

class syncRestApiImport
{
    /** @var modX */
    protected $modx;

    /** @var syncRestApi */
    protected $syncRestApi;

    /** @var array */
    protected $config = [];

    /**
     * Конструктор класса
     * 
     * @param syncRestApi $syncRestApi
     * @param array       $config
     */
    public function __construct(syncRestApi &$syncRestApi, array $config = [])
    {
        $this->syncRestApi = $syncRestApi;
        $this->modx = $syncRestApi->modx;
        $this->config = $config;
    }
    
    public function checkResource(int $id = 0)
    {
        $query = $this->modx->newQuery('modResource');
        $query->where(['id' => $id]);
        $query->select(['id']);
        return $this->modx->getValue($query->prepare()) ? true : false;
    }
    
    public function getResourceParent($syncParent = '')
    {
        if (empty($syncParent)) return $this->syncRestApi->getOption('sync_parent');
        
        $query = $this->modx->newQuery($this->syncRestApi->syncObject);
        $query->leftJoin('modResource', 'modResource', $this->syncRestApi->syncObject . '.sync_resource = modResource.id');
        $query->where([$this->syncRestApi->syncObject . '.sync_id' => $syncParent]);
        $query->select(['modResource.id']);
        return $this->modx->getValue($query->prepare());
    }
    
    public function formattingAlias(int $id)
    {
        if (!$id || !$this->modx->getOption('ms2_product_id_as_alias')) return;
        
        $query = $this->modx->newQuery('modResource');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set(['alias' => $id]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function createСategory($object)
    {
        if (!$this->syncRestApi->getOption('category_process_on_create') || !$object->get('sync_active')) return;
        
        $data = $object->get('sync_data');
        $parent = $this->getResourceParent($object->get('sync_parent') ?? '');
        $template = $this->syncRestApi->getOption('category_template', [], $this->modx->getOption('ms2_template_category_default'));
        $published = $this->syncRestApi->getOption('category_published');
        $hideMenu = $this->modx->getOption('hidemenu_default');
        $pagetitle = $data['name'];
        
        if(!$parent) return;

        $resourceData = array(
            'parent' => $parent,
            'template' => $template,
            'deleted' => 0,
            'published' => $published,
            'class_key' => 'msCategory',
            'pagetitle' => $pagetitle,
            'alias' => $pagetitle,
            'hidemenu' => $hideMenu,
        );
        
        $resource = $this->modx->newObject('msCategory');
        $resource->fromArray($resourceData);
        $resource->save();
        $id = $resource->get('id');
        
        $object->set('sync_resource', $id);
        $object->save();
        
        $this->modx->invokeEvent('syncRestApiOnCategoryImport', array(
            'mode' => 'create',
            'id' => $id,
            'resource' => $resource,
            'data' => $data,
        ));
    }
    
    public function updateСategory($object)
    {
        if (!$this->syncRestApi->getOption('category_process_on_update')) return;
        
        $data = $object->get('sync_data');
        $id = $object->get('sync_resource');
        $published = $object->get('sync_active');
        
        $resourceData = [
            'published' => $published,
        ];
        
        $resource = $this->modx->getObject('msCategory', $id);
        $resource->fromArray($resourceData);
        $resource->save();
        
        $this->modx->invokeEvent('syncRestApiOnCategoryImport', array(
            'mode' => 'update',
            'id' => $id,
            'resource' => $resource,
            'data' => $data,
        ));
    }
    
    public function createProduct($object)
    {
        if (!$this->syncRestApi->getOption('product_process_on_create') || !$object->get('sync_active')) return;
        
        $data = $object->get('sync_data');
        $parent = $this->getResourceParent($object->get('sync_parent') ?? '');
        $template = $this->syncRestApi->getOption('product_template', [], $this->modx->getOption('ms2_template_product_default'));
        $published = $this->syncRestApi->getOption('product_published');
        $hideMenu = $this->modx->getOption('hidemenu_default');
        $pagetitle = $data['name'];
        
        if(!$parent) return;

        $resourceData = array(
            'parent' => $parent,
            'template' => $template,
            'deleted' => 0,
            'published' => $published,
            'class_key' => 'msProduct',
            'pagetitle' => $pagetitle,
            'hidemenu' => $hideMenu,
            'show_in_tree' => $this->modx->getOption('ms2_product_show_in_tree_default'),
            'content' => $data['description'],
            'alias' => $pagetitle,
        );
        
        $resource = $this->modx->newObject('msProduct');
        $resource->fromArray($resourceData);
        $resource->save();
        $id = $resource->get('id');
        
        $object->set('sync_resource', $id);
        $object->save();
        
        $this->formattingAlias($id);
        $this->processProductProps($id, $data);
        $this->processMS2Price($id, $data['price'] ?? 0, $data['old_price'] ?? 0);
        //$this->processMS2Remains($id, $data['count'] ?? 0);
        
        $this->modx->invokeEvent('syncRestApiOnProductImport', array(
            'mode' => 'create',
            'id' => $id,
            'resource' => $resource,
            'data' => $data,
        ));
    }
    
    public function updateProduct($object)
    {
        if (!$this->syncRestApi->getOption('product_process_on_update')) return;
        
        $id = $object->get('sync_resource');
        $published = $object->get('sync_active');
        $data = $object->get('sync_data');
        
        $resourceData = [
            'published' => $published,
        ];
        
        $resource = $this->modx->getObject('msProduct', $id);
        $resource->fromArray($resourceData);
        $resource->save();

        $this->processProductProps($id, $data);
        $this->processMS2Price($id, $data['price'] ?? 0, $data['old_price'] ?? 0);
        //$this->processMS2Remains($id, $data['count'] ?? 0);
        
        $this->modx->invokeEvent('syncRestApiOnProductImport', array(
            'mode' => 'update',
            'id' => $id,
            'resource' => $resource,
            'data' => $data,
        ));
    }
    
    public function processProductProps(int $id, array $props = [])
    {
        if (empty($props) || !$id) return [];
        
        $prepareProps = $this->prepareProductProps($props);

        foreach ($prepareProps as $key => $value) {
            $propsType = strstr($key, '.', true) ?: 'resource';
            $propsKey = substr($key, strpos($key, '.') + 1);
            
            switch($propsType) {
                case 'tv': 
                    $this->processTv($id, $propsKey, $value);
                    break;
                case 'vendor': 
                    $this->processMS2Vendor($id, $value);
                    break;
                case 'msoption': 
                    $this->processMS2Option($id, $propsKey, $value);
                    break;
                case 'ms': 
                    $this->processMS2Data($id, $propsKey, $value);
                    break;
                case 'resource': 
                    $this->processResource($id, $propsKey, $value);
                    break;
            }
        }
    }
    
    public function prepareProductProps(array $props = [])
    {
        $relatedOptions = $this->modx->fromJSON($this->syncRestApi->getOption('product_fields'));

        if (empty($props) || empty($relatedOptions)) return [];
        
        $result = [];
        foreach ($props as $key => $value) {
            if (array_key_exists($key, $relatedOptions)) {
                $result[$relatedOptions[$key]] = $value;
            }
        }
        return $result;
    }
    
    public function processMS2Vendor(int $id, $value)
    {
        if (!$id || !$value) return;
        
        if (!$vendor = $this->modx->getObject('msVendor', ['name' => $value])) {
            $vendor = $this->modx->newObject('msVendor');
            $vendor->set('name', $value);
            $vendor->save();
        }

        if (!$vendor->id) return;
        
        $query = $this->modx->newQuery('msProductData');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set(['vendor' => $vendor->id]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function processResource(int $id, $key, $value)
    {
        if (!$id || !$key || !$value) return;
        
        $query = $this->modx->newQuery('modResource');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set([$key => $value]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function processTv(int $id, $key, $value)
    {
        if (!$id || !$value) return;
        
        if ($tvData = $this->modx->getObject('modTemplateVar', ['name' => $key])) {
            if ($tv = $this->modx->getObject('modTemplateVarResource', ['tmplvarid' => $tvData->get('id'), 'contentid' => $id])) {
                $query = $this->modx->newQuery('modTemplateVarResource');
                $query->command('UPDATE');
                $query->where(['tmplvarid' => $tvData->get('id'), 'contentid' => $id]);
                $query->set(['value' => $value]);
                $query->prepare();
                $query->stmt->execute();
            } else {
                $table = $this->modx->getTableName('modTemplateVarResource');
                if (!is_int($value)) {
                    $value = '"' . $value . '"';
                }
                $sql = "INSERT INTO {$table} (`tmplvarid`,`contentid`,`value`) VALUES ({$tvData->get('id')}, \"{$id}\", {$value});";
                $stmt = $this->modx->prepare($sql);
                $stmt->execute();
            }
        }
    }
    
    public function processMS2Data(int $id, $key, $value)
    {
        if (!$id || !$key || !$value) return;
        
        $query = $this->modx->newQuery('msProductData');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set([$key => $value]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function processMS2Option(int $id, $key, $value)
    {
        if (!$id || !$key || !$value) return;
        
        if ($option = $this->modx->getObject('msProductOption', ['product_id' => $id, 'key' => $key])) {
            $query = $this->modx->newQuery('msProductOption');
            $query->command('UPDATE');
            $query->where(['product_id' => $id, 'key' => $key]);
            $query->set(['value' => $value]);
            $query->prepare();
            $query->stmt->execute();
        } else {
            $table = $this->modx->getTableName('msProductOption');
            if (!is_int($value)) {
                $value = '"' . $value . '"';
            }
            $sql = "INSERT INTO {$table} (`product_id`,`key`,`value`) VALUES ({$id}, \"{$key}\", {$value});";
            $stmt = $this->modx->prepare($sql);
            $stmt->execute();
        }
    }
    
    public function processMS2Price(int $id, float $price = 0, float $old_price = 0)
    {
        if (!$id || !$this->syncRestApi->getOption('product_process_price')) return;
        
        $query = $this->modx->newQuery('msProductData');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set(['price' => $price, 'old_price' => $old_price]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function createMS2Option(string $key = '')
    {
        if (!$key || $this->isIgnoreOption($key)) return;
        
        if ($this->modx->getObject("msOption", ['key' => $key])) return;
            
        $option = $this->modx->newObject('msOption');
        $option->set('key', $key);
        $option->set('caption', $key);
        $option->set('type', 'textfield');
        $option->set('category', 0);
        $option->save();
    }
    
    public function processMS2Remains(int $id, int $remains = 0)
    {
        if (!$id || !$this->syncRestApi->getOption('product_process_stock')) return;
        
        $query = $this->modx->newQuery('msProductData');
        $query->command('UPDATE');
        $query->where(['id' => $id]);
        $query->set(['count' => $remains]);
        $query->prepare();
        $query->stmt->execute();
    }
    
    public function processMS2Gallery(int $id, array $images = [])
    {
        if (empty($images) || !$id) return;
        
        /*$this->modx->runProcessor('gallery/removeall', ['product_id' => $id], [
            'processors_path' => MODX_CORE_PATH.'components/minishop2/processors/mgr/'
        ]);*/
        
        for ($i = 0; $i < count($images); $i++) {
            $galleryItem = [
                'id' => $id,
                'file' => $images[$i]['path']
            ];

            $upload = $this->modx->runProcessor('gallery/upload', $galleryItem, [
                'processors_path' => MODX_CORE_PATH.'components/minishop2/processors/mgr/'
            ]);
            if ($upload->isError()) {
                return $this->modx->log(1, print_r($upload->getResponse()));
            }
        }
    }
    
    public function bindMS2OptionToCategory(string $key = '', int $categoryId)
    {
        if (!$key || !$categoryId) return;
        
        if (!$option = $this->modx->getObject('msOption', ['key' => $key])) return;
                
        $optionId = $option->get('id');
        if (!$categoryOption = $this->modx->getObject('msCategoryOption', ['option_id' => $optionId, 'category_id' => $categoryId])) {
            $categoryOption = $this->modx->newObject('msCategoryOption');
            $categoryOption->set('option_id', $optionId);
            $categoryOption->set('category_id', $categoryId);
            $categoryOption->set('value', '');
            $categoryOption->set('active', 1);
            $categoryOption->save();
        }
    }
    
    public function isIgnoreOption(string $key = '')
    {
        if (!$key) return true;
        
        $ignoreOptions = preg_split('/,\s*/', $this->syncRestApi->getOption('product_ignore_options'));
        $productFields = $this->modx->fromJSON($this->syncRestApi->getOption('product_fields')) ?: [];

        if (in_array($key, $ignoreOptions) || array_key_exists($key, $productFields)) return true;
    }
}
