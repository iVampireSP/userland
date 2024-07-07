window.addEventListener("load", function (event) {
    if (window.RECAPTCHA_SITE_KEY === undefined) {
        return
    }

    if (typeof grecaptcha === 'undefined') {
        grecaptcha = {};
    }

    grecaptcha.ready = function (cb) {
        if (typeof grecaptcha === 'undefined') {
            // window.__grecaptcha_cfg is a global variable that stores reCAPTCHA's
            // configuration. By default, any functions listed in its 'fns' property
            // are automatically executed when reCAPTCHA loads.
            const c = '___grecaptcha_cfg';
            window[c] = window[c] || {};
            (window[c]['fns'] = window[c]['fns'] || []).push(cb);
        } else {
            cb();
        }
    }


    grecaptcha.ready(function () {
        load()
    });

}, false);


function load() {
    const recaptcha_site_key = window.RECAPTCHA_SITE_KEY;


    let f = document.getElementsByClassName('recaptcha-form')

    for (let i = 0; i < f.length; i++) {
        // add recaptcha placeholder
        let recaptcha_placeholder = document.createElement('div');
        recaptcha_placeholder.classList.add('g-recaptcha');
        f[i].appendChild(recaptcha_placeholder);


        // init recaptcha
        let widgetId = grecaptcha.render(recaptcha_placeholder, {
            'sitekey': recaptcha_site_key,
            'size': 'invisible',
            'callback': function (response) {
                //submit
                f[i].submit();
            }
        });

        // 如果被点击了，则加载 recaptcha
        // f[i].addEventListener('click', function(event) {
        //     // 防止标记，用于检测是否点击过，如果点击过，就不加载
        //     if (event.target.getAttribute('data-recaptcha-clicked') === 'true') {
        //         return;
        //     }
        //
        //     event.target.setAttribute('data-recaptcha-clicked', 'true');
        //
        //     grecaptcha.execute(widgetId);
        // });


        // add attr
        f[i].setAttribute('data-recaptcha-widget-id', widgetId);

        // lock all input
        // for (let j = 0; j < f[i].elements.length; j++) {
        //     // 备份所有的input的disabled 属性
        //     f[i].elements[j].setAttribute('data-disabled', f[i].elements[j].disabled);
        //     f[i].elements[j].disabled = true;
        // }

        f[i].addEventListener('submit', function (event) {
            event.preventDefault();

            let form = event.target;

            grecaptcha.execute(form.getAttribute('data-recaptcha-widget-id')).then(function () {
                console.log('recaptcha loaded')
            }).catch(function (error) {
                console.log(error)
            });


            //
            // if (form.querySelector('input[name="g-recaptcha-response"]')) {
            // }
            //
            // console.log(form.getAttribute('data-recaptcha-widget-id'))

            // let resp = grecaptcha.getResponse(form.getAttribute('data-recaptcha-widget-id'))

            // 检查是否


            // console.log(resp)
            // let input = document.createElement('input');
            // input.type = 'hidden';
            // input.name = 'g-recaptcha-response';
            // console.log(grecaptcha.getResponse())
            // input.value = grecaptcha.getResponse();

            // form.appendChild(input);

        });
    }

    // grecaptcha.execute();
}
