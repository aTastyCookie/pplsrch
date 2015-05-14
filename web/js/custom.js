var MyRequestsCompleted = (function() {
    var numRequestToComplete, requestsCompleted, callBacks, singleCallBack;

    return function(options) {
        if (!options) options = {};

        numRequestToComplete = options.numRequest || 0;
        requestsCompleted = options.requestsCompleted || 0;
        callBacks = [];
        var fireCallbacks = function() {
            for (var i = 0; i < callBacks.length; i++) callBacks[i]();
        };
        if (options.singleCallback) callBacks.push(options.singleCallback);

        this.addCallbackToQueue = function(isComplete, callback) {
            if (isComplete) requestsCompleted++;
            if (callback) callBacks.push(callback);
            if (requestsCompleted == numRequestToComplete) fireCallbacks();
        };
        this.requestComplete = function(isComplete) {
            if (isComplete) requestsCompleted++;
            if (requestsCompleted == numRequestToComplete) fireCallbacks();
        };
        this.setCallback = function(callback) {
            callBacks.push(callBack);
        };
    };
})();



function compare() {
    var pics = collectPicsForCompare();
    var sources = pics.map(function(data) {
        return data.src;
    });
    var hashes = pics.map(function(data) {
        return data.hash;
    });

    $.ajax({
        url: '/index.php?r=search/compare-pics',
        type: 'post',
        data: {
            images: sources.join(','),
            hashes: hashes.join(','),
        },
        dataType: 'json',
        beforeSend: function() {
            alert('Начинается сравнение...');
        },
        success: function(response) {
            for (var key in response) {
                composeProfileGroup(key, response[key]);
            }
            alert('Сравнение окончено');
        },
        error: function() {
            alert('Ошибка!');
        }
    });
}

function collectPicsForCompare() {
    
    var pics = [];

    $('.profile').each(function() {
        var src = $(this).find('.top-profile').find('.non-default').attr('src');
        if (src) {
            var picData = [];
            picData.src = src;
            picData.hash = $(this).attr('id');
            pics.push(picData);
        }
    }); 

    return pics;
}

function composeProfileGroup(key, data) {
    var $group = $('<div class="profile-group" />');
    var $profile = $('#' + key);
    $group.append($profile);

    for (var i = 0; i < data.length; i++) {
        $profile = $('#' + data[i]);
        $group.append($profile);
    }

    $('#search-results').prepend($group);
}




$(document).ready(function() {

    var requestCallback = new MyRequestsCompleted({
        numRequest: $('.search-block').length,
        singleCallback: function(){
            compare();
        }
    });

    function searchRequest(q, client, offset)
    {
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q': q,
                'client': client,
                'offset': offset,
            },
            dataType: 'json',
            beforeSend: function() {
                $('#' + client + ' .profiles').html('<span class="preloader-' + client + '"></span>');
            },
            success: function(response) {
                if (!response.error) {
                    $('#' + client + ' .profiles').html(response.profiles);
                    if (response.more) {
                        $('#' + client).append('<div class="more-wrap">' + response.more + '</div>');
                    }
                } else {
                    $('#' + client + ' .profiles').html(response.error);
                }
                requestCallback.requestComplete(true);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }

    $('.search').click(function(e) {
        e.preventDefault();
        $('.more-wrap').remove();
        var q = $('input[name="q"]').val().trim();
        console.log(q);


        $('.client-hidden').each(function() {
            var clientPseudo;
            if ($(this).val() == 'vkontakte') {
                clientPseudo = 'vk';
            }
            if ($(this).val() == 'facebook') {
                clientPseudo = 'fb';
            }
            if ($(this).val() == 'twitter') {
                clientPseudo = 'tw';
            }
            if ($(this).val() == 'google') {
                clientPseudo = 'gg';
            }
            searchRequest(q, $(this).val(), 0);
        });
    });
});
$(document).on('click', '.show-more', function() {
    $profile = $(this).parents('.profile');
    $profile.find('.profile-more-data').slideToggle();
});