$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function updateContent(route, div) {
    $.ajax({
        url: route,
        method: 'POST',
        dataType: 'html',
        success: function( data, responseCode ) {
            if (responseCode === 'success') {
                console.log(div);
                $( "#" + div ).empty().html(data);
            } else {
                alert(responseCode);
            }
        }
    });
}

function deleteContent(route, div) {
    if (confirm("OK to submit?")) {
        $.ajax({
            url: route,
            method: 'POST',
            dataType: 'html',
            success: function( data ) {
                $( "#" + div ).remove();
            }});
    }
}

