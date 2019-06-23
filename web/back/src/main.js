import Vue from 'vue'
import LoginForm from './components/LoginForm.vue'
import AdminIndex from './components/AdminIndex.vue'
import MenuTop from './components/Menu.vue'
import AdminTypo from './components/AdminTypo.vue'
import AdminSinger from './components/AdminSinger.vue'
import AdminVote from './components/AdminVote.vue'
import AdminUser from './components/AdminUser.vue'
import Paginate from 'vuejs-paginate'

// Foundation start
import {Foundation} from 'foundation-sites/js/entries/plugins/foundation.core'
import {Keyboard} from 'foundation-sites/js/entries/plugins/foundation.util.keyboard'
import {MediaQuery} from 'foundation-sites/js/entries/plugins/foundation.util.mediaQuery'
import {Triggers} from 'foundation-sites/js/entries/plugins/foundation.util.triggers'
import {Motion} from 'foundation-sites/js/entries/plugins/foundation.util.motion'
import {Reveal, ResponsiveToggle, Abide} from 'foundation-sites/js/entries/foundation-plugins'

import './assets/scss/style.scss'
import $ from 'jquery'

require('motion-ui')
require('what-input')


Foundation.addToJquery($)
Foundation.Keyboard = Keyboard
Foundation.MediaQuery = MediaQuery
Foundation.Motion = Motion

Triggers.init($, Foundation)

Foundation.plugin(Reveal, 'Reveal')
Foundation.plugin(ResponsiveToggle, 'ResponsiveToggle')
Foundation.plugin(Abide, 'Abide')
// Foundation end

Vue.config.devtools = true;
Vue.component('paginate', Paginate)
new Vue({
    el: '#app',
    components: {LoginForm, AdminIndex, MenuTop, AdminTypo, AdminSinger, AdminVote, AdminUser}
})


