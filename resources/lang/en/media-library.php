<?php

return [
    'media' => 'Media',
    'actions' => [
        'view' => [
            'label' => 'View',

            'modal' => [

                'heading' => 'View :name',

            ],
        ],
        'delete' => [
            'label' => 'Delete',

            'notifications' => [
                'deleted' => [
                    'title' => 'File Deleted',
                ],
            ],
        ],
        'edit' => [
            'label' => 'Edit',

            'modal' => [

                'heading' => 'Edit :name',

            ],

            'notifications' => [
                'saved' => [
                    'title' => 'Saved',
                ],
            ],
        ],
        'open_folder' => [
            'label' => 'Open Folder',
        ],
        'create_folder' => [
            'label' => 'Create Folder',

            'modal' => [

                'heading' => 'Create Folder',

            ],

            'notifications' => [
                'created' => [
                    'title' => 'Folder Created',
                ],
            ],
        ],
        'upload' => [
            'label' => 'Upload',
        ],
    ],
    'detail_info' => [
        'file_name' => [
            'label' => 'File Name',
        ],
        'mime_type' => [
            'label' => 'Mime Type',
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
        'uploaded_by' => [
            'label' => 'Uploaded By',
        ],
        'created_by' => [
            'label' => 'Created By',
        ],
        'title' => [
            'label' => 'Title',
        ],
        'custom-property' => [
            'dimensions' => [
                'label' => 'Dimensions',
            ],
            'duration' => [
                'label' => 'Duration',
            ],
            'resolution' => [
                'label' => 'Resolution',
            ],
            'channels' => [
                'label' => 'Channels',
            ],
            'bit_rate' => [
                'label' => 'Bitrate',
            ],
            'frame_rate' => [
                'label' => 'Frame Rate',
            ],
        ],
    ],
    'filter' => [
        'title' => [
            'placeholder' => 'Search by title',
        ],
        'type' => [
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
    'sort' => [
        'type' => [
            'placeholder' => 'Sort Type',
            'options' => [
                'default' => 'Default',
                'name' => 'Name',
                'size' => 'Size',
                'created_at' => 'Created At',
                'updated_at' => 'Updated At',
            ],
        ],
        'direction' => [
            'placeholder' => 'Sort Direction',
            'options' => [
                'asc' => 'Ascending',
                'desc' => 'Descending',
            ],
        ],
    ],
    'forms' => [
        'title' => [
            'label' => 'Title',
        ],
        'description' => [
            'label' => 'Description',
        ],
        'file' => [
            'label' => 'File',
        ],
        'caption' => [
            'label' => 'Caption',
        ],
        'file_name' => [
            'label' => 'File Name',
        ],
        'files' => [
            'label' => 'Files',
        ],
        'mime_type' => [
            'label' => 'Mime Type',
        ],
    ],
];
