/*$(document).ready(function() {
	$('.search').click(function(e) {
        e.preventDefault();

        var queryParams = $('#search-form').serialize();
        console.log(queryParams);

        var script = document.createElement('SCRIPT'); 
        script.src = "https://api.vk.com/method/users.get?callback=callbackFunc"; 
        document.getElementsByTagName("head")[0].appendChild(script); 


        $.ajax({
        	url: 'https://api.vk.com/method/users.get',
        	type: 'get',
        	dataType: 'jsonp',
        	data: queryParams,
        	beforeSend: function() {
                console.log(queryParams);
        	},
        	success: function(response) {
            
        	},
        	error: function(xhr, ajaxOptions, thrownError) {
        		console.log(thrownError);
        	}
        });
	});
});

function callbackFunc(result) { 
    console.log(result); 
} */

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
                $('.vk .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('.vk .profiles').html(response.profiles);
                if (response.more) {
                    $('.vk').append('<div class="more-wrap">' + response.more + '</div>');
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
                $('.fb .profiles').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('.fb .profiles').html(response.profiles);
                if (response.more) {
                    $('.fb').append('<div class="more-wrap">' + response.more + '</div>');
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

var Twitter = {
    q : '',
    alias: 'tw',
    foo : '',
    init: function(q) {
        this.foo = 'set!';
        this.search();
        this.q = q;
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
            dataType: 'html',
            async: true,
            beforeSend: function() {
                $('#twitter').html('<span class="preloader-' + that.alias +'"></span>');
            },
            success: function(response) {
                $('#twitter').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    }
};

$(document).on('click', '.get-more', function() {
    $.ajax({
        url: '/index.php?r=search%2Fsearch-profiles',
        type: 'post',
        data: {
            'q' : $(this).attr('data-query'),
            'client' : $(this).attr('data-client'),
            'offset' : $(this).attr('data-offset')
        },
        dataType: 'json',
        async: true,
        beforeSend: function() {
            $('.vk .more-wrap').html('<span class="preloader-vk"></span>');
        },
        success: function(response) {
            $('.vk .profiles').append(response.profiles);
            if (response.more) {
                $('.vk .more-wrap').html(response.more);    
            } else {
                $('.vk .more-wrap').remove();
            }
            
        }
    });

    return false;
});

$(document).ready(function() {
    $('.search').click(function(e) {
        e.preventDefault();
        var q = $('input[name="q"]').val().trim();
        console.log(q);
        $('.client-hidden').each(function() {
            if ($(this).val() == 'vkontakte') {
                Vkontakte.init(q);
            }
            if ($(this).val() == 'facebook') {
                Facebook.init(q);
            }
            if ($(this).val() == 'twitter') {
                Twitter.init(q);
            }
        });

        /*$.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : $('input[name="q"]').val().trim(),
                'client' : 'vkontakte',
                'offset' : 0
            },
            dataType: 'html',
            async: true,
            beforeSend: function() {
                //$('#search-results').html('<span class="preloader"></span>');
            },
            success: function(response) {
                $('#search-results').append(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
        $.ajax({
            url: '/index.php?r=search%2Fsearch-profiles',
            type: 'post',
            data: {
                'q' : $('input[name="q"]').val().trim(),
                'client' : 'facebook',
                'offset' : 0
            },
            dataType: 'html',
            async: true,
            beforeSend: function() {
                //$('#search-results').html('<span class="preloader"></span>');
            },
            success: function(response) {
                $('#search-results').append(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });*/
    });
});
$(document).on('click', '.show-more', function() {
    $profile = $(this).parents('.profile');
    $profile.find('.profile-more-data').slideToggle();
});