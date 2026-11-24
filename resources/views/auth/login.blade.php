<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Merchant Center | SAM Kitchen</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>input::-ms-reveal, input::-ms-clear { display: none; }</style>
    <link rel="stylesheet" href="{{ asset('css/background.css') }}">
</head>
<script src="{{ asset('js/password-toggle.js') }}"></script>
<body class="pattern-food min-h-screen flex items-center justify-center p-4"
      style="font-family:'Plus Jakarta Sans',sans-serif">
    <form method="POST" action="{{ url('/merchant/login') }}"
          class="w-full max-w-sm rounded-2xl p-6 sm:p-8 space-y-4 shadow-xl" style="background:#FFFDF8;color:#4A2C1A">
        @csrf
        <div class="text-center">
            <h1 class="text-2xl font-extrabold" style="color:#D95D2B">SAM KITCHEN</h1>
            <p class="text-[10px] tracking-[.18em] font-semibold opacity-70">DELICIOUS FOOD &amp; DRINK</p>
            <p class="text-sm mt-3 opacity-70">Masuk ke Merchant Center</p>
        </div>

        @error('email')
            <div class="p-2.5 text-sm rounded-lg bg-red-100 text-red-700">{{ $message }}</div>
        @enderror

        <div>
            <label class="block text-xs font-bold mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                   class="w-full border rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
        <div>
            <label class="block text-xs font-bold mb-1">Kata Sandi</label>
            <input type="password" name="password" required
                   class="w-full border rounded-xl px-3 py-2.5 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-orange-300">
        </div>
        <label class="flex items-center gap-2 text-xs font-semibold">
            <input type="checkbox" name="remember"> Ingat saya
        </label>
        <button class="w-full text-white font-bold py-2.5 rounded-xl" style="background:#D95D2B">Masuk</button>
    </form>
</body>
</html>