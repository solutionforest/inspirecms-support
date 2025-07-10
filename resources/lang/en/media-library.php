<?php

return [

    'media' => [
        'singular' => 'Media',
        'plural' => 'Media',
    ],

    'folder' => [
        'singular' => 'Folder',
        'plural' => 'Folders',
    ],

    'detail_info' => [

        'heading' => 'Information',

        'model_id' => [
            'label' => 'ID',
        ],
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
        'id' => [
            'label' => 'ID',
            'validation_attribute' => 'ID',
        ],
        'title' => [
            'label' => 'Title',
            'validation_attribute' => 'title',
        ],
        'description' => [
            'label' => 'Description',
            'validation_attribute' => 'description',
        ],
        'file' => [
            'label' => 'File',
            'validation_attribute' => 'file',
        ],
        'caption' => [
            'label' => 'Caption',
            'validation_attribute' => 'caption',
        ],
        'file_name' => [
            'label' => 'File Name',
            'validation_attribute' => 'file name',
        ],
        'mime_type' => [
            'label' => 'Mime Type',
            'validation_attribute' => 'mime type',
        ],
        'upload_from' => [
            'label' => 'Upload From',
            'validation_attribute' => 'upload from',
            'options' => [
                'file' => 'Upload from File',
                'url' => 'Upload from URL',
            ],
        ],
        'files' => [
            'label' => 'Files',
            'validation_attribute' => 'files',
        ],
        'url' => [
            'label' => 'URL',
            'validation_attribute' => 'URL',
        ],
    ],

    'buttons' => [
        'view' => [
            'label' => 'View',
            'heading' => 'View :name',
        ],
        'delete' => [
            'label' => 'Delete',
            'heading' => 'Delete :name',
            'messages' => [
                'success' => [
                    'title' => 'File Deleted',
                ],
            ],

        ],
        'edit' => [
            'label' => 'Edit',
            'heading' => 'Edit :name',
            'messages' => [
                'success' => [
                    'title' => 'Saved',
                    'body' => 'If you have re-uploaded the file, please refresh the page to see the changes, e.g., thumbnail of media.',
                ],
                'error' => [
                    'title' => 'An error occurred while saving the media file.',
                ],
            ],
        ],
        'open_folder' => [
            'label' => 'Open folder',
        ],
        'create_folder' => [
            'label' => 'Create Folder',
            'messages' => [
                'success' => [
                    'title' => 'Folder Created',
                ],
            ],
        ],
        'upload' => [
            'label' => 'Upload',
            'heading' => 'Upload Files',
            'messages' => [
                'success' => [
                    'title' => 'File Uploaded',
                ],
                'error' => [
                    'title' => 'File Upload Failed',
                ],
            ],
        ],
        'clear' => [
            'label' => 'Clear',
        ],
        'rename' => [
            'label' => 'Rename',
            'heading' => 'Rename :name',
            'messages' => [
                'success' => [
                    'title' => 'File Renamed',
                ],
            ],
        ],
    ],

    'messages' => [
        'item_moved' => 'Item moved successfully.',
        'xxx_items_selected' => ':count items selected.',
        'total_xxx_items' => ':count items',
    ],
];
