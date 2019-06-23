<template>
    <div class="login">
        <div class="grid-x grid-padding-x align-center-middle">
            <div class="cell large-4">
                <div class="card callout">
                    <form :action="action" method="post">
                        <p class="login__title">Авторизация</p>
                        <div v-if="error" class="callout alert">
                            <p class="error">
                                {{ error }}
                            </p>
                        </div>
                        <p>
                            <span>Логин:</span>
                            <input id="login" class="input input-shadow" type="text" name="_username" :value="lastUsername"/>
                        </p>
                        <p>
                            <span>Пароль:</span>
                            <input id="pass" class="input input-shadow" type="password" name="_password" value=""/>
                        </p>
                        <p style="text-align: center;margin-bottom: 0">
                            <input class="button" type="submit" value="Войти"/>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        name: 'LoginForm',
        props: ['error', 'lastUsername', 'action'],
        data() {
            return {
                title: 'Login page'
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

    .button{
        min-width: 200px;
        margin-bottom: 0;
    }

    .login label > span {
        display: inline-block;
        margin: 8px 0;
    }
</style>
