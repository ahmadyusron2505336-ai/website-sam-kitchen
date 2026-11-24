document.addEventListener('DOMContentLoaded', function () {
    var EYE =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';

    var EYE_OFF =
        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" ' +
        'stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">' +
        '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>' +
        '<line x1="1" y1="1" x2="23" y2="23"/></svg>';

    document.querySelectorAll('input[type="password"]').forEach(function (input) {
        // Bungkus input agar tombol bisa diposisikan di dalam kolom
        var wrap = document.createElement('div');
        wrap.style.position = 'relative';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        input.style.paddingRight = '44px';

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.innerHTML = EYE;
        btn.title = 'Tampilkan password';
        btn.setAttribute('aria-label', 'Tampilkan password');
        btn.style.cssText =
            'position:absolute;top:0;bottom:0;right:0;width:44px;display:flex;' +
            'align-items:center;justify-content:center;background:none;border:0;' +
            'cursor:pointer;color:#8b7a6b;';

        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = show ? EYE_OFF : EYE;

            var label = show ? 'Sembunyikan password' : 'Tampilkan password';
            btn.title = label;
            btn.setAttribute('aria-label', label);
        });

        wrap.appendChild(btn);
    });
});