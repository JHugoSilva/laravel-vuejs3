import './bootstrap';

import "admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js"
import "admin-lte/dist/js/adminlte.js"

import { createApp } from 'vue/dist/vue.esm-bundler'
import { createRouter, createWebHistory } from 'vue-router'

import Routes from './routes.js'

const app = createApp({})

const router = createRouter({
    history:createWebHistory(),
    routes: Routes,
})

app.use(router)

app.mount('#app')
