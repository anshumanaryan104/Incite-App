const togglePassword = document.querySelector('#toggle-password');
const password = document.querySelector('#password');
const passwordIcon = document.querySelector('#password-icon'); 
const togglecPassword = document.querySelector('#toggle-cpassword');
const cpassword = document.querySelector('#cpassword');
const cpasswordIcon = document.querySelector('#cpassword-icon'); 
var optionCount = ($("#optionCount").val()!='')?$("#optionCount").val():0;
var adminTranslation = window.translations.messages;
$(document).ready(function() {
    $(".sub_category_id").select2({
      placeholder: adminTranslation.admin_select_an_option
      // Add other Select2 options as needed
    });
  });
togglePassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    // toggle the eye icon
    passwordIcon.classList.toggle('ti-eye');
    passwordIcon.classList.toggle('ti-eye-off');
});

togglecPassword.addEventListener('click', function (e) {
    // toggle the type attribute
    const type = cpassword.getAttribute('type') === 'password' ? 'text' : 'password';
    cpassword.setAttribute('type', type);
    // toggle the eye icon
    cpasswordIcon.classList.toggle('ti-eye');
    cpasswordIcon.classList.toggle('ti-eye-off');
});

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

function showQuestion(input_id) {
    if ($('#'+input_id).is(':checked')) {
        // If switch is turned on, unhide the div
        $('.showQuestion').removeClass('hide');
        $('#question').attr('required');
        $('.option').attr('required');
    } else {
        // If switch is turned off, hide the div
        $('.showQuestion').addClass('hide');
        $('#question').removeAttr('required');
        $('.option').removeAttr('required');
    }
}

function myToastr(msg, type) {
    toastr.remove();
    if (type == 'error') {
        toastr['error']('', msg);
    } else if (type == 'success') {
        toastr['success']('', msg);
    }
}

function selectLanguage(langCode){
    console.log(langCode);
    $(".formbody").addClass('hide');
    $("#translation_"+langCode).removeClass('hide');
}

function translateByThirdParty(name,code,input_id){
    var data = {};
    data.code = code;
    data.name = name;
    data.text = $("#"+input_id).val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: base_url+'/admin/translation-by-third-party',
        type: 'POST',
        data: data,
        dataType:'json',
        success: function (response) {
            if(response.status){
                $("#"+input_id).val(response.data);
                myToastr(response.message,'success');
            }else{
                myToastr(response.message,'error');
            }
        },
    });
}

function translateByGoogle(name,code,input_id){
    var data = {};
    data.code = code;
    data.name = name;
    data.text = $("#"+input_id).val();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url: base_url+'/admin/translation-by-google',
        type: 'POST',
        data: data,
        dataType:'json',
        success: function (response) {
            if(response.status){
                $("#"+input_id).val(response.data);
                myToastr(response.message,'success');
            }else{
                myToastr(response.message,'error');
            }
        },
    });
}

function showSubCategory(category_id, div_id) {
    var data = {};
    data.category_id = $("." + category_id).map(function() {
      return $(this).val();
    }).get();
    data.sub_category_id = $(".sub_category_id").map(function() {
        return $(this).val();
      }).get();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    $.ajax({
      url: base_url + '/admin/get-subcategory',
      type: 'POST',
      data: data,
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          loadHtmlByClass(div_id, response.data);
        } else {
          myToastr(response.message,'error');
        }
      },
    });
  }

function addRemoveOptions(type){
    if(type=='add'){
        optionCount = parseInt(optionCount) + 1;
        if(optionCount==4){
            $(".addOption").addClass('hide');
        }
        // fieldCounter++;
        var newField = $('<div class="row"><div class="mb-3 col-lg-6 col-xl-3 col-12 mb-0"><label class="form-label" for="option">'+adminTranslation.admin_option+'</label><input type="text" id="option" class="form-control" name="option[]" placeholder="'+adminTranslation.admin_option_placeholders+'" /></div><div class="mb-3 col-lg-12 col-xl-2 col-12 d-flex align-items-center mb-0"><button type="button" onclick="addRemoveOptions(`remove`);" class="btn btn-label-danger mt-4" data-repeater-delete><i class="ti ti-x ti-xs me-1"></i><span class="align-middle">'+adminTranslation.admin_delete+'</span></button></div></div><hr class="option-divider"/>');
        $(".showMoreOptions").append(newField);
    }else{
        optionCount = parseInt(optionCount) - 1;
        // $(".showMoreOptions .row:last").remove();
        $(".showMoreOptions .row:last, .showMoreOptions .option-divider:last").remove();
        if(optionCount<4){
            $(".addOption").removeClass('hide');
        }
    }    
}

function loadHtmlByClass(id,dataObj) {
    var html = "";
    var html = dataObj.html;
    $("."+id).html(html);
}
function setValue(id,value) {
    const targetElement = document.getElementById(id);
    targetElement.value = value;
}

function setTagsValue(value){
    $('input').on('itemAdded', function(event) {
        // event.item: contains the item
      });
    $('#seo_keyword').val(value);
}

$('#TagifyBasic').on('blur keyup', function() {
    var value = $(this).val();
    $('#seo_tag').val(value);
  });

function selectMediaType(type) {
    if(type=='image'){
        $(".showImage").removeClass('hide');
        $(".showVideoUrl").addClass('hide');
        $(".showVideo").addClass('hide');
        $("#change-video").val('');
        $("#video-preview").attr('src','');
        $("#video_url").val('');
    }else if(type=='video'){
        $(".showImage").addClass('hide');
        $(".showVideoUrl").addClass('hide');
        $(".showVideo").removeClass('hide');
        $("#change-picture").val('');
        $("#image-preview").attr('src','');
        $("#video_url").val('');
    }else if(type=='video_url'){
        $(".showImage").addClass('hide');
        $(".showVideoUrl").removeClass('hide');
        $(".showVideo").addClass('hide');
        $("#change-picture").val('');
        $("#image-preview").attr('src','');
        $("#change-video").val('');
        $("#video-preview").attr('src','');
    }
}

function deleteConfirm(id){
    Swal.fire({
        title: adminTranslation.admin_are_you_sure,
        text: adminTranslation.admin_delete_warning,
        icon: adminTranslation.admin_warning,
        showCancelButton: true,
        confirmButtonText: adminTranslation.admin_delete_warning_yes_button,
        cancelButtonText: adminTranslation.admin_delete_warning_no_button,
        customClass: {
          confirmButton: 'btn btn-primary me-3',
          cancelButton: 'btn btn-label-secondary'
        },
        buttonsStyling: false
    }).then(function (result) {
        if (result.value) {
            document.getElementById(id).submit();
        }
    });
    return false;
}

function submitImages(id){
    document.getElementById(id).submit();
}

function deleteImage(id){
    var data = {};
    data.image_id = id;
    $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: "POST",
        dataType: "json",
        url: base_url + "/admin/remove-image",
        data: data,
        success: function (response) {
            $("#previewsContainer").html(response.data.html);
        }
    });
}

function showImages(){
    $(".showLoader").addClass('hide');
    $("#previewsContainer").removeClass('hide');
}

function hideImages(){
    $("#previewsContainer").addClass('hide');
}

function showDropdownToSelectUser(value){
    if(value=='specific_user'){
        $('.show_user').removeClass('hide');
    }else{
        $('.show_user').addClass('hide');
    }
}
function showTitleInField(toId,fromId){
    var value = $("#"+toId).val();
    $("#"+fromId).val(value);
}
function selectAllSameData(source_class, des_class) {
    var sourceChecked = $("." + source_class + ":checked").length > 0;

    if (sourceChecked) {
        $("." + des_class).prop("checked", true);
    } else {
        $("." + des_class).prop("checked", false);
    }
}

function showSource(category_id, div_id) {
    var data = {};
    data.category_id = $("#category_id  ").val();
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
    });
    $.ajax({
      url: base_url + '/admin/get-source',
      type: 'POST',
      data: data,
      dataType: 'json',
      success: function(response) {
        if (response.status) {
          loadHtmlByClass(div_id, response.data);
        } else {
          myToastr(response.message,'error');
        }
      },
    });
  }

function translationKeyNotFoundError(){
    myToastr(adminTranslation.message_chat_gpt_key_not_found,'error');
}

function setTypeOfVoice(value){
    console.log(value);
    if(value=='google_text_to_speech'){
        $(".showgoogletextsettings").removeClass('hide');
    }else{
        $(".showgoogletextsettings").addClass('hide');
    }
}
function showVoiceType(input_id) {
    if ($('#'+input_id).is(':checked')) {
        $('.showVoiceType').removeClass('hide');
    } else {
        $('.showVoiceType').addClass('hide');
    }
}
