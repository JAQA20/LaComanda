## CREDENCIALES DE ACCESO - LA COMANDA

### ✅ Actualización completada
Se ha actualizado la base de datos con contraseñas hasheadas y el módulo de recuperación de contraseña.

---

## 📋 USUARIOS DE PRUEBA

| Email | Contraseña | Rol | Descripción |
|-------|-----------|-----|-------------|
| admin@lacomanda.com | admin123 | Admin | Acceso total al sistema |
| mesero@lacomanda.com | mesero123 | Mesero | Gestión de mesas y órdenes |
| cocina@lacomanda.com | cocina123 | Cocina | Preparación de órdenes |
| barista@lacomanda.com | barista123 | Barista | Preparación de bebidas |

---

## 🔐 CONFIGURACIÓN DE LA BASE DE DATOS

### Pasos para actualizar la BD:

1. **Importar el nuevo script SQL:**
   - Abre phpMyAdmin en `http://localhost/phpmyadmin`
   - Ve a tu base de datos `la_comanda`
   - Selecciona "Importar"
   - Carga el archivo: **`db/la_comanda_updated.sql`** ⬅️ USE ESTE ARCHIVO

   O si prefieres restaurar desde cero:
   - Elimina la base de datos `la_comanda` (si existe)
   - Ejecuta el nuevo script SQL

### Base de datos: `la_comanda`
### Usuario MySQL: `root`
### Contraseña MySQL: (vacía/sin contraseña)
### Host: `127.0.0.1`

---

## 🚀 NUEVAS CARACTERÍSTICAS IMPLEMENTADAS

✅ **Tabla `password_resets`** - Almacena tokens para recuperación de contraseña
✅ **Módulo de Olvido de Contraseña** - Formulario funcional
✅ **Módulo de Recuperación de Contraseña** - Cambio de contraseña con validación
✅ **Contraseñas Hasheadas** - Usa `password_hash()` con BCRYPT
✅ **Validación de Fortaleza** - Requiere mínimo 8 caracteres, mayúsculas, minúsculas, números y símbolos
✅ **Compilidad mysqli** - Eliminado PDO, solo se usa mysqli

---

## 🔗 URLS IMPORTANTES

### Recuperar Contraseña
- **Formulario inicial:** `http://localhost/LaComanda-main/views/forgotPassword.php`
- **Reset de contraseña:** `http://localhost/LaComanda-main/views/resetPassword.php?token=XXX&email=XXX`

### Login
- **Login:** `http://localhost/LaComanda-main/views/login.php`

---

## 📝 ARCHIVOS MODIFICADOS

### Backend
- ✅ `controller/forgot_passwordController.php` - Convertido a mysqli
- ✅ `views/resetPassword.php` - Convertido a mysqli
- ✅ `model/Conexion.php` - Contraseña MySQL vacía

### Base de Datos
- ✅ `db/la_comanda_updated.sql` - **Script SQL actualizado** (usar este)
- ✅ Tabla `password_resets` agregada
- ✅ Usuarios de prueba con contraseñas hasheadas

---

## ⚠️ REQUISITOS DE CONTRASEÑA

Para cambiar o crear contraseñas, debe contener:
- ✅ Mínimo 8 caracteres
- ✅ Al menos una letra MAYÚSCULA (A-Z)
- ✅ Al menos una letra minúscula (a-z)
- ✅ Al menos un número (0-9)
- ✅ Al menos un carácter especial (!@#$%^&*)

**Ejemplos válidos:**
- `Admin123!`
- `NewPass@2024`
- `Secure#Pass99`

---

## 🧪 TESTING

### Para probar el login:
1. Accede a `http://localhost/LaComanda-main/views/login.php`
2. Usa cualquiera de las credenciales arriba
3. Serás dirigido según tu rol

### Para probar recuperación de contraseña:
1. Accede a `http://localhost/LaComanda-main/views/forgotPassword.php`
2. Ingresa un email (ej: admin@lacomanda.com)
3. Se genera un enlace de recuperación
4. Haz clic en el enlace generado
5. Ingresa una nueva contraseña que cumpla los requisitos
6. ¡Listo! Usa la nueva contraseña en el login

---

## 🐛 NOTAS IMPORTANTES

⚠️ **IMPORTANTE:** Importa el arquivo **`la_comanda_updated.sql`** (no el archivo antiguo)

Si hay problemas con al conectarse:
1. Verifica que MySQL está corriendo ✅
2. Verifica que la contraseña MySQL es vacía ✅
3. Verifica que la BD `la_comanda` existe ✅
4. Recarga la página (Ctrl+F5) ✅

---

## 📧 SISTEMA DE EMAIL

Por defecto, los emails de recuperación se envían usando `mail()` de PHP.

Si tienes SMTP configurado, puedes personalizar en:
- `controller/forgot_passwordController.php` - línea ~75

Para desarrollo, siempre recibirás el link de recuperación en la pantalla.

---

## 🎯 PRÓXIMAS MEJORAS (OPCIONALES)

- [ ] Verificación de email en el registro
- [ ] Autenticación de dos factores
- [ ] Logs de acceso
- [ ] Historial de cambios de contraseña
- [ ] Sesiones con timeout

---

**¡Sistema listo para usar!** 🎉
