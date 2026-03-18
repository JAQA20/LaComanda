<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../model/Conexion.php";


function validatePasswordStrength($password)
{
    $errors = [];
    if (strlen($password) < 8) $errors[] = "mínimo 8 caracteres";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "una letra minúscula";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "una letra mayúscula";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "un número";
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/', $password)) $errors[] = "un carácter especial";
    return $errors;
}

$token = trim($_GET['token'] ?? $_POST['token'] ?? '');
$tokenHash = $token !== '' ? hash('sha256', $token) : '';
$error = null;
$success = false;
$tokenValid = false;
$usuarioId = null;

if ($tokenHash !== '') {
    $stmt = $conexion->prepare("SELECT usuario_id FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $tokenHash);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res ? $res->fetch_assoc() : null;
        if ($row) {
            $tokenValid = true;
            $usuarioId = (int)$row['usuario_id'];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = (string)($_POST['new_password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');

    if (!$tokenValid || $usuarioId === null) {
        $error = "El enlace de recuperación es inválido o expiró.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $passwordErrors = validatePasswordStrength($newPassword);
        if (!empty($passwordErrors)) {
            $error = "La contraseña debe contener: " . implode(', ', $passwordErrors) . ".";
        } else {
            try {
                // Actualizar contraseña (usar hash password)
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);

                $stmtUpdate = $conexion->prepare("UPDATE usuarios SET password = ? WHERE id = ?");
                if (!$stmtUpdate) {
                    $error = "Error en la preparación de la actualización";
                } else {
                    $stmtUpdate->bind_param("si", $hashedPassword, $usuarioId);

                    if ($stmtUpdate->execute()) {
                        // Marcar token como usado
                        $stmtMark = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE token = ?");
                        if ($stmtMark) {
                            $stmtMark->bind_param("s", $tokenHash);
                            $stmtMark->execute();
                        }
                        $success = true;
                    } else {
                        $error = "Error al actualizar la contraseña en la base de datos";
                    }
                }
            } catch (Exception $e) {
                $error = "Error al actualizar la contraseña: " . $e->getMessage();
            }
        }
    }
}

// Variables para la vista
$showForm = $tokenValid && !$success;


// header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
// header("Pragma: no-cache");
// header("Expires: 0");

// require_once __DIR__ . "/../model/Conexion.php";


// function validatePasswordStrength($password)
// {
//     $errors = [];
//     if (strlen($password) < 8) $errors[] = "mínimo 8 caracteres";
//     if (!preg_match('/[a-z]/', $password)) $errors[] = "una letra minúscula";
//     if (!preg_match('/[A-Z]/', $password)) $errors[] = "una letra mayúscula";
//     if (!preg_match('/[0-9]/', $password)) $errors[] = "un número";
//     if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"<>,.?\/]/', $password)) $errors[] = "un carácter especial";
//     return $errors;
// }

// $token = trim($_GET['token'] ?? $_POST['token'] ?? '');
// $tokenHash = $token !== '' ? hash('sha256', $token) : '';
// $error = null;
// $success = false;
// $tokenValid = false;
// $usuarioId = null;

// if ($tokenHash !== '') {
//     $stmt = $conexion->prepare("SELECT usuario_id FROM password_resets WHERE token = ? AND usado = 0 AND expira_en > NOW() LIMIT 1");
//     if ($stmt) {
//         $stmt->bind_param("s", $tokenHash);
//         $stmt->execute();
//         $res = $stmt->get_result();
//         $row = $res ? $res->fetch_assoc() : null;
//         if ($row) {
//             $tokenValid = true;
//             $usuarioId = (int)$row['usuario_id'];
//         }
//     }
// }

// if ($_SERVER['REQUEST_METHOD'] === 'POST') {
//     $newPassword = (string)($_POST['new_password'] ?? '');
//     $confirmPassword = (string)($_POST['confirm_password'] ?? '');

//     if (!$tokenValid || $usuarioId === null) {
//         $error = "El enlace de recuperación es inválido o expiró.";
//     } elseif ($newPassword !== $confirmPassword) {
//         $error = "Las contraseñas no coinciden.";
//     } else {
//         $passwordErrors = validatePasswordStrength($newPassword);
//         if (!empty($passwordErrors)) {
//             $error = "La contraseña debe contener: " . implode(', ', $passwordErrors) . ".";
//         } else {
//             try {
//                 // Validar que el token existe y no ha expirado
//                 $stmt = $conexion->prepare("
//                     SELECT usuario_id FROM password_resets 
//                     WHERE token = :token AND expira_en > NOW() AND usado = 0 LIMIT 1
//                 ");
//                 $stmt->execute([':token' => $token]);
//                 $reset = $stmt->fetch(PDO::FETCH_ASSOC);

//                 if (!$reset) {
//                     $error = "El enlace de recuperación ha expirado o no es válido";
//                 } else {
//                     // Verificar que el usuario existe y está activo
//                     $user_stmt = $conexion->prepare("
//                         SELECT id, email, activo FROM usuarios 
//                         WHERE id = :usuario_id AND activo = 1 LIMIT 1
//                     ");
//                     $user_stmt->execute([':usuario_id' => $reset['usuario_id']]);
//                     $user = $user_stmt->fetch(PDO::FETCH_ASSOC);

//                     if (!$user) {
//                         $error = "El usuario no existe o ha sido desactivado";
//                     } elseif (strtolower(trim($user['email'])) !== strtolower(trim($email))) {
//                         // Validar que el email coincida con el usuario
//                         $error = "El email no coincide con el usuario registrado";
//                     } else {
//                         // Actualizar contraseña y marcar token como usado
//                         $update_success = false;
//                         $token_marked = false;

//                         try {
//                             // Actualizar contraseña (usar hash password)
//                             $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);

//                             $upd = $conexion->prepare("
//                                 UPDATE usuarios SET password = :password WHERE id = :usuario_id
//                             ");
//                             $upd_result = $upd->execute([
//                                 ':password' => $hashed_password,
//                                 ':usuario_id' => $reset['usuario_id']
//                             ]);

//                             if ($upd_result) {
//                                 $update_success = true;
//                             } else {
//                                 $error = "Error al actualizar contraseña en BD";
//                             }
//                         } catch (Exception $upd_err) {
//                             $error = "Error UPDATE usuarios: " . $upd_err->getMessage();
//                         }

//                         // Marcar token como usado (solo si la contraseña se actualizó)
//                         if ($update_success) {
//                             try {
//                                 $del = $conexion->prepare("UPDATE password_resets SET usado = 1 WHERE token = :token");
//                                 $del_result = $del->execute([':token' => $token]);

//                                 if ($del_result) {
//                                     $token_marked = true;
//                                 }
//                             } catch (Exception $del_err) {
//                                 // No es crítico si el token no se marca
//                                 error_log("Error marking token: " . $del_err->getMessage());
//                             }
//                         }

//                         if ($update_success) {
//                             $success = true;
//                         }
//                     }
//                 }
//             } catch (Exception $e) {
//                 $error = "Error al actualizar la contraseña: " . $e->getMessage();
//             }
//         }
//     }
// }

// // Validar token GET (vista inicial)
// $token_valid = false;
// if ($token && $email && !$success) {
//     try {
//         $stmt = $conexion->prepare("
//             SELECT usuario_id FROM password_resets 
//             WHERE token = :token AND expira_en > NOW() AND usado = 0 LIMIT 1
//         ");
//         $stmt->execute([':token' => $token]);
//         $reset = $stmt->fetch(PDO::FETCH_ASSOC);
//         $token_valid = ($reset !== false);
//     } catch (Exception $e) {
//         $token_valid = false;
//     }
// }
