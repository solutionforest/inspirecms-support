
function mediaItem({
    livewireKey,
    isDraggable = true,
}) {
    return {
        livewireKey,
        isDraggable,
        isDragOver: false,
        isDragging: false,

        /**
         * Handle drag start event
         */
        onDragStart(event) {
            this.isDragging = true;
            const item = this.$el;
            // this.$dispatch('item-drag-start', { item });
            event.dataTransfer.setData('draggable-id', item.getAttribute('data-draggable-id'));
            event.dataTransfer.effectAllowed = 'move';
        },

        /**
         * Handle drag end event
         */
        onDragEnd(event) {
            this.isDragging = false;
            // this.$dispatch('item-drag-end');
        },

        /**
         * Handle drag over event
         */
        onDragOver(event) {
            event.preventDefault();
            this.isDragOver = true;
            event.dataTransfer.dropEffect = 'move';
        },

        /**
         * Handle drag leave event
         */
        onDragLeave(event) {
            // Only hide if we're actually leaving the element
            if (!event.currentTarget.contains(event.relatedTarget)) {
                this.isDragOver = false;
            }
        },

        /**
         * Handle drop event
         */
        onDrop(event) {

            const draggedItemId = event.dataTransfer.getData('draggable-id');
            const targetFolderId = this.$el.getAttribute('data-draggable-id');

            event.preventDefault();
            this.isDragOver = false;
            
            try {
                if (draggedItemId !== targetFolderId) {
                    Livewire.dispatch('moveMediaItem', { 
                        livewireKey: this.livewireKey, 
                        targetId: draggedItemId, 
                        toId: targetFolderId 
                    });
                } else {
                    console.warn('Cannot move item to itself:', {
                        'sourceId': draggedItemId,
                        'targetId': targetFolderId
                    });
                }
            } catch (error) {
                console.error('Error parsing dragged item:', error);
            }
        },
    };
}

function dynamicImage({
    baseUrl,
    mediaId,
    refreshLivewireEvents = [],
    refreshWindowEvents = [],
    refreshDocumentEvents = [],
    refreshKeysTracker = [],
    cacheBuster = true,
    retryAttempts = 3,
    retryDelay = 1000,
    fallbackSrc = null,
    onUpdate = null,
    onError = null,
    defaultLoadingState = false,
}) {
    return {
        src: null,
        baseUrl: baseUrl,
        mediaId: mediaId,
        refreshTracker: {
            events: {
                livewire: refreshLivewireEvents,
                window: refreshWindowEvents,
                document: refreshDocumentEvents,
            },
            monitoredKeys: refreshKeysTracker,
        },
        cacheBuster: cacheBuster,
        isLoading: defaultLoadingState,
        hasError: false,
        retryCount: 0,
        retryAttempts: retryAttempts,
        retryDelay: retryDelay,
        fallbackSrc: fallbackSrc,
        onUpdate: onUpdate,
        onError: onError,

        /**
         * Generate a new source URL with optional cache buster
         */
        generateSrc() {
            if (!this.baseUrl) return null;
            
            let url = this.baseUrl;
            if (this.cacheBuster) {
                const separator = url.includes('?') ? '&' : '?';
                url += `${separator}t=${Date.now()}`;
            }
            return url;
        },

        /**
         * Update the image source
         */
        updateSrc() {
            this.isLoading = true;
            const newSrc = this.generateSrc();
            if (newSrc !== this.src) {
                this.src = newSrc;
                this.hasError = false;
                this.retryCount = 0;
                
                if (this.onUpdate && typeof this.onUpdate === 'function') {
                    this.onUpdate(newSrc, this.mediaId);
                }
                
                //console.log(`Image source updated for media ${this.mediaId}: ${newSrc}`);
            }
            this.isLoading = false;
        },

        /**
         * Handle image load error with retry logic
         */
        handleImageError() {
            console.warn(`Image load error for media ${this.mediaId}, attempt ${this.retryCount + 1}/${this.retryAttempts}`);
            
            if (this.retryCount < this.retryAttempts) {
                this.retryCount++;
                setTimeout(() => {
                    this.updateSrc();
                }, this.retryDelay * this.retryCount);
            } else {
                this.hasError = true;
                if (this.fallbackSrc) {
                    this.src = this.fallbackSrc;
                }
                
                if (this.onError && typeof this.onError === 'function') {
                    this.onError(this.mediaId, this.retryCount);
                }
                
                console.error(`Failed to load image for media ${this.mediaId} after ${this.retryAttempts} attempts`);
            }
        },

        /**
         * Handle image load success
         */
        handleImageLoad() {
            this.isLoading = false;
            this.hasError = false;
            this.retryCount = 0;
            //console.log(`Image loaded successfully for media ${this.mediaId}`);
        },

        /**
         * Initialize the component
         */
        init() {
            // Set initial source
            this.updateSrc();

            // Listen to Livewire events
            if (window.Livewire) {
                this.refreshTracker.events.livewire.forEach(eventName => {
                    this.$wire.on(eventName, (eventDetail) => {
                        //console.log(`Livewire event '${eventName}' received for media ${this.mediaId}:`, eventDetail);
                        this.updateSrc();
                    });
                });
            }

            // Listen to window events
            this.refreshTracker.events.window.forEach(eventName => {
                window.addEventListener(`${eventName}`, (event) => {
                    //console.log(`Window event '${eventName}' received for media ${this.mediaId}:`, event.detail);
                    this.updateSrc();
                });
            });

            // Listen to document events
            this.refreshTracker.events.document.forEach(eventName => {
                document.addEventListener(`${eventName}`, (event) => {
                    // console.log(`Document event '${eventName}' received for media ${this.mediaId}:`, event.detail);
                    if (this.shouldRefreshForEvent(eventName, event.detail)) {
                        this.updateSrc();
                    }
                });
            });

            // Watch for changes in specified keys
            this.refreshTracker.monitoredKeys.forEach(key => {
                this.$watch(key, (newValue, oldValue) => {
                    // console.log(`Watched key '${key}' changed for media ${this.mediaId}:`, newValue)
                    this.updateSrc();
                });
            });
        },

        /**
         * Manually refresh the image
         */
        refresh() {
            this.updateSrc();
        },

        /**
         * Reset error state and retry
         */
        retry() {
            this.hasError = false;
            this.retryCount = 0;
            this.updateSrc();
        }
    }
}

// Global event listeners
document.addEventListener('alpine:init', () => {
    Alpine.data('mediaItem', mediaItem);
    Alpine.data('dynamicImage', dynamicImage);
    // Alpine.store('dragDrop', {
    //     draggedItem: null
    // });
});

// // Handle global drag and drop events
// document.addEventListener('item-drag-start', (event) => {
//     Alpine.store('dragDrop').draggedItem = event.detail.item;
// });

// document.addEventListener('item-drag-end', () => {
//     Alpine.store('dragDrop').draggedItem = null;
// });
