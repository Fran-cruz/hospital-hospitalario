import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

export default createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'light',
        themes: {
            light: {
                colors: {
                    primary:   '#4f46e5',  // indigo-600
                    secondary: '#0d9488',  // teal-600
                    accent:    '#f59e0b',  // amber-500
                    error:     '#ef4444',
                    success:   '#16a34a',
                    warning:   '#d97706',
                },
            },
        },
    },
})
