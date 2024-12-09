export default function mediaLibraryComponent({
    selectedMediaId,
    isSelectMultiple,
}) {
    return {
        selectedMediaId,
        isSelectMultiple,
        isMediaSelected: function(mediaId) {
            if (this.isSelectMultiple) {
                return this.selectedMediaId.includes(mediaId);
            } else {
                return this.selectedMediaId === mediaId;
            }
        },
        selectMedia: function(mediaId, isFolder) {
            if (this.isSelectMultiple && !isFolder) {
                if (this.selectedMediaId) {
                    if (this.selectedMediaId.includes(mediaId)) {
                        this.selectedMediaId = this.selectedMediaId.filter(id => id !== mediaId);
                    } else {
                        this.selectedMediaId = [...this.selectedMediaId, mediaId];
                    }
                } else {
                    this.selectedMediaId = [mediaId];
                }
            } else if (!this.isSelectMultiple) {
                this.selectedMediaId = mediaId;
            }
        },
        init: function() {
            this.selectedMediaId = this.isSelectMultiple ? [] : null;
        }
    }
}