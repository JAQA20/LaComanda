<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../../public/css/style.css">
    <title>Navbar</title>
</head>

<body>
    <!-- Navbar -->
    <nav id="navbar" class="custom-brown fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6 shadow-lg">
        <div class="flex items-center">
            <img class="h-10 w-10 object-contain mr-3" src="../public/img/logotipo2.PNG" alt="elegant coffee shop logo with toscana text, warm brown and mint colors, minimalist design" />
            <span class="text-beige text-xl font-semibold">Cafetería Toscana</span>
        </div>
        <div class="flex space-x-8">
            <button id="mesas-btn" class="text-beige hover-mint font-medium transition-all duration-200 border-b-2 border-mint">Mesas</button>
            <button id="cafes-btn" class="text-beige hover-mint font-medium transition-all duration-200">Cafés</button>
            <button id="comidas-btn" class="text-beige hover-mint font-medium transition-all duration-200">Comidas</button>
            <button id="especialidades-btn" class="text-beige hover-mint font-medium transition-all duration-200">Especialidades</button>
            <button id="postres-btn" class="text-beige hover-mint font-medium transition-all duration-200">Postres</button>
            <button id="bebidas-btn" class="text-beige hover-mint font-medium transition-all duration-200">Bebidas Frías</button>
            <div class="dropdown">
                <button class="btn  dropdown-toggl hover-mint" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user text-white text-2xl"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="../views/login.php">Log out</a></li>
                    <li><a class="dropdown-item" href="#">Admin</a></li>
                    <!--  <li><a class="dropdown-item" href="#">Something else here</a></li> -->

                </ul>
            </div>
        </div>
    </nav>

</body>

</html>