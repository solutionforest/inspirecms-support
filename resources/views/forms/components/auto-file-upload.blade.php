@php
    use Filament\Support\Enums\Alignment;
    use Filament\Support\Facades\FilamentView;

    $id = $getId();
    $imageCropAspectRatio = $getImageCropAspectRatio();
    $imageResizeTargetHeight = $getImageResizeTargetHeight();
    $imageResizeTargetWidth = $getImageResizeTargetWidth();
    $isAvatar = $isAvatar();
    $statePath = $getStatePath();
    $isDisabled = $isDisabled();
    $hasImageEditor = $hasImageEditor();
    $hasCircleCropper = $hasCircleCropper();

    $alignment = $getAlignment() ?? Alignment::Start;

    if (! $alignment instanceof Alignment) {
        $alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
    }
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
    label-tag="div"
>
    <div
        @if (FilamentView::hasSpaMode())
            {{-- format-ignore-start --}}x-load="visible || event (ax-modal-opened)"{{-- format-ignore-end --}}
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('file-upload', 'filament/forms') }}"
        x-data="fileUploadFormComponent({
                    acceptedFileTypes: @js($getAcceptedFileTypes()),
                    imageEditorEmptyFillColor: @js($getImageEditorEmptyFillColor()),
                    imageEditorMode: @js($getImageEditorMode()),
                    imageEditorViewportHeight: @js($getImageEditorViewportHeight()),
                    imageEditorViewportWidth: @js($getImageEditorViewportWidth()),
                    deleteUploadedFileUsing: async (fileKey) => {
                        return await $wire.deleteUploadedFile(@js($statePath), fileKey)
                    },
                    getUploadedFilesUsing: async () => {
                        return await $wire.getFormUploadedFiles(@js($statePath))
                    },
                    hasImageEditor: @js($hasImageEditor),
                    hasCircleCropper: @js($hasCircleCropper),
                    canEditSvgs: @js($canEditSvgs()),
                    isSvgEditingConfirmed: @js($isSvgEditingConfirmed()),
                    confirmSvgEditingMessage: @js(__('filament-forms::components.file_upload.editor.svg.messages.confirmation')),
                    disabledSvgEditingMessage: @js(__('filament-forms::components.file_upload.editor.svg.messages.disabled')),
                    imageCropAspectRatio: @js($imageCropAspectRatio),
                    imagePreviewHeight: @js($getImagePreviewHeight()),
                    imageResizeMode: @js($getImageResizeMode()),
                    imageResizeTargetHeight: @js($imageResizeTargetHeight),
                    imageResizeTargetWidth: @js($imageResizeTargetWidth),
                    imageResizeUpscale: @js($getImageResizeUpscale()),
                    isAvatar: @js($isAvatar),
                    isDeletable: @js($isDeletable()),
                    isDisabled: @js($isDisabled),
                    isDownloadable: @js($isDownloadable()),
                    isMultiple: @js($isMultiple()),
                    isOpenable: @js($isOpenable()),
                    isPasteable: @js($isPasteable()),
                    isPreviewable: @js($isPreviewable()),
                    isReorderable: @js($isReorderable()),
                    itemPanelAspectRatio: @js($getItemPanelAspectRatio()),
                    loadingIndicatorPosition: @js($getLoadingIndicatorPosition()),
                    locale: @js(app()->getLocale()),
                    panelAspectRatio: @js($getPanelAspectRatio()),
                    panelLayout: @js($getPanelLayout()),
                    placeholder: @js($getPlaceholder()),
                    maxFiles: @js($getMaxFiles()),
                    maxSize: @js(($size = $getMaxSize()) ? "{$size}KB" : null),
                    minSize: @js(($size = $getMinSize()) ? "{$size}KB" : null),
                    mimeTypeMap: @js($getMimeTypeMap()),
                    maxParallelUploads: @js($getMaxParallelUploads()),
                    removeUploadedFileUsing: async (fileKey) => {
                        return await $wire.removeFormUploadedFile(@js($statePath), fileKey)
                    },
                    removeUploadedFileButtonPosition: @js($getRemoveUploadedFileButtonPosition()),
                    reorderUploadedFilesUsing: async (files) => {
                        return await $wire.reorderFormUploadedFiles(@js($statePath), files)
                    },
                    shouldAppendFiles: @js($shouldAppendFiles()),
                    shouldOrientImageFromExif: @js($shouldOrientImagesFromExif()),
                    shouldTransformImage: @js($imageCropAspectRatio || $imageResizeTargetHeight || $imageResizeTargetWidth),
                    state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$statePath}')") }},
                    uploadButtonPosition: @js($getUploadButtonPosition()),
                    uploadingMessage: @js($getUploadingMessage()),
                    uploadProgressIndicatorPosition: @js($getUploadProgressIndicatorPosition()),
                    uploadUsing: (fileKey, file, success, error, progress) => {
                        $wire.upload(
                            `{{ $statePath }}.${fileKey}`,
                            file,
                            () => {
                                success(fileKey)
                                // Add custom event to start auto-upload
                                $wire.dispatchFormEvent('autoupload-file--start-upload', '{{ $statePath }}', fileKey );
                            },
                            error,
                            (progressEvent) => {
                                progress(true, progressEvent.detail.progress, 100)
                            },
                        )
                    },
                })"
        wire:ignore
        {{
            $attributes
                ->merge([
                    'aria-labelledby' => "{$id}-label",
                    'id' => $id,
                    'role' => 'group',
                ], escape: false)
                ->merge($getExtraAttributes(), escape: false)
                ->merge($getExtraAlpineAttributes(), escape: false)
                ->class([
                    'fi-fo-file-upload flex flex-col gap-y-2 [&_.filepond--root]:font-sans',
                    match ($alignment) {
                        Alignment::Start, Alignment::Left => 'items-start',
                        Alignment::Center => 'items-center',
                        Alignment::End, Alignment::Right => 'items-end',
                        default => $alignment,
                    },
                ])
        }}
    >
        <div
            @class([
                'h-full',
                'w-32' => $isAvatar,
                'w-full' => ! $isAvatar,
            ])
        >
            <input
                x-ref="input"
                {{
                    $getExtraInputAttributeBag()
                        ->merge([
                            'aria-labelledby' => "{$id}-label",
                            'disabled' => $isDisabled,
                            'multiple' => $isMultiple(),
                            'type' => 'file',
                        ], escape: false)
                }}
            />
        </div>

        <div
            x-show="error"
            x-html="error"
            x-cloak
            class="text-sm text-danger-600 dark:text-danger-400"
        ></div>
    </div>
</x-dynamic-component>