<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/rutas.php";

$usuarioAutenticado = isset($_SESSION['usuario_id']);
$rolId = isset($_SESSION['rol_id']) ? (int) $_SESSION['rol_id'] : 0;
$nombreUsuario = trim((string) (($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? '')));

$destinosPorRol = [
    1 => [
        'url' => BASE_URL . 'views/admin/admin.php',
        'label' => 'Volver al panel admin',
        'rol' => 'Administrador',
    ],
    2 => [
        'url' => BASE_URL . 'views/index.php',
        'label' => 'Volver a mesas',
        'rol' => 'Mesero',
    ],
    3 => [
        'url' => BASE_URL . 'views/cocina.php',
        'label' => 'Volver a cocina',
        'rol' => 'Cocina',
    ],
    4 => [
        'url' => BASE_URL . 'views/barista.php',
        'label' => 'Volver a barista',
        'rol' => 'Barista',
    ],
];

$destinoPrincipal = $destinosPorRol[$rolId] ?? [
    'url' => BASE_URL . 'views/login.php',
    'label' => $usuarioAutenticado ? 'Ir a una vista segura' : 'Ir al login',
    'rol' => $usuarioAutenticado ? 'Usuario autenticado' : 'Invitado',
];

$mensajeEstado = $usuarioAutenticado
    ? 'Tu sesión está activa, pero tu rol actual no tiene permiso para entrar a esta sección.'
    : 'Necesitas iniciar sesión para acceder a esta sección del sistema.';

$submensaje = $usuarioAutenticado
    ? 'Te devolvemos a la vista que sí corresponde a tu perfil dentro de La Comanda.'
    : 'Puedes volver al acceso principal para iniciar sesión con una cuenta autorizada.';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Acceso restringido | La Comanda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        espresso: '#3E2723',
                        moka: '#6D4C41',
                        crema: '#F7F1E8',
                        latte: '#EADBC8',
                        salvia: '#6FA18D',
                        cacao: '#8D6E63',
                        cafe: '#5D4037'
                    },
                    boxShadow: {
                        glow: '0 30px 80px rgba(62, 39, 35, 0.18)',
                        floaty: '0 18px 40px rgba(62, 39, 35, 0.12)'
                    }
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            color-scheme: light;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background:
                radial-gradient(circle at top left, rgba(111, 161, 141, 0.22), transparent 30%),
                radial-gradient(circle at bottom right, rgba(141, 110, 99, 0.22), transparent 28%),
                linear-gradient(135deg, #f9f5ef 0%, #f3e7d7 45%, #efe3d3 100%);
            overflow-x: hidden;
        }

        .coffee-grid::before,
        .coffee-grid::after {
            content: '';
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .coffee-grid::before {
            background-image:
                linear-gradient(rgba(93, 64, 55, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(93, 64, 55, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
            mask-image: radial-gradient(circle at center, black 48%, transparent 100%);
        }

        .coffee-grid::after {
            background:
                radial-gradient(circle at 15% 20%, rgba(255, 255, 255, 0.55), transparent 18%),
                radial-gradient(circle at 80% 10%, rgba(255, 255, 255, 0.35), transparent 20%),
                radial-gradient(circle at 60% 80%, rgba(255, 255, 255, 0.3), transparent 18%);
        }

        .scene {
            position: relative;
            width: min(360px, 82vw);
            height: 290px;
            margin: 0 auto;
        }

        .static-scene {
            width: min(420px, 92vw);
            height: auto;
            min-height: 260px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .static-spill-illustration {
            width: 100%;
            height: auto;
            display: block;
        }

        .steam {
            position: absolute;
            width: 18px;
            height: 72px;
            border-radius: 999px;
            filter: blur(2px);
            opacity: .42;
            background: linear-gradient(to top, rgba(255, 255, 255, 0), rgba(255, 255, 255, .92));
            animation: steamRise 3.6s ease-in-out infinite;
        }

        .steam:nth-child(1) {
            left: 22%;
            animation-delay: 0s;
        }

        .steam:nth-child(2) {
            left: 44%;
            animation-delay: .95s;
        }

        .steam:nth-child(3) {
            left: 64%;
            animation-delay: 1.9s;
        }

        .cup-wrap {
            position: absolute;
            right: 34px;
            bottom: 86px;
            width: 132px;
            height: 172px;
            transform-origin: 72% 94%;
            animation: cupMotion 5.2s cubic-bezier(.45, .03, .2, 1) infinite;
            z-index: 3;
        }

        .cup-shadow {
            position: absolute;
            right: 6px;
            bottom: -2px;
            width: 92px;
            height: 22px;
            border-radius: 999px;
            background: rgba(62, 39, 35, 0.16);
            filter: blur(7px);
            transform-origin: center;
            animation: shadowShift 5.2s ease-in-out infinite;
        }

        .cup {
            position: absolute;
            right: 0;
            bottom: 0;
            width: 112px;
            height: 150px;
            background: linear-gradient(180deg, #fffdf9 0%, #f4ebdf 100%);
            border: 4px solid #6d4c41;
            border-radius: 20px 20px 30px 30px;
            clip-path: polygon(12% 0%, 88% 0%, 100% 100%, 0% 100%);
            box-shadow: 0 20px 32px rgba(62, 39, 35, 0.17);
            overflow: visible;
        }

        .cup::before {
            content: '';
            position: absolute;
            inset: 14px 18px 24px;
            border-radius: 14px;
            background: linear-gradient(145deg, #e9d9c6, #ddc6ab);
            opacity: .96;
        }

        .cup::after {
            content: '';
            position: absolute;
            left: 50%;
            top: 48px;
            width: 50px;
            height: 58px;
            transform: translateX(-50%);
            border-radius: 14px;
            border: 2px solid rgba(109, 76, 65, 0.16);
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.04));
        }

        .lid {
            position: absolute;
            top: -17px;
            left: 50%;
            width: 124px;
            height: 28px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: linear-gradient(180deg, #7d5a50 0%, #4e342e 100%);
            box-shadow: 0 6px 12px rgba(62, 39, 35, 0.15);
        }

        .lid::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 5px;
            transform: translateX(-50%);
            width: 24px;
            height: 7px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.36);
        }

        .lid::after {
            content: '';
            position: absolute;
            inset: 4px 8px 12px;
            border-radius: 999px;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0));
        }

        .coffee-surface {
            position: absolute;
            top: 8px;
            left: 50%;
            width: 86px;
            height: 14px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: linear-gradient(180deg, #7b5138 0%, #5d3827 100%);
            box-shadow: inset 0 2px 0 rgba(255, 255, 255, 0.15);
        }

        .coffee-wave {
            position: absolute;
            top: 10px;
            left: 50%;
            width: 54px;
            height: 8px;
            transform: translateX(-50%);
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            animation: coffeeWave 5.2s ease-in-out infinite;
        }

        .pour {
            position: absolute;
            right: 120px;
            bottom: 108px;
            width: 22px;
            height: 112px;
            transform-origin: top center;
            animation: pourFlow 5.2s ease-in-out infinite;
            z-index: 2;
        }

        .pour::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            width: 14px;
            height: 100%;
            transform: translateX(-50%);
            border-radius: 999px 999px 40% 55%;
            background: linear-gradient(180deg, rgba(119, 74, 48, 0), rgba(101, 61, 40, 0.96) 24%, rgba(86, 52, 35, 0.98) 100%);
            filter: drop-shadow(0 5px 8px rgba(62, 39, 35, 0.16));
        }

        .pour::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -4px;
            width: 24px;
            height: 18px;
            transform: translateX(-50%);
            border-radius: 50% 50% 58% 42%;
            background: radial-gradient(circle at 50% 30%, rgba(150, 103, 70, 0.95), rgba(86, 52, 35, 0.98));
        }

        .splash,
        .splash-small {
            position: absolute;
            border-radius: 50%;
            background: radial-gradient(circle at 35% 35%, rgba(150, 103, 70, 0.95), rgba(86, 52, 35, 0.98));
            opacity: 0;
            z-index: 1;
        }

        .splash {
            right: 154px;
            bottom: 102px;
            width: 18px;
            height: 18px;
            animation: splashUp 5.2s ease-in-out infinite;
        }

        .splash-small {
            right: 138px;
            bottom: 92px;
            width: 10px;
            height: 10px;
            animation: splashUpSmall 5.2s ease-in-out infinite;
        }

        .spill {
            position: absolute;
            right: 88px;
            bottom: 42px;
            width: 188px;
            height: 78px;
            transform-origin: 82% 50%;
            animation: puddleSpread 5.2s ease-in-out infinite;
            z-index: 1;
        }

        .spill::before,
        .spill::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 56% 44% 58% 42% / 52% 38% 62% 48%;
            background: radial-gradient(circle at 34% 28%, rgba(145, 103, 74, 0.92), rgba(84, 50, 34, 0.98) 72%);
            box-shadow: inset 0 10px 18px rgba(255, 255, 255, 0.12), 0 18px 30px rgba(62, 39, 35, 0.12);
        }

        .spill::after {
            inset: 12px 18px 22px 22px;
            border-radius: 60% 40% 56% 44% / 45% 55% 45% 55%;
            background: radial-gradient(circle at 35% 35%, rgba(255, 255, 255, 0.14), rgba(255, 255, 255, 0));
            box-shadow: none;
        }

        .spill-tail {
            position: absolute;
            right: 214px;
            bottom: 62px;
            width: 76px;
            height: 22px;
            border-radius: 999px 16px 18px 999px;
            background: linear-gradient(90deg, rgba(98, 61, 41, 0.96), rgba(98, 61, 41, 0.65), rgba(98, 61, 41, 0));
            transform-origin: right center;
            filter: blur(0.2px);
            animation: trailSpread 5.2s ease-in-out infinite;
            z-index: 1;
        }

        .counter {
            position: absolute;
            inset: auto 0 0;
            height: 28px;
            border-radius: 999px;
            background: linear-gradient(90deg, #c6a586, #e8d7c3 45%, #d2b292 100%);
            box-shadow: 0 -1px 0 rgba(255, 255, 255, 0.5) inset;
        }

        @keyframes steamRise {
            0% {
                transform: translateY(16px) scaleX(0.9);
                opacity: 0;
            }

            20% {
                opacity: .32;
            }

            50% {
                transform: translateY(-8px) translateX(6px) scaleX(1.05);
                opacity: .42;
            }

            100% {
                transform: translateY(-34px) translateX(-5px) scaleX(1.14);
                opacity: 0;
            }
        }

        @keyframes cupMotion {

            0%,
            12% {
                transform: translate(0, 0) rotate(0deg);
            }

            22% {
                transform: translate(-3px, 1px) rotate(-5deg);
            }

            34% {
                transform: translate(-18px, 5px) rotate(-22deg);
            }

            48%,
            72% {
                transform: translate(-56px, 11px) rotate(-74deg);
            }

            84% {
                transform: translate(-58px, 11px) rotate(-74deg);
            }

            100% {
                transform: translate(0, 0) rotate(0deg);
            }
        }

        @keyframes shadowShift {

            0%,
            15% {
                transform: scaleX(1) translateX(0);
                opacity: .18;
            }

            48%,
            76% {
                transform: scaleX(1.18) translateX(-26px);
                opacity: .11;
            }

            100% {
                transform: scaleX(1) translateX(0);
                opacity: .18;
            }
        }

        @keyframes coffeeWave {

            0%,
            18%,
            100% {
                transform: translateX(-50%) scaleX(1);
                opacity: .4;
            }

            34% {
                transform: translateX(-50%) scaleX(1.12) translateY(1px);
                opacity: .18;
            }

            56%,
            72% {
                transform: translateX(-50%) scaleX(.92) translateY(-1px);
                opacity: .28;
            }
        }

        @keyframes pourFlow {

            0%,
            24% {
                transform: scaleY(0) translateY(-10px);
                opacity: 0;
            }

            34% {
                transform: scaleY(.52) translateY(-2px);
                opacity: .7;
            }

            46%,
            66% {
                transform: scaleY(1) translateY(0);
                opacity: 1;
            }

            80% {
                transform: scaleY(.35) translateY(4px);
                opacity: .36;
            }

            100% {
                transform: scaleY(0) translateY(-10px);
                opacity: 0;
            }
        }

        @keyframes puddleSpread {

            0%,
            24% {
                transform: scale(.12, .45);
                opacity: 0;
            }

            38% {
                transform: scale(.56, .72);
                opacity: .82;
            }

            52% {
                transform: scale(.92, .94);
                opacity: .96;
            }

            68%,
            82% {
                transform: scale(1.02, 1);
                opacity: 1;
            }

            100% {
                transform: scale(.2, .5);
                opacity: 0;
            }
        }

        @keyframes trailSpread {

            0%,
            26% {
                transform: scaleX(.05);
                opacity: 0;
            }

            44% {
                transform: scaleX(.7);
                opacity: .82;
            }

            58%,
            78% {
                transform: scaleX(1);
                opacity: .92;
            }

            100% {
                transform: scaleX(.08);
                opacity: 0;
            }
        }

        @keyframes splashUp {

            0%,
            42% {
                transform: translate(0, 0) scale(.4);
                opacity: 0;
            }

            52% {
                transform: translate(-22px, -18px) scale(1);
                opacity: .95;
            }

            64% {
                transform: translate(-34px, -8px) scale(.76);
                opacity: .45;
            }

            100% {
                transform: translate(-38px, 2px) scale(.2);
                opacity: 0;
            }
        }

        @keyframes splashUpSmall {

            0%,
            44% {
                transform: translate(0, 0) scale(.4);
                opacity: 0;
            }

            54% {
                transform: translate(-10px, -22px) scale(1);
                opacity: .88;
            }

            66% {
                transform: translate(-18px, -10px) scale(.68);
                opacity: .34;
            }

            100% {
                transform: translate(-22px, 0) scale(.2);
                opacity: 0;
            }
        }

        @media (prefers-reduced-motion: reduce) {

            .cup-wrap,
            .cup-shadow,
            .spill,
            .spill-tail,
            .pour,
            .steam,
            .splash,
            .splash-small,
            .coffee-wave {
                animation: none !important;
            }
        }
    </style>
</head>

<body class="coffee-grid min-h-screen text-espresso">
    <main class="relative z-10 min-h-screen flex items-center justify-center px-4 py-10">
        <section class="w-full max-w-3xl">
            <div class="bg-white/80 backdrop-blur-xl border border-white/70 rounded-[2rem] shadow-glow p-7 sm:p-10 lg:p-12 overflow-hidden relative">
                <div class="absolute -top-16 -right-16 w-44 h-44 rounded-full bg-salvia/15 blur-3xl"></div>
                <div class="absolute -bottom-16 -left-10 w-44 h-44 rounded-full bg-cacao/15 blur-3xl"></div>

                <div class="relative">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-espresso text-white text-xs font-semibold tracking-[0.18em] uppercase shadow-floaty">
                        <span class="inline-block w-2.5 h-2.5 rounded-full bg-[#f5c451]"></span>
                        Área protegida
                    </div>

                    <h1 class="mt-6 text-4xl sm:text-5xl font-extrabold leading-tight text-espresso">
                        Ups... esta mesa no es para ti.
                    </h1>

                    <p class="mt-4 text-base sm:text-lg text-cafe/85 leading-relaxed max-w-2xl">
                        <?= htmlspecialchars($mensajeEstado) ?>
                    </p>

                    <p class="mt-3 text-sm sm:text-base text-cacao leading-relaxed max-w-xl">
                        <?= htmlspecialchars($submensaje) ?>
                    </p>

                    <div class="mt-8 grid sm:grid-cols-2 gap-4">
                        <div class="rounded-3xl border border-[#eadfce] bg-crema px-5 py-4 shadow-floaty">
                            <p class="text-xs uppercase tracking-[0.2em] text-cacao/80 font-semibold">Sesión actual</p>
                            <p class="mt-2 text-lg font-bold text-espresso">
                                <?= $usuarioAutenticado && $nombreUsuario !== '' ? htmlspecialchars($nombreUsuario) : 'Sin sesión iniciada' ?>
                            </p>
                            <p class="mt-1 text-sm text-cafe/80">
                                <?= htmlspecialchars($destinoPrincipal['rol']) ?>
                            </p>
                        </div>

                        <div class="rounded-3xl border border-[#eadfce] bg-white px-5 py-4 shadow-floaty">
                            <p class="text-xs uppercase tracking-[0.2em] text-cacao/80 font-semibold">Qué puedes hacer</p>
                            <p class="mt-2 text-sm text-cafe/90 leading-6">
                                Regresar a tu módulo autorizado o cerrar sesión para entrar con otra cuenta.
                            </p>
                        </div>
                    </div>

                    <div class="mt-8 flex flex-col sm:flex-row gap-3">
                        <a href="<?= htmlspecialchars($destinoPrincipal['url']) ?>"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl bg-salvia px-6 py-4 text-white font-semibold shadow-floaty transition duration-200 hover:translate-y-[-1px] hover:bg-[#5f907d]">
                            <span>↩</span>
                            <?= htmlspecialchars($destinoPrincipal['label']) ?>
                        </a>

                        <a href="<?= BASE_URL ?>public/api/logout.php"
                            class="inline-flex items-center justify-center gap-2 rounded-2xl border border-[#d8cab7] bg-white px-6 py-4 text-espresso font-semibold transition duration-200 hover:bg-[#fbf7f1]">
                            <span>⎋</span>
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            </div>

        </section>
    </main>
</body>

</html>