import jQuery from "jquery";
window.$ = jQuery;

let currentUrl = ''
let error = $('#error');
let getFileBtn = $('#get-file-btn');
let name = $('#name');
let res = $('#res');
let address = $('#address');

function getNameFromUrl() {
    $.ajax({
        url: "/get-name",
        type: "POST",
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            url: currentUrl,
        },
        beforeSend: function () {
            getFileBtn.prop('disabled', true);
            name.html('<div class="spinner-grow spinner-grow-sm text-primary" role="status">\n' +
                '  <span class="visually-hidden"></span>\n' +
                '</div>')
            res.empty();
        },
        success: function (response) {
            name.empty();
            name.html(response.text);
            getFileBtn.prop('disabled', false);
        },
        error: function (request, status, error) {
            name.html('Не удалось получить имя');
            console.log(request.responseText);
        }
    })
}

//Очистка инфы
function clearInfo() {
    address.val('');
    error.empty();
    res.empty();
    name.empty();
    $.ajax({
        url: "/delete-files",
        type: "POST",
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            url: currentUrl,
        },
        beforeSend: function () {},
        success: function (response) {
            console.log('Folder clear')
        },
        error: function (request, status, error) {
            console.log(request.responseText);
        }
    })
}

//ввод в строку URL
address.on('input', function () {
    error.empty();
    getFileBtn.prop('disabled', true);

    if ($('#switchUrl').is(':checked')) {
        const link = $(this).val().startsWith("https://youtu.be/");
        let linkLive = $(this).val().startsWith("https://www.youtube.com/live/");
        if (!link && !linkLive) {
            name.empty();
            error.text('Ссылка должна начинаться с https://youtu.be/')
            getFileBtn.prop('disabled', true);
        } else if (linkLive) {
            let str = $('#address').val().replace('https://www.youtube.com/live/', '');
            let index = str.indexOf('?');
            currentUrl = str.slice(0, index);
            getFileBtn.prop('disabled', false);
        } else {
            getFileBtn.prop('disabled', false);
            currentUrl = $(this).val();
        }
    } else {
        if ($('#address').val().length < 5) {
            name.empty();
            error.text('Проверьте ID видео');
            getFileBtn.prop('disabled', true);
            return;
        }
        currentUrl = $(this).val();
        getFileBtn.prop('disabled', false);
    }
   getNameFromUrl();
})

//кнопка Получить файл
getFileBtn.on('click', function() {
    $.ajax({
        url: "/get-file",
        type: "POST",
        contentType: "application/x-www-form-urlencoded;charset=ISO-8859-15",
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            url: currentUrl,
        },
        beforeSend: function () {
            getFileBtn.prop('disabled', true);
            getFileBtn.html(
                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>' +
                ' Скачивние...'
            );
            res.empty();
        },
        success: function (response) {
            getFileBtn.prop('disabled', true);
            getFileBtn.html('Получить файл');
            name.empty();
            let img;
            if (response.webp) {
                img = `
                      <picture>
                      <source type="image/webp" srcset="${response.thumb}">
                      <img class="d-block mx-auto mx-lg-0" src="${response.thumb}"
                           width="360" height="203" style="border-radius: 12px;" alt="">
                      </picture>`;
            } else if (response.thumb) {
                img = `
                    <img class="d-block" src="${response.thumb}"
                         width="360" height="203" style="border-radius: 12px;" alt="">`;
            } else {
                console.log('no image');
            }

            let this_name = `<div class="mt-2 text-red">Undefined</div>`;
            if (response.name !== 'undefined') {
                this_name = `<div class="mt-2 text-white">${response.name}</div>
                          <a href="${response.file}" type="audio/mp3" download class="btn btn-success mt-2 btn-lg">Скачать</a>`
            }
            // res.append(img);
            res.append(this_name);
        },
        error: function (request, status, error) {
            getFileBtn.prop('disabled', true);
            getFileBtn.html('Получить файл');
            const name = `<div class="text-danger">
                          <p>Error!</p>
                          <p class="text-info">${request.responseText}</p></div>`
            res.append(name);
            console.log(request);
        }
    });
})

//кнопка Очистить
$('#clear-btn').on('click', function(){
    clearInfo();
})
$('#switchUrl').on('click', function(){
    clearInfo();
})

