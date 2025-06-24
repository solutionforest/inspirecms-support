<?php

namespace SolutionForest\InspireCms\Support\TreeNode;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNode\Concerns\HasTreeNodeItemActions;

abstract class TreeComponent extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;
    use HasTreeNodeItemActions;
}
