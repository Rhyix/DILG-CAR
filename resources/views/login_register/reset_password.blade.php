<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Forgot Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .font-montserrat {
            font-family: 'Montserrat', sans-serif;
        }
    </style>
</head>

<body class="bg-white h-screen flex flex-col">
    <!-- Header Bar -->
    <header class="bg-[#002b6d] flex items-center h-20 px-6 space-x-6">
        <div class="flex-shrink-0">
            <img
                src="{{ asset('images/dilg_logo.png') }}"
                alt="DILG Logo"
                class="mx-auto mb-5 mt-5 max-w-[67px]"
                loading="lazy"
            />
        </div>
        <div class="flex flex-col text-white leading-tight max-w-lg">
            <span class="font-bold text-sm font-montserrat">DEPARTMENT OF THE INTERIOR AND LOCAL GOVERNMENT</span>
            <span class="text-xs opacity-70 font-montserrat">CORDILLERA ADMINISTRATIVE REGION</span>
            <span class="font-bold text-yellow-400 text-xs font-montserrat">
                RECRUITMENT SELECTION AND PLACEMENT PORTAL
            </span>
        </div>
    </header>

    <!-- Main content -->
    <main class="flex-grow flex justify-center items-center">
        <form method="POST" action="{{ route('forgot.password.reset') }}">
            @csrf
            <input type="hidden" name="email" value="{{ old('email', $email ?? '') }}">
            <div class="bg-[#002b6d] rounded-3xl py-5 px-8 flex flex-col items-center shadow-md w-auto h-auto">
                <h1 class="text-white font-bold text-xl mb-1 text-center font-montserrat mt-10">RESET PASSWORD</h1>
                <p class="text-white text-sm text-center mb-10 max-w-xs font-montserrat">
                    Please enter your new password and confirm it.
                </p>

                <input
                    required
                    type="password"
                    name="password"
                    minlength="6"
                    placeholder="Enter your new password"
                    class="w-full max-w-xs py-2 px-6 -mt-6 rounded-full placeholder:font-semibold placeholder:text-gray-400 font-montserrat text-center focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                <input
                    required
                    type="password"
                    name="password_confirmation"
                    minlength="6"
                    placeholder="Confirm your new password"
                    class="w-full max-w-xs py-2 px-6 mt-6 rounded-full placeholder:font-semibold placeholder:text-gray-400 font-montserrat text-center focus:outline-none focus:ring-2 focus:ring-blue-400"
                />

                @if ($errors->any())
                    <div class="text-red-500 text-sm mt-3 mb-3 font-montserrat">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <button
                    class="bg-yellow-400 mb-10 hover:bg-yellow-500 text-gray-700 font-semibold rounded-full mt-6 py-2 px-14 shadow-md focus:outline-none focus:ring-4 focus:ring-blue-300"
                    type="submit">
                    CHANGE PASSWORD
                </button>
            </div>
        </form>
    </main>

    @include('partials.loader')
</body>
</html>
