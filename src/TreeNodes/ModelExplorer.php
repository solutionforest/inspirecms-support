<?php

namespace SolutionForest\InspireCms\Support\TreeNodes;

use Filament\Support\Components\ViewComponent;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasModelExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\ModelExplorer\Concerns;

class ModelExplorer extends ViewComponent
{
    use Concerns\BelongsToLivewire;
    use Concerns\HasModelItems;
    use Concerns\HasSelectedItemForm;
    use Concerns\ModelExplorerBase;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::model-explorer';

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
