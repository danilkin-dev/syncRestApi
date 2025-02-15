<?php

return [
    //main
    'api_endpoint' => [
        'xtype' => 'textfield',
        'value' => 'https://site.ru/api/v1',
        'area' => 'syncrestapi_main',
    ],
    'api_limit' => [
        'xtype' => 'numberfield',
        'value' => 50,
        'area' => 'syncrestapi_main',
    ],
    'company_token' => [
        'xtype' => 'text-password',
        'value' => '',
        'area' => 'syncrestapi_main',
    ],
    'access_token' => [
        'xtype' => 'text-password',
        'value' => '',
        'area' => 'syncrestapi_main',
    ],
    'sync_parent' => [
        'xtype' => 'numberfield',
        'value' => '',
        'area' => 'syncrestapi_main',
    ],
    
    
    //category
    'category_template' => [
        'xtype' => 'modx-combo-template',
        'value' => '',
        'area' => 'syncrestapi_category',
    ],
    'category_published' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_category',
    ],
    'category_process_on_create' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_category',
    ],
    'category_process_on_update' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_category',
    ],
    /*'category_image_source' => [
        'xtype' => 'modx-combo-source',
        'value' => 1,
        'area' => 'syncrestapi_category',
    ],
    'category_image_field' => [
        'xtype' => 'textfield',
        'value' => '',
        'area' => 'syncrestapi_category',
    ],
    'category_image_path' => [
        'xtype' => 'textfield',
        'value' => 'assets/images/categories/',
        'area' => 'syncrestapi_category',
    ],
    'category_process_image_import_create' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_category',
    ],
    'category_process_image_import_update' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_category',
    ],*/
    
    
    //product
    'product_template' => [
        'xtype' => 'modx-combo-template',
        'value' => '',
        'area' => 'syncrestapi_product',
    ],
    'product_process_price' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],
    'product_published' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],
    /*'product_process_image_import_create' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],
    'product_process_image_import_update' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],*/
    'product_process_on_create' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],
    'product_process_on_update' => [
        'xtype' => 'combo-boolean',
        'value' => true,
        'area' => 'syncrestapi_product',
    ],
    /*'product_auto_create_options' => [
        'xtype' => 'combo-boolean',
        'value' => false,
        'area' => 'syncrestapi_product',
    ],*/
   /* 'product_ignore_options' => [
        'xtype' => 'textfield',
        'value' => 'id, car_name, prices, avatar_url',
        'area' => 'syncrestapi_product',
    ],*/
    'product_fields' => [
        'xtype' => 'textarea',
        'value' => '{"key":"msoption.key"}',
        'area' => 'syncrestapi_product',
    ],
];