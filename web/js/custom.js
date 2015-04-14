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

$(document).ready(function() {
    $('.search').click(function(e) {
        e.preventDefault();
        $.ajax({
            url: '/index.php?r=search%2Findex',
            type: 'post',
            data: {
                'q' : $('input[name="q"]').val().trim()
            },
            dataType: 'html',
            async: true,
            beforeSend: function() {
                $('#search-results').html('<span class="preloader"></span>');
            },
            success: function(response) {
                $('#search-results').html(response);
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log(textStatus);
            }
        });
    });
});