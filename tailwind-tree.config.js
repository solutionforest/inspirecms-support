const preset = './tailwind.config.preset'

module.exports = {
    presets: [preset],
    content: [
        './resources/views/components/file-explorer/**/*.blade.php',
        './resources/views/components/model-explorer/**/*.blade.php',
        './resources/views/file-explorer.blade.php',
        './resources/views/model-explorer.blade.php',
    ],
}
