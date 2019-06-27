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
                    <li v-if="pollStatus === POLL_STARTED">
                        <a class="button alert" @click.prevent="openWinnerModal()">
                            {{pollWinnerText}}
                        </a>
                    </li>
                    <li v-else-if="pollStatus === POLL_WINNER_SELECTED" class="flex-container">
                        <a class="button alert" @click.prevent="openWinnerModal()">
                            {{pollWinnerText}}
                        </a>
                        <a class="button alert" style="margin-left: 16px"
                           @click.prevent="updatePollStatus(POLL_STOPPED)">
                            {{pollStopText}}
                        </a>
                    </li>
                    <li v-else-if="pollStatus === POLL_STOPPED">
                        <a @click.prevent="updatePollStatus(POLL_STARTED)" class="button button-start">
                            {{pollStartText}}
                        </a>
                    </li>
                    <li style="margin-left: 16px">
                        <a class="logout" :href="logoutPath"><img src="./../assets/images/logout.svg" alt=""></a>
                    </li>
                </ul>
            </div>
        </div>
        <div class="reveal modal" id="modal-winner-select" data-close-on-click="false">
            <form action="" data-abide novalidate>
                <p class="modal__title">Выбрать победителя</p>
                <div class="grid-x grid-padding-x">
                    <div class="cell">
                        <p>
                            <select v-model="winnerId">
                                <option v-for="(singer,key,index) in singers" :value="singer.id">
                                    {{singer.name}} {{ singer.surname }}</option>
                            </select>
                        </p>
                    </div>
                    <div class="cell large-6">
                        <button class="button expanded button-save" @click.prevent="selectWinner()">Подтвердить</button>
                    </div>
                    <div class="cell large-6">
                        <button type="button" data-close class="button expanded button-exit">Закрыть</button>
                    </div>
                </div>
            </form>
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
                POLL_STARTED:1,
                POLL_STOPPED:0,
                POLL_WINNER_SELECTED:2,
                title: 'Login page',
                responsiveToggle: null,
                pollStatus: null,
                pollStartText: 'Старт голосования',
                pollWinnerText: 'Выбрать победителя',
                pollStopText: 'Стоп голосование',
                winnerId:null,
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
                singers:[],
                modal:null,
                menuItems: []
            }
        },
        mounted() {
            if (this.roleMenu.hasOwnProperty(this.userRoles[0])) {
                this.menuItems = this.roleMenu[this.userRoles[0]];
            }
            this.responsiveToggle = new Foundation.ResponsiveToggle($('#responsive-toggle'), {});
            let modal = this.$el.querySelector('#modal-winner-select');
            new Foundation.Reveal($(modal), {});
            this.modal = $(modal);
            this.getPollStatus();
            this.getSinger();
        },
        methods: {
            updatePollStatus(statusCode){
                let fd = new FormData;
                fd.append('poll_status', statusCode);
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
            },
            openWinnerModal() {
                // Open modal form
                this.modal.foundation('open');
            },
            selectWinner(){
                if(!this.winnerId){
                    alert('Победитель не выбран');
                    return false;
                }else{
                    let fd = new FormData;
                    fd.append('winner_id', this.winnerId);
                    $.ajax({
                        url: '/ajax/poll/winner',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: (data) => {
                            this.updatePollStatus(this.POLL_WINNER_SELECTED);
                            this.modal.foundation('close');
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
            },
            getSinger () {
                $.ajax({
                    url: '/ajax/singer',
                    processData: false,
                    contentType: false,
                    type: 'GET',
                    success: (data) => {
                        this.singers = data
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
