<?php
switch ($modx->event->name) {
    case 'OnBeforeEmptyTrash':
        if (!$syncRestApi = $modx->getService('syncRestApi', '', MODX_CORE_PATH . 'components/syncrestapi/model/syncrestapi/')) {
            return 'Could not load syncRestApi class!';
        }
        
        $syncRestApi->initialize();
        
        foreach ($ids as $id) {
            $syncRestApi->storage->remove($id);
        }
        
        break;
}
