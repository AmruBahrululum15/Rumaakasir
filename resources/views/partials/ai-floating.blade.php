@if(auth()->check() && app(\App\Tenancy\TenantContext::class)->id() && app(\App\Services\PackageAccess::class)->allows('ai_agent'))

<div id="peAiRoot">

    {{-- =========================================================
         FLOATING AI BUTTON
    ========================================================== --}}
    <button
        type="button"
        id="peAiLauncher"
        class="pe-ai-launcher"
        aria-label="Buka AI Assistant"
        title="AI Assistant"
    >

        <span class="pe-ai-launcher-icon">
            <i class="fas fa-robot"></i>
        </span>

        <span class="pe-ai-online-dot"></span>

    </button>


    {{-- =========================================================
         AI WINDOW
    ========================================================== --}}
    <section
        id="peAiWindow"
        class="pe-ai-window"
        aria-hidden="true"
    >

        {{-- =====================================================
             HEADER
        ====================================================== --}}
        <header class="pe-ai-header">

            <div class="pe-ai-brand">

                <div class="pe-ai-brand-icon">
                    <i class="fas fa-robot"></i>
                </div>

                <div class="pe-ai-brand-info">

                    <div class="pe-ai-brand-title">
                        POS Enterprise AI
                    </div>

                    <div class="pe-ai-brand-status">

                        <span class="pe-ai-status-dot"></span>

                        Siap membantu

                    </div>

                </div>

            </div>


            {{-- WINDOW ACTIONS --}}
            <div class="pe-ai-header-actions">

                <button
                    type="button"
                    id="peAiMinimize"
                    class="pe-ai-header-btn"
                    title="Minimalkan"
                >
                    <i class="fas fa-minus"></i>
                </button>


                <button
                    type="button"
                    id="peAiMaximize"
                    class="pe-ai-header-btn"
                    title="Perbesar"
                >
                    <i class="fas fa-expand"></i>
                </button>


                <button
                    type="button"
                    id="peAiClose"
                    class="pe-ai-header-btn pe-ai-close-btn"
                    title="Tutup"
                >
                    <i class="fas fa-times"></i>
                </button>

            </div>

        </header>


        {{-- =====================================================
             BODY
        ====================================================== --}}
        <div class="pe-ai-body">


            {{-- =================================================
                 CHAT AREA
            ================================================== --}}
            <div
                id="peAiMessages"
                class="pe-ai-messages"
            >

                {{-- =================================================
                     WELCOME MESSAGE
                ================================================= --}}
                <div class="pe-ai-message pe-ai-message-ai">

                    <div class="pe-ai-message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>


                    <div class="pe-ai-message-content">

                        <div class="pe-ai-bubble pe-ai-welcome-bubble">

                            <div class="pe-ai-welcome-title">
                                Halo! 👋
                            </div>


                            <div class="pe-ai-welcome-text">
                                Saya siap membantu membaca dan menganalisis data POS Enterprise.
                            </div>


                            {{-- CAPABILITIES --}}
                            <div class="pe-ai-capabilities">

                                <div class="pe-ai-capability">

                                    <span class="pe-ai-capability-icon">
                                        <i class="fas fa-chart-line"></i>
                                    </span>

                                    <span class="pe-ai-capability-text">
                                        Penjualan
                                    </span>

                                </div>


                                <div class="pe-ai-capability">

                                    <span class="pe-ai-capability-icon">
                                        <i class="fas fa-box"></i>
                                    </span>

                                    <span class="pe-ai-capability-text">
                                        Stok
                                    </span>

                                </div>


                                <div class="pe-ai-capability">

                                    <span class="pe-ai-capability-icon">
                                        <i class="fas fa-star"></i>
                                    </span>

                                    <span class="pe-ai-capability-text">
                                        Produk terlaris
                                    </span>

                                </div>


                                <div class="pe-ai-capability">

                                    <span class="pe-ai-capability-icon">
                                        <i class="fas fa-shopping-cart"></i>
                                    </span>

                                    <span class="pe-ai-capability-text">
                                        Pembelian
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =================================================
                 QUICK QUESTIONS
            ================================================== --}}
            <div class="pe-ai-suggestions-wrap">

                <div class="pe-ai-suggestions-label">
                    Pertanyaan cepat
                </div>


                <div class="pe-ai-suggestions">

                    <button
                        type="button"
                        class="pe-ai-suggestion"
                        data-message="Berapa total penjualan hari ini?"
                    >
                        Penjualan hari ini
                    </button>


                    <button
                        type="button"
                        class="pe-ai-suggestion"
                        data-message="Produk apa yang paling banyak terjual?"
                    >
                        Produk terlaris
                    </button>


                    <button
                        type="button"
                        class="pe-ai-suggestion"
                        data-message="Produk apa saja yang stoknya menipis?"
                    >
                        Stok menipis
                    </button>


                    <button
                        type="button"
                        class="pe-ai-suggestion"
                        data-message="Bagaimana ringkasan pembelian terbaru?"
                    >
                        Pembelian
                    </button>

                </div>

            </div>


            {{-- =================================================
                 INPUT AREA
            ================================================== --}}
            <div class="pe-ai-input-area">

                <form id="peAiForm">

                    @csrf


                    <div class="pe-ai-input-box">

                        <input
                            type="text"
                            id="peAiInput"
                            class="pe-ai-input"
                            placeholder="Tanyakan sesuatu tentang POS..."
                            maxlength="2000"
                            autocomplete="off"
                        >


                        <button
                            type="submit"
                            id="peAiSend"
                            class="pe-ai-send"
                            title="Kirim"
                        >
                            <i class="fas fa-arrow-up"></i>
                        </button>

                    </div>

                </form>


                <div class="pe-ai-footer">

                    <div class="pe-ai-powered">

                        <i class="fas fa-sparkles"></i>

                        <span>
                            Gemini AI
                        </span>

                    </div>


                    <button
                        type="button"
                        id="peAiReset"
                        class="pe-ai-reset"
                    >
                        Reset chat
                    </button>

                </div>

            </div>

        </div>

    </section>

</div>


<style>

/* ============================================================
   ROOT
============================================================ */

#peAiRoot {

    position: relative;

    z-index: 99999;

}


/* ============================================================
   FLOATING BUTTON
============================================================ */

.pe-ai-launcher {

    position: fixed;

    right: 24px;
    bottom: 24px;

    width: 58px;
    height: 58px;

    padding: 0;

    border: 0;

    border-radius: 18px;

    background:
        linear-gradient(
            135deg,
            #ff8a2a 0%,
            #f36b0a 100%
        );

    color: #ffffff;

    display: flex;

    align-items: center;
    justify-content: center;

    cursor: pointer;

    outline: none;

    box-shadow:
        0 14px 35px
        rgba(243, 107, 10, .30);

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.pe-ai-launcher:hover {

    transform:
        translateY(-3px);

    box-shadow:
        0 18px 42px
        rgba(243, 107, 10, .36);

}


.pe-ai-launcher:focus {

    outline: none;

}


.pe-ai-launcher-icon {

    width: 40px;
    height: 40px;

    border-radius: 13px;

    background:
        rgba(255,255,255,.15);

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 18px;

}


.pe-ai-online-dot {

    position: absolute;

    top: 5px;
    right: 5px;

    width: 10px;
    height: 10px;

    border-radius: 50%;

    background: #22c55e;

    border: 2px solid #ffffff;

}


/* ============================================================
   WINDOW
============================================================ */

.pe-ai-window {

    position: fixed;

    right: 24px;
    bottom: 94px;

    width: 420px;
    height: 620px;

    min-width: 350px;
    min-height: 470px;

    max-width:
        calc(100vw - 30px);

    max-height:
        calc(100vh - 120px);

    display: none;

    flex-direction: column;

    overflow: hidden;

    background:
        rgba(255,255,255,.98);

    border:
        1px solid #e7e3eb;

    border-radius: 20px;

    box-shadow:
        0 28px 75px
        rgba(23,32,51,.18),

        0 8px 25px
        rgba(23,32,51,.08);

    resize: both;

}


.pe-ai-window.pe-ai-open {

    display: flex;

    animation:
        pe-ai-window-in .18s ease;

}


@keyframes pe-ai-window-in {

    from {

        opacity: 0;

        transform:
            translateY(10px)
            scale(.985);

    }

    to {

        opacity: 1;

        transform:
            translateY(0)
            scale(1);

    }

}


.pe-ai-window.pe-ai-minimized {

    height: 70px;

    min-height: 70px;

    resize: none;

}


.pe-ai-window.pe-ai-minimized
.pe-ai-body {

    display: none;

}


.pe-ai-window.pe-ai-maximized {

    top: 20px;
    right: 20px;
    bottom: 20px;
    left: 20px;

    width: auto;
    height: auto;

    min-width: 0;
    min-height: 0;

    max-width: none;
    max-height: none;

    border-radius: 18px;

}


/* ============================================================
   HEADER
============================================================ */

.pe-ai-header {

    flex-shrink: 0;

    height: 70px;

    padding:
        12px 14px;

    display: flex;

    align-items: center;
    justify-content: space-between;

    background:
        linear-gradient(
            180deg,
            #fffdfb 0%,
            #ffffff 100%
        );

    border-bottom:
        1px solid #eeeaf1;

}


.pe-ai-brand {

    display: flex;

    align-items: center;

    min-width: 0;

}


.pe-ai-brand-icon {

    width: 42px;
    height: 42px;

    flex-shrink: 0;

    border-radius: 13px;

    background: #fff1e7;

    color: #f36b0a;

    display: flex;

    align-items: center;
    justify-content: center;

    margin-right: 10px;

}


.pe-ai-brand-info {

    min-width: 0;

}


.pe-ai-brand-title {

    color: #172033;

    font-size: 14px;

    font-weight: 800;

    line-height: 1.25;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


.pe-ai-brand-status {

    display: flex;

    align-items: center;

    margin-top: 3px;

    color: #9197a4;

    font-size: 10px;

}


.pe-ai-status-dot {

    width: 7px;
    height: 7px;

    flex-shrink: 0;

    margin-right: 5px;

    border-radius: 50%;

    background: #22c55e;

}


/* ============================================================
   HEADER ACTIONS
============================================================ */

.pe-ai-header-actions {

    display: flex;

    align-items: center;

    gap: 3px;

    margin-left: 10px;

}


.pe-ai-header-btn {

    width: 31px;
    height: 31px;

    padding: 0;

    border: 0;

    border-radius: 9px;

    background: transparent;

    color: #818897;

    display: flex;

    align-items: center;
    justify-content: center;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease;

}


.pe-ai-header-btn:hover {

    background: #f4f2f6;

    color: #172033;

}


.pe-ai-close-btn:hover {

    background: #fff0f0;

    color: #dc2626;

}


/* ============================================================
   BODY
============================================================ */

.pe-ai-body {

    flex: 1;

    min-height: 0;

    display: flex;

    flex-direction: column;

    background: #f8f7fb;

}


/* ============================================================
   MESSAGE AREA
============================================================ */

.pe-ai-messages {

    flex: 1;

    min-height: 0;

    overflow-y: auto;

    padding:
        18px 16px;

    scroll-behavior: smooth;

}


.pe-ai-messages::-webkit-scrollbar {

    width: 5px;

}


.pe-ai-messages::-webkit-scrollbar-track {

    background: transparent;

}


.pe-ai-messages::-webkit-scrollbar-thumb {

    background: #d8d4de;

    border-radius: 999px;

}


/* ============================================================
   MESSAGE ROW
============================================================ */

.pe-ai-message {

    width: 100%;

    display: flex;

    align-items: flex-start;

    gap: 9px;

    margin-bottom: 14px;

}


.pe-ai-message-ai {

    justify-content: flex-start;

}


.pe-ai-message-user {

    justify-content: flex-end;

}


/* ============================================================
   MESSAGE AVATAR
============================================================ */

.pe-ai-message-avatar {

    width: 34px;
    height: 34px;

    flex-shrink: 0;

    border-radius: 10px;

    background: #fff1e7;

    color: #f36b0a;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 12px;

}


.pe-ai-message-user
.pe-ai-message-avatar {

    order: 2;

    background: #172033;

    color: #ffffff;

}


/* ============================================================
   MESSAGE CONTENT
============================================================ */

.pe-ai-message-content {

    min-width: 0;

    width: auto;

    max-width: 84%;

}


.pe-ai-message-user
.pe-ai-message-content {

    max-width: 76%;

}


/* ============================================================
   CHAT BUBBLE
============================================================ */

.pe-ai-bubble {

    display: block;

    width: auto;

    max-width: 100%;

    padding:
        11px 13px;

    border-radius: 15px;

    border:
        1px solid #e7e4eb;

    background: #ffffff;

    color: #263247;

    font-size: 13px;

    line-height: 1.6;

    text-align: left;

    word-break: break-word;

    overflow-wrap: break-word;

    box-shadow:
        0 2px 8px
        rgba(23,32,51,.035);

}


.pe-ai-message-user
.pe-ai-bubble {

    border-color: #f36b0a;

    background: #f36b0a;

    color: #ffffff;

    line-height: 1.5;

}


/* ============================================================
   WELCOME
============================================================ */

.pe-ai-welcome-bubble {

    width: auto !important;

    max-width: 360px !important;

    padding:
        14px !important;

}


.pe-ai-welcome-title {

    margin: 0 0 5px;

    color: #172033;

    font-size: 14px;

    font-weight: 800;

    line-height: 1.35;

    text-align: left;

}


.pe-ai-welcome-text {

    margin: 0 0 12px;

    color: #6e7687;

    font-size: 12px;

    line-height: 1.5;

    text-align: left;

}


/* ============================================================
   CAPABILITY GRID
============================================================ */

.pe-ai-capabilities {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 7px;

    width: 100%;

}


.pe-ai-capability {

    min-width: 0;

    min-height: 36px;

    padding:
        8px 9px;

    display: flex;

    align-items: center;

    gap: 7px;

    background: #faf9fc;

    border:
        1px solid #eeeaf2;

    border-radius: 9px;

}


.pe-ai-capability-icon {

    width: 23px;
    height: 23px;

    flex-shrink: 0;

    border-radius: 7px;

    background: #fff1e7;

    color: #f36b0a;

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 9px;

}


.pe-ai-capability-text {

    min-width: 0;

    color: #626b7d;

    font-size: 10px;

    line-height: 1.2;

    white-space: nowrap;

    overflow: hidden;

    text-overflow: ellipsis;

}


/* ============================================================
   AI RESPONSE TYPOGRAPHY
============================================================ */

.pe-ai-message-ai
.pe-ai-bubble {

    font-size: 13px;

    line-height: 1.65;

}


/* PARAGRAPH */

.pe-ai-message-ai
.pe-ai-bubble p {

    margin:
        0 0 8px;

}


.pe-ai-message-ai
.pe-ai-bubble p:last-child {

    margin-bottom: 0;

}


/* HEADINGS */

.pe-ai-message-ai
.pe-ai-bubble h3 {

    margin:
        0 0 8px;

    color: #172033;

    font-size: 15px;

    line-height: 1.35;

    font-weight: 800;

}


.pe-ai-message-ai
.pe-ai-bubble h4 {

    margin:
        0 0 7px;

    color: #172033;

    font-size: 13px;

    line-height: 1.4;

    font-weight: 800;

}


/* BOLD */

.pe-ai-message-ai
.pe-ai-bubble strong {

    color: #172033;

    font-weight: 800;

}


/* ITALIC */

.pe-ai-message-ai
.pe-ai-bubble em {

    color: #697184;

    font-style: italic;

}


/* BULLET */

.pe-ai-message-ai
.pe-ai-bubble ul {

    margin:
        7px 0 9px;

    padding-left: 19px;

}


.pe-ai-message-ai
.pe-ai-bubble ul li {

    margin-bottom: 4px;

    padding-left: 2px;

}


.pe-ai-message-ai
.pe-ai-bubble ul li:last-child {

    margin-bottom: 0;

}


/* NUMBERED */

.pe-ai-message-ai
.pe-ai-bubble ol {

    margin:
        7px 0 9px;

    padding-left: 20px;

}


.pe-ai-message-ai
.pe-ai-bubble ol li {

    margin-bottom: 4px;

    padding-left: 2px;

}


.pe-ai-message-ai
.pe-ai-bubble ol li:last-child {

    margin-bottom: 0;

}


/* INLINE CODE */

.pe-ai-inline-code {

    display: inline-block;

    padding:
        2px 6px;

    border-radius: 6px;

    background: #f4f2f7;

    border:
        1px solid #e6e2ea;

    color: #c2410c;

    font-family:
        Consolas,
        Monaco,
        monospace;

    font-size: 11px;

    vertical-align: baseline;

}


/* ============================================================
   QUICK QUESTIONS
============================================================ */

.pe-ai-suggestions-wrap {

    flex-shrink: 0;

    padding:
        9px 14px 8px;

    background: #ffffff;

    border-top:
        1px solid #ece9f0;

}


.pe-ai-suggestions-label {

    margin-bottom: 7px;

    color: #949aa7;

    font-size: 9px;

    font-weight: 700;

}


.pe-ai-suggestions {

    display: flex;

    gap: 6px;

    overflow-x: auto;

    scrollbar-width: none;

}


.pe-ai-suggestions::-webkit-scrollbar {

    display: none;

}


.pe-ai-suggestion {

    flex-shrink: 0;

    padding:
        7px 10px;

    border:
        1px solid #e6e2ea;

    border-radius: 999px;

    background: #faf9fc;

    color: #626b7d;

    font-size: 10px;

    line-height: 1;

    cursor: pointer;

    transition:
        background .15s ease,
        color .15s ease,
        border-color .15s ease;

}


.pe-ai-suggestion:hover {

    background: #fff1e7;

    color: #f36b0a;

    border-color: #ffd4b3;

}


/* ============================================================
   INPUT
============================================================ */

.pe-ai-input-area {

    flex-shrink: 0;

    padding:
        10px 14px 11px;

    background: #ffffff;

    border-top:
        1px solid #ece9f0;

}


.pe-ai-input-box {

    display: flex;

    align-items: center;

    gap: 7px;

    padding: 4px;

    background: #f8f7fb;

    border:
        1px solid #ddd9e4;

    border-radius: 14px;

    transition:
        background .15s ease,
        border-color .15s ease,
        box-shadow .15s ease;

}


.pe-ai-input-box:focus-within {

    background: #ffffff;

    border-color: #f36b0a;

    box-shadow:
        0 0 0 3px
        rgba(243,107,10,.07);

}


.pe-ai-input {

    flex: 1;

    min-width: 0;

    height: 38px;

    padding:
        0 9px;

    border: 0;

    outline: none;

    background: transparent;

    color: #172033;

    font-size: 12px;

}


.pe-ai-input::placeholder {

    color: #a4a9b3;

}


.pe-ai-send {

    width: 38px;
    height: 38px;

    flex-shrink: 0;

    padding: 0;

    border: 0;

    border-radius: 11px;

    background: #f36b0a;

    color: #ffffff;

    display: flex;

    align-items: center;
    justify-content: center;

    cursor: pointer;

    transition:
        background .15s ease,
        transform .15s ease;

}


.pe-ai-send:hover {

    background: #df5d08;

    transform:
        translateY(-1px);

}


.pe-ai-send:disabled {

    opacity: .5;

    cursor: not-allowed;

    transform: none;

}


/* ============================================================
   FOOTER
============================================================ */

.pe-ai-footer {

    margin-top: 6px;

    padding:
        0 2px;

    display: flex;

    align-items: center;

    justify-content: space-between;

}


.pe-ai-powered {

    display: flex;

    align-items: center;

    gap: 4px;

    color: #a1a6b1;

    font-size: 9px;

}


.pe-ai-powered i {

    color: #f36b0a;

}


.pe-ai-reset {

    padding: 0;

    border: 0;

    background: transparent;

    color: #969ca8;

    font-size: 9px;

    cursor: pointer;

}


.pe-ai-reset:hover {

    color: #dc2626;

}


/* ============================================================
   TYPING
============================================================ */

.pe-ai-typing {

    color: #9198a5;

    font-style: italic;

}


/* ============================================================
   MOBILE
============================================================ */

@media (max-width: 576px) {

    .pe-ai-launcher {

        right: 16px;
        bottom: 16px;

        width: 54px;
        height: 54px;

        border-radius: 16px;

    }


    .pe-ai-window {

        right: 10px;
        bottom: 78px;

        width:
            calc(100vw - 20px);

        height:
            calc(100vh - 95px);

        min-width: 0;
        min-height: 0;

        max-width: none;
        max-height: none;

        border-radius: 18px;

    }


    .pe-ai-window.pe-ai-maximized {

        top: 0;
        right: 0;
        bottom: 0;
        left: 0;

        border-radius: 0;

    }


    .pe-ai-message-content {

        max-width: 88%;

    }


    .pe-ai-message-user
    .pe-ai-message-content {

        max-width: 80%;

    }


    .pe-ai-welcome-bubble {

        max-width: 100% !important;

    }

}


/* ============================================================
   SMALL MOBILE
============================================================ */

@media (max-width: 380px) {

    .pe-ai-capabilities {

        grid-template-columns:
            1fr;

    }

}

</style>


<script>

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const launcher =
            document.getElementById(
                'peAiLauncher'
            );

        const windowElement =
            document.getElementById(
                'peAiWindow'
            );

        const minimizeButton =
            document.getElementById(
                'peAiMinimize'
            );

        const maximizeButton =
            document.getElementById(
                'peAiMaximize'
            );

        const closeButton =
            document.getElementById(
                'peAiClose'
            );

        const form =
            document.getElementById(
                'peAiForm'
            );

        const input =
            document.getElementById(
                'peAiInput'
            );

        const sendButton =
            document.getElementById(
                'peAiSend'
            );

        const messages =
            document.getElementById(
                'peAiMessages'
            );

        const resetButton =
            document.getElementById(
                'peAiReset'
            );


        /*
        |--------------------------------------------------------------------------
        | ELEMENT CHECK
        |--------------------------------------------------------------------------
        */

        if (
            !launcher ||
            !windowElement ||
            !minimizeButton ||
            !maximizeButton ||
            !closeButton ||
            !form ||
            !input ||
            !sendButton ||
            !messages ||
            !resetButton
        ) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        const chatUrl =
            @json(route('ai.chat'));

        const resetUrl =
            @json(route('ai.reset'));


        /*
        |--------------------------------------------------------------------------
        | STORAGE
        |--------------------------------------------------------------------------
        */

        const storageKey =
            'pos-enterprise-ai-window-state';


        /*
        |--------------------------------------------------------------------------
        | SCROLL
        |--------------------------------------------------------------------------
        */

        function scrollBottom() {

            messages.scrollTop =
                messages.scrollHeight;

        }


        /*
        |--------------------------------------------------------------------------
        | OPEN
        |--------------------------------------------------------------------------
        */

        function openAi() {

            windowElement.classList.add(
                'pe-ai-open'
            );

            windowElement.classList.remove(
                'pe-ai-minimized'
            );

            windowElement.setAttribute(
                'aria-hidden',
                'false'
            );

            setTimeout(
                function () {

                    input.focus();

                    scrollBottom();

                },
                80
            );

            saveState();

        }


        /*
        |--------------------------------------------------------------------------
        | CLOSE
        |--------------------------------------------------------------------------
        */

        function closeAi() {

            windowElement.classList.remove(
                'pe-ai-open'
            );

            windowElement.setAttribute(
                'aria-hidden',
                'true'
            );

            saveState();

        }


        /*
        |--------------------------------------------------------------------------
        | SAVE STATE
        |--------------------------------------------------------------------------
        */

        function saveState() {

            try {

                localStorage.setItem(
                    storageKey,

                    JSON.stringify({

                        open:
                            windowElement.classList.contains(
                                'pe-ai-open'
                            ),

                        minimized:
                            windowElement.classList.contains(
                                'pe-ai-minimized'
                            ),

                        maximized:
                            windowElement.classList.contains(
                                'pe-ai-maximized'
                            )

                    })
                );

            } catch (error) {

                console.warn(
                    'AI window state tidak dapat disimpan.',
                    error
                );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | LAUNCHER
        |--------------------------------------------------------------------------
        */

        launcher.addEventListener(
            'click',
            function () {

                if (
                    windowElement.classList.contains(
                        'pe-ai-open'
                    )
                ) {

                    closeAi();

                } else {

                    openAi();

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MINIMIZE
        |--------------------------------------------------------------------------
        */

        minimizeButton.addEventListener(
            'click',
            function () {

                windowElement.classList.remove(
                    'pe-ai-maximized'
                );

                windowElement.classList.toggle(
                    'pe-ai-minimized'
                );

                saveState();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | MAXIMIZE
        |--------------------------------------------------------------------------
        */

        maximizeButton.addEventListener(
            'click',
            function () {

                windowElement.classList.remove(
                    'pe-ai-minimized'
                );

                const maximized =
                    windowElement.classList.toggle(
                        'pe-ai-maximized'
                    );


                maximizeButton.innerHTML =
                    maximized
                        ? '<i class="fas fa-compress"></i>'
                        : '<i class="fas fa-expand"></i>';


                maximizeButton.title =
                    maximized
                        ? 'Kembalikan ukuran'
                        : 'Perbesar';


                saveState();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | CLOSE
        |--------------------------------------------------------------------------
        */

        closeButton.addEventListener(
            'click',
            function () {

                closeAi();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeAiHtml(text) {

            return String(text ?? '')
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );

        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT AI RESPONSE
        |--------------------------------------------------------------------------
        |
        | Mendukung:
        |
        | **bold**
        | *italic*
        | `code`
        | # heading
        | ## heading
        | - bullet
        | * bullet
        | 1. numbered
        |
        */

        function formatAiResponse(text) {

            let value =
                escapeAiHtml(text);


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE NEWLINE
            |--------------------------------------------------------------------------
            */

            value =
                value
                    .replace(
                        /\r\n/g,
                        '\n'
                    )
                    .replace(
                        /\r/g,
                        '\n'
                    );


            /*
            |--------------------------------------------------------------------------
            | CODE
            |--------------------------------------------------------------------------
            */

            value =
                value.replace(
                    /`([^`]+)`/g,
                    '<code class="pe-ai-inline-code">$1</code>'
                );


            /*
            |--------------------------------------------------------------------------
            | BOLD
            |--------------------------------------------------------------------------
            */

            value =
                value.replace(
                    /\*\*(.+?)\*\*/g,
                    '<strong>$1</strong>'
                );


            /*
            |--------------------------------------------------------------------------
            | ITALIC
            |--------------------------------------------------------------------------
            */

            value =
                value.replace(
                    /(^|[^\*])\*([^*\n]+)\*(?!\*)/g,
                    '$1<em>$2</em>'
                );


            const lines =
                value.split('\n');


            let html = '';

            let paragraph = [];

            let inList = false;

            let listType = null;


            /*
            |--------------------------------------------------------------------------
            | CLOSE LIST
            |--------------------------------------------------------------------------
            */

            function closeList() {

                if (!inList) {

                    return;

                }


                html +=
                    listType === 'ol'
                        ? '</ol>'
                        : '</ul>';


                inList = false;

                listType = null;

            }


            /*
            |--------------------------------------------------------------------------
            | FLUSH PARAGRAPH
            |--------------------------------------------------------------------------
            */

            function flushParagraph() {

                if (
                    !paragraph.length
                ) {

                    return;

                }


                const content =
                    paragraph
                        .join(' ')
                        .trim();


                if (content) {

                    html +=
                        '<p>' +
                        content +
                        '</p>';

                }


                paragraph = [];

            }


            /*
            |--------------------------------------------------------------------------
            | LINE PROCESSOR
            |--------------------------------------------------------------------------
            */

            lines.forEach(
                function (line) {

                    const trimmed =
                        line.trim();


                    /*
                    |--------------------------------------------------------------------------
                    | EMPTY
                    |--------------------------------------------------------------------------
                    */

                    if (!trimmed) {

                        flushParagraph();

                        closeList();

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | H3
                    |--------------------------------------------------------------------------
                    */

                    if (
                        trimmed.startsWith(
                            '### '
                        )
                    ) {

                        flushParagraph();

                        closeList();

                        html +=
                            '<h4>' +
                            trimmed.substring(4) +
                            '</h4>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | H2
                    |--------------------------------------------------------------------------
                    */

                    if (
                        trimmed.startsWith(
                            '## '
                        )
                    ) {

                        flushParagraph();

                        closeList();

                        html +=
                            '<h4>' +
                            trimmed.substring(3) +
                            '</h4>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | H1
                    |--------------------------------------------------------------------------
                    */

                    if (
                        trimmed.startsWith(
                            '# '
                        )
                    ) {

                        flushParagraph();

                        closeList();

                        html +=
                            '<h3>' +
                            trimmed.substring(2) +
                            '</h3>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | BULLET
                    |--------------------------------------------------------------------------
                    */

                    const bullet =
                        trimmed.match(
                            /^[-•*]\s+(.+)$/
                        );


                    if (bullet) {

                        flushParagraph();


                        if (
                            !inList ||
                            listType !== 'ul'
                        ) {

                            closeList();

                            html +=
                                '<ul>';

                            inList = true;

                            listType = 'ul';

                        }


                        html +=
                            '<li>' +
                            bullet[1] +
                            '</li>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NUMBERED LIST
                    |--------------------------------------------------------------------------
                    */

                    const numbered =
                        trimmed.match(
                            /^\d+\.\s+(.+)$/
                        );


                    if (numbered) {

                        flushParagraph();


                        if (
                            !inList ||
                            listType !== 'ol'
                        ) {

                            closeList();

                            html +=
                                '<ol>';

                            inList = true;

                            listType = 'ol';

                        }


                        html +=
                            '<li>' +
                            numbered[1] +
                            '</li>';

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | NORMAL LINE
                    |--------------------------------------------------------------------------
                    */

                    closeList();

                    paragraph.push(
                        trimmed
                    );

                }
            );


            flushParagraph();

            closeList();


            return (
                html ||
                '<p>-</p>'
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ADD MESSAGE
        |--------------------------------------------------------------------------
        */

        function addMessage(
            text,
            type
        ) {

            const row =
                document.createElement(
                    'div'
                );


            row.className =
                'pe-ai-message ' +
                (
                    type === 'user'
                        ? 'pe-ai-message-user'
                        : 'pe-ai-message-ai'
                );


            /*
            |--------------------------------------------------------------------------
            | AVATAR
            |--------------------------------------------------------------------------
            */

            const avatar =
                document.createElement(
                    'div'
                );


            avatar.className =
                'pe-ai-message-avatar';


            const icon =
                document.createElement(
                    'i'
                );


            icon.className =
                type === 'user'
                    ? 'fas fa-user'
                    : 'fas fa-robot';


            avatar.appendChild(
                icon
            );


            /*
            |--------------------------------------------------------------------------
            | CONTENT
            |--------------------------------------------------------------------------
            */

            const content =
                document.createElement(
                    'div'
                );


            content.className =
                'pe-ai-message-content';


            /*
            |--------------------------------------------------------------------------
            | BUBBLE
            |--------------------------------------------------------------------------
            */

            const bubble =
                document.createElement(
                    'div'
                );


            bubble.className =
                'pe-ai-bubble';


            /*
            |--------------------------------------------------------------------------
            | USER / AI
            |--------------------------------------------------------------------------
            */

            if (
                type === 'ai'
            ) {

                bubble.innerHTML =
                    formatAiResponse(
                        text
                    );

            } else {

                bubble.textContent =
                    String(
                        text || ''
                    );

            }


            /*
            |--------------------------------------------------------------------------
            | APPEND
            |--------------------------------------------------------------------------
            */

            content.appendChild(
                bubble
            );


            row.appendChild(
                avatar
            );


            row.appendChild(
                content
            );


            messages.appendChild(
                row
            );


            scrollBottom();

        }


        /*
        |--------------------------------------------------------------------------
        | TYPING INDICATOR
        |--------------------------------------------------------------------------
        */

        function showTyping() {

            removeTyping();


            const row =
                document.createElement(
                    'div'
                );


            row.id =
                'peAiTyping';


            row.className =
                'pe-ai-message pe-ai-message-ai';


            const avatar =
                document.createElement(
                    'div'
                );


            avatar.className =
                'pe-ai-message-avatar';


            avatar.innerHTML =
                '<i class="fas fa-robot"></i>';


            const content =
                document.createElement(
                    'div'
                );


            content.className =
                'pe-ai-message-content';


            const bubble =
                document.createElement(
                    'div'
                );


            bubble.className =
                'pe-ai-bubble pe-ai-typing';


            bubble.textContent =
                'Sedang menganalisis...';


            content.appendChild(
                bubble
            );


            row.appendChild(
                avatar
            );


            row.appendChild(
                content
            );


            messages.appendChild(
                row
            );


            scrollBottom();

        }


        function removeTyping() {

            const typing =
                document.getElementById(
                    'peAiTyping'
                );


            if (typing) {

                typing.remove();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SEND
        |--------------------------------------------------------------------------
        */

        async function sendMessage(
            text
        ) {

            text =
                String(
                    text || ''
                ).trim();


            if (!text) {

                return;

            }


            addMessage(
                text,
                'user'
            );


            input.value = '';

            sendButton.disabled =
                true;


            showTyping();


            try {

                const response =
                    await fetch(
                        chatUrl,
                        {

                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        ?.getAttribute(
                                            'content'
                                        ) || ''

                            },

                            body:
                                JSON.stringify({
                                    message: text
                                })

                        }
                    );


                const data =
                    await response.json();


                removeTyping();


                if (
                    !response.ok ||
                    !data.success
                ) {

                    addMessage(
                        data.message ||
                        'AI gagal memproses pertanyaan.',
                        'ai'
                    );

                    return;

                }


                addMessage(
                    data.message ||
                    data.response ||
                    'AI tidak memberikan jawaban.',
                    'ai'
                );


            } catch (error) {

                removeTyping();


                console.error(
                    'AI Error:',
                    error
                );


                addMessage(
                    'Tidak dapat terhubung ke AI. Periksa koneksi server.',
                    'ai'
                );

            } finally {

                sendButton.disabled =
                    false;

                input.focus();

            }

        }


        /*
        |--------------------------------------------------------------------------
        | FORM
        |--------------------------------------------------------------------------
        */

        form.addEventListener(
            'submit',
            function (event) {

                event.preventDefault();

                sendMessage(
                    input.value
                );

            }
        );


        /*
        |--------------------------------------------------------------------------
        | QUICK QUESTIONS
        |--------------------------------------------------------------------------
        */

        document
            .querySelectorAll(
                '.pe-ai-suggestion'
            )
            .forEach(
                function (button) {

                    button.addEventListener(
                        'click',
                        function () {

                            input.value =
                                button.dataset.message ||
                                '';

                            input.focus();

                        }
                    );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        resetButton.addEventListener(
            'click',
            async function () {

                if (
                    !confirm(
                        'Reset percakapan AI?'
                    )
                ) {

                    return;

                }


                try {

                    const response =
                        await fetch(
                            resetUrl,
                            {

                                method: 'POST',

                                headers: {

                                    'Accept':
                                        'application/json',

                                    'X-CSRF-TOKEN':
                                        document
                                            .querySelector(
                                                'meta[name="csrf-token"]'
                                            )
                                            ?.getAttribute(
                                                'content'
                                            ) || ''

                                }

                            }
                        );


                    const data =
                        await response.json();


                    if (
                        !response.ok ||
                        !data.success
                    ) {

                        alert(
                            data.message ||
                            'Gagal mereset percakapan.'
                        );

                        return;

                    }


                    messages.innerHTML = '';


                    /*
                    |--------------------------------------------------------------------------
                    | WELCOME AGAIN
                    |--------------------------------------------------------------------------
                    */

                    const welcome =
                        document.createElement(
                            'div'
                        );


                    welcome.className =
                        'pe-ai-message pe-ai-message-ai';


                    welcome.innerHTML = `

                        <div class="pe-ai-message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>

                        <div class="pe-ai-message-content">

                            <div class="pe-ai-bubble pe-ai-welcome-bubble">

                                <div class="pe-ai-welcome-title">
                                    Halo! 👋
                                </div>

                                <div class="pe-ai-welcome-text">
                                    Saya siap membantu membaca dan menganalisis data POS Enterprise.
                                </div>

                                <div class="pe-ai-capabilities">

                                    <div class="pe-ai-capability">

                                        <span class="pe-ai-capability-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </span>

                                        <span class="pe-ai-capability-text">
                                            Penjualan
                                        </span>

                                    </div>

                                    <div class="pe-ai-capability">

                                        <span class="pe-ai-capability-icon">
                                            <i class="fas fa-box"></i>
                                        </span>

                                        <span class="pe-ai-capability-text">
                                            Stok
                                        </span>

                                    </div>

                                    <div class="pe-ai-capability">

                                        <span class="pe-ai-capability-icon">
                                            <i class="fas fa-star"></i>
                                        </span>

                                        <span class="pe-ai-capability-text">
                                            Produk terlaris
                                        </span>

                                    </div>

                                    <div class="pe-ai-capability">

                                        <span class="pe-ai-capability-icon">
                                            <i class="fas fa-shopping-cart"></i>
                                        </span>

                                        <span class="pe-ai-capability-text">
                                            Pembelian
                                        </span>

                                    </div>

                                </div>

                            </div>

                        </div>

                    `;


                    messages.appendChild(
                        welcome
                    );


                    scrollBottom();


                } catch (error) {

                    console.error(
                        'Reset AI Error:',
                        error
                    );


                    alert(
                        'Gagal mereset percakapan.'
                    );

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RESTORE WINDOW STATE
        |--------------------------------------------------------------------------
        */

        try {

            const saved =
                JSON.parse(
                    localStorage.getItem(
                        storageKey
                    ) || 'null'
                );


            if (
                saved &&
                saved.open
            ) {

                windowElement.classList.add(
                    'pe-ai-open'
                );


                if (
                    saved.minimized
                ) {

                    windowElement.classList.add(
                        'pe-ai-minimized'
                    );

                }


                if (
                    saved.maximized
                ) {

                    windowElement.classList.add(
                        'pe-ai-maximized'
                    );


                    maximizeButton.innerHTML =
                        '<i class="fas fa-compress"></i>';


                    maximizeButton.title =
                        'Kembalikan ukuran';

                }

            }

        } catch (error) {

            console.warn(
                'AI window state gagal dipulihkan.',
                error
            );

        }

    }
);

</script>

@endif
