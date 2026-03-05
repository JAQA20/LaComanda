<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../../model/Categorias.php"; // Para listar categorías en el navbar
$categorias = Categorias::listarActivas();
?>

<nav id="navbar" class="custom-brown fixed top-0 left-0 right-0 z-50 h-16 flex items-center justify-between px-6 shadow-lg">
    <div class="flex items-center">
        <img class="h-10 w-10 object-contain mr-3"
            src="/LaComanda-main/public/img/logotipo2.PNG"
            alt="Cafetería Toscana" />
        <span class="text-beige text-xl font-semibold">Cafetería Toscana</span>
    </div>

    <div class="flex space-x-8 items-center">

        <!-- Mesas fijo -->
        <!-- <button type="button"
            id="mesas-btn"
            data-slug="mesas"
            class="text-beige hover-mint font-medium transition-all duration-200 border-b-2 border-mint">
            Mesas
        </button> -->

        <!-- Categorías desde BD -->
        <?php foreach ($categorias as $cat): ?>
            <button type="button"
                data-slug="<?= htmlspecialchars($cat["slug"]) ?>"
                id="<?= htmlspecialchars($cat["slug"]) ?>-btn"
                class="text-beige hover-mint font-medium transition-all duration-200">
                <?= htmlspecialchars($cat["nombre"]) ?>
            </button>
        <?php endforeach; ?>

        <!-- Dropdown usuario (Bootstrap) -->
        <div class="dropdown">
            <button class="btn dropdown-toggle hover-mint" type="button" data-bs-toggle="dropdown">
                <i class="fas fa-user text-white text-2xl"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">

                <li class="px-3 py-2 text-sm text-gray-700 font-semibold border-b">
                    <?php if (isset($_SESSION["nombre"])): ?>
                        <?= htmlspecialchars($_SESSION["nombre"] . " " . $_SESSION["apellido"]) ?>
                    <?php else: ?>
                        Usuario
                    <?php endif; ?>
                </li>

                <?php if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 1): ?>
                    <li class="px-3 py-2">
                        <a class="dropdown-item" href="/LaComanda-main/views/admin/admin.php">
                            <i class="fas fa-shield-halved me-2"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>

                <?php if (isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 3 or isset($_SESSION["rol_id"]) && (int)$_SESSION["rol_id"] === 1): ?>
                    <li>
                        <a class="dropdown-item" href="/LaComanda-main/views/cocina.php">
                            <i class="fas fa-fire me-2"></i> Cocina
                        </a>
                    </li>
                <?php endif; ?>

                <li>
                    <hr class="dropdown-divider">
                </li>

                <li>
                    <a class="dropdown-item text-danger" href="/LaComanda-main/controller/logoutController.php">
                        <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
                    </a>
                </li>
            </ul>
        </div>

    </div>
</nav>