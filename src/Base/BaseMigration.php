<?php

namespace SolutionForest\InspireCms\Support\Base;

use Illuminate\Database\Migrations\Migration;
use SolutionForest\InspireCms\Support\Facades\ModelRegistry;

abstract class BaseMigration extends Migration
{
    protected ?string $prefix = null;

    public function __construct()
    {
        $this->prefix = ModelRegistry::getTablePrefix();
    }

    protected function getCmsTableNames()
    {
        return [
            'allowed_document_type' => $this->prefix . 'document_type_allowed_document_type',
            'content_lock' => $this->prefix . 'content_locks',
            'content_path' => $this->prefix . 'content_paths',
            'content_publish_version' => $this->prefix . 'content_publish_version',
            'content_route' => $this->prefix . 'content_routes',
            'content_version' => $this->prefix . 'content_versions',
            'content_web_setting' => $this->prefix . 'content_web_settings',
            'content' => $this->prefix . 'content',
            'document_type_inheritance' => $this->prefix . 'document_type_inheritance',
            'document_type' => $this->prefix . 'document_types',
            'export' => $this->prefix . 'exports',
            'field_groupable' => $this->prefix . 'field_groupables',
            'import' => $this->prefix . 'imports',
            'key_value' => $this->prefix . 'key_values',
            'language' => $this->prefix . 'languages',
            'media_asset' => $this->prefix . 'media_assets',
            'navigation' => $this->prefix . 'navigation',
            'sitemap' => $this->prefix . 'sitemaps',
            'templatable' => $this->prefix . 'templateable',
            'template' => $this->prefix . 'templates',
            'user_login_activity' => $this->prefix . 'user_login_activities',
            'user' => $this->prefix . 'users',
        ];
    }
}
