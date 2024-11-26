@props(['sidebar', 'main' => null])
<div 
    {{ $attributes->merge([
        'class' => 'tree-node-layout',
    ]) }}
>
    <div class="tree-node-sidebar-container">
        <div {{ $sidebar->attributes->class(['tree-node-sidebar']) }}>
            {{ $sidebar }}
        </div>
    </div>
    
    <div class="tree-node-main-container">
        @isset($main)
            <div {{ $main->attributes->class(['tree-node-main']) }}>
                {{ $main }}
            </div>
        @endisset
    </div>
</div>