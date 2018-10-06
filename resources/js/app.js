require('bootstrap');
require('jquery');
require("expose-loader?$!jquery");
require('cookieconsent');
require('moment');

$(document).ready(function() {
	var offset = 10,
		//browser window scroll (in pixels) after which the "back to top" link opacity is reduced
		offset_opacity = 100,
		//duration of the top scrolling animation (in ms)
		scroll_top_duration = 700;

	//smooth scroll to top
	$("#top").on('click', function(event) {
		event.preventDefault();
		$('body,html').animate({
			scrollTop: 0,
		}, scroll_top_duration);
	});

	$('.alert-success').removeClass('hidden');
	//Messaggio di avviso salvataggio a comparsa sulla destra solo nella versione a desktop intero
	if ($(window).width() > 1023) {
		var i = 0;

		$('.alert-success').each(function() {
			i++;
			tops = 60 * i + 25;

			$(this).css({
				'position': 'absolute',
				'top': -100,
				'right': 10,
				'opacity': 1
			}).delay(1000).animate({
				'top': tops,
				'opacity': 1
			}).delay(3000).animate({
				'top': -100,
				'opacity': 0
			});

			$(this).html($(this).find('.container').html());
		});
    }


    // Pulsante per il ritorno a inizio pagina
    var slideToTop = $("<div />");
    slideToTop.html('<i class="fa fa-chevron-up"></i>');
    slideToTop.css({
        position: 'fixed',
        bottom: '20px',
        right: '25px',
        width: '40px',
        height: '40px',
        color: '#eee',
        'font-size': '',
        'line-height': '40px',
        'text-align': 'center',
        'background-color': 'rgba(255, 78, 0)',
        'box-shadow': '0 0 10px rgba(0, 0, 0, 0.05)',
        cursor: 'pointer',
        'z-index': '99999',
        opacity: '.7',
        'display': 'none'
    });

    slideToTop.on('mouseenter', function () {
        $(this).css('opacity', '1');
    });

    slideToTop.on('mouseout', function () {
        $(this).css('opacity', '.7');
    });

    $('.wrapper').append(slideToTop);
    $(window).scroll(function () {
        if ($(window).scrollTop() >= 150) {
            if (!$(slideToTop).is(':visible')) {
                $(slideToTop).fadeIn(500);
            }
        } else {
            $(slideToTop).fadeOut(500);
        }
    });

    $(slideToTop).click(function () {
        $("html, body").animate({
            scrollTop: 0
        }, 500);
    });
});
