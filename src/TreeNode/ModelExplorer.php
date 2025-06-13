<?php

namespace SolutionForest\InspireCms\Support\TreeNode;

use Filament\Support\Components\ViewComponent;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\HasModelExplorer;
use SolutionForest\InspireCms\Support\TreeNode\Contracts\TreeNode;
use SolutionForest\InspireCms\Support\TreeNode\ModelExplorer\Concerns;

class ModelExplorer extends ViewComponent implements TreeNode
{
    use Concerns\BelongsToLivewire;
    use Concerns\CanSelectItem;
    use Concerns\HasActions;
    use Concerns\HasModelItems;
    use Concerns\ModelExplorerBase;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::tree-node.model-explorer';

    protected string $viewIdentifier = 'modelExplorer';

    protected string $evaluationIdentifier = 'modelExplorer';

    final public function __construct(HasModelExplorer $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasModelExplorer $livewire): static
    {
        $static = app(static::class, ['livewire' => $livewire]);
        $static->configure();

        return $static;
    }

    protected function resolveDefaultClosureDependencyForEvaluationByName(string $parameterName): array
    {
        return match ($parameterName) {
            'livewire' => [$this->getLivewire()],
            default => parent::resolveDefaultClosureDependencyForEvaluationByName($parameterName),
        };
    }
}
