<template>
    <div class="top-nav">
        <div id="responsive-toggle" class="title-bar" data-responsive-toggle="example-menu" data-hide-for="medium">
            <button class="menu-icon" type="button" data-toggle="example-menu"></button>
            <div class="title-bar-title">Menu</div>
        </div>
        <div class="top-bar" id="example-menu">
            <div class="top-bar-left">
                <ul class="menu">
                    <li class="menu-text">{{userName}} {{userSurname}}</li>
                    <li v-for="item in menuItems">
                        <a :class="currentPage === item.href ? 'active':''" :href="item.href">{{item.name}}</a>
                    </li>
                </ul>
            </div>
            <div class="top-bar-right">
                <ul class="menu">
                    <li v-if="pollStatus !== null">
                        <a @click.prevent="updatePollStatus()"
                           :class="['button',{'button-start':pollStatus === 0,'alert': pollStatus === 1}]">
                            {{pollStatus === 1 ? pollStopText : pollStartText}}
                        </a>
                    </li>
                    <li style="margin-left: 16px">
                        <a class="logout" :href="logoutPath"><img src="./../assets/images/logout.svg" alt=""></a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>

<script>
    import {Foundation} from 'foundation-sites/js/entries/plugins/foundation.core'
    import $ from 'jquery'

    export default {
        name: 'MenuTop',
        props: ['userName', 'userSurname', 'userRoles', 'logoutPath', 'currentPage'],
        data() {
            return {
                title: 'Login page',
                responsiveToggle: null,
                pollStatus: null,
                pollStartText: 'Старт голосования',
                pollStopText: 'Стоп голосование',
                roleMenu: {
                    'ROLE_ADMIN': [
                        {
                            name: 'Оформление',
                            href: '/admin/typo'
                        },
                        {
                            name: 'Участники',
                            href: '/admin/singer'
                        },
                        {
                            name: 'Управление голосованием',
                            href: '/admin/vote'
                        },
                        {
                            name: 'Управление пользователями',
                            href: '/admin/user'
                        }
                    ],
                    'ROLE_MANAGER': [
                        {
                            name: 'Оформление',
                            href: '/admin/typo'
                        },
                        {
                            name: 'Участники',
                            href: '/admin/singer'
                        },
                        {
                            name: 'Управление голосованием',
                            href: '/admin/vote'
                        }
                    ]
                },
                menuItems: []
            }
        },
        mounted() {
            if (this.roleMenu.hasOwnProperty(this.userRoles[0])) {
                this.menuItems = this.roleMenu[this.userRoles[0]];
            }
            this.responsiveToggle = new Foundation.ResponsiveToggle($('#responsive-toggle'), {});
            this.getPollStatus();
        },
        methods: {
            updatePollStatus() {
                if (this.pollStatus !== null) {
                    let fd = new FormData;
                    let pollStatus = this.pollStatus === 0 ? '1' : '0';
                    fd.append('poll_status', pollStatus);
                    $.ajax({
                        url: '/ajax/poll/update',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: (data) => {
                            this.getPollStatus();
                        }
                    });
                }
            },
            getPollStatus() {
                $.ajax({
                    url: '/poll/status',
                    processData: false,
                    contentType: false,
                    type: 'GET',
                    success: (data) => {
                        this.pollStatus = data.status
                    }
                });
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .top-bar {
        background-color: #2d353c;
    }

    .menu .menu-text {
        color: #fff;
        padding: 8px;
    }

    .top-bar ul {
        background-color: #2d353c;
    }

    .button.alert {
        background-color: #ff5b57;
        color: #fff;
        font-size: 16px;
    }

    .button.alert:hover,
    .button.alert:focus {
        background-color: #e95551;
    }

    .top-bar ul > li > a {
        color: #889097;
        padding: 8px;
        // font-size: 14px;
    }

    .top-bar ul > li > a.active {
        font-weight: bold;
        color: #fff;
    }

    .top-bar ul > li > a:hover {
        color: #fff;
    }

    .top-bar ul > li > a.button-start {
        color: #fff;
        font-size: 16px;
    }

    .logout {
        opacity: 0.5;
    }

    .logout img {
        width: 20px;
        height: 20px;
    }

    .logout:hover {
        opacity: 1;
    }
</style>
