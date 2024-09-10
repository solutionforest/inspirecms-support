<?php

namespace SolutionForest\InspireCms\Support;

use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Livewire\Attributes\Locked;
use Livewire\Attributes\On;
use Livewire\Component;

class ModelExplorerComponent extends Component implements HasActions, HasForms, HasInfolists
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithInfolists;

    #[Locked]
    public string $modelClass;

    #[Locked]
    public ?int $rootId = null;

    #[Locked]
    public $parentColumnName = '';

    public Collection $items;

    public ?int $selectedModelId = null;

    public ?array $selectedModelData = [];

    public array $expandedItems = [];

    public function mount(string $modelClass, string $parentColumnName = 'parent_id', ?int $rootId = null)
    {
        $this->modelClass = $modelClass;
        $this->rootId = $rootId;
        $this->parentColumnName = $parentColumnName;
        $this->form->fill();
        $this->loadRootItems();
    }

    public function selectedDataInfolist(Infolist $infolist): Infolist
    {
        $normalizeData = $this->getNormizeDataForDisplay();

        $schema = [];
        foreach (array_keys($normalizeData) as $key) {
            $schema[] = Infolists\Components\TextEntry::make($key);
        }

        return $infolist
            ->state($normalizeData)
            ->schema($schema);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ...
                Forms\Components\TextInput::make('id'),
            ]);
    }

    protected function fillData($id)
    {
        if (! $id) {
            return [];
        }

        return $this->findItemById($id) ?? [];
    }

    public function save($data)
    {
        // to build
        throw new \Exception('Please build your action.');
    }

    public function loadRootItems()
    {
        $query = $this->modelClass::query();
        if ($this->rootId) {
            $query->where('id', $this->rootId);
        } else {
            $query->whereNull($this->parentColumnName);
        }
        $this->items = $query->get()->map(function ($item) {
            return $this->formatItem($item);
        });
    }

    #[On('toggleItem')]
    public function toggleItem($itemId)
    {
        if (in_array($itemId, $this->expandedItems)) {
            $this->expandedItems = array_diff($this->expandedItems, [$itemId]);
        } else {
            $this->expandedItems[] = $itemId;
            $this->loadChildren($itemId);
        }
        $this->dispatch('selectModel', $itemId);
    }

    #[On('selectModel')]
    public function selectModel(int $id)
    {
        $this->selectedModelId = $id;
        $selectedItem = $this->findItemById($id);

        if ($selectedItem) {
            $this->selectedModelData = $selectedItem;
        } else {
            $this->selectedModelData = null;
            $this->getModelLoadFailedNotification()->send();
        }
    }

    #[On('loadChildren')]
    public function loadChildren($parentId)
    {
        $children = $this->modelClass::where($this->parentColumnName, $parentId)->get();
        $formattedChildren = $children->map(function ($child) {
            return $this->formatItem($child);
        });

        $this->items = $this->updateItemChildren($this->items, $parentId, $formattedChildren);
    }

    public function render()
    {
        return view('inspirecms-support::model-explorer.index');
    }

    protected function getModelLoadFailedNotification()
    {
        return Notification::make()
            ->title(__('inspirecms-support::notification.model_load_failed.title'))
            ->body(__('inspirecms-support::notification.model_load_failed.body'))
            ->danger();
    }

    protected function formatItem($model)
    {
        return [
            'id' => $model->id,
            'name' => $model->name,
            'hasChildren' => $model->children()->exists(),
            'children' => [],
        ];
    }

    protected function updateItemChildren($items, $parentId, $formattedChildren)
    {
        return $items->map(function ($item) use ($parentId, $formattedChildren) {
            if ($item['id'] == $parentId) {
                $item['children'] = $formattedChildren;
            } elseif (! empty($item['children'])) {
                $item['children'] = $this->updateItemChildren(collect($item['children']), $parentId, $formattedChildren);
            }

            return $item;
        });
    }

    protected function findItemById($id, $items = null)
    {
        if ($items === null) {
            $items = $this->items;
        }

        foreach ($items as $item) {
            if ($item['id'] == $id) {
                return $item;
            }
            if (! empty($item['children'])) {
                $found = $this->findItemById($id, $item['children']);
                if ($found) {
                    return $found;
                }
            }
        }

        return null;
    }

    protected function getNormizeDataForDisplay()
    {
        return collect($this->selectedModelData)
            ->filter(function ($value, $key) {
                return $key === 'id' || is_string($value) || is_int($value);
            })
            ->all();
    }

    public function getItemActions($item): array
    {
        return [

            $this->editAction()->arguments($item),
        ];
    }

    public function editAction(): Action
    {
        return EditAction::make('edit')
            ->record(fn ($arguments) => $this->modelClass::findOrFail($arguments['id']))
            ->form(fn (Form $form) => $this->form($form))
            ->action(fn (array $data) => dd($data) && $this->save($data));
    }
}
