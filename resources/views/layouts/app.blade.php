<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Corpsaf Comprobantes</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Estilos personalizados -->
    <style>
        body {
            padding-top: 70px;
            background-color: #f1f3f6;
        }

        /* Navbar */
        .navbar {
            box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        }

        /* Tabla */
        table.table {
            background-color: #fff;
        }

        table.table th {
            background-color: #0d6efd;
            color: #fff;
        }

        table.table tbody tr:hover {
            background-color: #e9f5ff;
        }

        /* Imagen miniatura */
        .thumbnail {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            transition: transform 0.3s;
            cursor: pointer;
        }

        .thumbnail:hover {
            transform: scale(1.2);
            z-index: 10;
        }

        /* Botones */
        .btn-primary {
            background-color: #0d6efd;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0b5ed7;
        }

        .alert {
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('comprobantes.index') }}">
                <i class="fas fa-file-invoice-dollar me-2"></i> Corpsaf Comprobantes
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('comprobantes.index') }}">
                            <i class="fas fa-list me-1"></i> Listado
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('comprobantes.create') }}">
                            <i class="fas fa-plus me-1"></i> Nuevo
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Contenido principal -->
    <main class="container my-4">
        @yield('content')
    </main>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
