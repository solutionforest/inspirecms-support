<?php

return [
    'media' => '媒體',
    'actions' => [
        'view' => [
            'label' => '查看',

            'modal' => [

                'heading' => '查看:name',

            ],
        ],
        'delete' => [
            'label' => '刪除',

            'notifications' => [
                'deleted' => [
                    'title' => '檔案已刪除',
                ],
            ],
        ],
        'edit' => [
            'label' => '編輯',

            'modal' => [

                'heading' => '編輯:name',

            ],

            'notifications' => [
                'saved' => [
                    'title' => '已保存',
                ],
            ],
        ],
        'open_folder' => [
            'label' => '打開資料夾',
        ],
        'create_folder' => [
            'label' => '創建資料夾',

            'modal' => [

                'heading' => '創建資料夾',

            ],

            'notifications' => [
                'created' => [
                    'title' => '資料夾已創建',
                ],
            ],
        ],
        'upload' => [
            'label' => '上傳',
        ],
    ],
    'detail_info' => [
        'file_name' => [
            'label' => '檔案名稱',
        ],
        'mime_type' => [
            'label' => 'MIME 類型',
        ],
        'size' => [
            'label' => '大小',
        ],
        'created_at' => [
            'label' => '創建於',
            'empty' => '從未',
        ],
        'updated_at' => [
            'label' => '更新於',
            'empty' => '從未',
        ],
        'uploaded_by' => [
            'label' => '上傳者',
        ],
        'created_by' => [
            'label' => '創建者',
        ],
        'title' => [
            'label' => '標題',
        ],
        'custom-property' => [
            'dimensions' => [
                'label' => '尺寸',
            ],
            'duration' => [
                'label' => '時長',
            ],
            'resolution' => [
                'label' => '解析度',
            ],
            'channels' => [
                'label' => '聲道',
            ],
            'bit_rate' => [
                'label' => '比特率',
            ],
            'frame_rate' => [
                'label' => '幀率',
            ],
        ],
    ],
    'filter' => [
        'title' => [
            'placeholder' => '按標題搜索',
        ],
        'type' => [
            'placeholder' => '所有類型',
            'options' => [
                'image' => '圖片',
                'video' => '視頻',
                'audio' => '音頻',
                'document' => '文件',
                'archive' => '壓縮檔案',
            ],
        ],
    ],
    'sort' => [
        'type' => [
            'placeholder' => '排序類型',
            'options' => [
                'default' => '默認',
                'name' => '名稱',
                'size' => '大小',
                'created_at' => '創建於',
                'updated_at' => '更新於',
            ],
        ],
        'direction' => [
            'placeholder' => '排序方向',
            'options' => [
                'asc' => '升序',
                'desc' => '降序',
            ],
        ],
    ],
    'forms' => [
        'title' => [
            'label' => '標題',
        ],
        'description' => [
            'label' => '描述',
        ],
        'file' => [
            'label' => '檔案',
        ],
        'caption' => [
            'label' => '說明文字',
        ],
        'file_name' => [
            'label' => '檔案名稱',
        ],
        'files' => [
            'label' => '檔案',
        ],
        'mime_type' => [
            'label' => 'MIME 類型',
        ],
    ],
];
