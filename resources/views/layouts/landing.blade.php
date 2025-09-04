<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        {{-- favicon --}}
        <link rel="icon" href="images/main-logo.webp" type="image/x-icon">
        
        <title>{{ config('app.name', 'Laravel') }}</title>
        
        @vite(['resources/js/app.js'])
        
        <style>
            .loader {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255, 255, 255, 0.8);
                z-index: 9999;
                transition: opacity 0.5s ease-out;
            }
            .loader.hidden {
                opacity: 0;
                visibility: hidden;
            }
            .spinner {
                border: 8px solid #f3f3f3;
                border-radius: 50%;
                border-top: 8px solid #3498db;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
            }
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
            main.hidden {
                opacity: 0;
                transition: opacity 0.5s ease-in;
            }
            main.visible {
                opacity: 1;
            }
        </style>
    </head>
<body>
    <div class="loader" id="loader">
        <div class="spinner"></div>
    </div>
    <main id="main-content" class="hidden h-screen w-screen relative grid place-items-center bg-cover bg-center px-2 md:px-0" style="background-image: url('/images/wallpaper-ubt.webp');">
        <div class="absolute inset-0 bg-lavender bg-opacity-45"></div>
        <div class="relative w-full sm:w-2/3 xl:w-1/3 bg-black bg-opacity-80 rounded-tr-3xl rounded-bl-3xl w-96 text-white py-3">
            <div class="information p-3 text-center flex flex-col items-center">
                <div>
                    <img src="images/main-logo.webp" width="150px" height="150px"/>
                </div>
                <small class="block text-4xl italic text-lavender mt-5 font-semibold">
                    UBT TEST
                </small>
                <small class="block text-lg italic text-lavender mt-2">
                   UBT TESTING WEB APPLICATION
                </small>
                @if (session('error'))
                    <div class="text-lg flex flex-col items-center bg-lavender rounded text-red-500 font-semibold px-4 py-2 mt-2 mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="3em" height="3em" viewBox="0 0 256 256"><path fill="red" d="M128 112a28 28 0 0 0-8 54.83V184a8 8 0 0 0 16 0v-17.17a28 28 0 0 0-8-54.83m0 40a12 12 0 1 1 12-12a12 12 0 0 1-12 12m80-72h-32V56a48 48 0 0 0-96 0v24H48a16 16 0 0 0-16 16v112a16 16 0 0 0 16 16h160a16 16 0 0 0 16-16V96a16 16 0 0 0-16-16M96 56a32 32 0 0 1 64 0v24H96Zm112 152H48V96h160z"/></svg>
                        {{ session('error') }}
                    </div>
                @else
                    <a href="{{ \App\Http\Middleware\Sso::getLoginLink() }}" 
                        class="my-10 bg-blue-700 border border-transparent rounded-md py-2 sm:px-28 px-4 inline-flex justify-center text-lg text-white hover:text-lavender font-semibold hover:transition ease-in-out delay-150 hover:scale-110">
                        Login SSO UBT
                    </a>
                @endif
                <p class="text-center text-lavender">
                    © PT. Thamrin Surya Group<br />
                    Universitas Bunda Thamrin<br />
                    2025
                </p>
            </div>
        </div>
    </main>

    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('loader').classList.add('hidden');
                document.getElementById('main-content').classList.remove('hidden');
                document.getElementById('main-content').classList.add('visible');
            }, 300);
        });
    </script>
</body>
</html>
