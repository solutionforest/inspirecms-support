<div>
    <form wire:submit="save">

        <div class="pb-4">
            {{ $this->form }}
        </div>
        
        <x-filament-panels::form.actions
            :actions="$this->getFormActions()"
        />
    </form>
    
    <x-filament-actions::modals />
</div>