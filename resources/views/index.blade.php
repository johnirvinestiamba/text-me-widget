<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Text Me Widget</title>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js" integrity="sha512-bZS47S7sPOxkjU/4Bt0zrhEtWx0y0CRkhEp8IckzK+ltifIIE9EMIMTuT/mEzoIMewUINruDBIR/jJnbguonqQ==" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="{{asset('css/font-awesome.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/app.css')}}">
    </head>
    <body>
        <div class="container">
            <span class>We will text you</span>
            <input type="text" class="form-control" name="replyTo" id="replyTo" placeholder="Enter your phone number">
            <textarea name="message" class="form-control" id="message" cols="20" rows="3" placeholder="Enter topic"></textarea>
            <button type="button" class="btn btn-light" id="submit">Submit</button>
        </div>
        <script>
            const csrfToken = document.head.querySelector('meta[name="csrf-token"]');
            const submitBtn = document.querySelector('#submit');

            // axios defaults
            axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
            axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken.content;

            // On button click
            submitBtn.onclick = () => {
                // disable button
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i>';

                // UI form
                const replyToInput = document.querySelector('#replyTo');
                const messageInput = document.querySelector('#message');

                // Validate Phone Number
                const phoneRegex = new RegExp('^[0-9]{10,11}$');
                if (!phoneRegex.test(replyToInput.value)) {
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
                if (!messageInput.value.length) {
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

                const postData = {
                    reply_to: replyToInput.value,
                    message: messageInput.value
                };

                axios.post('/api/message/send', postData).then((response) => {
                    replyToInput.value = '';
                    messageInput.value = '';
                    if (response.status === 200) {
                        submitBtn.innerHTML = '<i class="fa fa-check" aria-hidden="true"></i>';
                        submitBtn.classList.add('btn-success');
                        submitBtn.classList.remove('btn-light');
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
                });;
            }
        </script>
    </body>
</html>
