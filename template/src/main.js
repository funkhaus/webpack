import './main.css'
import transitionPageFadeCMP from 'src/components/transitions/PageFade.vue'
import RspImageCMP from 'src/components/shared/ResponsiveImg.vue'
import inViewCMP from 'src/components/shared/InView.vue'
import App from './components/App.vue'
import Vue from 'vue'
import router from './router'

// register global components
Vue.component('transition-page-fade', transitionPageFadeCMP)
Vue.component('responsive-image', RspImageCMP)
Vue.component('in-view', inViewCMP)

// boot app
new Vue({
    el: '#app',
    {{#router}}
    router,
    {{/router}}
    {{#if_eq build "runtime"}}
    render: h => h(App),
    {{/if_eq}}
    {{#if_eq build "standalone"}}
    template: '<App/>',
    components: { App },
    {{/if_eq}}
})
