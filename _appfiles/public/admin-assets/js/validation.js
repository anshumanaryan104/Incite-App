var adminTranslation = window.translations.messages;

function myToastr(msg, type) {
    toastr.remove();
    if (type == 'error') {
        toastr['error']('', msg);
    } else if (type == 'success') {
        toastr['success']('', msg);
    }
}

function getFormData($form) {
    var unindexed_array = $form.serializeArray();
    var indexed_array = {};
    var l = 0;
    $.map(unindexed_array, function (n, i) {
        if (n['name'] == 'category_id[]') {
            if (l == 0) {
                indexed_array['category_id'] = [];
            }
            indexed_array['category_id'].push(n['value']);
            l++;
        } else {
            indexed_array[n['name']] = n['value'];
        }
    });
    return indexed_array;
}


function showImagePreview(input_id,preview_id,width,height) {
    console.log(width);
    console.log(height);
    $('#'+input_id).on('change', function() {
        var file = this.files[0];
        var reader = new FileReader();
        reader.onload = function(event) {
            var img = new Image();
            img.onload = function() {
                $("#"+preview_id).removeClass('hide');
                $('#'+preview_id).attr('src', event.target.result);
                // if(width!='' && height!=''){
                //     if(this.width == width && this.height == height){
                //         $("#"+preview_id).removeClass('hide');
                //         $('#'+preview_id).attr('src', event.target.result);
                //     }else{
                //         myToastr(adminTranslation.admin_image_size_error, 'error');
                //         $('#'+input_id).val('');
                //     }
                // }else{
                //     
                // }               
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    });
}

function showVideoPreview(input_id, preview_id, width, height) {
    $('#' + input_id).on('change', function() {
      var file = this.files[0];
      var video = document.getElementById(preview_id);
      video.src = URL.createObjectURL(file);
  
      video.onloadedmetadata = function() {
        $("#" + preview_id).removeClass('hide');
        // Additional checks or actions for video size
      };
    });
  }

function showMultipleImagePreview(){
    $('#previewsContainer').html('');
    $('#formFileMultiple').on('change', function() {
        var files = this.files;
        console.log(this.files);
        var previewsContainer = $('#previewsContainer');
        // previewsContainer.empty();
        for (var i = 0; i < files.length; i++) {
            var file = files[i];
            var reader = new FileReader();

            reader.onload = function(event) {
                var div = '<div class="col" style="height: 270px;"><div class="card h-100"><img class="card-img-top" src="'+event.target.result+'" alt="'+adminTranslation.admin_blog_image+'" style="height: 50%;" /><div class="card-body" style="text-align: center;padding: 0;"><button type="button" class="btn btn-label-danger mt-4 waves-effect deleteButton"><i class="ti ti-x ti-xs me-1"></i><span class="align-middle">'+adminTranslation.admin_delete+'</span></button></div></div></div>';
                var preview = $(div);
                
                preview.find('.deleteButton').click(function() {
                    $(this).closest('.col').remove();
                    files.splice(i, 1);
                });
                
                previewsContainer.append(preview);
            };
            reader.readAsDataURL(file);
        }
    });
}

// function showMultipleImagePreview() {
//     $('#formFileMultiple').on('change', function() {
//     console.log(this.files);
//       var files = this.files;
//       var selectedFiles = []; // Array to store selected files
//       var previewsContainer = $('#previewsContainer');
//       for (var i = 0; i < files.length; i++) {
//         (function(index) {
//           var file = files[index];
//           var reader = new FileReader();
  
//           reader.onload = function(event) {
//             var div = '<div class="col" style="height: 270px;"><div class="card h-100"><img class="card-img-top" src="' + event.target.result + '" alt="' + adminTranslation.admin_blog_image + '" style="height: 50%;" /><div class="card-body" style="text-align: center;padding: 0;"><button type="button" class="btn btn-label-danger mt-4 waves-effect deleteButton"><i class="ti ti-x ti-xs me-1"></i><span class="align-middle">' + adminTranslation.admin_delete + '</span></button></div></div></div>';
//             var preview = $(div);
  
//             preview.find('.deleteButton').click(function() {
//               $(this).closest('.col').remove();
//               selectedFiles.splice(index, 1); // Update selectedFiles array
//             });
  
//             previewsContainer.append(preview);
//           };
//           reader.readAsDataURL(file);
//           selectedFiles.push(file); // Add the selected file to selectedFiles array
//         })(i);
//       }
//     });
//   }

function isValidUrl(url) {
    var regex = /^(http|https):\/\/[^ "]+$/;
    return regex.test(url);
}

function validateCategory(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var imageFile = data.get('image');
    if (data.get('name') == '') {
        myToastr(adminTranslation.admin_name_error, 'error');
        return false;
    } else if (data.get('color') == '') {
        myToastr(adminTranslation.admin_color_error, 'error');
        return false;
    } else if (imageFile.name=='') {
        myToastr(adminTranslation.admin_image_error, 'error');
        return false;
    } else {
        return true;
    }
}

function validateLiveNews(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var imageFile = data.get('image');
    if (data.get('company_name') == '') {
        myToastr(adminTranslation.admin_company_name_error, 'error');
        return false;
    } else if (data.get('url') == '') {
        myToastr(adminTranslation.admin_url_error, 'error');
        return false;
    } else if (!isValidUrl(data.get('url'))) {
        myToastr(adminTranslation.admin_valid_url_error, 'error');
        return false;
    } else if (imageFile.name=='') {
        myToastr(adminTranslation.admin_image_error, 'error');
        return false;
    } else {
        return true;
    }
}

function validateEpaper(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var imageFile = data.get('image');
    if (data.get('name') == '') {
        myToastr(adminTranslation.admin_name_error, 'error');
        return false;
    } else if (imageFile.name=='') {
        myToastr(adminTranslation.admin_image_error, 'error');
        return false;
    } else {
        return true;
    }
}

function validateQuotes(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var imageFile = data.get('background_image');
    var category_id = $(".category_id").map(function(){return $(this).val();}).get();
    if(category_id.length==0){
        myToastr(adminTranslation.admin_category_error, 'error');
        return false;
    }else if (data.get('title') == '') {
        myToastr(adminTranslation.admin_title_error, 'error');
        return false;
    } else if (imageFile.name=='') {
        if($("#image_name").val()==''){
            myToastr(adminTranslation.admin_background_image_error, 'error');
            return false;
        }else{
            return true;
        }        
    } else {
        return true;
    }
}

function validateProfile(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    var imageFile = data.get('photo');
    if (data.get('name') == '') {
        myToastr(adminTranslation.admin_name_error, 'error');
        return false;
    } else if (data.get('email') == '') {
        myToastr(adminTranslation.admin_email_error, 'error');
        return false;
    } else if (!emailRegex.test(data.get('email'))) {
        myToastr(adminTranslation.admin_valid_email_error, 'error');
        return false;
    } else if (data.get('phone') == '') {
        myToastr(adminTranslation.admin_phone_error, 'error');
        return false;
    } else {
        return true;
    }
}

function validateSocialMedia(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    if (data.get('name') == '') {
        myToastr(adminTranslation.admin_name_error, 'error');
        return false;
    } else if (data.get('url') == '') {
        myToastr(adminTranslation.admin_url_error, 'error');
        return false;
    } else if (!isValidUrl(data.get('url'))) {
        myToastr(adminTranslation.admin_valid_url_error, 'error');
        return false;
    } else if (data.get('icon') == '') {
        myToastr(adminTranslation.admin_icon_error, 'error');
        return false;
    } else {
        return true;
    }
}

function validateBlog(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    var editorContent = editorInstance.getData();
    console.log(editorContent);
    var category_id = $(".category_id").map(function(){return $(this).val();}).get();
    if(category_id.length==0){
        myToastr(adminTranslation.admin_category_error, 'error');
        return false;
    }else if (data.get('title') == '') {
        myToastr(adminTranslation.admin_title_error, 'error');
        return false;
    }else if (editorContent == '') {
        myToastr(adminTranslation.admin_description_error, 'error');
        return false;
    }else {
        return true;
    }
}

// function validateBlog(formid) {
//     var $form = $("#" + formid);
//     var data = new FormData($form[0]);
//     if (data.get('display_name') == '') {
//         myToastr(adminTranslation.admin_name_error, 'error');
//         return false;
//     }else {
//         return true;
//     }
// }

function validateAd(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    if (data.get('title') == '') {
        myToastr(adminTranslation.admin_title_error, 'error');
        return false;
    } else if (data.get('start_date') == '') {
        myToastr(adminTranslation.admin_start_date_error, 'error');
        return false;
    } else if (data.get('end_date') == '') {
        myToastr(adminTranslation.admin_end_date_error, 'error');
        return false;
    } else if (data.get('end_date')<data.get('start_date')) {
        myToastr(adminTranslation.admin_start_date_should_smaller_end_date_error, 'error');
        return false;
    } else if (data.get('frequency') == '') {
        myToastr(adminTranslation.admin_frequency_error, 'error');
        return false;
    } else if (data.get('media_type') == '') {
        myToastr(adminTranslation.admin_media_type_error, 'error');
        return false;
    } else {
        var mediaFile = data.get('media');
        var imageFile = data.get('image');
        if(data.get('media_type')=='image'){
            if(imageFile.name==''){
                myToastr(adminTranslation.admin_image_error, 'error');
                return false;
            }else{
                return true;
            }    
        }else if(data.get('media_type')=='video'){
            if(mediaFile.name==''){
                myToastr(adminTranslation.admin_video_error, 'error');
                return false;
            }else{
                return true;
            }
        }else if(data.get('media_type')=='image_url'){
            if(data.get('image_url')==''){
                myToastr(adminTranslation.admin_image_url_error, 'error');
                return false;
            }else{
                return true;
            }
        }else{
            return true;
        }
    }
}

function validateLanguage(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    // if (data.get('name') == '') {
    //     myToastr(adminTranslation.admin_name_error, 'error');
    //     return false;
    // } else 
    if (data.get('code_id') == '') {
        myToastr(adminTranslation.admin_code_error, 'error');
        return false;
    } else if (data.get('position') == '') {
        myToastr(adminTranslation.admin_position_error, 'error');
        return false;
    } else{
        return true;
    }
}

function validateRole(formid) {
    var $form = $("#" + formid);
    var data = new FormData($form[0]);
    if (data.get('name') == '') {
        myToastr(adminTranslation.admin_name_error, 'error');
        return false;
    } else {
        return true;
    }
}