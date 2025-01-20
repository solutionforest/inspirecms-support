<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use SolutionForest\InspireCms\Support\Base\BaseMigration;

return new class extends BaseMigration
{
    public function up()
    {
        $tableNames = $this->getTableNames();
        
        Schema::create($tableNames['nestable_tree'], function (Blueprint $table) {
            $table->id();

            $table->nestedSet(); // This method adds left, right, depth columns.

            $table->uuidMorphs('nestable');

            $table->timestamps();
        });
    }

    public function down()
    {
        $tableNames = $this->getTableNames();

        Schema::dropIfExists($tableNames['nestable_tree']);
    }

    private function getTableNames()
    {
        return [
            'nestable_tree' => $this->prefix . 'nestable_trees',
        ];
    }
};
