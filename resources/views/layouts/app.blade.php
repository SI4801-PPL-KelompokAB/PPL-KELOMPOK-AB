
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - Bonamu</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <div class="flex min-h-screen">

        <!-- Sidebar -->
        <aside class="w-64 bg-blue-900 text-white p-6">
            <h1 class="text-2xl font-bold mb-8">Bonamu</h1>

            <nav class="space-y-3">
                <a href="{{ route('dashboard') }}"
                   class="block p-3 rounded-lg bg-blue-800">
                    Dashboard
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1">

            <!-- Navbar -->
            <header class="bg-white shadow p-5 flex justify-between items-center">
                <h2 class="text-xl font-semibold">
                    @yield('page-title', 'Dashboard')
                </h2>

                <div class="flex items-center gap-4">
                    <span>{{ auth()->user()->name }}</span>

                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-red-500 text-white px-4 py-2 rounded-lg">
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- Content -->
            <main class="p-6">
                @yield('content')
            </main>

        </div>
    </div>

</body>
</html>
