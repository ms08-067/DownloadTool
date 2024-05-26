/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */
require('./app_echo_bootstrap');

//window.Vue = require('vue');
//import Vue from 'vue';

/**
 * The following block of code may be used to automatically register your
 * Vue components. It will recursively scan this directory for the Vue
 * components and automatically register them with their "basename".
 *
 * Eg. ./components/ExampleComponent.vue -> <example-component></example-component>
 */

/** Thanks Huy!*/
// const files = require.context('./', true, /\.vue$/);
// /**console.log(files.keys());*/
// files.keys().forEach(key => {
//     /** console.log('component name', key.split('/').pop());*/
//     /**console.log(files(key));*/
//     Vue.component(key.split('/').pop().split('.')[0], files(key).default);
// });
 
/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

// const app = new Vue({
//   el: '#app'
// });

import { createApp } from "vue";

const app = createApp({});

app.mount("#app");

//  Vue.component('button-counter', {
//   data: function() {
//     return {
//       count: 0
//     }
//   },
//   template: '<button v-on:click="count++">You clicked me {{ count }} times.</button>'
// });


// Vue.component('ExampleComponent', {
//   template: '<h1>rwreaw</h1>',
//   mounted() {
//       console.log('Component mounted.')
//   },
//   name: 'ExampleComponent',
// });