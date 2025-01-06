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

            'modal' => [

                'heading' => 'Delete :name',

            ],

            'notification' => [
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

            'notification' => [
                'saved' => [
                    'title' => 'Saved',
                ],
            ],
        ],
        'create_folder' => [
            'label' => 'Create Folder',

            'modal' => [

                'heading' => 'Create Folder',

            ],

            'notification' => [
                'created' => [
                    'title' => 'Folder Created',
                ],
            ],
        ],
        'upload' => [
            'label' => 'Upload',

            'modal' => [

                'heading' => 'Upload Files',

                'submit' => [
                    'label' => 'Upload',
                ],
            ],

            'notification' => [
                'uploaded' => [
                    'title' => 'File Uploaded',
                ],
            ],
        ],
        'clear' => [
            'label' => 'Clear',
        ],
        'rename' => [
            'label' => 'Rename',

            'modal' => [

                'heading' => 'Rename :name',

            ],

            'notification' => [
                'renamed' => [
                    'title' => 'File Renamed',
                ],
            ],
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
