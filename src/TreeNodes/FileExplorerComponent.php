<?php

namespace SolutionForest\InspireCms\Support\TreeNodes;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Livewire\Component;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;

abstract class FileExplorerComponent extends Component implements HasActions, HasFileExplorer, HasForms
{
    use Concerns\InteractsWithFileExplorer;
    use InteractsWithActions;
    use InteractsWithForms;
}
