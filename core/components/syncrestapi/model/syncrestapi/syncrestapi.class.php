<?php

class syncRestApi
{
    /** @var modX $modx */
    public $modx;
    
    /** @var syncRestApiApi $api */
    public $api;
    
    /** @var syncRestApiStorage $storage */
    public $storage;
    
    /** @var syncRestApiImport $storage */
    public $import;
    
    /** @var mixed|null $namespace */
    public $namespace = 'syncrestapi';

    /** @var array $config */
    public $config = [];
    
    /** @var array $initialized */
    public $initialized = [];
    
    /** @var miniShop2 $miniShop2 */
    public $miniShop2;
    
    public $apiHandlerClass = 'syncRestApiApi';
    public $storageHandlerClass = 'syncRestApiStorage';
    public $importHandlerClass = 'syncRestApiImport';
    
    public $syncObject = 'syncRestApiObject';
    public $syncCategoryType = 'msCategory';
    public $syncProductType = 'msProduct';

    protected $limit = 50;

    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = MODX_CORE_PATH . 'components/syncrestapi/';
        $assetsUrl = MODX_ASSETS_URL . 'components/syncrestapi/';

        $this->config = array_merge([
            'namespace' => $this->namespace,
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',
            'handlersPath' => $corePath . 'handlers/',
            'connectorUrl' => $assetsUrl . 'connector.php',
            'assetsUrl' => $assetsUrl,
            'cssUrl' => $assetsUrl . 'css/',
            'jsUrl' => $assetsUrl . 'js/',
        ], $config);

        $this->modx->addPackage('syncrestapi', $this->config['modelPath']);
        $this->modx->lexicon->load('syncrestapi:default');
        
        $this->miniShop2 = $modx->getService('miniShop2');
    }
    
    
    /**
     * @param string $ctx
     * @param array  $sp
     *
     * @return boolean
     */
    public function initialize($ctx = 'web', $sp = [])
    {
        $this->config = array_merge($this->config, $sp, ['ctx' => $ctx]);

        $this->getApi();
        $this->getStorage();
        $this->getImport();

        return ($this->initialized[$ctx] = true);
    }
    
    
    /**
     * @param       $key
     * @param array $config
     * @param null  $default
     *
     * @return mixed|null
     */
    public function getOption($key, $config = array(), $default = null, $skipEmpty = false)
    {
        $option = $default;
        if (!empty($key) AND is_string($key)) {
            if ($config != null AND array_key_exists($key, $config)) {
                $option = $config[$key];
            } elseif (array_key_exists($key, $this->config)) {
                $option = $this->config[$key];
            } elseif (array_key_exists("{$this->namespace}_{$key}", $this->modx->config)) {
                $option = $this->modx->getOption("{$this->namespace}_{$key}");
            }
        }
        if ($skipEmpty AND empty($option)) {
            $option = $default;
        }
        return $option;
    }
    
    
    /**
     * @param       $key
     * @param null  $default
     *
     * @return mixed|null
     */
    public function getRawOption($key, $default = null, $skipEmpty = false)
    {
        $option = $default;
        if (!empty($key) AND is_string($key)) {
            $option = $this->modx->getObject('modSystemSetting', "{$this->namespace}_{$key}")->get('value');
            return $option;
        }
        if ($skipEmpty AND empty($option)) {
            $option = $default;
        }
        return $option;
    }
    
    
    /**
     * @param       $key
     * @param null  $value
     *
     * @return mixed|null
     */
    public function setOption($key, $value)
    {
        $this->config[$key] = $value;
    }
    
    
    public function updateSetting($key, $value)
    {
        $setting = $this->modx->getObject('modSystemSetting', "{$this->namespace}_{$key}");
        $setting->set('value', $value);
        $setting->save();
        $this->modx->cacheManager->refresh(['system_settings' => []]);
        return $value;
    }
    
    
    public function getApi(array $config = [])
    {
        if (!is_object($this->api)) {
            if ($class = $this->modx->loadClass($this->apiHandlerClass, $this->config['handlersPath'], true, true)) {
                $this->api = new $class($this, $config);
            }
        }

        return $this->api;
    }
    
    
    public function getStorage(array $config = [])
    {
        if (!is_object($this->storage)) {
            if ($class = $this->modx->loadClass($this->storageHandlerClass, $this->config['handlersPath'], true, true)) {
                $this->storage = new $class($this, $config);
            }
        }

        return $this->storage;
    }
    
    
    public function getImport(array $config = [])
    {
        if (!is_object($this->import)) {
            if ($class = $this->modx->loadClass($this->importHandlerClass, $this->config['handlersPath'], true, true)) {
                $this->import = new $class($this, $config);
            }
        }

        return $this->import;
    }
    
    
    public function updateDateTime()
    {
        $date = new DateTime('', new DateTimeZone('UTC'));
        $isoDate = $date->format('Y-m-d\TH:i:s.u\Z');
        
        return $isoDate;
    }
    
    
    public function syncCategories(array $data = [])
    {
        $result = $this->api->getCategories($data);
        
        foreach($result['data'] as $item) {
            $query = [
                'sync_id' => $item['id'],
                'sync_type' => $this->syncCategoryType,
                'sync_parent' => $this->getOption('sync_parent'),
                'sync_data' => $item,
                'sync_active' => $this->getOption('category_published'),
                'sync_datetime' => $this->updateDateTime(),
            ];
            if ($this->storage->check($item['id'], $this->syncCategoryType)) {
                $this->storage->update($query);
            } else {
                $this->storage->create($query);
            }
        }
    }
    
    
    public function importCategories()
    {
        $data = [
           'sync_type' => $this->syncCategoryType,
        ];
        $total = $this->storage->getLimit($data);
        $offset = 0;
        $limit = $this->getOption('api_limit', [], $this->limit);
        $pageLimit = ceil($total / $limit);
        
        for ($i = 1; $i <= $pageLimit; $i++) {
            $query = $this->modx->newQuery($this->syncObject);
            $query->limit($limit, $offset);
            $query->where($data);
            
            $syncObjects = $this->modx->getIterator($this->syncObject, $query);
            foreach ($syncObjects as $item) {
                if ($this->import->checkResource($item->get('sync_resource'))) {
                    $this->import->updateСategory($item);
                } else {
                    $this->import->createСategory($item);
                }
            }
            $offset += $limit;
        }
        $this->modx->cacheManager->refresh(['resource' => []]);
    }
    
    
    public function syncProducts(array $data = [])
    {
        $result = $this->api->getProducts($data);
        
        foreach($result['data'] as $item) {
            $query = [
                'sync_id' => $item['id'],
                'sync_type' => $this->syncProductType,
                'sync_parent' => $this->getOption('sync_parent'),
                'sync_data' => $item,
                'sync_active' => $this->getOption('product_published'),
                'sync_datetime' => $this->updateDateTime(),
            ];
            if ($this->storage->check($item['id'], $this->syncProductType)) {
                $this->storage->update($query);
            } else {
                $this->storage->create($query);
            }
        }
    }
    
    
    public function importProducts()
    {
        $data = [
           'sync_type' => $this->syncProductType,
        ];
        $total = $this->storage->getLimit($data);
        $offset = 0;
        $limit = $this->getOption('api_limit', [], $this->limit);
        $pageLimit = ceil($total / $limit);
        
        for ($i = 1; $i <= $pageLimit; $i++) {
            $query = $this->modx->newQuery($this->syncObject);
            $query->limit($limit, $offset);
            $query->where($data);
            
            $syncObjects = $this->modx->getIterator($this->syncObject, $query);
            foreach ($syncObjects as $item) {
                if ($this->import->checkResource($item->get('sync_resource'))) {
                    $this->import->updateProduct($item);
                } else {
                    $this->import->createProduct($item);
                }
            }
            $offset += $limit;
        }
        $this->modx->cacheManager->refresh(['resource' => []]);
    }
}