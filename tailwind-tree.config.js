const preset = './tailwind.config.preset'

module.exports = {
    presets: [preset],
    content: [
        './resources/views/components/tree-node/**/*.blade.php',
        './resources/views/tree-node/**/*.blade.php'
    ],
}
