<?php

return [
    'actions' => [
        'view' => [
            'label' => 'View',
        ],
        'delete' => [
            'label' => 'Delete',
        ],
        'open_folder' => [
            'label' => 'Open Folder',
        ],
        'create_folder' => [
            'label' => 'Create Folder',
        ],
        'upload' => [
            'label' => 'Upload',
        ],
    ],
    'detail_info' => [
        'mime_type' => [
            'label' => 'Mime Type',
        ],
        'disk' => [
            'label' => 'Disk',
        ],
        'size' => [
            'label' => 'Size',
        ],
        'created_at' => [
            'label' => 'Created At',
            'empty' => 'Never',
        ],
        'updated_at' => [
            'label' => 'Updated At',
            'empty' => 'Never',
        ],
    ],
    'filter' => [
        'title' => [
            'label' => 'Title',
            'placeholder' => 'Search by title',
        ],
        'type' => [
            'label' => 'Type',
            'placeholder' => 'All types',
            'options' => [
                'image' => 'Image',
                'video' => 'Video',
                'audio' => 'Audio',
                'document' => 'Document',
                'archive' => 'Archive',
            ],
        ],
    ],
];
