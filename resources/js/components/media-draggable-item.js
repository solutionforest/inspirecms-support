export default function mediaDraggableItemComponent({
    livewireKey
}) {
    return {
        livewireKey: livewireKey,
        dragging: false,

        onDragStart(event) {
            this.dragging = true;
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('draggable-id', this.$el.getAttribute('data-draggable-id'));
        },

        onDragEnd(event) {
            this.dragging = false;
        },

        onDragOver(event) {
            event.preventDefault();
            event.dataTransfer.dropEffect = 'move';
            this.$el.classList.add('drag-and-drop__item--drag-over');
        },

        onDragLeave(event) {
            this.$el.classList.remove('drag-and-drop__item--drag-over');
        },

        onDrop(event) {
            event.preventDefault();
            this.$el.classList.remove('drag-and-drop__item--drag-over');

            const targetId = event.dataTransfer.getData('draggable-id');
            const toId = this.$el.getAttribute('data-draggable-id');
            
            if (this.$el.getAttribute('data-draggable-type') === 'folder' && targetId !== toId) {
                this.moveItem(targetId, toId);
            }
        },

        moveItem(targetId, toId) {
            Livewire.dispatch('moveMediaItem', { livewireKey: this.livewireKey, targetId: targetId, toId: toId });
        }
    }
}