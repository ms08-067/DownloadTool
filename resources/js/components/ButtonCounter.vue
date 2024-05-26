<template>
    <button class="unselectable" v-on:mousedown="count++">You clicked me {{ count }} times.</button>
</template>

<script>
    export default {
        /**template: '<h1>dasdas</h1>',*/
        mounted() {
            /**console.log('Component mounted.');*/
        },
        name: 'ButtonCounter',
        data: function() {
            return {
                count: 0
            }
        },
    }
</script>
