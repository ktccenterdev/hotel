$(document).ready(function() {
    'use strict'
    
    loaderOut();

    $(".formelement").hide();    

    $(window).resize(function(){
      minimizeMenu();
    });

    minimizeMenu();

    function minimizeMenu() {
      if(window.matchMedia('(min-width: 992px)').matches && window.matchMedia('(max-width: 1299px)').matches) {
        // show only the icons and hide left menu label by default
        $('.menu-item-label,.menu-item-arrow').addClass('op-lg-0-force d-lg-none');
        $('body').addClass('collapsed-menu');
        $('.show-sub + .br-menu-sub').slideUp();
      } else if(window.matchMedia('(min-width: 1300px)').matches && !$('body').hasClass('collapsed-menu')) {
        $('.menu-item-label,.menu-item-arrow').removeClass('op-lg-0-force d-lg-none');
        $('body').removeClass('collapsed-menu');
        $('.show-sub + .br-menu-sub').slideDown();
      }
    }
    return false;
});

function show() {
    $(".formelement").show();
  }
  function hide() {
    $(".formelement").hide();
  }

function getPage(url) {
    loaderIn();
    this.axios.get(url)
        .then(function(response) {
            if (response.data.success) {
                rendenContent(response.data.data)
            } else {
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
            }
            loaderOut();
        })
        .catch(function(error) {
            $("#divError").fadeIn();
            if (error.response) {
                console.log(error.response.data);
                console.log(error.response.status);
                console.log(error.response.headers);
                $(".messageError").text(error.response.message);
            } else if (error.request) {
                console.log(error.request);
                console.log(error.request);
                $(".messageError").text(error.request);
            } else {
                console.log('Error', error.message);
                console.log('Error', error.message);
                $(".messageError").text(error.message);
            }
            loaderOut();
        })
        .then(function() {
            loaderOut();
        });

}


function rendenContent(content) {
    $("#js-main-content").empty();
    $("#js-main-content").append(content);
}

$(".js-form-sendallocation").on('submit', function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var method = $(this).attr("method");
    var data = $(this).serialize();
    var idForm = $(this).attr('id');
    var element = $("#" + idForm + " #btn-save-nouveau");
    element.html('Chargement... <span class="fa fa-spinner fa-pulse"></span>');
    element.attr("disabled", "true");
    Postsaveallocation(url, data, element);

})

$(".js-form-sendavecfile").on('submit', function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var form = $('.sauvegardededonne')[0];
    var data = new FormData(form);
    var method = $(this).attr("method");
    var idForm = $(this).attr('id');
    var element = $("#" + idForm + " #btn-save-nouveau");
    element.html('Chargement... <span class="fa fa-spinner fa-pulse"></span>');
    element.attr("disabled", "true");

    if (method === "POST") {
        Postsenddata(url, data);
    } else {
        // Put(url, data, element);
    }

})

function Postsenddata(url, dataForm) {
    axios.post(url, dataForm, { "Content-Type": "multipart/form-data" })
        .then(function(response) {
            if (response.data.success) {
                sweetNotification(response.data.message);
                getPage(response.data.link);
            } else {
                if (response.data.type == "text") {
                    $("#divError").fadeIn();
                    $(".messageError").text(response.data.message);
                } else {
                    sweetNotificationbad(response.data.message);
                }
            }
        })
        .catch(function(error) {
            if (response.data.type == "text") {
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
            } else {
                sweetNotificationbad(response.data.message);
            }
        });
}


function Delele(url, button, textContent) {

    axios.delete(url)
        .then(function(response) {
            if (response.data.success) {
                getPage(response.data.link);
                sweetNotification(response.data.message);
            } else {
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
                button.removeAttr("disabled");
                button.html(textContent);
            }
        })
        .catch(function(error) {
            button.removeAttr("disabled");
            button.html(textContent);
            //writeError(error)
        });

}

function Put(url, dataForm, button = null) {
    axios.put(url, dataForm)
        .then(function(response) {
            if (response.data.success) {
                getPage(response.data.link);
                sweetNotification(response.data.message);
            } else {
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
            }
            hideLoaderButton(button);
        })
        .catch(function(error) {
            writeError(error)
            hideLoaderButton(button);
        });
}

function Post(url, dataForm, button = null) {
    axios.post(url, dataForm)
        .then(function(response) {
            if (response.data.success) {
                // $('.modal').modal('hide');
                getPage(response.data.link);
                sweetNotification(response.data.message);
            } else {
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
            }
            hideLoaderButton(button);
        })
        .catch(function(error) {
            hideLoaderButton(button);
            writeError(error)
        });
}

function Postsaveallocation(url, dataForm, button = null) {
    axios.post(url, dataForm)
        .then(function(response) {
            if (response.data.success) {
                //alert("nous somme la");
                //$('.modal').modal('hide');
                alert("merci beaucoup ca va");
                sweetNotification(response.data.message);
                getPage(response.data.link);

            } else {
                if (response.data.type == "text") {
                    // $("#divError").fadeIn();
                    // $(".messageError").text(response.data.message);
                } else {
                    sweetNotificationbad(response.data.message);
                }


            }
            //hideLoaderButton(button);
        })
        .catch(function(error) {
            // hideLoaderButton(button);
            // writeError(error)
        });
}

function sweetNotificationbad(message, footer = null) {
    
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        footer: footer
    })
}






function sweetNotification(message) {
    Swal.fire({
        position: 'top-end',
        icon: 'success',
        title: message,
        showConfirmButton: false,
        timer: 2000
    })
}

function showNouveau() {
    var v = document.getElementById("nouveau");
    var b = document.getElementById("btn-nouveau");
    var d = document.getElementById("divError");
    if (v.style.display === "none") {
        v.style.display = "block";
        b.style.display = "none";
    } else {
        $('form')[0].reset();
        v.style.display = "none";
        b.style.display = "block";
    }
    d.style.display = "none";
}

function makeEditable() {
    var b = document.getElementById("btn-makeEditable");
    var d = document.getElementById("divError");
    var t = document.getElementById("titre-nouveau");
    var div_btn = document.getElementById("btn-footer");
    if (b.style.display === "none") {
        b.style.display = "block";
        t.style.display = "none";
        div_btn.style.display = "none";
        $("form#form-send :input").each(function() {
            $(this).attr("disabled", true);
        });
    } else {
        $('form')[0].reset();
        t.style.display = "block";
        b.style.display = "none";
        $("form#form-send :input").each(function() {
            $(this).removeAttr("disabled");
        });
        div_btn.style.display = "block";
    }
    d.style.display = "none";
}

function hideLoaderButton(button) {
    button.removeAttr("disabled");
    button.html('Enrégistrer');
}

function writeError(error) {
    var message = "";
    if (error.request) {
        // The request was made but no response was received
        console.log();
        message = error.request;
    } else {
        message = error.message;
    }

    $("#divError").fadeIn();
    $(".messageError").text(message);
}

function showError(error) {
    $("#divError").fadeIn();
    $(".messageError").text(message);
    sweetNotificationbad(message);
}

function chargerFormNouveau(id, url, tab) {
    var element = $("#editer" + id);
    tab.forEach(item => {
        $("#" + item).val(element.data(item)).change();
    });
    $("#form-send").attr("method", "PUT");
    $("#form-send").attr("action", url);
    $(".titre-nouveau").text("Modification");
    $("#nouveau").css("display", "block");
    $("#btn-nouveau").css("display", "none");
}

function showFilter() {
    var f = document.getElementById("form-filter");
    var b = document.getElementById("btn-show-filter");
    if (f.style.display === "none") {
        f.style.display = "block";
        b.style.display = "none";
    } else {
        $('form')[0].reset();
        f.style.display = "none";
        b.style.display = "block";
    }

    $('.select2').select2({
        width: 'resolve'
    });

    // Select2 by showing the search
    $('.select2-show-search').select2({
        minimumResultsForSearch: ''
    });

    // Select2 with tagging support
    $('.select2-tag').select2({
        tags: true,
        tokenSeparators: [',', ' ']
    });
}