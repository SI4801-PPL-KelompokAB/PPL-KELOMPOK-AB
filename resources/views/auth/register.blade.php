
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien - Bona Mitra</title>

    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            margin: 0;
            background: #f4f7fb;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
        }

        .container {
            background: white;
            width: 100%;
            max-width: 450px;
            padding: 35px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        h2 {
            text-align: center;
            color: #1e3a5f;
            margin-bottom: 8px;
        }

        .subtitle {
            text-align: center;
            color: #777;
            margin-bottom: 25px;
        }

        label {
            display: block;
            margin-bottom: 7px;
            font-weight: bold;
            font-size: 14px;
        }

        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 7px;
        }

        input:focus {
            outline: none;
            border-color: #2563eb;
        }

        .field {
            margin-bottom: 17px;
        }

        .error-text {
            color: #dc2626;
            font-size: 13px;
            margin-top: 5px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
            padding: 12px;
            border-radius: 7px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 13px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 7px;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #1d4ed8;
        }

        .link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .link a {
            color: #2563eb;
            text-decoration: none;
        }
    </style>
</head>

<body>

<div class="container">

    <h2>Registrasi Pasien</h2>
    <p class="subtitle">Buat akun untuk menggunakan layanan Bona Mitra</p>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ url('/register') }}">
        @csrf

        <div class="field">
            <label>Nama Lengkap</label>
            <input type="text" name="name"
                value="{{ old('name') }}"
                placeholder="Masukkan nama lengkap">

            @error('name')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label>Email</label>
            <input type="email" name="email"
                value="{{ old('email') }}"
                placeholder="Masukkan email">

            @error('email')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label>Nomor Telepon</label>
            <input type="text" name="no_hp"
                value="{{ old('no_hp') }}"
                placeholder="Masukkan nomor telepon">

            @error('no_hp')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label>Password</label>
            <input type="password" name="password"
                placeholder="Minimal 8 karakter">

            @error('password')
                <div class="error-text">{{ $message }}</div>
            @enderror
        </div>

        <div class="field">
            <label>Konfirmasi Password</label>
            <input type="password" name="password_confirmation"
                placeholder="Ulangi password">
        </div>

        <button type="submit">Daftar</button>
    </form>

    <div class="link">
        Sudah punya akun?
        <a href="{{ url('/login') }}">Login di sini</a>
    </div>

</div>

</body>
</html>
