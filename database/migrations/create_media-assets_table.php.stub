<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\Support\Base\BaseMigration;

return new class extends BaseMigration
{
    public function up()
    {
        $tableNames = $this->getTableNames();
        
        Schema::create($tableNames['media_asset'], function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->uuid('parent_id')->default(0);
            $table->boolean('is_folder')->default(false);
            $table->string('caption')->nullable();
            $table->text('description')->nullable();
            $table->author(userType: 'uuid', nullable: true);
            $table->timestamps();

            $table->index('title');
            $table->index('parent_id');
        });
    }

    public function down()
    {
        $tableNames = $this->getTableNames();

        Schema::dropIfExists($tableNames['media_asset']);
    }

    private function getTableNames()
    {
        return [
            'media_asset' => $this->prefix . 'media_assets',
        ];
    }
};
