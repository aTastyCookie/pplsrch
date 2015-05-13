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

var requestCallback = new MyRequestsCompleted({
    numRequest: 3,
    singleCallback: function(){
        compare();
    }
});

function compare() {
    var imgs = [];
    var hashes = [];

    $('.profile').each(function() {
        imgs.push($(this).find('.top-profile').find('img').attr('src'));
        hashes.push($(this).attr('id'));
    });

    $.ajax({
        url: '/index.php?r=search/compare-pics',
        type: 'post',
        data: {
            images: imgs.join(','),
            hashes: hashes.join(','),
        },
        dataType: 'json',
        success: function(response) {
            for (var key in response) {
                createProfileGroup(key, response[key]);


                //$profile = $('#' + key);
                //$profile.insertAfter('#w0');

            }
        },
        error: function() {
            alert('Ошибка!');
        }
    });
}

function createProfileGroup(key, data) {
    $group = $('<div class="profile-group" />');
    $img = $('#' + key).find('img');
    $('#' + key).remove();
    $group.append($img);
    $group.insertAfter('#w0');
    
    for (var i = 0; i < data.length; i++) {
        $('#' + data[i]).remove();
    }

    console.log(key);
    console.log(data);
}


function searchRequest(q, client, offset, clientPseudo)
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
            $('#' + clientPseudo + ' .profiles').html('<span class="preloader-' + clientPseudo + '"></span>');
        },
        success: function(response) {
            if (!response.error) {
                $('#' + clientPseudo + ' .profiles').html(response.profiles);
                if (response.more) {
                    $('#' + clientPseudo).append('<div class="more-wrap">' + response.more + '</div>');
                }
            } else {
                $('#' + clientPseudo + ' .profiles').html(response.error);
            }
            requestCallback.requestComplete(true);
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log(textStatus);
        }
    });
}

var Vkontakte = {
    alias : 'vk',
    foo : '',
    init: function(q) {
        this.foo = 'set!';
        this.q = q;
        this.search();
    },
    search: function() {
        var that = this;
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : that.q,
                'client' : 'vkontakte',
                'offset' : 0
            },
            dataType: 'json',
            async: true,
            beforeSend: function() {
                $('#vk .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                if (!response.error) {
                    $('#vk .profiles').html(response.profiles);
                    if (response.more) {
                        $('#vk').append('<div class="more-wrap">' + response.more + '</div>');
                    }
                } else {
                    $('#vk .profiles').html(response.error);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

var Facebook = {
    alias: 'fb',
    foo : '',
    init: function(q) {
        this.foo = 'set!';
        this.q = q;
        this.search();
    },
    search: function() {
        var that = this;
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : that.q,
                'client' : 'facebook',
                'offset' : 0
            },
            dataType: 'json',
            async: true,
            beforeSend: function() {
                $('#fb .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('#fb .profiles').html(response.profiles);
                if (response.more) {
                    $('#fb').append('<div class="more-wrap">' + response.more + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

var Google = {
    alias: 'gg',
    foo : '',
    init: function(q) {
        this.foo = 'set!';
        this.q = q;
        this.search();
    },
    search: function() {
        var that = this;
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : that.q,
                'client' : 'google',
                'offset' : 0
            },
            dataType: 'json',
            async: true,
            beforeSend: function() {
                $('#gg .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('#gg .profiles').html(response.profiles);
                if (response.more) {
                    $('#gg').append('<div class="more-wrap">' + response.more + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

var Twitter = {
    alias: 'tw',
    foo : '',
    init: function(q) {
        this.foo = 'set!';
        this.q = q;
        this.search();
    },
    search: function() {
        var that = this;
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : that.q,
                'client' : 'twitter',
                'offset' : 0
            },
            dataType: 'json',
            async: true,
            beforeSend: function() {
                $('#tw .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('#tw .profiles').html(response.profiles);
                if (response.more) {
                    $('#tw').append('<div class="more-wrap">' + response.more + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

$(document).on('click', '.get-more', function() {
    $block = $(this).parents('.search-results');
    clientId = $block.attr('id');
    $.ajax({
        url: '/index.php?r=search%2Fsearch-profiles',
        type: 'post',
        data: {
            'q' : $(this).attr('data-query'),
            'client' : $(this).attr('data-client'),
            'offset' : $(this).attr('data-offset'),
            'after': $(this).attr('data-after'),
        },
        dataType: 'json',
        async: true,
        beforeSend: function() {
            $block.find('.more-wrap').html('<span class="preloader-' + clientId + '"></span>');
        },
        success: function(response) {
            $block.find('.profiles').append(response.profiles);
            if (response.more) {
                $block.find('.more-wrap').html(response.more);    
            } else {
                $block.find('.more-wrap').remove();
            }
            
        }
    });

    return false;
});

$(document).ready(function() {
    $('.search').click(function(e) {
        e.preventDefault();
        $('.more-wrap').remove();
        var q = $('input[name="q"]').val().trim();
        console.log(q);
        $('.client-hidden').each(function() {
            var clientPseudo;
            /*if ($(this).val() == 'vkontakte') {
                Vkontakte.init(q);
            }
            if ($(this).val() == 'facebook') {
                Facebook.init(q);
            }
            if ($(this).val() == 'twitter') {
                Twitter.init(q);
            }
            if ($(this).val() == 'google') {
                Google.init(q);
            }*/
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
            searchRequest(q, $(this).val(), 0, clientPseudo);
        });
    });
});
$(document).on('click', '.show-more', function() {
    $profile = $(this).parents('.profile');
    $profile.find('.profile-more-data').slideToggle();
});