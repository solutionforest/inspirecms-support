<?php

namespace SolutionForest\InspireCms\Support\TreeNode;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\HasModelExplorer;

abstract class ModelExplorerComponent extends Component implements HasActions, HasForms, HasModelExplorer
{
    use Concerns\InteractsWithModelExplorer;
    use InteractsWithActions;
    use InteractsWithForms;
}
