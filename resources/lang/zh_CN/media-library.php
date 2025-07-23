<?php

return [

    'media' => [
        'singular' => '媒体',
        'plural' => '媒体',
    ],

    'folder' => [
        'singular' => '文件夹',
        'plural' => '文件夹',
    ],

    'detail_info' => [

        'heading' => '信息',

        'model_id' => [
            'label' => 'ID',
        ],
        'file_name' => [
            'label' => '文件名称',
        ],
        'mime_type' => [
            'label' => 'MIME 类型',
        ],
        'size' => [
            'label' => '大小',
        ],
        'created_at' => [
            'label' => '创建于',
            'empty' => '从未',
        ],
        'updated_at' => [
            'label' => '更新于',
            'empty' => '从未',
        ],
        'uploaded_by' => [
            'label' => '上传者',
        ],
        'created_by' => [
            'label' => '创建者',
        ],
        'title' => [
            'label' => '标题',
        ],
        'custom-property' => [
            'dimensions' => [
                'label' => '尺寸',
            ],
            'duration' => [
                'label' => '时长',
            ],
            'resolution' => [
                'label' => '分辨率',
            ],
            'channels' => [
                'label' => '声道',
            ],
            'bit_rate' => [
                'label' => '比特率',
            ],
            'frame_rate' => [
                'label' => '帧率',
            ],
        ],
    ],

    'filter' => [
        'title' => [
            'placeholder' => '按标题搜索',
        ],
        'type' => [
            'placeholder' => '所有类型',
            'options' => [
                'image' => '图片',
                'video' => '视频',
                'audio' => '音频',
                'document' => '文件',
                'archive' => '压缩文件',
            ],
        ],
    ],

    'sort' => [
        'type' => [
            'placeholder' => '排序类型',
            'options' => [
                'default' => '默认',
                'name' => '名称',
                'size' => '大小',
                'created_at' => '创建于',
                'updated_at' => '更新于',
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
            'label' => '标题',
            'validation_attribute' => '标题',
        ],
        'description' => [
            'label' => '描述',
            'validation_attribute' => '描述',
        ],
        'file' => [
            'label' => '文件',
            'validation_attribute' => '文件',
        ],
        'caption' => [
            'label' => '说明文字',
            'validation_attribute' => '说明文字',
        ],
        'file_name' => [
            'label' => '文件名称',
            'validation_attribute' => '文件名称',
        ],
        'mime_type' => [
            'label' => 'MIME 类型',
            'validation_attribute' => 'MIME 类型',
        ],
        'upload_from' => [
            'label' => '上传来源',
            'validation_attribute' => '上传来源',
            'options' => [
                'file' => '从文件上传',
                'url' => '从 URL 上传',
            ],
        ],
        'files' => [
            'label' => '文件',
            'validation_attribute' => '文件',
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
            'label' => '删除',
            'heading' => '删除 :name',
            'messages' => [
                'success' => [
                    'title' => '已删除',
                ],
                'error' => [
                    'title' => '删除失败',
                ],
            ],
        ],
        'edit' => [
            'label' => '编辑',
            'heading' => '编辑:name',
            'messages' => [
                'success' => [
                    'title' => '已保存',
                    'body' => '如果您重新上传了文件，请刷新页面以查看更改，例如媒体的缩略图。',
                ],
            ],
        ],
        'open_folder' => [
            'label' => '打开文件夹',
        ],
        'create_folder' => [
            'label' => '创建文件夹',
            'messages' => [
                'success' => [
                    'title' => '文件夹已创建',
                ],
            ],
        ],
        'upload' => [
            'label' => '上传',
        ],
        'upload_by_type' => [
            'label' => '按类型上传',
            'heading' => '上传',
            'messages' => [
                'success' => [
                    'title' => '文件已上传',
                ],
                'error' => [
                    'title' => '文件上传失败',
                ],
            ],
        ],
        'clear' => [
            'label' => '清除',
        ],
        'select' => [
            'label' => '选择',
            'heading' => '选择',
        ],
        'cancel' => [
            'label' => '取消',
        ],
        'rename' => [
            'label' => '重命名',
            'heading' => '重命名 :name',
            'messages' => [
                'success' => [
                    'title' => '文件已重命名',
                ],
            ],
        ],
        'refresh' => [
            'label' => '刷新',
        ],
    ],

    'messages' => [
        'item_moved' => '项目已成功移动。',
        'xxx_items_selected' => ':count 项目已选择。',
        'total_xxx_items' => ':count 项目',
        'item_deleted' => '已删除',
        'item_deletion_failed' => '删除失败',
        'uploaded' => '已上传',
    ],
];
