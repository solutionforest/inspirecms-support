import defaultPreset from './vendor/filament/support/tailwind.config.preset.js'
import defaultTheme from 'tailwindcss/defaultTheme'

defaultPreset.theme.extend.fontFamily = {
    sans: ['var(--font-family)', ...defaultTheme.fontFamily.sans],
}

export default defaultPreset
