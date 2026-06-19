<!DOCTYPE html>
<html lang="zh-Hant">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="light">
    <title>Telescope 登入</title>
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f3f4f6;
            font-family: -apple-system, "Segoe UI", "Microsoft JhengHei", sans-serif;
        }

        .card {
            width: 320px;
            padding: 32px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, .08);
        }

        h1 {
            margin: 0 0 24px;
            font-size: 18px;
            text-align: center;
            color: #111827;
        }

        label {
            display: block;
            margin-bottom: 4px;
            font-size: 13px;
            color: #374151;
        }

        input {
            width: 100%;
            box-sizing: border-box;
            padding: 10px 12px;
            margin-bottom: 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
        }

        button {
            width: 100%;
            padding: 10px;
            border: 0;
            border-radius: 8px;
            background: #4f46e5;
            color: #fff;
            font-size: 14px;
            cursor: pointer;
        }

        button:hover {
            background: #4338ca;
        }

        .error {
            margin-bottom: 16px;
            padding: 10px 12px;
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            color: #b91c1c;
            font-size: 13px;
        }
    </style>
</head>

<body>
    <form class="card" method="POST" action="{{ route('telescope.login.attempt') }}">
        @csrf
        <h1>Telescope 後台登入</h1>

        @if ($errors->any())
            <div class="error">{{ $errors->first() }}</div>
        @endif

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>

        <label for="password">密碼</label>
        <input id="password" type="password" name="password" required>

        <button type="submit">登入</button>
    </form>
</body>

</html>
