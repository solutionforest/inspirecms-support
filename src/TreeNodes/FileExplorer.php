<?php

namespace SolutionForest\InspireCms\Support\TreeNodes;

use Filament\Support\Components\ViewComponent;
use SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasFileExplorer;
use SolutionForest\InspireCms\Support\TreeNodes\FileExplorer\Concerns;

class FileExplorer extends ViewComponent
{
    use Concerns\BelongsToLivewire;
    use Concerns\CanSelectFileItem;
    use Concerns\FileExplorerBase;
    use Concerns\HasFileItems;
    use Concerns\HasSelectedItemForm;

    /**
     * @var view-string
     */
    protected string $view = 'inspirecms-support::file-explorer.index';

    protected string $viewIdentifier = 'fileExplorer';

    protected string $evaluationIdentifier = 'fileExplorer';

    final public function __construct(HasFileExplorer $livewire)
    {
        $this->livewire($livewire);
    }

    public static function make(HasFileExplorer $livewire): static
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
