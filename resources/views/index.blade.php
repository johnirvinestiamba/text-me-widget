<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Text Me Widget</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>
        <script src="{{asset('js/helpers.js')}}"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/app.css')}}">

        <style>
            .textMeLabel {
                <?php if (array_key_exists('labelAlign', $_GET) && in_array($_GET['labelAlign'], ['left', 'right', 'center'])) : ?>
                    text-align: <?= $_GET['labelAlign'] . ';' ?>
                <?php endif; ?>
                <?php if (array_key_exists('labelColor', $_GET)): ?>
                    color: <?= $_GET['labelColor'] . ';' ?>
                <?php endif; ?>
                <?php if (array_key_exists('labelSize', $_GET)): ?>
                    font-size: <?= $_GET['labelSize'] . ';' ?>
                <?php endif; ?>
            }

            #messageSentLabel {
                <?php if (array_key_exists('sentLabelAlign', $_GET) && in_array($_GET['sentLabelAlign'], ['left', 'right', 'center'])) : ?>
                    text-align: <?= $_GET['sentLabelAlign'] . ';' ?>
                <?php endif; ?>
                <?php if (array_key_exists('sentLabelColor', $_GET)): ?>
                    color: <?= $_GET['sentLabelColor'] . ';' ?>
                <?php endif; ?>
                <?php if (array_key_exists('sentLabelSize', $_GET)): ?>
                    font-size: <?= $_GET['sentLabelSize'] . ';' ?>
                <?php else: ?>
                    font-size: 10px;
                <?php endif; ?>
            }

            .btn-light {
                <?php if (array_key_exists('btnFontColor', $_GET)): ?>
                    color: <?= $_GET['btnFontColor'] . '!important;' ?>
                <?php endif; ?>
                <?php if (array_key_exists('btnFontSize', $_GET)): ?>
                    font-size: <?= $_GET['btnFontSize'] . '!important;' ?>
                <?php endif; ?>
                <?php if (array_key_exists('btnBgColor', $_GET)): ?>
                    background-color: <?= $_GET['btnBgColor'] . ' !important;' ?>
                <?php endif; ?>
            }

            .btn-light:hover {
                <?php if (array_key_exists('btnHoverBgColor', $_GET)): ?>
                    background-color: <?= $_GET['btnHoverBgColor'] . '!important;' ?>
                <?php endif; ?>
                <?php if (array_key_exists('btnHoverFontColor', $_GET)): ?>
                    color: <?= $_GET['btnHoverFontColor'] . '!important;' ?>
                <?php endif; ?>
            }
        </style>
    </head>
    <body>
        <div class="container">
            <span class="textMeLabel">
                <?php if (array_key_exists('label', $_GET)): ?>
                    <?= $_GET['label'] ?>
                <?php else: ?>
                    We will text you
                <?php endif; ?>
            </span>
            <?php 
                $placeholder = array_key_exists('messagePlaceholder', $_GET) ? $_GET['messagePlaceholder'] : 'Enter topic';
                $recipient = array_key_exists('recipient', $_GET) ? $_GET['recipient'] : '';
            ?>
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text" id="basic-addon1">+1</span>
                </div>
                <input type="text" class="form-control" name="replyTo" id="replyTo" placeholder="(xxx) xxx-xxxx">
            </div>
            <textarea name="message" class="form-control" id="message" cols="20" rows="3"placeholder="<?= $placeholder ?>"></textarea>
            <button type="button" class="btn btn-light" id="submit">Submit</button>
            <?php $sentLabel = array_key_exists('sentLabel', $_GET) ? $_GET['sentLabel'] : 'Message sent. We\'ll get in touch with you soon!'; ?>
            <span id="messageSentLabel" style="display: none"><?= $sentLabel ?></span>
        </div>
        <script>
            const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
            const submitBtn = document.querySelector('#submit');
            const sentLabel = document.querySelector('#messageSentLabel');
            const replyToInput = document.querySelector('#replyTo');
            const messageInput = document.querySelector('#message');

            // mask replyToInput
            replyToInput.addEventListener('input', (e) => {
                let x = e.target.value.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
            });

            replyToInput.addEventListener('paste', async (e) => {
                let copiedPhNo = e.clipboardData.getData('Text');

                copiedPhNo = copiedPhNo.length > 10 ? copiedPhNo.substring(1) : copiedPhNo;
                let x = copiedPhNo.replace(/\D/g, '').match(/(\d{0,3})(\d{0,3})(\d{0,4})/);
                e.target.value = !x[2] ? x[1] : '(' + x[1] + ') ' + x[2] + (x[3] ? '-' + x[3] : '');
            });

            // axios defaults
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;

            // On button click
            submitBtn.onclick = () => {
                // disable button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';

                const postData = {
                    reply_to: '1' + replyToInput.value.replace(/[\(\)\s\-]/g, ''),
                    message: messageInput.value,
                    destination: '<?= $recipient ?>'
                };

                // Validate Phone Number
                const phoneRegex = new RegExp('^[0-9]{10,11}$');
                if (!phoneRegex.test(postData.reply_to)) {
                    submitBtn.classList.add('btn-danger');
                    submitBtn.classList.remove('btn-light');
                    submitBtn.innerHTML = 'Invalid Phone Number';
                    replyToInput.focus();
                    setTimeout(() => {
                        submitBtn.classList.add('btn-light');
                        submitBtn.classList.remove('btn-danger');
                        submitBtn.innerHTML = 'Submit';
                        submitBtn.disabled = false;
                    }, 2000);
                    return;
                }

                // Validate Message
                if (!postData.message.length) {
                    submitBtn.classList.add('btn-danger');
                    submitBtn.classList.remove('btn-light');
                    submitBtn.innerHTML = 'Can\'t send empty message';
                    messageInput.focus();
                    setTimeout(() => {
                        submitBtn.classList.add('btn-light');
                        submitBtn.classList.remove('btn-danger');
                        submitBtn.innerHTML = 'Submit';
                        submitBtn.disabled = false;
                    }, 2000);
                    return;
                }

                axios.post('/api/message/send', postData).then((response) => {
                    replyToInput.value = '';
                    messageInput.value = '';
                    if (response.status === 200) {
                        submitBtn.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
                        fadeIn(sentLabel);
                        submitBtn.classList.add('btn-success');
                        submitBtn.classList.remove('btn-light');
                        setTimeout(() => {
                            fadeOut(sentLabel);
                        }, 4000);
                        setTimeout(() => {
                            submitBtn.innerHTML = 'Submit';
                            submitBtn.disabled = false;
                            submitBtn.classList.add('btn-light');
                            submitBtn.classList.remove('btn-success');
                        }, 2000);
                    } else {
                        submitBtn.classList.add('btn-danger');
                        submitBtn.classList.remove('btn-light');
                        submitBtn.innerHTML = 'Please try again.';
                        setTimeout(() => {
                            submitBtn.classList.add('btn-light');
                            submitBtn.classList.remove('btn-danger');
                            submitBtn.innerHTML = 'Submit';
                            submitBtn.disabled = false;
                        }, 2000);
                    }
                }).catch(function (error) {
                    replyToInput.value = '';
                    messageInput.value = '';
                    submitBtn.classList.add('btn-danger');
                    submitBtn.classList.remove('btn-light');
                    submitBtn.innerHTML = 'Please try again.';
                    setTimeout(() => {
                        submitBtn.classList.add('btn-light');
                        submitBtn.classList.remove('btn-danger');
                        submitBtn.innerHTML = 'Submit';
                        submitBtn.disabled = false;
                    }, 2000);
                });
            }
        </script>
    </body>
</html>
