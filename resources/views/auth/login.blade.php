<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ECSA-HC Performance Tracking Platform - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: #fff;
            color: #000;
        }

        /* Use plain styling for borders and text */
        .plain-border {
            border-color: #000 !important;
        }

        .plain-text::placeholder {
            color: #666;
        }

        .plain-btn {
            background-color: #fff;
            color: #000;
            border-color: #000;
        }

        .plain-btn:hover {
            background-color: #f0f0f0;
        }

        /* Card styling for a white background with a subtle border */
        .plain-card {
            background-color: #fff;
            border: 1px solid #000;
        }
    </style>
</head>

<body class="h-full flex items-center justify-center">
    <div class="w-full max-w-md p-8 plain-card rounded-2xl shadow-md">
        <div class="text-center mb-8">
            <img class="mx-auto h-20 w-auto plain-border rounded-full"
                src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSprutZGJpPgDTYg_gFf3qAxKeriNs6Wma7_w&s"
                alt="ECSA-HC Logo">
            <h2 class="mt-6 text-3xl font-bold">
                ECSA-HC
            </h2>
            <p class="mt-2 text-xl">
                Performance Tracking Platform
            </p>
        </div>
        <!-- Display Validation Errors -->
        @if ($errors->any())
            <div class="mb-4">
                <ul class="list-disc list-inside text-red-500">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 text-red-500">
                {{ session('error') }}
            </div>
        @endif

        <form class="mt-8 space-y-6" action="{{ route('login') }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label for="email" class="sr-only">Email address</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="w-full px-4 py-2 bg-transparent border-b-2 plain-border text-black plain-text focus:outline-none focus:border-black transition-colors"
                        placeholder="Email address">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" required
                        class="w-full px-4 py-2 bg-transparent border-b-2 plain-border text-black plain-text focus:outline-none focus:border-black transition-colors"
                        placeholder="Password">
                </div>
            </div>

            <div class="flex items-center justify-between text-sm mt-4">
                <div class="flex items-center">
                    <input id="remember" name="remember" type="checkbox"
                        class="h-4 w-4 text-black focus:ring-black border-black rounded">
                    <label for="remember" class="ml-2 block">
                        Remember me
                    </label>
                </div>
                <!-- Forgot password link removed as per requirements -->
            </div>

            <div>
                <button type="submit"
                    class="group relative w-full flex justify-center py-2 px-4 border border-solid plain-btn rounded-md text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-black transition-colors">
                    Sign in
                </button>
            </div>
        </form>
        <!-- Register link removed as per requirements -->
    </div>
</body>

</html>
