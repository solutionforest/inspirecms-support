<?php

// translations for SolutionForest/InspireCms/Support
return [
    // General
    'select_file_to_view' => 'Select a file to view its content.',
    'loading_file_content' => 'Loading file content...',
    'unable_to_load_content' => 'Unable to load file content.',

    // File Explorer
    'file_content' => 'File Content:',
    'no_files_or_directories' => 'No files or directories found.',

    'no_models_found' => 'No models found.',
    'model_details' => 'Model Details',
    'select_model_to_view' => 'Select a model to view its details.',

    'notification' => [
        'access_restricted' => [
            'title' => 'Access Restricted',
            'body' => 'You lack the necessary permissions to view this file or directory.',
        ],
        'file_read_error' => [
            'title' => 'Oops! File Trouble',
            'body' => 'We couldn\'t open the file you selected. It might be damaged or locked.',
        ],
        'loading_children_failed' => [
            'title' => 'Oops! Folder Trouble',
            'body' => 'We couldn\'t open this folder for you. It might be empty, or you might not have permission to view its contents.',
        ],
        'model_load_failed' => [
            'title' => 'Model Load Error',
            'body' => 'We couldn\'t load the selected model. It might have been deleted or you might not have permission to view it.',
        ],
    ],

    // Exceptions
    'file_read_exception' => 'Failed to read file: :path. File does not exist or is not accessible.',

    // Buttons/Actions
    'load_more' => 'Load More',
    'expand' => 'Expand',
    'collapse' => 'Collapse',
    'actions' => 'Actions',
    'more_actions' => 'More actions',

    // File Types
    'directory' => 'Directory',
    'file' => 'File',

    // Misc
    'empty_directory' => 'This directory is empty.',
    'loading' => 'Loading...',
    'error' => 'Error',
];
