<style>
.registration-password-wrap{position:relative}
.registration-password-wrap input{padding-right:52px}
.registration-password-wrap .registration-password-toggle{position:absolute;right:3px;top:3px;bottom:3px;width:44px;min-height:40px;padding:10px;margin:0;background:transparent;color:#697586;border:0;border-radius:8px;cursor:pointer;display:grid;place-items:center}
.registration-password-toggle:hover{background:#fff1e7;color:#ad460e}
.registration-password-toggle:focus-visible{outline:2px solid #ea580c;outline-offset:1px}
.registration-password-toggle svg{width:22px;height:22px;stroke-linecap:round;stroke-linejoin:round}
.registration-password-toggle .password-eye-slash{display:none}
.registration-password-toggle[aria-pressed="true"] .password-eye-slash{display:block}
</style>
<script src="{{ asset('js/registration-password.js') }}" defer></script>
