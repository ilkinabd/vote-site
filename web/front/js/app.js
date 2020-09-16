(function ($) {
    'use strict';
    // Variables
    var last_known_scroll_position = 0;
    var ticking = false;
    var parallax = document.querySelector('.parallax');

    var audioContainer = document.createElement("AUDIO");

    // Functions
    function doSomething(last_known_scroll_position) {
        //console.log(last_known_scroll_position);
        parallax.style.transform = 'translateY(' + ((last_known_scroll_position / 2)) + 'px)';
    }

    // Event handlers
    $('.singer__vote:not(.active):not(.inactive)').on('click', '[data-vote]', function () {
        event.preventDefault();
        event.stopImmediatePropagation();
        var elem = this;
        var singerId = elem.dataset.singerId;
        var span = elem.querySelectorAll('a.button.button-vote > span')[1];
        var oldHtml = span.innerHTML;
        span.innerHTML = 'Подождите...';
        $.ajax({
            method: "POST",
            url: "/vote",
            data: {singer_id: singerId},
            dataType: "json"
        }).done(function (data) {
            span.innerHTML = data.message;
            var elemParent = $(elem).closest('.singer__vote')[0];
            $('.singer__vote').not(elemParent).each(function () {
                this.classList.add('inactive');
            });
            elemParent.classList.add('active');
        }).fail(function (err) {
            var err = err.responseJSON;
            switch (err.code) {
                case 1:
                    err.message = 'Чтобы проголосовать пожалуйста авторизируйтесь!';
                    break;
                case 2:
                    err.message = 'Ваш голос уже был принят!';
                    break;
            }
            alert(err.message);
            span.innerHTML = oldHtml;
        });
    });

    $('.singer__play').on('click', function () {
        event.preventDefault();
        event.stopImmediatePropagation();
        if ($(this).hasClass('playing')) {
            $(this).removeClass('playing');
            console.log('stop');
            audioContainer.pause();
        } else {
            $('.singer__play').not(this).removeClass('playing');
            $(this).addClass('playing');
            console.log('playing');
            if (!audioContainer.paused) audioContainer.pause();
            audioContainer.src = this.dataset.music;
            audioContainer.load();
            audioContainer.play();
        }

    });

    // if (parallax) {
    //     window.addEventListener('scroll', function (e) {
    //         last_known_scroll_position = window.scrollY;
    //         if (!ticking) {
    //             doSomething(last_known_scroll_position);
    //             window.requestAnimationFrame(function () {
    //                 doSomething(last_known_scroll_position);
    //                 ticking = false;
    //             });
    //
    //             ticking = true;
    //         }
    //
    //
    //     });
    // }
}(jQuery));
