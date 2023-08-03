$(document).ready(function() {

    var imgs = $('li.pbSlider'),
        totalImages = imgs.length,
        delay = 5000,
        slideNo = 0,
        firstImage = imgs.eq(0).find('img'),
        sliderInterval;

    firstImage.on("load", function() {
        var imgHeight = imgs.eq(slideNo).find('img').height();
        $('.pbSliderContainer').css('height', imgHeight + 'px');
    }).each(function() {
        if (this.complete) $(this).trigger('load');
    });

    function startSlider() {
        sliderInterval = setInterval(changeSlide, delay);
    }

    function stopSlider() {
        clearInterval(sliderInterval);
    }

    startSlider();

    $(window).on('resize', function() {
        imgHeight = imgs.eq(slideNo).height();
        $('.pbSliderContainer').css('height', imgHeight + 'px');
    });

    $(window).on('blur', function() {
        stopSlider();
    });

    $(window).on('focus', function() {
        startSlider();
    });

    function changeSlide() {
        imgs.eq(slideNo).slideUp(2500);

        slideNo++;
        if (slideNo === totalImages) {
            slideNo = 0;
        }
        imgs.eq(slideNo).fadeIn(3500);
    }
});
