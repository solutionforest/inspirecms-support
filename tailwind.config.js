const preset = './tailwind.config.preset'

module.exports = {
    presets: [preset],
    content: [
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
