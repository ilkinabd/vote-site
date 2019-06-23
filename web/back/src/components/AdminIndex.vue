<template>
    <div class="admin admin-index">
        <p>{{title}}</p>
    </div>
</template>

<script>
    export default {
        name: 'AdminIndex',
        // props: ['error', 'lastUsername', 'action'],
        data() {
            return {
                title: 'Admin index page'
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .login {
        margin-top: 128px;
    }

    .login__title {
        font-size: 18px;
        text-align: center;
        font-weight: bold;
    }

    .login label > span {
        display: inline-block;
        margin: 8px 0;
    }
</style>
