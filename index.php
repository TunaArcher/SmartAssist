<!DOCTYPE html>
<html lang="en" data-bs-theme="">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico">
    <!-- Page Title -->
    <title>ChatBot | ConnectMe | Chat Application HTML Template</title>
    <!-- Page Stylesheets -->
    <link rel="stylesheet" href="./assets/css/bundle.css?v1310">
    <link rel="stylesheet" href="./assets/css/app.css?v1310">
    <style>
        .tyn-appbar-logo {
            text-align: center;
            border-right: 1px solid var(--border-color);
            padding: 1rem var(--bs-body-gap-x);
            display: block;
        }
    </style>

    <style>
        .holder {
            display: flex;
            flex-direction: row;
            justify-content: space-around;
            align-items: baseline;
            margin: 25px;
        }

        .message {
            display: inline-block;
            position: relative;
            max-width: 400px;
            background-color: #87cefa;
            margin-left: 12px;
            padding: 4px;
            border-radius: 18px 18px 18px 0;
            color: #fff;
            overflow-wrap: break-word;
            height: 30px;
        }

        .message::before {
            content: "";
            position: absolute;
            right: 100%;
            bottom: 0;
            width: 0;
            height: 0;
            border-width: 11.2px 0 4.8px 8px;
            border-style: solid;
            border-top-left-radius: 1.6px;
            border-bottom-left-radius: 8px;
            border-color: #87cefa;
            z-index: 10;
        }

        .message::after {
            content: "";
            position: absolute;
            right: 100%;
            bottom: 8px;
            width: 0;
            height: 0;
            border-width: 4px 0 4px 8px;
            border-style: solid;
            border-bottom-right-radius: 8px;
            border-color: #dbeafe;
            z-index: 20;
        }

        .message__item {
            margin: 0 4px;
            text-align: left;
            overflow: hidden;
        }

        .message--inverted {
            background-color: #b0c4de;
            margin-right: 12px;
            margin-left: 0;
            border-radius: 18px 18px 0 18px;
            color: #4d4d4d;
        }

        .message--inverted::before {
            right: auto;
            left: 100%;
            transform: scale(-1, 1);
            border-color: #b0c4de;
        }

        .message--inverted::after {
            right: auto;
            left: 100%;
            transform: scale(-1, 1);
        }

        .message--typing {
            min-width: 56px;
        }

        .message .message__dot {
            display: inline-block;
            height: 8px;
            width: 8px;
            margin: 0 1px;
            background-color: #fff;
            border-radius: 50%;
            opacity: 0.4;
        }

        .message .message__dot:nth-of-type(1) {
            animation: 1s blink infinite 0.3333s;
        }

        .message .message__dot:nth-of-type(2) {
            animation: 1s blink infinite 0.6666s;
        }

        .message .message__dot:nth-of-type(3) {
            animation: 1s blink infinite 0.9999s;
        }

        @keyframes blink {
            50% {
                opacity: 1;
            }
        }
    </style>
</head>

<body class="tyn-body">
    <div class="tyn-root">
        <nav class="tyn-appbar">
            <div class="tyn-appbar-wrap">
                <div class="tyn-appbar-logo" style="width: 100%; text-align: center;">
                    <a class="tyn-logo" href="">
                        <img src="https://evx.services/assets/img/logo.png">
                    </a>
                </div><!-- .tyn-appbar-logo -->
            </div><!-- .tyn-appbar-wrap -->
        </nav><!-- .tyn-appbar -->
        <div class="tyn-content tyn-content-full-height tyn-chatbot tyn-chatbot-page has-aside-base">
            <div class="tyn-aside tyn-aside-base">
                <div class="tyn-aside-head">
                    <div class="tyn-aside-head-text">
                        <h3 class="tyn-aside-title tyn-title">Chat Archive</h3>
                        <span class="tyn-subtext">200+ Conversations </span>
                    </div><!-- .tyn-aside-head-text -->
                    <div class="tyn-aside-head-tools">
                        <ul class="tyn-list-inline gap gap-3">
                            <li><a class="btn btn-icon btn-light btn-md btn-pill" href="chat-bot-welcome.html">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-lg" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M8 2a.5.5 0 0 1 .5.5v5h5a.5.5 0 0 1 0 1h-5v5a.5.5 0 0 1-1 0v-5h-5a.5.5 0 0 1 0-1h5v-5A.5.5 0 0 1 8 2" />
                                    </svg><!-- plus-lg -->
                                </a></li>
                        </ul>
                    </div><!-- .tyn-aside-head-tools -->
                </div><!-- .tyn-aside-head -->
                <div class="tyn-aside-body" data-simplebar>
                    <ul class="tyn-aside-list">
                        <li class="tyn-aside-item js-toggle-main active">
                            <div class="tyn-media-group">
                                <div class="tyn-media tyn-size-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-chat-right-text-fill" viewBox="0 0 16 16">
                                        <path d="M16 2a2 2 0 0 0-2-2H2a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h9.586a1 1 0 0 1 .707.293l2.853 2.853a.5.5 0 0 0 .854-.353zM3.5 3h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1 0-1m0 2.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1 0-1m0 2.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1 0-1" />
                                    </svg><!-- chat-right-text-fill -->
                                </div>
                                <div class="tyn-media-col">
                                    <div class="content">what can you do for me ?</div>
                                </div>
                            </div><!-- .tyn-media-group -->
                        </li><!-- .tyn-aside-item -->
                    </ul><!-- .tyn-aside-list -->
                </div><!-- .tyn-aside-body -->
            </div><!-- .tyn-aside -->
            <div class="tyn-main" id="tynMain">
                <ul class="tyn-list-inline d-md-none translate-middle-x position-absolute start-50 z-1">
                    <li>
                        <button class="btn btn-icon btn-pill btn-white js-toggle-main">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-x-lg" viewBox="0 0 16 16">
                                <path d="M2.146 2.854a.5.5 0 1 1 .708-.708L8 7.293l5.146-5.147a.5.5 0 0 1 .708.708L8.707 8l5.147 5.146a.5.5 0 0 1-.708.708L8 8.707l-5.146 5.147a.5.5 0 0 1-.708-.708L7.293 8z" />
                            </svg><!-- x-lg -->
                        </button>
                    </li>
                </ul><!-- .tyn-list-inline -->
                <div class="tyn-chat-body m-4 rounded-3" data-simplebar>
                    <div class="container px-0">
                        <div class="tyn-qa tyn-qa-bubbly" id="chat-box">
                    
                        </div><!-- .tyn-qa -->
                    </div><!-- .container -->
                </div><!-- .tyn-chat-body -->
                <div class="tyn-chat-form border-0 px-4">
                    <div class="container px-0">
                        <div class="ps-3 pe-4 py-3 bg-white mb-4 rounded-3">
                            <div class="tyn-chat-form-enter">
                                <textarea class="tyn-chat-form-input" id="user-input" placeholder="พิมพ์ข้อความที่นี่..." contenteditable></textarea>
                                <ul class="tyn-list-inline me-n2 my-1">
                                    <li><button class="btn btn-icon btn-white btn-md btn-pill" id="send-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send-fill" viewBox="0 0 16 16">
                                                <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855H.766l-.452.18a.5.5 0 0 0-.082.887l.41.26.001.002 4.995 3.178 3.178 4.995.002.002.26.41a.5.5 0 0 0 .886-.083zm-1.833 1.89L6.637 10.07l-.215-.338a.5.5 0 0 0-.154-.154l-.338-.215 7.494-7.494 1.178-.471z" />
                                            </svg><!-- send-fill -->
                                        </button></li>
                                </ul>
                            </div><!-- .tyn-chat-form-enter -->
                        </div>
                    </div><!-- .container -->
                </div><!-- .tyn-chat-form -->
            </div><!-- .tyn-main -->
        </div><!-- .tyn-content -->
    </div><!-- .tyn-root -->
    <!-- Page Scripts -->
    <script src="./assets/js/bundle.js?v1310"></script>
    <script src="./assets/js/app.js?v1310"></script>

    <script src="./scripts/script.js"></script>
</body>

</html>