$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function submitOption(route, formId, divId) {
    console.log("form id "+formId);
    console.log($('textarea[name='+formId+'-option]').val());
    var formData = {
        'option': $('textarea[name='+formId+'-option]').val(),
        'correct': $('input[name='+formId+'-correct]').prop('checked')
    };
    JSON.stringify(formData);
    console.log(formData);
    $.ajax({
        url: route,
        method: 'POST',
        dataType: 'html',
        data: formData,
        success: function( data, responseCode ) {
            if (responseCode === 'success') {
                console.log(divId);
                $( "#" + divId ).empty().html(data);
            } else {
                alert(responseCode);
            }
        }
    });
}

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

function deleteContent(route, div, name) {
    if (confirm("Are you sure you want to delete this "+name+"?")) {
        updateContent(route, div);
    }
}

