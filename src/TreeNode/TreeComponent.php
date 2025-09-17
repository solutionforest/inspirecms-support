<?php

namespace SolutionForest\InspireCms\Support\TreeNode;

use Filament\Actions\Contracts\HasActions;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\HasTreeNodeItemActions;

abstract class TreeComponent extends Component implements HasActions, HasSchemas
{
    use HasTreeNodeItemActions;
    use InteractsWithSchemas;
}
