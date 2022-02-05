$(document).ready(function() {});




$(".ajax-link").on('click', function(e) {

    e.preventDefault();
    var url = $(this).attr('href');
     loaderIn();
    getPage(url);
});

function getPage(url) {
    loaderIn();
    this.axios.get(url)
        .then(function(response) {
            console.log(response);
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


$(".js-form-send").on('submit', function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    var method = $(this).attr("method");
    var data = $(this).serialize();

    if (method === "DELETE") {
        var button = $(this).children("button[type=submit]");
        var textContent = button.html();
        Swal.fire({
            title: "Voulez vous supprimer cet élément?",
            text: "Cette opération est irréversible!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: "Oui supprimer!"
        }).then((result) => {
            if (result.isConfirmed) {
                button.html('<span class="fa fa-spinner fa-pulse"></span>');
                button.attr("disabled", "true");
                Delele(url, button, textContent);
            }
        })
    } else {
        var idForm = $(this).attr('id');
        var element = $("#" + idForm + " #btn-save-nouveau");
        element.html('Chargement... <span class="fa fa-spinner fa-pulse"></span>');
        element.attr("disabled", "true");
        if (method === "POST") {
            Post(url, data, element);
        } else {
            Put(url, data, element);
        }
    }
})




// $(".js-form-sendallocation").on('submit', function(e) {
//     e.preventDefault();
//     var url = $(this).attr('action');
//     var method = $(this).attr("method");
//     var data = $(this).serialize();
//     var idForm = $(this).attr('id');
//     var element = $("#" + idForm + " #btn-save-nouveau");
//     element.html('Chargement... <span class="fa fa-spinner fa-pulse"></span>');
//     element.attr("disabled", "true");
//     Postsaveallocation(url, data, element);
    
// })


    $(".js-form-sendavecfile").on('submit',function(e){
        e.preventDefault();
        var url = $(this).attr('action');
        console.log(url);
        var form = $('.sauvegardededonne')[0];
        var data = new FormData(form);
        var method = $(this).attr("method");
            if(method === "POST"){
                Postsenddata(url,data);
            }else{
               Put(url, data, element);
            }
        
    })






$(".js-form-sendavecfile").on('submit', function(e) {
    e.preventDefault();
    var url = $(this).attr('action');
    console.log(url);
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
            if(response.data.success){
                sweetNotification(response.data.message);
                getPage(response.data.link);
            }else{
                if(response.data.type=="text"){
                    $("#divError").fadeIn();
                    $(".messageError").text(response.data.message);
                }else{
                    sweetNotificationbad(response.data.message);
                }
            }
        })
        .catch(function(error) {
            if(response.data.type=="text"){
                $("#divError").fadeIn();
                $(".messageError").text(response.data.message);
            }else{
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



function Post(url, dataForm, button = null) {
    axios.post(url, dataForm)
        .then(function(response) {
            if (response.data.success) {
                //alert("nous somme la");
                //$('.modal').modal('hide');
                sweetNotification(response.data.message);
                getPage(response.data.link);

            } else {
                if(response.data.type=="text"){
                    $("#divError").fadeIn();
                    $(".messageError").text(response.data.message);
                }else{
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
                if(response.data.type=="text"){
                    // $("#divError").fadeIn();
                    // $(".messageError").text(response.data.message);
                }else{
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

function sweetNotificationbad(message) {
    Swal.fire({
        position: 'top-end',
        icon: 'error',
        title: message,
        showConfirmButton: false,
        timer: 2000
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

