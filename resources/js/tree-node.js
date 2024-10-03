document.addEventListener('alpine:init', () => {

    window.Alpine.store('treeNodeSidebar', {
        isResizing: false,
        startX: 0,
        startWidth: 0,

        init() {

            const resizer = document.querySelector('.tree-node-resizer');
            const sidebar = document.querySelector('.tree-node-sidebar');

            if (!resizer || !sidebar) {
                return;
            }

            const startResize = (e) => {
                this.isResizing = true;
                this.startX = e.pageX;
                this.startWidth = parseInt(window.getComputedStyle(sidebar).width, 10);
                document.body.classList.add('resizing');
            };

            const stopResize = () => {
                this.isResizing = false;
                document.body.classList.remove('resizing');
            };

            const resize = (e) => {
                if (!this.isResizing) return;

                const width = this.startWidth + (e.pageX - this.startX);
                sidebar.style.width = `${Math.max(100, Math.min(width, window.innerWidth * 0.5))}px`;
            };

            resizer.addEventListener('mousedown', startResize);
            document.addEventListener('mousemove', resize);
            document.addEventListener('mouseup', stopResize);
        },
    })
})