<?php

class syncRestApiStorage
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

    public function check($syncId = 0, $syncType)
    {
        if (!$syncType) return false;
        $query = $this->modx->newQuery($this->syncRestApi->syncObject);
        $query->where(['sync_id' => $syncId, 'sync_type' => $syncType]);
        $query->select(['sync_id']);
        return $this->modx->getValue($query->prepare()) ? true : false;
    }

    public function create(array $data = [])
    {
        $object = $this->modx->newObject($this->syncRestApi->syncObject);
        $object->fromArray($data);
        $object->save();
    }

    public function update(array $data = [])
    {
        if (!$object = $this->modx->getObject($this->syncRestApi->syncObject, ['sync_id' => $data['sync_id'], 'sync_type' => $data['sync_type']])) return;
        $object->fromArray([
            'sync_data' => $data['sync_data'],
            'sync_active' => $data['sync_active'],
            'sync_datetime' => $data['sync_datetime'],
        ]);
        $object->save();
    }

    public function remove(int $syncResource = 0)
    {
        if ($object = $this->modx->getObject($this->syncRestApi->syncObject, ['sync_resource' => $syncResource]) ){
            $object->remove();
        }
    }

    public function get(array $where = [])
    {
        $object = $this->modx->getObject($this->syncRestApi->syncObject, $where);
        return $object ? $object->toArray() : [];
    }

    public function getLimit(array $where = [])
    {   
        $query = $this->modx->newQuery($this->syncRestApi->syncObject);
        $query->where($where);
        
        return $this->modx->getCount($this->syncRestApi->syncObject, $query);
    }
}
