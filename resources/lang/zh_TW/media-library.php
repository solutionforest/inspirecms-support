<?php

return [

    'media' => [
        'singular' => '媒體',
        'plural' => '媒體',
    ],

    'folder' => [
        'singular' => '資料夾',
        'plural' => '資料夾',
    ],

    'detail_info' => [

        'heading' => '資訊',

        'model_id' => [
            'label' => 'ID',
        ],
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
        'id' => [
            'label' => 'ID',
            'validation_attribute' => 'ID',
        ],
        'title' => [
            'label' => '標題',
            'validation_attribute' => '標題',
        ],
        'description' => [
            'label' => '描述',
            'validation_attribute' => '描述',
        ],
        'file' => [
            'label' => '檔案',
            'validation_attribute' => '檔案',
        ],
        'caption' => [
            'label' => '說明文字',
            'validation_attribute' => '說明文字',
        ],
        'file_name' => [
            'label' => '檔案名稱',
            'validation_attribute' => '檔案名稱',
        ],
        'mime_type' => [
            'label' => 'MIME 類型',
            'validation_attribute' => 'MIME 類型',
        ],
        'upload_from' => [
            'label' => '上傳來源',
            'validation_attribute' => '上傳來源',
            'options' => [
                'file' => '從檔案上傳',
                'url' => '從 URL 上傳',
            ],
        ],
        'files' => [
            'label' => '檔案',
            'validation_attribute' => '檔案',
        ],
        'url' => [
            'label' => 'URL',
            'validation_attribute' => 'URL',
        ],
    ],

    'buttons' => [
        'view' => [
            'label' => '查看',
            'heading' => '查看:name',
        ],
        'delete' => [
            'label' => '刪除',
            'heading' => '刪除 :name',
            'messages' => [
                'success' => [
                    'title' => '已刪除',
                ],
                'error' => [
                    'title' => '刪除失敗',
                ],
            ],
        ],
        'edit' => [
            'label' => '編輯',
            'heading' => '編輯:name',
            'messages' => [
                'success' => [
                    'title' => '已保存',
                    'body' => '如果您重新上傳了檔案，請刷新頁面以查看更改，例如媒體的縮略圖。',
                ],
            ],
        ],
        'open_folder' => [
            'label' => '打開資料夾',
        ],
        'create_folder' => [
            'label' => '創建資料夾',
            'messages' => [
                'success' => [
                    'title' => '資料夾已創建',
                ],
            ],
        ],
        'upload' => [
            'label' => '上傳',
        ],
        'upload_by_type' => [
            'label' => '按類型上傳',
            'heading' => '上傳',
            'messages' => [
                'success' => [
                    'title' => '檔案已上傳',
                ],
                'error' => [
                    'title' => '檔案上傳失敗',
                ],
            ],
        ],
        'clear' => [
            'label' => '清除',
        ],
        'select' => [
            'label' => '選擇',
            'heading' => '選擇',
        ],
        'cancel' => [
            'label' => '取消',
        ],
        'rename' => [
            'label' => '重命名',
            'heading' => '重命名 :name',
            'messages' => [
                'success' => [
                    'title' => '檔案已重命名',
                ],
            ],
        ],
    ],

    'messages' => [
        'item_moved' => '項目已成功移動。',
        'xxx_items_selected' => ':count 項目已選擇。',
        'total_xxx_items' => ':count 項目',
        'item_deleted' => '已刪除',
        'item_deletion_failed' => '刪除失敗',
        'uploaded' => '已上傳',
    ],
];
