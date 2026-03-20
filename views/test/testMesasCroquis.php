<?php
require_once __DIR__ . "/../config/env.php";
app_configure_errors();
require_once __DIR__ . "/../middleware/auth.php";
require_once __DIR__ . "/../middleware/roles.php";
require_once __DIR__ . "/../config/rutas.php";

verificarRol([1, 2]); // Admin(1) y Mesero(2)

$isAdminLayout = isset($_SESSION['rol_id']) && (int)$_SESSION['rol_id'] === 1;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Comanda</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.FontAwesomeConfig = {
            autoReplaceSvg: 'nest'
        };
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/css/style.css">
    <a href="../config/rutas.php"></a>

    <style>
        :root {
            --floor-shell-bg: #fffdf8;
            --floor-panel-bg: linear-gradient(180deg, rgba(112, 163, 143, 0.12), rgba(139, 69, 19, 0.06));
            --floor-stroke: rgba(139, 69, 19, 0.12);
            --floor-text: #4a2c17;
            --floor-muted: #7b6a5f;
            --floor-wood-1: #d7b188;
            --floor-wood-2: #c49667;
            --table-free-1: #8fd2b7;
            --table-free-2: #70a38f;
            --table-pending-1: #a06b45;
            --table-pending-2: #8b4513;
            --table-ready-1: #4ade80;
            --table-ready-2: #16a34a;
            --table-selected: #f59e0b;
            --chair: #97b7e6;
            --floor-shell-padding: clamp(.8rem, 1.4vw, 1.15rem);
            --floor-canvas-padding: clamp(.45rem, 1.1vw, .9rem);
            --floor-gap: clamp(.65rem, 1vw, .85rem);
            --control-gap: clamp(.55rem, 1vw, .75rem);
            --control-font: clamp(.78rem, .92vw, .92rem);
        }

        .floor-shell {
            background: var(--floor-shell-bg);
            border: 1px solid rgba(139, 69, 19, 0.08);
            border-radius: 24px;
            padding: var(--floor-shell-padding);
            box-shadow: 0 20px 40px rgba(74, 44, 23, 0.08);
        }

        .floor-layout {
            display: grid;
            gap: var(--floor-gap);
        }

        .floor-legend-bar {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem .65rem;
            align-items: center;
        }

        .floor-legend-item {
            display: inline-flex;
            align-items: center;
            gap: .5rem;
            padding: clamp(.35rem, .8vw, .45rem) clamp(.6rem, 1vw, .8rem);
            border-radius: 999px;
            background: rgba(255, 255, 255, .72);
            border: 1px solid rgba(139, 69, 19, .08);
            color: var(--floor-text);
            font-size: clamp(.74rem, .88vw, .85rem);
            font-weight: 700;
        }

        .sw {
            width: 12px;
            height: 12px;
            border-radius: 999px;
            display: inline-block;
            flex: 0 0 auto;
        }

        .sw.libre {
            background: linear-gradient(180deg, var(--table-free-1), var(--table-free-2));
        }

        .sw.pendiente {
            background: linear-gradient(180deg, var(--table-pending-1), var(--table-pending-2));
        }

        .sw.lista {
            background: linear-gradient(180deg, var(--table-ready-1), var(--table-ready-2));
        }

        .sw.kitchen {
            background: linear-gradient(135deg, #8b5cf6, #60a5fa);
        }

        .floor-canvas {
            border-radius: 20px;
            border: 1px solid var(--floor-stroke);
            background: rgba(255, 255, 255, .45);
            padding: var(--floor-canvas-padding);
            overflow: hidden;
        }

        .restaurant-plan {
            width: 100%;
            aspect-ratio: 1280 / 700;
            min-height: clamp(430px, 62vh, 760px);
            position: relative;
            border-radius: 18px;
            overflow: hidden;
            background: linear-gradient(180deg, rgba(255, 255, 255, .2), rgba(255, 255, 255, .05));
            container-type: inline-size;
            --plan-unit: clamp(8px, 1.18cqi, 13px);
            --table-square-size: clamp(74px, calc(var(--plan-unit) * 7.6), 94px);
            --table-rect-width: clamp(132px, calc(var(--plan-unit) * 13.2), 164px);
            --table-rect-height: clamp(74px, calc(var(--plan-unit) * 7.6), 94px);
            --table-radius: clamp(14px, calc(var(--plan-unit) * 1.4), 18px);
            --table-shadow: 0 clamp(12px, calc(var(--plan-unit) * 1.45), 18px) clamp(22px, calc(var(--plan-unit) * 2.8), 35px) rgba(74, 44, 23, .16);
            --table-label-size: clamp(1rem, calc(var(--plan-unit) * 1.1), 1.2rem);
            --table-status-size: clamp(.66rem, calc(var(--plan-unit) * .62), .78rem);
            --table-status-padding-y: clamp(.24rem, calc(var(--plan-unit) * .18), .32rem);
            --table-status-padding-x: clamp(.45rem, calc(var(--plan-unit) * .42), .6rem);
            --table-content-gap: clamp(.22rem, calc(var(--plan-unit) * .2), .36rem);
            --chair-width: clamp(18px, calc(var(--plan-unit) * 1.75), 22px);
            --chair-height: clamp(11px, calc(var(--plan-unit) * 1.1), 14px);
            --chairs-inset: clamp(-20px, calc(var(--plan-unit) * -2), -28px);
            --kitchen-width: clamp(190px, calc(var(--plan-unit) * 21), 260px);
            --kitchen-height: clamp(152px, calc(var(--plan-unit) * 17), 210px);
            --drag-handle-size: clamp(34px, calc(var(--plan-unit) * 3), 42px);
            --drag-handle-offset: clamp(7px, calc(var(--plan-unit) * .7), 10px);
            --drag-handle-radius: clamp(10px, calc(var(--plan-unit) * .8), 12px);
            --drag-handle-font: clamp(.95rem, calc(var(--plan-unit) * .9), 1.15rem);
        }

        .floor-controls {
            display: flex;
            flex-wrap: wrap;
            gap: var(--control-gap);
            align-items: center;
            justify-content: space-between;
        }

        .floor-actions {
            display: flex;
            flex-wrap: wrap;
            gap: var(--control-gap);
        }

        .floor-btn {
            border: 1px solid rgba(139, 69, 19, .14);
            padding: clamp(.75rem, .95vw, .95rem) clamp(1rem, 1.4vw, 1.2rem);
            border-radius: 14px;
            font-weight: 700;
            font-size: var(--control-font);
            min-height: 46px;
            cursor: pointer;
            transition: transform .15s ease, box-shadow .15s ease, background .15s ease;
        }

        .floor-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 10px 24px rgba(74, 44, 23, .12);
        }

        .floor-btn.primary {
            background: linear-gradient(135deg, #70a38f, #8fd2b7);
            color: #fff;
        }

        .floor-btn.ghost {
            background: transparent;
            color: var(--floor-text);
        }

        .floor-edit-wrap {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: .55rem .75rem;
        }

        .floor-chip {
            align-self: flex-start;
            border: 1px solid rgba(112, 163, 143, .25);
            background: rgba(112, 163, 143, .12);
            color: var(--floor-text);
            font-size: clamp(.78rem, .9vw, .86rem);
            font-weight: 800;
            padding: clamp(.58rem, .9vw, .7rem) clamp(.85rem, 1.2vw, 1rem);
            border-radius: 999px;
            min-height: 44px;
            cursor: pointer;
        }

        .floor-chip.active {
            background: rgba(112, 163, 143, .2);
        }

        .floor-hint {
            color: var(--floor-muted);
            font-size: .82rem;
        }

        .floor-controls-note {
            width: 100%;
            color: var(--floor-muted);
            font-size: .78rem;
        }

        .restaurant-floor {
            position: absolute;
            inset: 0;
            margin: clamp(.2rem, .55vw, .35rem);
            border-radius: 16px;
            background: linear-gradient(0deg, rgba(0, 0, 0, .08), rgba(0, 0, 0, .08)), repeating-linear-gradient(90deg, rgba(255, 255, 255, .08) 0px, rgba(255, 255, 255, .08) 18px, rgba(255, 255, 255, .03) 18px, rgba(255, 255, 255, .03) 36px), linear-gradient(180deg, var(--floor-wood-1), var(--floor-wood-2));
            box-shadow: inset 0 0 0 1px rgba(74, 44, 23, .14);
        }

        .draggable {
            position: absolute;
            touch-action: none;
            user-select: none;
            -webkit-user-select: none;
            -webkit-tap-highlight-color: transparent;
        }

        .drag-handle {
            position: absolute;
            left: var(--drag-handle-offset);
            top: var(--drag-handle-offset);
            width: var(--drag-handle-size);
            height: var(--drag-handle-size);
            border-radius: var(--drag-handle-radius);
            background: rgba(255, 255, 255, .9);
            display: grid;
            place-items: center;
            color: #3b2415;
            font-size: var(--drag-handle-font);
            font-weight: 900;
            box-shadow: 0 10px 20px rgba(74, 44, 23, .18);
            cursor: grab;
            z-index: 2;
        }

        .layout-readonly .drag-handle {
            display: none;
        }

        .dragging .drag-handle {
            cursor: grabbing;
        }

        .dragging {
            filter: brightness(1.04);
        }

        .kitchen {
            width: var(--kitchen-width);
            height: var(--kitchen-height);
            border-radius: clamp(16px, calc(var(--plan-unit) * 1.5), 18px);
            background: linear-gradient(180deg, #3d4f77, #253552);
            border: 1px solid rgba(255, 255, 255, .18);
            box-shadow: 0 clamp(12px, calc(var(--plan-unit) * 1.5), 18px) clamp(22px, calc(var(--plan-unit) * 2.8), 35px) rgba(0, 0, 0, .18);
            overflow: hidden;
        }

        .kitchen::after {
            content: 'Cocina';
            position: absolute;
            right: clamp(10px, calc(var(--plan-unit) * .9), 12px);
            top: clamp(8px, calc(var(--plan-unit) * .7), 10px);
            color: rgba(255, 255, 255, .88);
            font-size: clamp(.7rem, calc(var(--plan-unit) * .72), .82rem);
            font-weight: 900;
            letter-spacing: .04em;
        }

        .kitchen .burners {
            position: absolute;
            left: clamp(10px, calc(var(--plan-unit) * 1.1), 14px);
            top: clamp(34px, calc(var(--plan-unit) * 3.7), 44px);
            width: clamp(52px, calc(var(--plan-unit) * 5.4), 66px);
            height: clamp(104px, calc(var(--plan-unit) * 12), 150px);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: clamp(12px, calc(var(--plan-unit) * 1.1), 14px);
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: clamp(7px, calc(var(--plan-unit) * .75), 10px);
            padding: clamp(9px, calc(var(--plan-unit) * .9), 12px);
            pointer-events: none;
        }

        .kitchen .burners span {
            width: clamp(14px, calc(var(--plan-unit) * 1.45), 18px);
            height: clamp(14px, calc(var(--plan-unit) * 1.45), 18px);
            border-radius: 999px;
            background: rgba(0, 0, 0, .22);
            box-shadow: inset 0 0 0 2px rgba(255, 255, 255, .25);
            justify-self: center;
            align-self: center;
        }

        .kitchen .prep {
            position: absolute;
            left: clamp(76px, calc(var(--plan-unit) * 8), 98px);
            top: clamp(48px, calc(var(--plan-unit) * 5), 60px);
            width: clamp(94px, calc(var(--plan-unit) * 11.8), 145px);
            height: clamp(58px, calc(var(--plan-unit) * 7.5), 92px);
            background: rgba(255, 255, 255, .1);
            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: clamp(12px, calc(var(--plan-unit) * 1.3), 16px);
        }

        .kitchen .prep::before,
        .kitchen .prep::after {
            content: '';
            position: absolute;
            top: clamp(10px, calc(var(--plan-unit) * 1.25), 16px);
            width: clamp(11px, calc(var(--plan-unit) * 1.3), 16px);
            height: clamp(11px, calc(var(--plan-unit) * 1.3), 16px);
            border-radius: 999px;
            background: rgba(255, 255, 255, .86);
        }

        .kitchen .prep::before {
            left: clamp(20px, calc(var(--plan-unit) * 2.4), 30px);
        }

        .kitchen .prep::after {
            left: clamp(58px, calc(var(--plan-unit) * 7), 88px);
        }

        .kitchen .sinks {
            position: absolute;
            left: clamp(76px, calc(var(--plan-unit) * 8), 98px);
            bottom: clamp(12px, calc(var(--plan-unit) * 1.5), 18px);
            display: flex;
            gap: clamp(8px, calc(var(--plan-unit) * .9), 12px);
        }

        .sink {
            width: clamp(40px, calc(var(--plan-unit) * 5), 62px);
            height: clamp(25px, calc(var(--plan-unit) * 3.1), 38px);
            border-radius: clamp(10px, calc(var(--plan-unit) * 1.1), 14px);
            background: rgba(255, 255, 255, .08);
            border: 1px solid rgba(255, 255, 255, .12);
            position: relative;
        }

        .sink::before {
            content: '';
            position: absolute;
            left: clamp(7px, calc(var(--plan-unit) * .8), 10px);
            top: clamp(6px, calc(var(--plan-unit) * .8), 10px);
            width: clamp(11px, calc(var(--plan-unit) * 1.45), 18px);
            height: clamp(10px, calc(var(--plan-unit) * 1.3), 16px);
            border-radius: 8px;
            background: rgba(255, 255, 255, .88);
        }

        .sink::after {
            content: '';
            position: absolute;
            right: clamp(7px, calc(var(--plan-unit) * .8), 10px);
            top: clamp(9px, calc(var(--plan-unit) * 1), 14px);
            width: clamp(10px, calc(var(--plan-unit) * 1.3), 16px);
            height: clamp(4px, calc(var(--plan-unit) * .45), 6px);
            border-radius: 999px;
            background: rgba(0, 0, 0, .35);
            transform: rotate(20deg);
        }

        .table {
            display: grid;
            place-items: center;
            color: #1f2937;
            font-weight: 900;
            border: 1px solid rgba(74, 44, 23, .14);
            box-shadow: var(--table-shadow);
            cursor: pointer;
            overflow: visible;
        }

        .table.square {
            width: var(--table-square-size);
            height: var(--table-square-size);
            border-radius: var(--table-radius);
        }

        .table.rect {
            width: var(--table-rect-width);
            height: var(--table-rect-height);
            border-radius: var(--table-radius);
        }

        .table.state-libre {
            background: linear-gradient(180deg, var(--table-free-1), var(--table-free-2));
        }

        .table.state-pendiente {
            background: linear-gradient(180deg, var(--table-pending-1), var(--table-pending-2));
            color: #fff7ed;
        }

        .table.state-lista {
            background: linear-gradient(180deg, var(--table-ready-1), var(--table-ready-2));
            color: white;
        }

        .table.is-selected {
            box-shadow: inset 0 0 0 clamp(3px, calc(var(--plan-unit) * .34), 4px) var(--table-selected), var(--table-shadow);
        }

        .table-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: var(--table-content-gap);
            text-align: center;
            padding: clamp(.6rem, calc(var(--plan-unit) * .62), .78rem) clamp(.32rem, calc(var(--plan-unit) * .28), .42rem);
        }

        .table-label {
            font-size: var(--table-label-size);
            line-height: 1;
            font-weight: 900;
        }

        .table-status {
            font-size: var(--table-status-size);
            font-weight: 800;
            letter-spacing: .04em;
            text-transform: uppercase;
            background: rgba(255, 255, 255, .22);
            padding: var(--table-status-padding-y) var(--table-status-padding-x);
            border-radius: 999px;
        }

        .btn-entregar-plan {
            border: 0;
            background: rgba(255, 255, 255, .92);
            color: #166534;
            font-size: clamp(.69rem, calc(var(--plan-unit) * .62), .78rem);
            font-weight: 900;
            border-radius: 999px;
            padding: clamp(.38rem, calc(var(--plan-unit) * .34), .48rem) clamp(.7rem, calc(var(--plan-unit) * .62), .9rem);
            min-height: 36px;
            cursor: pointer;
            box-shadow: 0 8px 16px rgba(0, 0, 0, .14);
        }

        .chairs {
            position: absolute;
            inset: var(--chairs-inset);
            pointer-events: none;
            opacity: .95;
        }

        .chair {
            position: absolute;
            width: var(--chair-width);
            height: var(--chair-height);
            border-radius: clamp(6px, calc(var(--plan-unit) * .6), 8px);
            background: linear-gradient(180deg, var(--chair), #6f90c0);
            box-shadow: inset 0 0 0 2px rgba(0, 0, 0, .08);
        }

        .c-top {
            top: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .c-bottom {
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
        }

        .c-left {
            left: 0;
            top: 50%;
            transform: translateY(-50%) rotate(90deg);
        }

        .c-right {
            right: 0;
            top: 50%;
            transform: translateY(-50%) rotate(90deg);
        }

        .c-top2 {
            top: 0;
            left: 35%;
            transform: translateX(-50%);
        }

        .c-top3 {
            top: 0;
            left: 65%;
            transform: translateX(-50%);
        }

        .c-bottom2 {
            bottom: 0;
            left: 35%;
            transform: translateX(-50%);
        }

        .c-bottom3 {
            bottom: 0;
            left: 65%;
            transform: translateX(-50%);
        }

        @media (max-width: 1180px) {
            .restaurant-plan {
                min-height: clamp(440px, 68vh, 760px);
            }
        }

        @media (max-width: 1024px) {
            .restaurant-plan {
                --plan-unit: clamp(8px, 1.35cqi, 13px);
            }

            .floor-controls {
                align-items: stretch;
            }

            .floor-actions,
            .floor-edit-wrap {
                width: 100%;
            }
        }

        @media (max-width: 1024px) and (orientation: portrait) {
            .restaurant-plan {
                aspect-ratio: 4 / 5;
                min-height: clamp(560px, 72vh, 900px);
                --plan-unit: clamp(9px, 1.65cqi, 14px);
            }
        }

        @media (max-width: 1024px) and (orientation: landscape) {
            .restaurant-plan {
                aspect-ratio: 1280 / 760;
                min-height: clamp(430px, 62vh, 680px);
            }
        }

        @media (max-width: 768px) {
            .floor-controls {
                flex-direction: column;
            }

            .floor-actions .floor-btn,
            .floor-edit-wrap .floor-chip {
                flex: 1 1 auto;
                justify-content: center;
                text-align: center;
            }

            .floor-hint,
            .floor-controls-note {
                font-size: .78rem;
            }
        }

        @media (max-width: 640px) {
            .restaurant-plan {
                aspect-ratio: 4 / 5;
                min-height: clamp(460px, 68vh, 780px);
                --plan-unit: clamp(8px, 1.9cqi, 13px);
            }

            .floor-legend-bar {
                gap: .45rem;
            }
        }
    </style>

</head>

<body class="custom-beige min-h-screen">

    <!-- Navbar -->
    <?php require_once ROOT_PATH . '/views/layout/navbar.php'; ?>

    <!-- Main Content -->
    <div class="pt-20 min-h-screen">
        <div class="max-w-[1760px] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col xl:flex-row gap-6 items-start">

                <!-- Content Area -->
                <main id="content-area" class="w-full flex-1 min-w-0">

                    <!-- Mesas View -->
                    <div id="mesas-view" class="block">
                        <h1 class="text-brown text-3xl font-bold mb-6">Mesas disponibles</h1>

                        <section class="floor-shell">
                            <div class="floor-layout">
                                <div class="floor-legend-bar" aria-label="Leyenda de estados de mesa">
                                    <div class="floor-legend-item"><span class="sw libre"></span> Disponible</div>
                                    <div class="floor-legend-item"><span class="sw pendiente"></span> En cocina</div>
                                    <div class="floor-legend-item"><span class="sw lista"></span> Lista para entregar</div>
                                    <!-- <div class="floor-legend-item"><span class="sw kitchen"></span> Cocina movible</div> -->
                                </div>

                                <div class="floor-canvas">
                                    <div class="restaurant-plan">
                                        <div class="restaurant-floor <?= $isAdminLayout ? '' : 'layout-readonly' ?>" id="restaurant-floor">
                                            <div class="draggable kitchen" data-id="kitchen" style="left: 2%; top: 2%;">
                                                <?php if ($isAdminLayout): ?>
                                                    <div class="drag-handle" title="Arrastrar">⠿</div>
                                                <?php endif; ?>
                                                <div class="burners" aria-hidden="true">
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                    <span></span><span></span>
                                                </div>
                                                <div class="prep" aria-hidden="true"></div>
                                                <div class="sinks" aria-hidden="true">
                                                    <div class="sink"></div>
                                                    <div class="sink"></div>
                                                </div>
                                            </div>

                                            <div class="draggable mesa-card table square" data-id="t1" data-mesa="1" data-shape="square" style="left: 34%; top: 9%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t2" data-mesa="2" data-shape="square" style="left: 47%; top: 9%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t3" data-mesa="3" data-shape="rect" style="left: 63%; top: 8%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t4" data-mesa="4" data-shape="square" style="left: 34%; top: 26%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t5" data-mesa="5" data-shape="square" style="left: 47%; top: 26%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t6" data-mesa="6" data-shape="rect" style="left: 75%; top: 31%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t7" data-mesa="7" data-shape="square" style="left: 34%; top: 47%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t8" data-mesa="8" data-shape="square" style="left: 47%; top: 47%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t9" data-mesa="9" data-shape="rect" style="left: 19%; top: 72%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t10" data-mesa="10" data-shape="square" style="left: 34%; top: 73%;"></div>
                                            <div class="draggable mesa-card table square" data-id="t11" data-mesa="11" data-shape="square" style="left: 47%; top: 73%;"></div>
                                            <div class="draggable mesa-card table rect" data-id="t12" data-mesa="12" data-shape="rect" style="left: 63%; top: 72%;"></div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($isAdminLayout): ?>
                                    <div class="floor-controls">
                                        <div class="floor-actions">
                                            <button class="floor-btn primary" id="saveLayoutBtn" type="button">Guardar posiciones</button>
                                            <button class="floor-btn ghost" id="resetLayoutBtn" type="button">Restablecer</button>
                                        </div>

                                        <div class="floor-edit-wrap">
                                            <button class="floor-chip active" id="editLayoutChip" type="button">Modo edición: ON</button>
                                            <span class="floor-hint">Desactívalo para evitar mover el plano por accidente.</span>
                                        </div>

                                        <div class="floor-controls-note">Selecciona mesas desde el croquis y usa el asa ⠿ para mover mesas y cocina. Las posiciones ahora se guardan de forma compartida para todo el equipo.</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </section>
                    </div>

                    <!-- Menu Views -->
                    <div id="menu-view" class="hidden">
                        <h1 id="menu-title" class="text-brown text-3xl font-bold mb-8">Menú</h1>
                        <div id="productos-grid" class="grid grid-cols-1 md:grid-cols-2 2xl:grid-cols-3 gap-6">
                        </div>
                    </div>

                </main>

                <!-- Sidebar - Orden Actual -->
                <?php require_once ROOT_PATH . '/views/layout/ordenActual.php'; ?>

            </div>
        </div>
        <a href="../controller/listarProductosController.php"></a>
    </div>

    <!-- Footer -->
    <?php require_once ROOT_PATH . '/views/layout/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        const LAYOUT_API_URL = `${BASE_URL}public/api/layoutMesas.php`;
        const USER_IS_ADMIN = <?= $isAdminLayout ? 'true' : 'false' ?>;
        let mesaActual = null;
        let ordenActual = [];
        let totalOrden = 0;
        let dragState = null;
        let editLayoutMode = USER_IS_ADMIN;

        // Estado visual por mesa: libre | pendiente | lista
        let mesaEstados = JSON.parse(localStorage.getItem('mesaEstados') || '{}');

        function guardarEstadoMesas() {
            localStorage.setItem('mesaEstados', JSON.stringify(mesaEstados));
        }

        function aplicarSeleccionMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);
            mesaActual = numeroMesa;

            const mesaActualSpan = document.getElementById('mesa-actual');
            if (mesaActualSpan) {
                mesaActualSpan.textContent = `Mesa ${numeroMesa}`;
            }

            document.querySelectorAll('.mesa-card').forEach(card => {
                card.classList.toggle('is-selected', card.getAttribute('data-mesa') === numeroMesa);
            });

            const notasField = document.getElementById('notas-orden');
            if (notasField) notasField.value = '';

            actualizarBotones();
        }

        function seleccionarMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);

            // Si es la misma mesa, no hacer nada
            if (mesaActual === numeroMesa) return;

            // Si ya hay productos y quiere cambiar a otra mesa, pedir confirmación
            if (mesaActual && ordenActual.length > 0 && mesaActual !== numeroMesa) {
                Swal.fire({
                    title: '¿Cambiar de mesa?',
                    html: `
                La orden actual tiene productos seleccionados.<br><br>
                <strong>No se eliminarán</strong>, pero quedarán asignados a la nueva mesa.<br><br>
                Cambiar de <strong>Mesa ${mesaActual}</strong> a <strong>Mesa ${numeroMesa}</strong>.
            `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, cambiar mesa',
                    cancelButtonText: 'Cancelar',
                    reverseButtons: true,
                    confirmButtonColor: '#70A38F',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        aplicarSeleccionMesa(numeroMesa);
                    }
                });

                return;
            }

            aplicarSeleccionMesa(numeroMesa);
        }

        function actualizarEstadoMesa(numeroMesa) {
            numeroMesa = String(numeroMesa);

            const card = document.querySelector(`.mesa-card[data-mesa="${numeroMesa}"]`);
            if (!card) return;

            const estado = mesaEstados[numeroMesa] || "libre";
            const shape = card.dataset.shape || 'square';
            const isRect = shape === 'rect';

            card.className = `draggable mesa-card table ${shape} state-${estado}`;
            card.dataset.mesa = numeroMesa;
            card.classList.toggle('is-selected', mesaActual === numeroMesa);

            const chairs = isRect ? `
                <div class="chairs" aria-hidden="true">
                    <span class="chair c-top2"></span><span class="chair c-top3"></span>
                    <span class="chair c-bottom2"></span><span class="chair c-bottom3"></span>
                    <span class="chair c-left"></span><span class="chair c-right"></span>
                </div>
            ` : `
                <div class="chairs" aria-hidden="true">
                    <span class="chair c-top"></span><span class="chair c-bottom"></span>
                    <span class="chair c-left"></span><span class="chair c-right"></span>
                </div>
            `;

            let statusLabel = 'Disponible';
            let statusHtml = '<span class="table-status">Disponible</span>';

            if (estado === 'pendiente') {
                statusLabel = 'En cocina';
                statusHtml = '<span class="table-status">En cocina</span>';
            } else if (estado === 'lista') {
                statusLabel = 'Lista';
                statusHtml = '<button class="btn-entregar btn-entregar-plan" type="button">Entregar orden</button>';
            }

            card.setAttribute('aria-label', `Mesa ${numeroMesa} - ${statusLabel}`);
            card.innerHTML = `
                ${USER_IS_ADMIN ? '<div class="drag-handle" title="Arrastrar">⠿</div>' : ''}
                <div class="table-content">
                    <div class="table-label">${numeroMesa}</div>
                    ${statusHtml}
                </div>
                ${chairs}
            `;
        }

        function actualizarTodasLasMesas() {
            document.querySelectorAll('.mesa-card').forEach(card => {
                const mesa = card.getAttribute('data-mesa');
                if (mesa) actualizarEstadoMesa(mesa);
            });
        }

        function mostrarToastMesaLista(numeroMesa) {
            const toastEl = document.getElementById('toastMesaLista');
            const toastBody = document.getElementById('toastMesaListaBody');

            if (!toastEl || !toastBody) return;

            toastBody.textContent = `La orden de la Mesa ${numeroMesa} está lista para entregar.`;

            const toast = new bootstrap.Toast(toastEl, {
                delay: 4000
            });

            toast.show();
        }

        function reproducirSonidoNotificacion() {
            const audio = document.getElementById('audio-notificacion');
            if (!audio) return;

            audio.currentTime = 0;
            audio.play().catch(error => {
                console.warn("No se pudo reproducir el sonido automáticamente:", error);
            });
        }

        async function sincronizarEstadosMesas(mostrarNotificacion = false) {
            try {
                const resp = await fetch(`${BASE_URL}public/api/estadoMesas.php?_=${Date.now()}`);

                if (!resp.ok) {
                    const errorText = await resp.text();
                    console.error("Respuesta 500 de estadoMesas.php:", errorText);
                    throw new Error(`HTTP ${resp.status}`);
                }

                const json = await resp.json();

                if (json.status !== "OK") throw new Error(json.message || "Error obteniendo estados");

                const nuevosEstados = json.data || {};
                const estadosAnteriores = {
                    ...mesaEstados
                };

                // Normalizar: si una mesa no viene en la respuesta, queda libre
                const estadosNormalizados = {};
                document.querySelectorAll('.mesa-card').forEach(card => {
                    const mesa = card.getAttribute('data-mesa');
                    if (!mesa) return;

                    const estado = nuevosEstados[mesa];
                    if (estado === "pendiente" || estado === "lista") {
                        estadosNormalizados[mesa] = estado;
                    } else {
                        estadosNormalizados[mesa] = "libre";
                    }
                });

                mesaEstados = estadosNormalizados;
                guardarEstadoMesas();
                actualizarTodasLasMesas();

                if (mostrarNotificacion) {
                    Object.keys(mesaEstados).forEach(mesa => {
                        if (mesaEstados[mesa] === "lista" && estadosAnteriores[mesa] !== "lista") {
                            mostrarToastMesaLista(mesa);
                            reproducirSonidoNotificacion();
                        }
                    });
                }

            } catch (error) {
                console.error("Error sincronizando estados de mesas:", error);
            }
        }

        function clampLayout(n, min, max) {
            return Math.max(min, Math.min(max, n));
        }

        function getRestaurantFloorRect() {
            const floor = document.getElementById('restaurant-floor');
            return floor ? floor.getBoundingClientRect() : null;
        }

        function obtenerItemsLayoutActual() {
            const floorRect = getRestaurantFloorRect();
            if (!floorRect) return {};

            const items = {};
            document.querySelectorAll('#restaurant-floor .draggable').forEach(el => {
                const id = el.dataset.id;
                const rect = el.getBoundingClientRect();
                const leftPx = rect.left - floorRect.left;
                const topPx = rect.top - floorRect.top;
                items[id] = {
                    left: clampLayout((leftPx / floorRect.width) * 100, -2, 98),
                    top: clampLayout((topPx / floorRect.height) * 100, -2, 98)
                };
            });

            return items;
        }

        function aplicarPosicionesLayout(items) {
            if (!items || typeof items !== 'object') return;

            Object.entries(items).forEach(([id, pos]) => {
                const el = document.querySelector(`#restaurant-floor .draggable[data-id="${id}"]`);
                if (!el || typeof pos !== 'object') return;

                if (typeof pos.left !== 'undefined') {
                    el.style.left = `${clampLayout(Number(pos.left), -2, 98)}%`;
                }
                if (typeof pos.top !== 'undefined') {
                    el.style.top = `${clampLayout(Number(pos.top), -2, 98)}%`;
                }
            });
        }

        async function guardarPosicionesLayout() {
            if (!USER_IS_ADMIN) return;

            const items = obtenerItemsLayoutActual();

            try {
                const resp = await fetch(LAYOUT_API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        items
                    })
                });

                const json = await resp.json();
                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                Swal.fire({
                    toast: true,
                    position: 'bottom',
                    icon: 'success',
                    title: 'Posiciones guardadas',
                    showConfirmButton: false,
                    timer: 1800
                });
            } catch (error) {
                console.error('No se pudo guardar el layout compartido:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo guardar',
                    text: error.message || 'Error guardando el layout compartido.'
                });
            }
        }

        async function cargarPosicionesLayout() {
            try {
                const resp = await fetch(`${LAYOUT_API_URL}?_=${Date.now()}`);
                const json = await resp.json();

                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                aplicarPosicionesLayout(json.data || {});
            } catch (error) {
                console.warn('No se pudo cargar el layout compartido:', error);
            }
        }

        async function restablecerPosicionesLayout() {
            if (!USER_IS_ADMIN) return;

            try {
                const resp = await fetch(LAYOUT_API_URL, {
                    method: 'DELETE'
                });
                const json = await resp.json();

                if (!resp.ok || json.status !== 'OK') {
                    throw new Error(json.message || `HTTP ${resp.status}`);
                }

                window.location.reload();
            } catch (error) {
                console.error('No se pudo restablecer el layout compartido:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo restablecer',
                    text: error.message || 'Error restableciendo el layout compartido.'
                });
            }
        }

        function actualizarChipEdicion() {
            const chip = document.getElementById('editLayoutChip');
            if (!chip) return;

            chip.classList.toggle('active', editLayoutMode);
            chip.textContent = `Modo edición: ${editLayoutMode ? 'ON' : 'OFF'}`;
        }

        function onLayoutPointerDown(e) {
            if (!USER_IS_ADMIN || !editLayoutMode) return;

            const handle = e.target.closest('.drag-handle');
            if (!handle) return;

            const el = handle.closest('.draggable');
            const floor = document.getElementById('restaurant-floor');
            if (!el || !floor || !floor.contains(el)) return;

            e.preventDefault();

            const rect = el.getBoundingClientRect();
            dragState = {
                el,
                pointerId: e.pointerId,
                offsetX: e.clientX - rect.left,
                offsetY: e.clientY - rect.top
            };

            el.classList.add('dragging');
            el.setPointerCapture?.(e.pointerId);
        }

        function onLayoutPointerMove(e) {
            if (!dragState || e.pointerId !== dragState.pointerId) return;

            const floorRect = getRestaurantFloorRect();
            if (!floorRect) return;

            const x = e.clientX - floorRect.left - dragState.offsetX;
            const y = e.clientY - floorRect.top - dragState.offsetY;

            dragState.el.style.left = `${clampLayout((x / floorRect.width) * 100, -2, 98)}%`;
            dragState.el.style.top = `${clampLayout((y / floorRect.height) * 100, -2, 98)}%`;
        }

        function onLayoutPointerUp(e) {
            if (!dragState || e.pointerId !== dragState.pointerId) return;
            dragState.el.classList.remove('dragging');
            dragState = null;
        }

        async function mostrarProductos(slug, nombreCategoria = "Menú") {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');
            const productosGrid = document.getElementById('productos-grid');
            const menuTitle = document.getElementById('menu-title');

            if (!menuView || !mesasView || !productosGrid || !menuTitle) return;

            mesasView.classList.add('hidden');
            menuView.classList.remove('hidden');
            menuTitle.textContent = nombreCategoria;

            productosGrid.innerHTML = `
                <div class="col-span-full text-center text-gray-500 py-10">
                    <i class="fas fa-circle-notch fa-spin text-2xl mb-3"></i>
                    <p>Cargando productos...</p>
                </div>
            `;
            try {

                // const resp = await fetch(`${BASE_URL}controller/listarProductosController.php?categoria=${encodeURIComponent(slug)}`);
                const resp = await fetch(`${BASE_URL}public/api/listarProductos.php?categoria=${encodeURIComponent(slug)}`);
                if (!resp.ok) throw new Error("HTTP " + resp.status);

                const json = await resp.json();
                if (json.status !== "OK") throw new Error(json.message || "Error API");

                const items = json.data || [];

                if (!items.length) {
                    renderPlaceholderSinProductos(nombreCategoria);
                    return;
                }

                productosGrid.innerHTML = items.map(p => {
                    const nombre = p.nombre ?? "";
                    const precio = Number(p.precio ?? 0);
                    const icono = p.icono ?? "fa-box";
                    const nombreSafe = nombre.replace(/'/g, "\\'");

                    return `
                        <div class="bg-white rounded-xl p-6 shadow-sm hover:shadow-md transition-all duration-200">
                            <div class="text-center">
                                <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="fas ${icono} text-white text-xl"></i>
                                </div>
                                <h3 class="text-brown font-semibold text-lg mb-2">${nombre}</h3>
                                <p class="text-mint font-bold text-xl mb-4">₡${precio.toLocaleString()}</p>
                                <button onclick="agregarProducto('${nombreSafe}', ${precio})"
                                        class="w-full custom-mint text-white py-2 rounded-lg hover-mint-bg transition-all duration-200 flex items-center justify-center">
                                    <i class="fas fa-plus mr-2"></i>
                                    Agregar
                                </button>
                            </div>
                        </div>
                    `;
                }).join("");
            } catch (e) {
                console.error(e);
                productosGrid.innerHTML = `
                    <div class="col-span-full bg-white rounded-xl p-8 shadow-sm border border-red-100">
                        <div class="text-center">
                            <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-triangle-exclamation text-white text-xl"></i>
                            </div>
                            <h3 class="text-brown font-semibold text-xl mb-2">No se pudieron cargar los productos</h3>
                            <p class="text-gray-500">Intenta nuevamente.</p>
                        </div>
                    </div>
                `;
            }
        }

        function renderPlaceholderSinProductos(nombreCategoria = "esta categoría") {
            const productosGrid = document.getElementById('productos-grid');
            if (!productosGrid) return;

            productosGrid.innerHTML = `
                <div class="col-span-full bg-white rounded-xl p-10 shadow-sm border border-gray-100">
                    <div class="text-center">
                        <div class="w-16 h-16 custom-mint rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-box-open text-white text-2xl"></i>
                        </div>
                        <h3 class="text-brown font-semibold text-2xl mb-2">No hay productos registrados</h3>
                        <p class="text-gray-500">
                            Aún no existen productos para <strong>${nombreCategoria}</strong>.
                        </p>
                    </div>
                </div>
            `;
        }

        function mostrarMesas() {
            const menuView = document.getElementById('menu-view');
            const mesasView = document.getElementById('mesas-view');

            if (menuView) menuView.classList.add('hidden');
            if (mesasView) mesasView.classList.remove('hidden');
        }

        function agregarProducto(nombre, precio) {
            if (!mesaActual) {
                Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            const itemExistente = ordenActual.find(item => item.nombre === nombre);
            if (itemExistente) {
                itemExistente.cantidad++;
            } else {
                ordenActual.push({
                    nombre,
                    precio,
                    cantidad: 1
                });
            }

            actualizarOrden();
        }

        function actualizarOrden() {
            const ordenItems = document.getElementById('orden-items');
            const totalElement = document.getElementById('total-orden');

            if (!ordenItems || !totalElement) return;

            if (ordenActual.length === 0) {
                ordenItems.innerHTML = `
                    <div class="text-gray-500 text-center py-8">
                        <i class="fas fa-coffee text-4xl mb-2"></i>
                        <p>Selecciona una mesa y agrega productos</p>
                    </div>
                `;
            } else {
                ordenItems.innerHTML = ordenActual.map((item, index) => `
                    <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg">
                        <div class="flex-1">
                            <p class="text-brown font-medium">${item.nombre}</p>
                            <p class="text-mint text-sm">₡${item.precio.toLocaleString()} c/u</p>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="cambiarCantidad(${index}, -1)" class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center text-sm">-</button>
                            <span class="text-brown font-medium w-8 text-center">${item.cantidad}</span>
                            <button onclick="cambiarCantidad(${index}, 1)" class="w-6 h-6 custom-mint text-white rounded-full flex items-center justify-center text-sm">+</button>
                        </div>
                    </div>
                `).join('');
            }

            totalOrden = ordenActual.reduce((total, item) => total + (item.precio * item.cantidad), 0);
            totalElement.textContent = `₡${totalOrden.toLocaleString()}`;
            actualizarBotones();
        }

        async function entregarOrden(numeroMesa) {
            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Marcar la orden de la Mesa ${numeroMesa} como ENTREGADA?`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Sí, entregar",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            try {
                const respuesta = await fetch("<?= BASE_URL ?>public/api/entregarOrden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `mesa=${encodeURIComponent(numeroMesa)}`
                });

                if (!respuesta.ok) {
                    const errorText = await respuesta.text();
                    console.error("Respuesta entregarOrden:", errorText);
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();
                console.log("Resultado entregarOrden:", result);

                if (result.status === "OK") {
                    mesaEstados[numeroMesa] = "libre";
                    guardarEstadoMesas();
                    actualizarEstadoMesa(numeroMesa);

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `<strong>Mesa ${numeroMesa}</strong><br>Orden marcada como ENTREGADA`,
                        showConfirmButton: false,
                        timer: 2000
                    });

                    sincronizarEstadosMesas(false);

                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo entregar la orden"
                    });
                }

            } catch (error) {
                console.error("Error en entregarOrden:", error);
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: error.message || "Error al entregar la orden"
                });
            }
        }

        function cambiarCantidad(index, cambio) {
            ordenActual[index].cantidad += cambio;
            if (ordenActual[index].cantidad <= 0) {
                ordenActual.splice(index, 1);
            }
            actualizarOrden();
        }

        function actualizarBotones() {
            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');

            const tieneOrden = ordenActual.length > 0 && mesaActual;
            if (eliminarBtn) eliminarBtn.disabled = !tieneOrden;
            if (enviarBtn) enviarBtn.disabled = !tieneOrden;
        }

        function eliminarOrden() {
            if (ordenActual.length === 0) return;

            Swal.fire({
                title: "¿Eliminar orden?",
                text: "¿Estás seguro de eliminar toda la orden?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#8B0000",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then((result) => {
                if (result.isConfirmed) {
                    ordenActual = [];
                    const notasField = document.getElementById('notas-orden');
                    if (notasField) notasField.value = '';
                    actualizarOrden();
                    actualizarBotones();

                    Swal.fire({
                        icon: "success",
                        title: "Orden eliminada",
                        text: "La orden fue eliminada correctamente",
                        timer: 2500,
                        showConfirmButton: false
                    });
                }
            });
        }

        async function enviarCocina() {
            if (!mesaActual) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Seleccione una mesa primero"
                });
                return;
            }

            if (ordenActual.length === 0) {
                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: "Agrega productos antes de enviar la orden"
                });
                return;
            }

            const {
                isConfirmed
            } = await Swal.fire({
                title: `¿Enviar orden de la Mesa ${mesaActual} a cocina?`,
                text: "Esta acción enviará la orden a cocina.",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Enviar a cocina",
                cancelButtonText: "Cancelar",
                reverseButtons: true
            });

            if (!isConfirmed) return;

            const listaProductos = ordenActual
                .map(item => `${item.nombre} x${item.cantidad}`)
                .join("\n");


            const notasField = document.getElementById('notas-orden');
            const notas = notasField ? notasField.value.trim() : '';

            const data = {
                mesa: mesaActual,
                items: listaProductos,
                notas: notas
            };

            try {
                Swal.fire({
                    title: "Enviando orden...",
                    html: `Mesa <strong>${mesaActual}</strong>`,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });

                const respuesta = await fetch("<?= BASE_URL ?>public/api/guardarOrden.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(data)
                });

                if (!respuesta.ok) {
                    throw new Error(`HTTP ${respuesta.status}`);
                }

                const result = await respuesta.json();

                if (result.status === "OK") {
                    console.log("Orden guardada correctamente en backend");
                    console.log("mesaActual antes de actualizar estado:", mesaActual);
                    console.log("mesaEstados antes:", mesaEstados);

                    mesaEstados[mesaActual] = "pendiente";
                    console.log("mesaEstados después:", mesaEstados);

                    guardarEstadoMesas();
                    console.log("Estado guardado en localStorage");

                    actualizarEstadoMesa(mesaActual);
                    console.log("UI de mesa actualizada");

                    await Swal.fire({
                        position: "center",
                        icon: "success",
                        html: `Orden enviada a cocina para <strong>Mesa ${mesaActual}</strong> ✔`,
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: false
                    });

                    ordenActual = [];
                    mesaActual = null;
                    document.querySelectorAll('.mesa-card').forEach(card => card.classList.remove('is-selected'));

                    const mesaActualSpan = document.getElementById("mesa-actual");
                    if (mesaActualSpan) mesaActualSpan.textContent = "No seleccionada";

                    const notasField = document.getElementById('notas-orden');
                    if (notasField) notasField.value = '';

                    actualizarOrden();
                    actualizarBotones();

                    window.location.href = "<?= BASE_URL ?>index.php";

                } else {
                    await Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: result.message || "No se pudo enviar la orden a cocina"
                    });
                }

            } catch (error) {
                console.error("Error real en enviarCocina:", error);

                await Swal.fire({
                    icon: "error",
                    title: "Oops...",
                    text: error.message || "Error al enviar la orden"
                });
            }
        }

        function actualizarNavbar(activeBtn) {
            const navbar = document.getElementById('navbar');
            if (!navbar) return;

            navbar.querySelectorAll('button').forEach(btn => {
                btn.classList.remove('border-b-2', 'border-mint');
            });
            activeBtn.classList.add('border-b-2', 'border-mint');
        }

        document.addEventListener('DOMContentLoaded', async () => {
            document.querySelectorAll('#navbar button[data-slug]').forEach(btn => {
                btn.addEventListener('click', async () => {
                    const slug = btn.getAttribute('data-slug');
                    const nombre = btn.textContent.trim();

                    if (slug === "mesas") {
                        mostrarMesas();
                    } else {
                        await mostrarProductos(slug, nombre);
                    }

                    actualizarNavbar(btn);
                });
            });

            const mesasBtn = document.querySelector('#navbar button[data-slug="mesas"]');
            if (mesasBtn) actualizarNavbar(mesasBtn);

            const eliminarBtn = document.getElementById('eliminar-orden');
            const enviarBtn = document.getElementById('enviar-cocina');
            const saveLayoutBtn = document.getElementById('saveLayoutBtn');
            const resetLayoutBtn = document.getElementById('resetLayoutBtn');
            const editLayoutChip = document.getElementById('editLayoutChip');

            if (eliminarBtn) eliminarBtn.addEventListener('click', eliminarOrden);
            if (enviarBtn) enviarBtn.addEventListener('click', enviarCocina);
            if (saveLayoutBtn) saveLayoutBtn.addEventListener('click', guardarPosicionesLayout);
            if (resetLayoutBtn) resetLayoutBtn.addEventListener('click', restablecerPosicionesLayout);
            if (editLayoutChip) {
                editLayoutChip.addEventListener('click', () => {
                    editLayoutMode = !editLayoutMode;
                    actualizarChipEdicion();
                });
            }

            actualizarChipEdicion();
            await cargarPosicionesLayout();
            actualizarTodasLasMesas();
            sincronizarEstadosMesas(false);

            document.addEventListener('pointerdown', onLayoutPointerDown);
            document.addEventListener('pointermove', onLayoutPointerMove);
            document.addEventListener('pointerup', onLayoutPointerUp);
            document.addEventListener('pointercancel', onLayoutPointerUp);

            setInterval(() => {
                sincronizarEstadosMesas(true);
            }, 5000);

            document.addEventListener('click', (e) => {
                const botonEntregar = e.target.closest('.btn-entregar');
                if (botonEntregar) return;
                if (e.target.closest('.drag-handle')) return;

                const card = e.target.closest('.mesa-card');
                if (!card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                const estadoMesa = mesaEstados[mesa] || "libre";

                if ((estadoMesa === "pendiente" || estadoMesa === "lista") && mesaActual !== mesa) {
                    Swal.fire({
                        icon: "info",
                        title: "Mesa ocupada",
                        text: estadoMesa === "lista" ?
                            `La Mesa ${mesa} tiene una orden lista para entregar.` : `La Mesa ${mesa} tiene una orden en cocina.`
                    });
                    return;
                }

                seleccionarMesa(mesa);
            });
            document.addEventListener('click', (e) => {
                const boton = e.target.closest('.btn-entregar');
                if (!boton) return;

                e.preventDefault();
                e.stopPropagation();

                const card = e.target.closest('.mesa-card');
                if (!card) return;

                const mesa = card.getAttribute('data-mesa');
                if (!mesa) return;

                entregarOrden(mesa);
            });
        });
    </script>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastMesaLista" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body" id="toastMesaListaBody">
                    La orden está lista para entregar.
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Cerrar"></button>
            </div>
        </div>
    </div>
    <audio id="audio-notificacion" preload="auto">
        <source src="<?= BASE_URL ?>public/sounds/notificacion.mp3" type="audio/mpeg">
    </audio>

</body>

</html>