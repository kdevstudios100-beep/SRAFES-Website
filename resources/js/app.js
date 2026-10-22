import Alpine from 'alpinejs'
import persist from '@alpinejs/persist'
import AlpineFloatingUI from '@awcodes/alpine-floating-ui'

if (!window.Alpine) {
    window.Alpine = Alpine
}

Alpine.plugin(persist)
Alpine.plugin(AlpineFloatingUI)

Alpine.start()
