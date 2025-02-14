
@if ($this instanceof \SolutionForest\InspireCms\Support\TreeNodes\Contracts\HasTreeNodeActions && (! $this->hasTreeNodeItemActionModalRendered))
    <form wire:submit.prevent="callMountedTreeNodeItemAction">
        @php
            $action = $this->getMountedTreeNodeItemAction();
        @endphp

        <x-filament::modal
            :alignment="$action?->getModalAlignment()"
            :autofocus="$action?->isModalAutofocused()"
            :close-button="$action?->hasModalCloseButton()"
            :close-by-clicking-away="$action?->isModalClosedByClickingAway()"
            :close-by-escaping="$action?->isModalClosedByEscaping()"
            :description="$action?->getModalDescription()"
            display-classes="block"
            :extra-modal-window-attribute-bag="$action?->getExtraModalWindowAttributeBag()"
            :footer-actions="$action?->getVisibleModalFooterActions()"
            :footer-actions-alignment="$action?->getModalFooterActionsAlignment()"
            :heading="$action?->getModalHeading()"
            :icon="$action?->getModalIcon()"
            :icon-color="$action?->getModalIconColor()"
            :id="$this->getId() . '-treenodeitem-action'"
            :slide-over="$action?->isModalSlideOver()"
            :sticky-footer="$action?->isModalFooterSticky()"
            :sticky-header="$action?->isModalHeaderSticky()"
            :visible="filled($action)"
            :width="$action?->getModalWidth()"
            :wire:key="$action ? $this->getId() . '.treenodeitem.actions.' . $action->getName() . '.modal' : null"
            x-on:closed-form-component-action-modal.window="if (($event.detail.id === '{{ $this->getId() }}') && $wire.mountedTreeNodeItemActions.length) open()"
            x-on:modal-closed.stop="
                const mountedTreeNodeItemActionShouldOpenModal = {{ \Illuminate\Support\Js::from($action && $this->mountedTreeNodeItemActionShouldOpenModal(mountedAction: $action)) }}

                if (! mountedTreeNodeItemActionShouldOpenModal) {
                    return
                }

                if ($wire.mountedFormComponentActions.length) {
                    return
                }

                $wire.unmountTreeNodeItemAction(false, false)
            "
            x-on:opened-form-component-action-modal.window="if ($event.detail.id === '{{ $this->getId() }}') close()"
        >
            @if ($action)
                {{ $action->getModalContent() }}

                @if (count(($infolist = $action->getInfolist())?->getComponents() ?? []))
                    {{ $infolist }}
                @elseif ($this->mountedTreeNodeItemActionHasForm(mountedAction: $action))
                    {{ $this->getMountedTreeNodeItemActionForm(mountedAction: $action) }}
                @endif

                {{ $action->getModalContentFooter() }}
            @endif
        </x-filament::modal>
    </form>

    @php
        $this->hasTreeNodeItemActionModalRendered = true;
    @endphp
@endif