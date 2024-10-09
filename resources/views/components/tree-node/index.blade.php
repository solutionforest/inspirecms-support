@props(['mainViewContent' => null])
<div 
    {{ $attributes->merge([
        'class' => 'tree-node-layout',
    ]) }}
>

    <div class="tree-node-sidebar">
        {{ $slot }}
    </div>
    
    <div class="tree-node-main">
        @isset($mainViewContent)
            {{ $mainViewContent }}
        @endisset
    </div>
</div>