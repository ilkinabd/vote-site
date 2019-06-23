<template>
    <div class="form form-singer">
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Участники</legend>
                    <div class="grid-x grid-padding-x" :key="index" v-for="(singer,key,index) in singers">
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">Фото участника<br>
                                    <span>Рекомендуемый размер</span> <b>270px X 270px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singers[key]['img64']" alt="">
                                </p>
                                <p>
                                    <label :for="`singer-${singers[key]['id']}-pic`"
                                           class="button expanded">Загрузить</label>
                                    <input :id="`singer-${singers[key]['id']}-pic`" type="file" class="show-for-sr"
                                           @change.prevent="singers[key]['img'] = $event.target.files[0]">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-8">
                            <div class="grid-x grid-padding-x">
                                <div class="cell">
                                    <p>
                                        <span>Город:</span>
                                        <input type="text" v-model="singers[key]['city']" class="input input-shadow">
                                    </p>
                                </div>
                                <div class="cell large-4">
                                    <p>
                                        <span>Имя:</span>
                                        <input type="text" v-model="singers[key]['name']" class="input input-shadow">
                                    </p>
                                </div>
                                <div class="cell large-4">
                                    <p>
                                        <span>Фамилия:</span>
                                        <input type="text" v-model="singers[key]['surname']" class="input input-shadow">
                                    </p>
                                </div>
                                <div class="cell large-4">
                                    <p>
                                        <span>Возраст:</span>
                                        <input type="text" v-model="singers[key]['age']" class="input input-shadow">
                                    </p>
                                </div>
                                <div class="cell">
                                    <span>Песня:</span>
                                    <div class="grid-x grid-padding-x">
                                        <div class="cell large-6">
                                            <input class="input-group-field" type="text" placeholder="Название песни"
                                                   v-model="singers[key]['music_name']">
                                        </div>
                                        <div class="cell large-3">
                                            <label :for="`singer-${singers[key]['id']}-music`" class="button expanded">Загрузить</label>
                                            <input :id="`singer-${singers[key]['id']}-music`" type="file"
                                                   class="show-for-sr"
                                                   @change.prevent="singers[key]['music'] = $event.target.files[0]">
                                        </div>
                                        <div v-if="singers[key]['music_link']" class="cell large-3">
                                            <button class="button expanded play"
                                                    @click.prevent="playMusic(singers[key]['music_link'],$event.target)">
                                                Прослушать
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="cell large-3 large-offset-3">
                            <p class="singer__save">
                                <a href="#" class="button expanded button-save" style="margin-top: 16px"
                                   @click.prevent="setSinger(singers[key],$event.target)">Сохранить</a>
                                <img src="./../assets/images/checked.svg" alt="">
                            </p>
                        </div>
                        <div class="cell large-3">
                            <p class="singer__save">
                                <a href="#" class="button expanded button-exit" style="margin-top: 16px"
                                   @click.prevent="deleteSinger(singers[key],$event.target)">Удалить</a>
                                <img src="./../assets/images/checked.svg" alt="">
                            </p>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" class="button expanded button-save"
                           @click.prevent="showSingerModal()">Добавить учасника</a>
                    </p>
                </fieldset>
            </div>
        </div>
        <div class="reveal modal modal-singer" data-reveal data-close-on-click="false">
            <form action="" data-abide novalidate>
                <p class="modal__title">Добавить участника</p>
                <div class="grid-x grid-padding-x">
                    <div class="cell">
                        <p>
                            <span>Имя</span>
                            <input v-model="singer.name" type="text" class="input input-shadow" required>
                        </p>
                    </div>
                    <div class="cell">
                        <p>
                            <span>Фамилия</span>
                            <input v-model="singer.surname" type="text" class="input input-shadow" required>
                        </p>
                    </div>
                    <div class="cell large-6">
                        <p>
                            <span>Фото png</span>
                            <label for="modal-pic" class="button expanded">Загрузить</label>
                            <input id="modal-pic" type="file" aria-errormessage="pic-error" required
                                   class="show-for-sr" pattern="image_only"
                                   @change.prevent="singer.img = $event.target.files[0]">
                            <span class="form-error" id="pic-error">Не загружен</span>
                            <!--@change.prevent="singers[key]['img'] = $event.target.files[0]">-->
                        </p>
                    </div>
                    <div class="cell large-6">
                        <p>
                            <span>Песня mp3</span>
                            <label for="modal-song" class="button expanded">Загрузить</label>
                            <input id="modal-song" aria-errormessage="song-error" type="file" required
                                   class="show-for-sr" pattern="music_only"
                                   @change.prevent="singer.music = $event.target.files[0]">
                            <span class="form-error" id="song-error">Не загружен</span>
                            <!--@change.prevent="singers[key]['img'] = $event.target.files[0]">-->
                        </p>
                    </div>
                    <div class="cell">
                        <p>
                            <span>Город</span>
                            <input v-model="singer.city" type="text" class="input input-shadow" required>
                        </p>
                    </div>
                    <div class="cell">
                        <p>
                            <span>Возраст</span>
                            <input v-model="singer.age" type="text" class="input input-shadow" required>
                        </p>
                    </div>
                    <div class="cell">
                        <p>
                            <span>Название песни</span>
                            <input v-model="singer.music_name" type="text" class="input input-shadow" required>
                        </p>
                    </div>
                    <div class="cell large-8">
                        <button class="button expanded button-save" @click.prevent="addSinger(singer)">Добавить</button>
                    </div>
                    <div class="cell large-4">
                        <button type="button" data-close class="button expanded button-exit">Закрыть</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
    import $ from 'jquery'
    import {Foundation} from 'foundation-sites/js/entries/plugins/foundation.core'

    const PLAYMUSIC = 'Прослушать';
    const STOPMUSIC = 'Пауза';
    export default {
        name: 'AdminSinger',
        data() {
            return {
                singers: [],
                singer:{
                    name:null,
                    surname:null,
                    music_name:null,
                    age:null,
                    city:null,
                    img:null,
                    music:null
                },
                modal: null,
                audioContainer: document.createElement("AUDIO")
            }
        },
        mounted() {
            console.log('Mounted');
            Foundation.Abide.defaults.patterns['image_only'] = /^.+?\.(png)$/
            Foundation.Abide.defaults.patterns['music_only'] = /^.+?\.(mp3)$/
            let modal = this.$el.querySelector('[data-reveal]');
            let form = this.$el.querySelector('[data-abide]');
            new Foundation.Reveal($(modal), {});
            new Foundation.Abide($(form), {});
            this.modal = $(modal);
            this.form = $(form);
            this.getSinger();
        },
        methods: {
            playMusic: function (musicLink, target) {
                if ($(target).hasClass('playing')) {
                    $(target).removeClass('playing');
                    target.innerHTML = PLAYMUSIC;
                    this.audioContainer.pause();
                } else {
                    $('.play').not(target).removeClass('playing').html(PLAYMUSIC);
                    $(target).addClass('playing').html(STOPMUSIC);
                    console.log('playing');
                    if (!this.audioContainer.paused) this.audioContainer.pause();
                    this.audioContainer.src = musicLink;
                    this.audioContainer.load()
                    this.audioContainer.play().catch(function (err) {
                        alert(err.message);
                    });
                }
            },
            validateForm: function () {
                this.form.foundation('validateForm');
                return this.form.find('.is-invalid-input').length === 0;
            },
            addSinger: function (obj) {
                if (this.validateForm()) {
                    console.log('submitting');
                    let fd = new FormData;

                    for (let field in obj) {
                        if (obj.hasOwnProperty(field)) {
                            if (obj[field]) {
                                fd.append(field, obj[field])
                            }
                        }
                    }

                    $.ajax({
                        url: '/ajax/singer/add',
                        data: fd,
                        processData: false,
                        contentType: false,
                        type: 'POST',
                        success: (data) => {
                            this.modal.foundation('close');
                            alert('Успех');
                            this.getSinger();
                        }
                    });
                }
            },
            showSingerModal: function (obj) {
                this.form.foundation('resetForm');
                this.modal.foundation('open');
            },
            deleteSinger: function (obj,target) {
                let button = $(target);
                let statusOk = $(target).parent().find('img');
                button.text('Подождите...')
                let fd = new FormData;

                for (let field in obj) {
                    if (obj.hasOwnProperty(field) &&
                        field !== 'img64' && field !== 'music_link') {
                        if (obj[field]) {
                            fd.append(field, obj[field])
                        }
                    }

                }

                $.ajax({
                    url: '/ajax/singer/delete',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        button.text('Сохранено');
                        statusOk.fadeIn('slow').fadeOut('slow',()=>{
                            button.text('Сохранить');
                        })
                        this.getSinger();
                    }
                });

            },
            setSinger: function (obj,target) {
                let button = $(target);
                let statusOk = $(target).parent().find('img');
                button.text('Подождите...')

                let fd = new FormData;

                for (let field in obj) {
                    if (obj.hasOwnProperty(field) &&
                        field !== 'img64' && field !== 'music_link') {
                        if (obj[field]) {
                            fd.append(field, obj[field])
                        }
                    }

                }

                $.ajax({
                    url: '/ajax/singer',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        button.text('Сохранено');
                        statusOk.fadeIn('slow').fadeOut('slow',()=>{
                            button.text('Сохранить');
                        })
                        this.getSinger();
                    }
                });

            },
            getSinger: function () {
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

</style>