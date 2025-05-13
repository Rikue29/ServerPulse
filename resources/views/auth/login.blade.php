<x-guest-layout>
    <div class="flex min-h-screen">
        <!-- Left: Image -->
        <div class="w-1/2 hidden md:block">
            <img src="{{ asset('images/serverpulseimage.jpg') }}" alt="Data Center" class="w-full h-full object-cover">
        </div>

        <!-- Right: Login Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center bg-white">
            <div class="w-full max-w-md px-8 py-10">
                <h2 class="text-3xl font-semibold mb-2">Login</h2>
                <p class="text-gray-500 mb-6">Login to access to the server</p>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div class="mb-4">
                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:border-indigo-500">
                        <x-input-error :messages="$errors->get('email')" class="mt-1 text-sm text-red-600" />
                    </div>

                    <!-- Password -->
                    <div class="mb-4">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input id="password" name="password" type="password" required
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-indigo-200 focus:border-indigo-500">
                        <x-input-error :messages="$errors->get('password')" class="mt-1 text-sm text-red-600" />
                    </div>

                    <!-- Terms -->
                    <div class="mb-2">
                        <label class="inline-flex items-center">
                            <input type="checkbox" required class="form-checkbox text-indigo-600" />
                            <span class="ml-2 text-sm text-gray-600">
                                Agree to our <a href="#" class="text-indigo-600 underline">Terms of use</a> and <a href="#" class="text-indigo-600 underline">Privacy Policy</a>
                            </span>
                        </label>
                    </div>

                    <!-- Subscribe -->
                    <div class="mb-4">
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox text-indigo-600" />
                            <span class="ml-2 text-sm text-gray-600">Subscribe to our monthly newsletter</span>
                        </label>
                    </div>

                    <!-- Fake Captcha -->
                    <div class="mb-4">
                        <div class="border rounded p-3 flex items-center">
                            <input type="checkbox" class="form-checkbox text-green-500" checked disabled>
                            <span class="ml-2 text-sm">I'm not a robot</span>
                            <img src="{{ asset('images/recaptcha.png') }}" alt="Captcha" class="h-6 ml-auto">
                        </div>
                    </div>

                    <!-- Submit -->
                    <div>
                        <button type="submit"
                                class="w-full py-2 px-4 bg-gray-300 text-white rounded-md cursor-not-allowed"
                                disabled>
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-guest-layout>
