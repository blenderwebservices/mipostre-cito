<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Mi Postrecito | Delicias Cremosas Artesanales de Jaan' }}</title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Caveat:wght@600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Tailwind Configuration: Estilo híbrido Mi Mandadito (azul fresco) + Postrecito (frambuesa/crema) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#fdf2f8',
                            100: '#fce7f3',
                            200: '#fbcfe8',
                            500: '#ec4899', // Postrecito Pink
                            600: '#db2777',
                            700: '#be185d',
                        },
                        gepp: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#0284c7', // Mi Mandadito Blue
                            600: '#0072b2',
                            700: '#0369a1',
                        },
                        cream: '#fffdf5',
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                        handwriting: ['Caveat', 'cursive'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            background-color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .blob-bg {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath fill='%23FCE7F3' d='M44.7,-76.4C58.8,-69.2,71.8,-59.1,81.4,-46.3C91,-33.5,97.2,-18.1,96.6,-2.9C95.9,12.3,88.4,27.3,78.2,39.6C68.1,51.9,55.3,61.4,41.4,68.9C27.5,76.4,12.5,81.9,-2.4,85.9C-17.3,89.9,-32.1,92.5,-45,86.5C-57.9,80.5,-68.9,65.9,-77.3,50.1C-85.7,34.3,-91.5,17.2,-91.2,0.2C-90.9,-16.8,-84.5,-33.6,-74.6,-47.5C-64.7,-61.4,-51.3,-72.4,-36.8,-79.3C-22.3,-86.2,-6.7,-89.1,7.9,-85.4C22.5,-81.7,40.1,-71.4,44.7,-76.4Z' transform='translate(100 100)' /%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: center right;
            background-size: cover;
        }
    </style>

    @livewireStyles
</head>
<body class="text-slate-800 antialiased min-h-screen flex flex-col justify-between selection:bg-brand-500 selection:text-white">

    {{ $slot }}

    <!-- Livewire Scripts -->
    @livewireScripts
</body>
</html>

