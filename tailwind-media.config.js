const preset = './tailwind.config.preset'

module.exports = {
    presets: [preset],
    content: [
        './resources/views/components/media-library**/*.blade.php',
        './resources/views/livewire/**/*.blade.php',
        './resources/views/forms/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        "./node_modules/flowbite/**/*.js",
    ],
}
