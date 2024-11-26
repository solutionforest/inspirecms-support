import defaultPreset from './vendor/filament/support/tailwind.config.preset'
import defaultTheme from 'tailwindcss/defaultTheme'

defaultPreset.theme.extend.fontFamily = {
    sans: ['var(--font-family)', ...defaultTheme.fontFamily.sans],
}

export default defaultPreset
