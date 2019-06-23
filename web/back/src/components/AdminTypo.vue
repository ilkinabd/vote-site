<template>
    <form class="form form-typo" action="">
        <div class="grid-x grid-padding-x grid-padding-y">
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Логотипы партнеров</legend>
                    <div class="grid-x grid-padding-x">
                        <div v-for="(partner,key,index) in partners" :key="index" class="cell large-4">
                            <div class="card callout">
                                <button class="close-button" aria-label="Close alert" type="button" data-close
                                        @click.prevent="deletePartner(partner)">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                                <p class="card__title">Партнер - {{key + 1}}<br>
                                    <span>Рекомендуемый размер</span> <b>280px X 280px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="partner.img64" alt="">
                                </p>
                                <div class="input-group">
                                    <span class="input-group-label">Ссылка</span>
                                    <input class="input-group-field" type="text" placeholder="Партнер - 1"
                                           v-model="partner.link">
                                </div>
                                <p>
                                    <label :for="`partner-${key}-pic`" class="button expanded">Загрузить</label>
                                    <input :id="`partner-${key}-pic`" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],partner,'img','img64')">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save grid-x grid-padding-x">
                            <span class="cell large-6">
                                    <a href="#" class="button expanded"
                                       @click.prevent="addPartner($event.target)">Добавить</a>
                            </span>
                        <span class="cell large-6"><a href="#" class="button expanded button-save"
                                                      @click.prevent="setPartners($event.target)">Сохранить</a>
                            </span>
                    </p>
                </fieldset>
            </div>
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Лого сайта</legend>
                    <div class="grid-x grid-padding-x">
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">Левый элемент<br>
                                    <span>Рекомендуемый размер</span> <b>43px X 122px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="logo.logoLeftImg64" alt="">
                                </p>
                                <p>
                                    <label for="logo-left-pic" class="button expanded">Загрузить</label>
                                    <input id="logo-left-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],logo,'logoLeftImg','logoLeftImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">Лого<br>
                                    <span>Рекомендуемый размер</span> <b>522px X 203px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="logo.logoImg64" alt="">
                                </p>
                                <p>
                                    <label for="logo-pic" class="button expanded">Загрузить</label>
                                    <input id="logo-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],logo,'logoImg','logoImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">Правый элемент<br>
                                    <span>Рекомендуемый размер</span> <b>43px X 122px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="logo.logoRightImg64" alt="">
                                </p>
                                <p>
                                    <label for="logo-right-pic" class="button expanded">Загрузить</label>
                                    <input id="logo-right-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],logo,'logoRightImg','logoRightImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell">
                            <div class="card callout">
                                <p class="card__title">Фон<br>
                                    <span>Рекомендуемый размер</span> <b>353px X 336px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="logo.logoBgImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-logo-bg-img" type="checkbox" v-model="logo.logoHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-logo-bg-img">Спрятать</label>
                                </p>
                                <p>
                                    <label for="logo-bg-pic" class="button expanded">Загрузить</label>
                                    <input id="logo-bg-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],logo,'logoBgImg','logoBgImg64')">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" class="button expanded button-save" @click.prevent="setConfig(logo,$event.target)">Сохранить</a>
                        <img src="./../assets/images/checked.svg" alt="">
                    </p>
                </fieldset>
            </div>
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Авторизация через соцсети</legend>
                    <div class="grid-x grid-padding-x">
                        <div class="cell">
                            <div class="card callout">
                                <div class="input-group">
                                    <span class="input-group-label">Текст до авторизации:</span>
                                    <input class="input-group-field" type="text" placeholder="Партнер - 1"
                                           v-model="social.authTextBefore">
                                </div>
                            </div>
                        </div>
                        <div class="cell">
                            <div class="card callout">
                                <div class="input-group">
                                    <span class="input-group-label">Текст после авторизации:</span>
                                    <input class="input-group-field" type="text" placeholder="Партнер - 1"
                                           v-model="social.authTextAfter">
                                </div>
                            </div>
                        </div>
                        <div class="cell">
                            <div class="card callout">
                                <div class="input-group">
                                    <span class="input-group-label">Текст кнопки выход: </span>
                                    <input class="input-group-field" type="text" v-model="social.exitButtonText">
                                </div>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет блока авторизированного пользователя</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="social.authBgColor">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="social.authBgColor">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет текста блока авторизированного пользователя</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="social.authTextColor">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="social.authTextColor">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет кнопки «выход»</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="social.exitButtonBgColor">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="social.exitButtonBgColor">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>
                                    Цвет текста кнопки «выход»
                                </p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="social.exitButtonColorInactive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="social.exitButtonColorInactive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>
                                    Цвет текста кнопки «выход» при наведении
                                </p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="social.exitButtonColorActive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="social.exitButtonColorActive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка VK после авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>48px X 22px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.iconVkImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-vk-icon" class="button expanded">Загрузить</label>
                                    <input id="social-vk-icon" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'iconVkImg','iconVkImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка OK после авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>48px X 22px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.iconOkImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-ok-icon" class="button expanded">Загрузить</label>
                                    <input id="social-ok-icon" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'iconOkImg','iconOkImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка MR после авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>48px X 22px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.iconMrImg64" alt="">
                                </p>
                                <p>
                                    <label for="logo-mr-icon" class="button expanded">Загрузить</label>
                                    <input id="logo-mr-icon" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'iconMrImg','iconMrImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка YX после авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>48px X 22px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.iconYxImg64" alt="">
                                </p>
                                <p>
                                    <label for="logo-yx-icon" class="button expanded">Загрузить</label>
                                    <input id="logo-yx-icon" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'iconYxImg','iconYxImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка VK до авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>158px X 158px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.authVkImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-vk-pic" class="button expanded">Загрузить</label>
                                    <input id="social-vk-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'authVkImg','authVkImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка OK до авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>158px X 158px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.authOkImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-ok-pic" class="button expanded">Загрузить</label>
                                    <input id="social-ok-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'authOkImg','authOkImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка MR до авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>158px X 158px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.authMrImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-mr-pic" class="button expanded">Загрузить</label>
                                    <input id="social-mr-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'authMrImg','authMrImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-3">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка YX до авторизации<br>
                                    <span>Рекомендуемый размер</span> <b>158px X 158px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="social.authYxImg64" alt="">
                                </p>
                                <p>
                                    <label for="social-yx-pic" class="button expanded">Загрузить</label>
                                    <input id="social-yx-pic" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],social,'authYxImg','authYxImg64')">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" class="button button-save expanded"
                           @click.prevent="setConfig(social,$event.target)">Сохранить</a>
                        <img src="./../assets/images/checked.svg" alt="">
                    </p>
                </fieldset>
            </div>
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Участники</legend>
                    <div class="grid-x grid-padding-x">
                        <div class="cell large-6">
                            <p>
                                <span>Текст кнопки «проголосовать» после нажатия</span>
                                <input type="text" class="input input-shadow" v-model="singer.buttonTextActive">
                            </p>
                        </div>
                        <div class="cell large-6">
                            <p>
                                <span>Текст кнопки «проголосовать» до нажатия</span>
                                <input type="text" class="input input-shadow" v-model="singer.buttonTextInactive">
                            </p>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет текста кнопки «проголосовать» до нажатия</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="singer.buttonTextColorInactive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.buttonTextColorInactive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет текста кнопки «проголосовать» после нажатия</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="singer.buttonTextColorActive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.buttonTextColorActive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p>Цвет фона кнопки «проголосовать» после нажатия</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                                <input type="color" v-model="singer.buttonBgColorActive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.buttonBgColorActive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <span>Цвет фона кнопки «проголосовать» до нажатия</span>
                                <p class="grid-x grid-padding-x">
                                            <span class="cell large-8">
                                                <input type="color" v-model="singer.buttonBgColorInactive">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.buttonBgColorInactive">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <span>Цвет рамки фото участника</span>
                                <p class="grid-x grid-padding-x">
                                            <span class="cell large-8">
                                                <input type="color" v-model="singer.portraitBorderColor">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.portraitBorderColor">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <span>Цвет текста информации об участнике</span>
                                <p class="grid-x grid-padding-x">
                                            <span class="cell large-8">
                                                <input type="color" v-model="singer.textColor">
                                            </span>
                                    <span class="cell large-4">
                                                <input type="text" placeholder="#000000"
                                                       v-model="singer.textColor">
                                            </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Элемент Портрета<br>
                                    <span>Рекомендуемый размер</span> <b>89px X 113px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.portraitElementImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-elem-img" type="checkbox"
                                           v-model="singer.portraitElementHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-elem-img">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-portrait-front-elem" class="button expanded">Загрузить</label>
                                    <input id="btn-portrait-front-elem" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'portraitElementImg','portraitElementImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Фон портрета <br>
                                    <span>Рекомендуемый размер</span> <b>414px X 363px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.portraitBgImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-bg-img" type="checkbox" v-model="singer.portraitBgHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-bg-img">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-portrait-bg" class="button expanded">Загрузить</label>
                                    <input id="btn-portrait-bg" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'portraitBgImg','portraitBgImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Фон кнопки «проголосовать» после наведении / нажатии <br>
                                    <span>Рекомендуемый размер</span> <b>286px X 251px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.buttonBgActiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-button-bg-img-active" type="checkbox"
                                           v-model="singer.buttonBgActiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-button-bg-img-active">Спрятать</label>
                                </p>
                                <p>
                                    <label for="vote-btn-bg-active" class="button expanded">Загрузить</label>
                                    <input id="vote-btn-bg-active" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'buttonBgActiveImg','buttonBgActiveImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Фон кнопки «проголосовать»<br>
                                    <span>Рекомендуемый размер</span> <b>286px X 251px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.buttonBgInactiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-button-bg-img-inactive" type="checkbox"
                                           v-model="singer.buttonBgInactiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-button-bg-img-inactive">Спрятать</label>
                                </p>
                                <p>
                                    <label for="vote-btn-bg-inactive" class="button expanded">Загрузить</label>
                                    <input id="vote-btn-bg-inactive" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'buttonBgInactiveImg','buttonBgInactiveImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка кнопки «проголосовать»<br>
                                    <span>Рекомендуемый размер</span> <b>16px X 16px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.buttonIconInactiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-button-icon-img-inactive" type="checkbox"
                                           v-model="singer.buttonIconInactiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-button-icon-img-inactive">Спрятать</label>
                                </p>
                                <p>
                                    <label for="vote-btn-icon-inactive" class="button expanded">Загрузить</label>
                                    <input id="vote-btn-icon-inactive" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'buttonIconInactiveImg','buttonIconInactiveImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Иконка кнопки «проголосовать» после нажатия<br>
                                    <span>Рекомендуемый размер</span> <b>16px X 16px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.buttonIconActiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-portrait-button-icon-img-active" type="checkbox"
                                           v-model="singer.buttonIconActiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-portrait-button-icon-img-active">Спрятать</label>
                                </p>
                                <p>
                                    <label for="vote-btn-icon-active" class="button expanded">Загрузить</label>
                                    <input id="vote-btn-icon-active" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'buttonIconActiveImg','buttonIconActiveImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Изображение кнопки play<br>
                                    <span>Рекомендуемый размер</span> <b>59px X 59px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.playImg64" alt="">
                                </p>
                                <p>
                                    <label for="btn-play-play" class="button expanded">Загрузить</label>
                                    <input id="btn-play-play" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'playImg','playImg64')">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-4">
                            <div class="card callout">
                                <p class="card__title">
                                    Изображение кнопки stop<br>
                                    <span>Рекомендуемый размер</span> <b>59px X 59px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="singer.stopImg64" alt="">
                                </p>
                                <p>
                                    <label for="btn-play-pause" class="button expanded">Загрузить</label>
                                    <input id="btn-play-pause" type="file" class="show-for-sr"
                                           @change.prevent="readImg($event.target.files[0],singer,'stopImg','stopImg64')">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" class="button expanded button-save"
                           @click.prevent="setConfig(singer,$event.target)">Сохранить</a>
                        <img src="./../assets/images/checked.svg" alt="">
                    </p>
                </fieldset>
            </div>
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Кнопка вернуться на сайт</legend>
                    <div class="grid-x grid-padding-x">
                        <div class="cell">
                            <div class="card callout">
                                <div class="input-group">
                                    <span class="input-group-label">Текст кнопки</span>
                                    <input class="input-group-field" type="text"
                                           v-model="footer.backToSiteBtnText">
                                </div>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>Цвет текста кнопки active</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                        <input type="color"
                                               v-model="footer.backToSiteBtnColorActive">
                                    </span>
                                    <span class="cell large-4">
                                            <input type="text" placeholder="#000000"
                                                   v-model="footer.backToSiteBtnColorActive">
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>Цвет текста кнопки inactive</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                        <input type="color"
                                               v-model="footer.backToSiteBtnColorInactive">
                                    </span>
                                    <span class="cell large-4">
                                            <input type="text" placeholder="#000000"
                                                   v-model="footer.backToSiteBtnColorInactive">
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>Цвет bg active</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                        <input type="color"
                                               v-model="footer.backToSiteBtnBgColorActive">
                                    </span>
                                    <span class="cell large-4">
                                            <input type="text" placeholder="#000000"
                                                   v-model="footer.backToSiteBtnBgColorActive">
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p>Цвет bg inactive</p>
                                <p class="grid-x grid-padding-x">
                                    <span class="cell large-8">
                                        <input type="color"
                                               v-model="footer.backToSiteBtnBgColorInactive">
                                    </span>
                                    <span class="cell large-4">
                                            <input type="text" placeholder="#000000"
                                                   v-model="footer.backToSiteBtnBgColorInactive">
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p class="card__title">
                                    Изображение фона кнопки при наведении / нажатии<br>
                                    <span>Рекомендуемый размер</span> <b>286px X 251px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="footer.backToSiteBtnBgActiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-back-button-bg-active" type="checkbox"
                                           v-model="footer.backToSiteBtnBgActiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-back-button-bg-active">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-return-site-active" class="button expanded">Загрузить</label>
                                    <input id="btn-return-site-active" type="file" class="show-for-sr"
                                           @change.prevent="footer.backToSiteBtnBgActiveImg = $event.target.files[0]">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p class="card__title">
                                    Изображение фона кнопки<br>
                                    <span>Рекомендуемый размер</span> <b>286px X 251px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="footer.backToSiteBtnBgInactiveImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-back-button-bg-inactive" type="checkbox"
                                           v-model="footer.backToSiteBtnBgInactiveHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-back-button-bg-inactive">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-return-bg-inactive" class="button expanded">Загрузить</label>
                                    <input id="btn-return-bg-inactive" type="file" class="show-for-sr"
                                           @change.prevent="footer.backToSiteBtnBgInactiveImg = $event.target.files[0]">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" @click.prevent="setConfig(footer,$event.target)"
                           class="button expanded button-save">Сохранить</a>
                        <img src="./../assets/images/checked.svg" alt="">
                    </p>
                </fieldset>
            </div>
            <div class="cell">
                <fieldset class="fieldset">
                    <legend>Background сайта</legend>
                    <div class="grid-x grid-padding-x">
                        <div class="cell large-6">
                            <div class="card callout">
                                <p class="card__title">
                                    Parallax<br>
                                    <span>Рекомендуемый размер</span> <b>1920px X 5520px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="body.bodyBgFrontImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-body-bg-front" type="checkbox" v-model="body.bodyBgFrontHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-body-bg-front">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-site-bg-front" class="button expanded">Загрузить</label>
                                    <input id="btn-site-bg-front" type="file" class="show-for-sr"
                                           @change.prevent="body.bodyBgFrontImg = $event.target.files[0]">
                                </p>
                            </div>
                        </div>
                        <div class="cell large-6">
                            <div class="card callout">
                                <p class="card__title">
                                    Изображение background-а сайта<br>
                                    <span>Рекомендуемый размер</span> <b>1920px X 5520px</b>
                                </p>
                                <p class="thumbnail">
                                    <img :src="body.bodyBgBackImg64" alt="">
                                </p>
                                <p>
                                    <input id="hide-body-bg-back" type="checkbox" v-model="body.bodyBgBackHidden"
                                           true-value="true" false-value="false">
                                    <label for="hide-body-bg-back">Спрятать</label>
                                </p>
                                <p>
                                    <label for="btn-site-bg-back" class="button expanded">Загрузить</label>
                                    <input id="btn-site-bg-back" type="file" class="show-for-sr"
                                           @change.prevent="body.bodyBgBackImg = $event.target.files[0]">
                                </p>
                            </div>
                        </div>
                    </div>
                    <p class="fieldset__save">
                        <a href="#" @click.prevent="setConfig(body,$event.target)"
                           class="button expanded button-save">Сохранить</a>
                        <img src="./../assets/images/checked.svg" alt="">
                    </p>
                </fieldset>
            </div>
        </div>
    </form>
</template>

<script>
    import $ from 'jquery'

    export default {
        name: 'AdminTypo',
        data() {
            return {
                title: 'Admin typo page',
                partners: [],
                config: {
                    partners: [],
                    logo: [],
                    body: {},
                    social: {},
                    singer: {},
                    footer: {}
                },
                widget: {
                    image: {
                        title: null,
                        code: null,
                        columnSize: null,
                        value: null,
                        recommended: null,
                        canHide: false,
                        isHidden: false
                    },
                    partner: {
                        title: null,
                        code: null,
                        columnSize: null,
                        link: null,
                        img: null,
                        recommended: null
                    },
                    text: {
                        title: null,
                        code: null,
                        columnSize: null,
                        value: null
                    },
                    color: {
                        title: null,
                        code: null,
                        columnSize: null,
                        value: null
                    }
                },
                body: {
                    name: 'body',
                    bodyBgFrontImg: null,
                    bodyBgFrontHidden: null,
                    bodyBgBackImg: null,
                    bodyBgBackHidden: null,
                    bodyBgFrontImg64: null,
                    bodyBgBackImg64: null,
                },
                logo: {
                    name: 'logo',
                    logoRightImg: null,
                    logoImg: null,
                    logoLeftImg: null,
                    logoRightImg64: null,
                    logoImg64: null,
                    logoLeftImg64: null,
                    logoBgImg: null,
                    logoBgImg64: null,
                    logoHidden: null
                },
                social: {
                    name: 'social',
                    authTextBefore: null,
                    exitButtonText: null,
                    authBgColor: '#000000',
                    authTextColor: '#000000',
                    exitButtonBgColor: '#000000',
                    exitButtonColorActive: '#000000',
                    exitButtonColorInactive: '#000000',
                    iconVkImg: null,
                    iconOkImg: null,
                    iconMrImg: null,
                    iconYxImg: null,
                    iconVkImg64: null,
                    iconOkImg64: null,
                    iconMrImg64: null,
                    iconYxImg64: null,
                    authVkImg: null,
                    authOkImg: null,
                    authMrImg: null,
                    authYxImg: null,
                    authVkImg64: null,
                    authOkImg64: null,
                    authMrImg64: null,
                    authYxImg64: null
                },
                singer: {
                    name: 'singer',
                    buttonTextInactive: null,
                    buttonTextActive: null,
                    buttonTextColorActive: '#000000',
                    buttonTextColorInactive: '#000000',
                    buttonBgColorActive: '#000000',
                    buttonBgColorInactive: '#000000',
                    buttonBgActiveImg: null,
                    buttonBgActiveImg64: null,
                    buttonBgActiveHidden: null,
                    buttonBgInactiveImg: null,
                    buttonBgInactiveImg64: null,
                    buttonBgInactiveHidden: null,
                    portraitBorderColor: '#000000',
                    portraitElementImg: null,
                    portraitElementImg64: null,
                    portraitElementHidden: null,
                    portraitBgHidden: null,
                    portraitBgImg: null,
                    portraitBgImg64: null,
                    playImg: null,
                    playImg64: null,
                    stopImg: null,
                    stopImg64: null,
                    buttonIconInactiveImg64: null,
                    buttonIconInactiveHidden: null,
                    buttonIconInactiveImg: null,
                    buttonIconActiveImg64: null,
                    buttonIconActiveHidden: null,
                    buttonIconActiveImg: null,
                    textColor: '#000000'
                },
                footer: {
                    name: 'footer',
                    backToSiteBtnText: null,
                    backToSiteBtnColorActive: null,
                    backToSiteBtnColorInactive: null,
                    backToSiteBtnBgColorActive: null,
                    backToSiteBtnBgColorInactive: null,
                    backToSiteBtnBgActiveImg64: null,
                    backToSiteBtnBgActiveImg: null,
                    backToSiteBtnBgActiveHidden: null,
                    backToSiteBtnBgInactiveImg64: null,
                    backToSiteBtnBgInactiveImg: null,
                    backToSiteBtnBgInactiveHidden: null
                }
            }
        },
        mounted() {
            console.log('Mounted');
            this.getConfig();
            this.reader = new FileReader();
        },
        methods: {
            setConfig(obj, target) {
                let button = $(target);
                let statusOk = $(target).parent().find('img');
                button.text('Подождите...')
                let fd = new FormData;

                for (let field in obj) {
                    if (obj.hasOwnProperty(field)) {
                        if (obj[field] && !field.includes('Img64')) {
                            fd.append(field, obj[field])
                        }
                    }
                }

                $.ajax({
                    url: '/ajax/config',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        button.text('Сохранено');
                        statusOk.fadeIn('slow').fadeOut('slow', () => {
                            button.text('Сохранить');
                        })
                        this.getConfig();
                    }
                });

            },
            setPartners(target) {
                let button = $(target);
                button.text('Подождите...')
                let fd = new FormData;
                for (let i = 0; i < this.partners.length; i++) {
                    let partner = this.partners[i];
                    for (let field in partner) {
                        if (partner.hasOwnProperty(field)) {
                            if (partner[field] && !field.includes('img64') && field !== 'id') {
                                fd.append(`${field}[${partner.id}]`, partner[field])
                            }
                        }
                    }
                }

                $.ajax({
                    url: '/ajax/partners/save',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        button.text('Сохранено');
                        button.text('Сохранить');
                        this.getConfig();
                    }
                });
            },
            addPartner(target) {
                let button = $(target);
                button.text('Подождите...')
                $.ajax({
                    url: '/ajax/partner/add',
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        button.text('Добавлено');
                        button.text('Добавить');
                        this.getConfig();
                    }
                });
            },
            deletePartner(obj) {
                let fd = new FormData;

                for (let field in obj) {
                    if (obj.hasOwnProperty(field)) {
                        if (obj[field] && !field.includes('Img64')) {
                            fd.append(field, obj[field])
                        }
                    }
                }

                $.ajax({
                    url: '/ajax/partner/delete',
                    data: fd,
                    processData: false,
                    contentType: false,
                    type: 'POST',
                    success: (data) => {
                        alert('Успех');
                        this.getConfig();
                    }
                });
            },
            getPartner() {
                $.ajax({
                    url: '/ajax/partner',
                    processData: false,
                    contentType: false,
                    type: 'GET',
                    success: (data) => {
                        this.partners = data;
                    }
                });
            },
            getConfig() {
                $.ajax({
                    url: '/ajax/config',
                    processData: false,
                    contentType: false,
                    type: 'GET',
                    success: (data) => {
                        this.logo.logoLeftImg64 = data.logoLeftImg;
                        this.logo.logoImg64 = data.logoImg;
                        this.logo.logoRightImg64 = data.logoRightImg;
                        this.logo.logoBgImg64 = data.logoBgImg;
                        this.logo.logoHidden = data.logoHidden;

                        this.social.authBgColor = data.authBgColor;
                        this.social.authTextColor = data.authTextColor;
                        this.social.authTextBefore = data.authTextBefore;
                        this.social.authTextAfter = data.authTextAfter;
                        this.social.exitButtonBgColor = data.exitButtonBgColor;
                        this.social.exitButtonText = data.exitButtonText;
                        this.social.exitButtonColorInactive = data.exitButtonColorInactive;
                        this.social.exitButtonColorActive = data.exitButtonColorActive;
                        this.social.iconVkImg64 = data.iconVkImg;
                        this.social.iconOkImg64 = data.iconOkImg;
                        this.social.iconMrImg64 = data.iconMrImg;
                        this.social.iconYxImg64 = data.iconYxImg;
                        this.social.authVkImg64 = data.authVkImg;
                        this.social.authOkImg64 = data.authOkImg;
                        this.social.authMrImg64 = data.authMrImg;
                        this.social.authYxImg64 = data.authYxImg;

                        this.singer.buttonTextInactive = data.buttonTextInactive;
                        this.singer.buttonTextActive = data.buttonTextActive;
                        this.singer.buttonTextColorActive = data.buttonTextColorActive;
                        this.singer.buttonTextColorInactive = data.buttonTextColorInactive;
                        this.singer.buttonBgColorActive = data.buttonBgColorActive;
                        this.singer.buttonBgColorInactive = data.buttonBgColorInactive;
                        this.singer.buttonBgActiveImg64 = data.buttonBgActiveImg;
                        this.singer.buttonBgActiveHidden = data.buttonBgActiveHidden;
                        this.singer.buttonBgInactiveImg64 = data.buttonBgInactiveImg;
                        this.singer.buttonBgInactiveHidden = data.buttonBgInactiveHidden;

                        // New button icons
                        this.singer.buttonIconInactiveImg64 = data.buttonIconInactiveImg;
                        this.singer.buttonIconInactiveHidden = data.buttonIconInactiveHidden;

                        this.singer.buttonIconActiveImg64 = data.buttonIconActiveImg;
                        this.singer.buttonIconActiveHidden = data.buttonIconActiveHidden;

                        this.singer.portraitBorderColor = data.portraitBorderColor;
                        this.singer.portraitElementImg64 = data.portraitElementImg;
                        this.singer.portraitElementHidden = data.portraitElementHidden;
                        this.singer.portraitBgImg64 = data.portraitBgImg;
                        this.singer.portraitBgHidden = data.portraitBgHidden;
                        this.singer.playImg64 = data.playImg;
                        this.singer.stopImg64 = data.stopImg;
                        this.singer.textColor = data.textColor;

                        this.footer.backToSiteBtnText = data.backToSiteBtnText;
                        this.footer.backToSiteBtnText = data.backToSiteBtnText;
                        this.footer.backToSiteBtnColorActive = data.backToSiteBtnColorActive;
                        this.footer.backToSiteBtnColorInactive = data.backToSiteBtnColorInactive;
                        this.footer.backToSiteBtnBgColorActive = data.backToSiteBtnBgColorActive;
                        this.footer.backToSiteBtnBgColorInactive = data.backToSiteBtnBgColorInactive;
                        this.footer.backToSiteBtnBgActiveImg64 = data.backToSiteBtnBgActiveImg;
                        this.footer.backToSiteBtnBgActiveHidden = data.backToSiteBtnBgActiveHidden;
                        this.footer.backToSiteBtnBgInactiveImg64 = data.backToSiteBtnBgInactiveImg;
                        this.footer.backToSiteBtnBgInactiveHidden = data.backToSiteBtnBgInactiveHidden;
                        this.body.bodyBgFrontImg64 = data.bodyBgFrontImg;
                        this.body.bodyBgFrontHidden = data.bodyBgFrontHidden;
                        this.body.bodyBgBackImg64 = data.bodyBgBackImg;
                        this.body.bodyBgBackHidden = data.bodyBgBackHidden;
                        this.getPartner();
                    }
                });
            },
            readImg(file, obj, imgField, img64Field) {
                if (file) {
                    if (file.size > 500000) {
                        alert('Превышен лимит файла 500кб');
                        return false;
                    } else {
                        this.reader.onload = function (e) {
                            obj[img64Field] = e.target.result;
                            obj[imgField] = file;
                        };
                        this.reader.readAsDataURL(file);
                    }
                }
            }
        }
    }
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style scoped lang="scss">
    .offset-right {
        right: 2.5rem;
    }

    .thumbnail {
        text-align: center;
        height: 150px;
        overflow-y: hidden;
        background-color: #2d353c;
    }
</style>