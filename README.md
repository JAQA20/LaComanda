La Comanda 🍽️

La Comanda es un sistema web para la gestión de órdenes en cafeterías y restaurantes.
Permite administrar mesas, órdenes, productos y usuarios con distintos roles como admin, mesero, cocina y barista.

El proyecto está desarrollado con:

PHP
MySQL
Apache
Docker
Bootstrap / JS

Docker permite ejecutar el proyecto sin instalar manualmente PHP, Apache o MySQL.

Requisitos:

Antes de ejecutar el proyecto asegúrate de tener instalado:

1️⃣ Docker Desktop

Descargar desde:

https://www.docker.com/products/docker-desktop/

Disponible para:

Windows

Mac

Verifica que esté instalado y cual version se instalo ejecutando:

**docker --version**

y
**docker compose version**

2️⃣ Git

Descargar desde:

https://git-scm.com/downloads

Verificar version de instalación:

git --version
Instalación del proyecto:

1️⃣ Clonar el repositorio
git clone URL_DEL_REPOSITORIO
cd LaComanda

2️⃣ Crear archivo de variables de entorno

En la raíz del proyecto copia el archivo .env.example y renómbralo a ".env" .

En Windows puedes copiarlo manualmente.

3️⃣ Variables de entorno

El archivo .env debe verse similar a esto:

APP_ENV=development

APP_URL=http://localhost:8080

BASE_URL=/

DB_HOST=db

DB_PORT=3306

DB_NAME=la_comanda

DB_USER=lacomanda_user

DB_PASSWORD=ClaveSegura123

MYSQL_ROOT_PASSWORD=rootpassword

4️⃣ Levantar los contenedores

Desde la carpeta del proyecto ejecutar:

**docker compose up --build**

Docker descargará e iniciará:

Apache + PHP

MySQL

Base de datos inicial

La primera vez puede tardar unos minutos.

5️⃣ Abrir la aplicación

Ir al navegador:

http://localhost:8080
Usuarios de prueba
Rol Email Password
Admin admin@proyecto.com
Admin123!
Mesero mesero@proyecto.com
Mesero123!
Cocina cocina@proyecto.com
Cocina123!
Barista barista@lacomanda.com
Barista123!

Comandos útiles:
Iniciar contenedores
**docker compose up**
Reconstruir contenedores
**docker compose up --build**
Detener contenedores
**docker compose down**
Reiniciar base de datos
**docker compose down -v**
**docker compose up --build**
Inicalizar MySQL en Docker:
**docker exec -it la-comanda-db mysql -u root -p**
Esto inicializa MySQL dentro del contenedor y permite hacer consultas

Tecnologías utilizadas

PHP 8

MySQL 8

Apache

Docker

Bootstrap

FontAwesome

Beneficios de usar Docker

Con Docker el proyecto:

Funciona igual en Mac, Windows y Linux

Evita problemas de configuración

No requiere instalar XAMPP

Permite replicar fácilmente el entorno de desarrollo

Equipo de desarrollo
