export default function treeNode({
    expanded,
    selected,
}) {
    return {
        
        expanded,

        selected,

        init () {
            this.expanded = this.expanded || [];
            this.selected = this.selected || [];
            this.$watch('expanded', (value) => {
                // Ensure expanded is always an array
                this.expanded = Array.isArray(value) ? value : (Array.from(value) || []);
            });
            this.$watch('selected', (value) => {
                // Ensure selected is always an array
                this.selected = Array.isArray(value) ? value : (Array.from(value) || []);
            });
        },

        toggleItem(key) {
            if (this.expanded.includes(key)) {
                this.expanded = this.expanded.filter(item => item !== key);
            } else {
                this.expanded.push(key);
            }
        },

        selectItem(key) {
            if (this.selected.includes(key)) {
                this.selected = this.selected.filter(item => item !== key);
            } else {
                this.selected.push(key);
            }
        },

        isExpanded(key) {
            return this.expanded && Array.from(this.expanded).includes(key);
        },

        isSelected(key) {
            return this.selected && Array.from(this.selected).includes(key);
        },
    }
}