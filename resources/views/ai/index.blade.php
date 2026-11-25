@extends('layouts.app')

@section('title', 'AI Assistant')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="mb-1">
                <i class="fas fa-robot mr-2"></i>
                AI Assistant
            </h1>
            <p class="text-muted mb-0">
                Asisten AI untuk membantu membaca dan menganalisis data POS Enterprise.
            </p>
        </div>

        <button type="button" class="btn btn-outline-danger" id="btnReset">
            <i class="fas fa-rotate-left mr-1"></i>
            Reset Percakapan
        </button>
    </div>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-lg-10 col-xl-9">

        <div class="card shadow-sm border-0 ai-card">

            {{-- Header --}}
            <div class="card-header bg-white border-bottom">
                <div class="d-flex align-items-center">

                    <div class="ai-avatar mr-3">
                        <i class="fas fa-robot"></i>
                    </div>

                    <div>
                        <h5 class="mb-0 font-weight-bold">
                            POS Enterprise AI
                        </h5>

                        <small class="text-success">
                            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i>
                            Online
                        </small>
                    </div>

                </div>
            </div>

            {{-- Chat --}}
            <div class="card-body p-0">

                <div id="chatMessages" class="chat-messages">

                    {{-- Welcome --}}
                    <div class="message-row ai-message">
                        <div class="message-avatar">
                            <i class="fas fa-robot"></i>
                        </div>

                        <div class="message-content">
                            <div class="message-bubble">
                                Halo! Saya AI Assistant POS Enterprise.

                                <br><br>

                                Saya dapat membantu menganalisis data seperti:

                                <ul class="mb-0 mt-2">
                                    <li>Total penjualan</li>
                                    <li>Produk terlaris</li>
                                    <li>Stok produk</li>
                                    <li>Produk stok menipis</li>
                                    <li>Data pembelian</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Suggestions --}}
                <div class="suggestion-wrapper">

                    <div class="small text-muted mb-2">
                        Contoh pertanyaan:
                    </div>

                    <div class="d-flex flex-wrap">

                        <button
                            type="button"
                            class="btn btn-sm btn-light suggestion-btn"
                            data-message="Berapa total penjualan hari ini?"
                        >
                            Penjualan hari ini
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-light suggestion-btn"
                            data-message="Produk apa yang paling banyak terjual?"
                        >
                            Produk terlaris
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-light suggestion-btn"
                            data-message="Produk apa saja yang stoknya menipis?"
                        >
                            Stok menipis
                        </button>

                        <button
                            type="button"
                            class="btn btn-sm btn-light suggestion-btn"
                            data-message="Bagaimana ringkasan pembelian terbaru?"
                        >
                            Ringkasan pembelian
                        </button>

                    </div>

                </div>

                {{-- Input --}}
                <div class="chat-input-wrapper">

                    <form id="chatForm">

                        @csrf

                        <div class="input-group">

                            <input
                                type="text"
                                id="messageInput"
                                class="form-control"
                                placeholder="Tulis pertanyaan tentang POS..."
                                maxlength="2000"
                                autocomplete="off"
                            >

                            <div class="input-group-append">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                    id="sendButton"
                                >
                                    <i class="fas fa-paper-plane mr-1"></i>
                                    Kirim
                                </button>

                            </div>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    </div>
</div>

@stop


@section('css')

<style>

    .ai-card {
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
    }

    .ai-card .card-header {
        padding: 18px 22px;
    }

    .ai-avatar,
    .message-avatar {
        width: 42px;
        height: 42px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff1e7;
        color: #f36b0a;
        flex-shrink: 0;
    }

    .chat-messages {
        min-height: 430px;
        max-height: 550px;
        overflow-y: auto;
        padding: 24px;
        background: #f8f7fb;
    }

    .message-row {
        display: flex;
        margin-bottom: 20px;
        gap: 12px;
    }

    .user-message {
        justify-content: flex-end;
    }

    .user-message .message-avatar {
        order: 2;
        background: #172033;
        color: #fff;
    }

    .message-content {
        max-width: 78%;
    }

    .message-bubble {
        padding: 13px 16px;
        border-radius: 16px;
        line-height: 1.6;
        font-size: 14px;
        white-space: pre-wrap;
    }

    .ai-message .message-bubble {
        background: #fff;
        border: 1px solid #e6e3ec;
        color: #172033;
    }

    .user-message .message-bubble {
        background: #f36b0a;
        color: #fff;
    }

    .suggestion-wrapper {
        padding: 15px 22px 5px;
        background: #fff;
        border-top: 1px solid #eee;
    }

    .suggestion-btn {
        margin-right: 8px;
        margin-bottom: 8px;
        border-radius: 20px;
        border: 1px solid #e6e3ec;
        padding: 7px 13px;
    }

    .suggestion-btn:hover {
        background: #fff1e7;
        color: #f36b0a;
        border-color: #f36b0a;
    }

    .chat-input-wrapper {
        padding: 18px 22px 22px;
        background: #fff;
    }

    .chat-input-wrapper .form-control {
        height: 48px;
        border-radius: 12px 0 0 12px;
    }

    .chat-input-wrapper .btn {
        height: 48px;
        border-radius: 0 12px 12px 0;
    }

    .typing {
        opacity: .7;
        font-style: italic;
    }

</style>

@stop


@section('js')

<script>

document.addEventListener('DOMContentLoaded', function () {

    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const chatMessages = document.getElementById('chatMessages');
    const sendButton = document.getElementById('sendButton');
    const resetButton = document.getElementById('btnReset');

    const chatUrl = @json(route('ai.chat'));
    const resetUrl = @json(route('ai.reset'));

    function scrollToBottom() {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    function addMessage(message, type) {

        const row = document.createElement('div');
        row.className = 'message-row ' +
            (type === 'user' ? 'user-message' : 'ai-message');

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';

        const icon = document.createElement('i');

        icon.className = type === 'user'
            ? 'fas fa-user'
            : 'fas fa-robot';

        avatar.appendChild(icon);

        const content = document.createElement('div');
        content.className = 'message-content';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble';

        // Aman dari XSS.
        bubble.textContent = message;

        content.appendChild(bubble);

        row.appendChild(avatar);
        row.appendChild(content);

        chatMessages.appendChild(row);

        scrollToBottom();
    }

    function addTyping() {

        const row = document.createElement('div');

        row.className = 'message-row ai-message';
        row.id = 'typingMessage';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';

        avatar.innerHTML = '<i class="fas fa-robot"></i>';

        const content = document.createElement('div');
        content.className = 'message-content';

        const bubble = document.createElement('div');
        bubble.className = 'message-bubble typing';

        bubble.textContent = 'AI sedang menganalisis...';

        content.appendChild(bubble);

        row.appendChild(avatar);
        row.appendChild(content);

        chatMessages.appendChild(row);

        scrollToBottom();
    }

    function removeTyping() {

        const typing = document.getElementById('typingMessage');

        if (typing) {
            typing.remove();
        }
    }

    async function sendMessage(message) {

        if (!message || !message.trim()) {
            return;
        }

        addMessage(message, 'user');

        messageInput.value = '';
        sendButton.disabled = true;

        addTyping();

        try {

            const response = await fetch(chatUrl, {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || ''
                },

                body: JSON.stringify({
                    message: message
                })

            });

            const data = await response.json();

            removeTyping();

            if (!response.ok || !data.success) {

                addMessage(
                    data.message || 'Terjadi kesalahan saat menghubungi AI.',
                    'ai'
                );

                return;
            }

            addMessage(
                data.message || data.response || 'AI tidak memberikan jawaban.',
                'ai'
            );

        } catch (error) {

            removeTyping();

            addMessage(
                'Tidak dapat terhubung ke server AI. Periksa koneksi Laravel dan konfigurasi Gemini.',
                'ai'
            );

            console.error(error);

        } finally {

            sendButton.disabled = false;
            messageInput.focus();

        }
    }


    chatForm.addEventListener('submit', function (event) {

        event.preventDefault();

        const message = messageInput.value.trim();

        sendMessage(message);

    });


    document.querySelectorAll('.suggestion-btn').forEach(function (button) {

        button.addEventListener('click', function () {

            const message = button.dataset.message;

            messageInput.value = message;
            messageInput.focus();

        });

    });


    resetButton.addEventListener('click', async function () {

        const confirmed = confirm(
            'Reset percakapan AI?'
        );

        if (!confirmed) {
            return;
        }

        try {

            const response = await fetch(resetUrl, {

                method: 'POST',

                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document
                        .querySelector('meta[name="csrf-token"]')
                        ?.getAttribute('content') || ''
                }

            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                alert(
                    data.message || 'Gagal mereset percakapan.'
                );
                return;
            }

            chatMessages.innerHTML = '';

            addMessage(
                'Percakapan telah direset. Silakan ajukan pertanyaan baru.',
                'ai'
            );

        } catch (error) {

            alert(
                'Gagal mereset percakapan.'
            );

            console.error(error);

        }

    });

    messageInput.focus();

});

</script>

@stop