$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function submitSegmentForm(route, prompt_id, index) {
    console.log("form id segment-"+index);
    var formData = {
        'prompt_id': prompt_id,
        'segment_id': $('hidden[name=question_id'+index+']').val(),
        'segment_title': $('input[name=segment_title'+index+']').val(),
        'accessory_type': $('select[name=accessory_type'+index+']').val(),
        'segment_url': $('input[name=segment_url'+index+']').val(),
        'segment_image_url': $('input[name=segment_image_url'+index+']').val(),
        'image_alt_text': $('input[name=image_alt_text'+index+']').val(),
        'segment_accessory_text': $('input[name=segment_accessory_text'+index+']').val(),
        'segment_accessory_options': $('input[name=segment_accessory_options'+index+']').val(),
        'segment_answers': $('input[name=segment_answers'+index+']').val(),
        'segment_text': $('textarea[name=segment_text'+index+']').val()
    };
    if (formData['segment_image_url'].length > 3 &&
        formData['image_alt_text'].length < 2) {
        alert("Image alt text is required when an image url is set.");
        return;
    }
    JSON.stringify(formData);
    console.log(formData);
    $.ajax({
        url: route,
        method: 'POST',
        dataType: 'html',
        data: formData,
        success: function( data, responseCode ) {
            if (responseCode === 'success') {
                console.log("reload #segment-" + index);
                if (index === '0') {
                    let newIndex = $('#all-segments > div').length;
                    console.log("new segment = #segment-" + newIndex);
                    $( "#segment-0" )
                        .before( "<div id='segment-"+newIndex+"' class='prompt-"+prompt_id+"-segment'></div>" );
                    $( "#segment-" + newIndex ).replaceWith(data);
                } else {
                    $( "#segment-" + index ).empty().html(data);
                }
            } else {
                alert(responseCode);
            }
        }
    });
}

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

function deleteContent(route, name, div) {
    if (confirm("Are you sure you want to delete: "+name+"?")) {
        updateContent(route, div);
    }
}

