<template>
    <div class="vote">
        <p>Vote page</p>
        <table>
            <thead>
            <tr>
                <th>#</th>
                <th>Имя</th>
                <th>Фамилия</th>
                <th>Кол. голосов</th>
                <th>Действие</th>
            </tr>
            </thead>
            <tbody>
            <tr :key="index" v-for="(vote,key,index) in votes">
                <td>{{vote.id}}</td>
                <td>{{vote.name}}</td>
                <td>{{vote.surname}}</td>
                <td>{{vote.vote_count}}</td>
                <td>
                    <button @click.prevent="openModalWarning(vote)" class="button button-exit button-icon"
                            title="Подозрительные голоса">
                        <i class="fi-alert"></i>
                    </button>
                    <button class="button button-save button-icon" @click.prevent="openModalVoteAdd(vote)"
                            title="Добавить голоса">
                        <i class="fi-plus"></i>
                    </button>
                    <button class="button button-icon" @click.prevent="openModalVoteSubtract(vote)"
                            title="Удалить Голоса">
                        <i class="fi-minus"></i>
                    </button>
                </td>
            </tr>
            </tbody>
        </table>
        <div id="modal-warning" class="reveal modal modal-singer" data-reveal data-close-on-click="false">
            <p class="modal__title">Подозрительные голоса {{singer.name}}</p>
            <table v-if="loading === false">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Имя</th>
                    <th>Фамилия</th>
                    <th>Соц сеть</th>
                    <th>Разница</th>
                </tr>
                </thead>
                <tbody>
                <tr :key="index" v-for="(item,key,index) in sus">
                    <td>{{item.id}}</td>
                    <td>{{item.first_name}}</td>
                    <td>{{item.last_name}}</td>
                    <td>{{item.auth_type}}</td>
                    <td>{{item.diff}} сек</td>
                </tr>
                </tbody>
            </table>
            <p v-else style="text-align: center">Подождите...</p>
            <paginate v-if="pageCount > 1"
                      v-model="page"
                      :page-count="pageCount"
                      :click-handler="pageClick"
                      :prev-text="'Prev'"
                      :next-text="'Next'"
                      :prev-class="'pager-prev'"
                      :next-class="'pager-next'"
                      :container-class="'pager'">
            </paginate>
            <p style="text-align: center">
                <button type="button" data-close class="button button-exit">Закрыть</button>
                <button type="button" @click.prevent="cancelVotes()" class="button">Отменить голоса</button>
            </p>
        </div>
        <div id="modal-vote-add" class="reveal modal modal-singer" data-reveal data-close-on-click="false">
            <p class="modal__title">Добавить голоса {{singer.name}}</p>
            <form action="">
                <label>
                    Кол. голосов
                    <input type="number" min="1" v-model.number="voteCount">
                </label>
            </form>
            <p style="text-align: center">
                <button type="button" data-close class="button button-exit">Закрыть</button>
                <button type="button" @click.prevent="addVotes()" class="button">Применить</button>
            </p>
        </div>
        <div id="modal-vote-subtract" class="reveal modal modal-singer" data-reveal data-close-on-click="false">
            <p class="modal__title">Удалить голоса {{singer.name}}</p>
            <form action="">
                <label>
                    Кол. голосов
                    <input type="number" min="1" v-model.number="voteCount">
                </label>
            </form>
            <p style="text-align: center">
                <button type="button" data-close class="button button-exit">Закрыть</button>
                <button type="button" @click.prevent="subtractVotes()" class="button">Применить</button>
            </p>
        </div>
    </div>
</template>

<script>
    import $ from 'jquery'
    import {Foundation} from 'foundation-sites/js/entries/plugins/foundation.core'

    export default {
        name: 'AdminVote',
        data() {
            return {
                votes: [],
                sus: [],
                vote: {
                    id: null,
                    music_name: null,
                    name: null,
                    surname: null,
                    vote_count: null
                },
                voteCount: 1,
                page: 1,
                pageCount: 0,
                singer: {},
                modalWarning: null,
                modalVoteAdd: null,
                modalVoteSubtract: null,
                loading: false
            }
        },
        mounted() {
            let modalWarning = this.$el.querySelector('#modal-warning');
            let modalVoteAdd = this.$el.querySelector('#modal-vote-add');
            let modalVoteSubtract = this.$el.querySelector('#modal-vote-subtract');

            new Foundation.Reveal($(modalWarning), {});
            new Foundation.Reveal($(modalVoteAdd), {});
            new Foundation.Reveal($(modalVoteSubtract), {});

            this.modalWarning = $(modalWarning);
            this.modalVoteAdd = $(modalVoteAdd);
            this.modalVoteSubtract = $(modalVoteSubtract);

            this.getVotes();
        },
        methods: {
            openModalWarning(obj) {
                this.singer = obj;
                this.modalWarning.foundation('open');
                this.page = 1;
                this.pageCount = 0;
                this.getSuspicious();
            },
            openModalVoteAdd(obj) {
                this.voteCount = 1;
                this.singer = obj;
                this.modalVoteAdd.foundation('open');
            },
            openModalVoteSubtract(obj) {
                this.voteCount = 1;
                this.singer = obj;
                this.modalVoteSubtract.foundation('open');
            },
            getSuspicious() {
                this.loading = true
                let fd = new FormData;
                fd.append('singer_id', this.singer.id)
                $.ajax({
                    url: `/ajax/suspicious/${this.page}`,
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        this.pageCount = data.page_count;
                        this.page = data.current_page;
                        this.sus = data.records;
                        this.loading = false;
                    }
                });
            },
            cancelVotes() {
                let fd = new FormData;
                fd.append('singer_id', this.singer.id)
                $.ajax({
                    url: '/ajax/vote/cancel',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        alert('Успех');
                        this.modalWarning.foundation('close');
                        this.getVotes();
                    }
                });
            },
            subtractVotes() {
                if (this.voteCount <= 0) {
                    alert('Введите кол. голоса')
                    return false;
                }
                if (this.voteCount > this.singer.vote_count) {
                    alert(`Можно удалить максимум ${this.singer.vote_count} голосов`)
                    return false;
                }
                let fd = new FormData;
                fd.append('singer_id', this.singer.id)
                fd.append('vote_count', this.voteCount)
                $.ajax({
                    url: '/ajax/vote/subtract',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        alert('Успех');
                        this.modalVoteSubtract.foundation('close');
                        this.getVotes();
                    }
                });
            },
            addVotes() {
                if (this.voteCount <= 0) {
                    alert('Введите кол. голосов')
                    return false;
                }
                let fd = new FormData;
                fd.append('singer_id', this.singer.id)
                fd.append('vote_count', this.voteCount)
                $.ajax({
                    url: '/ajax/vote/add',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        alert('Успех');
                        this.modalVoteAdd.foundation('close');
                        this.getVotes();
                    }
                });
            },
            getVotes() {
                $.ajax({
                    url: '/ajax/vote',
                    processData: false,
                    contentType: false,
                    type: 'GET',
                    success: (data) => {
                        this.votes = data;
                    }
                });
            },
            pageClick(pageNum) {
                this.getSuspicious();
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .button-icon {
        margin-bottom: 0;
        border-radius: 0;
        padding: 8px;
    }
</style>