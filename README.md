# FarmaSys — Sistema de Gestión Farmacéutica

## 📁 Estructura de carpetas

```
farmacia/
├── api/
│   ├── login.php          # POST → autenticación
│   ├── logout.php         # GET  → cerrar sesión
│   ├── productos.php      # CRUD completo
│   ├── categorias.php     # CRUD básico
│   ├── ventas.php         # Registro y listado de ventas
│   ├── devoluciones.php   # Registro y listado de devoluciones
│   ├── compras.php        # Registro y listado de compras
│   ├── reportes.php       # Reportes con filtros
│   ├── empresa.php        # GET/PUT datos empresa
│   └── usuarios.php       # CRUD usuarios (solo ADMIN)
├── assets/
│   ├── css/main.css       # Estilos globales
│   └── js/
│       ├── app.js         # Utilidades globales (API, Toast, Modal, Fmt)
│       ├── ventas.js      # Módulo punto de venta
│       ├── devoluciones.js
│       ├── productos.js
│       ├── categorias.js
│       ├── compras.js
│       ├── reportes.js
│       ├── usuarios.js
│       └── empresa.js
├── config/
│   ├── database.php       # Conexión PDO (Singleton)
│   ├── session.php        # Manejo de sesiones + helpers
│   ├── response.php       # Helpers jsonSuccess / jsonError
│   └── seed.sql           # Datos iniciales
├── views/                 # Fragmentos HTML incluidos en dashboard
│   ├── ventas.php
│   ├── devoluciones.php
│   ├── productos.php
│   ├── categorias.php
│   ├── compras.php
│   ├── reportes.php
│   ├── usuarios.php
│   └── empresa.php
├── index.php              # LOGIN (punto de entrada)
└── dashboard.php          # Dashboard principal (SPA-style)
```

## ⚙️ Instalación

### 1. Requisitos

- XAMPP (PHP 8.0+ / MySQL 5.7+)
- Módulo PDO habilitado

### 2. Base de datos

1. Abre phpMyAdmin → http://localhost/phpmyadmin
2. Importa el **schema** (el SQL del proyecto)
3. Luego importa `config/seed.sql` para el usuario admin y datos de prueba

### 3. Credenciales de acceso por defecto

| Email                 | Password | Rol      |
| --------------------- | -------- | -------- |
| admin@farmacia.com    | password | ADMIN    |
| empleado@farmacia.com | password | EMPLEADO |

> ⚠️ **Cambia las contraseñas** en producción. El hash en seed.sql corresponde a "password" (de Laravel faker — solo para desarrollo).

### 4. Configuración de BD

Edita `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');      // tu usuario MySQL
define('DB_PASS', '');          // tu contraseña MySQL
define('DB_NAME', 'farmacia');
```

### 5. Colocar en XAMPP

```
C:/xampp/htdocs/farmacia/
```

Accede en: http://localhost/farmacia

---

## 🔐 Seguridad implementada

- ✅ `password_hash` / `password_verify`
- ✅ PDO con Prepared Statements (anti SQL Injection)
- ✅ Validación de sesión en cada endpoint PHP
- ✅ Control de roles (ADMIN / EMPLEADO)
- ✅ Soft-delete en productos (no se elimina data)
- ✅ Transacciones MySQL en ventas, compras y devoluciones

## 📋 Módulos disponibles

| Módulo       | ADMIN | EMPLEADO         |
| ------------ | ----- | ---------------- |
| Dashboard    | ✅    | ✅               |
| Ventas (POS) | ✅    | ✅               |
| Devoluciones | ✅    | ✅               |
| Productos    | ✅    | 👁 solo consulta |
| Categorías   | ✅    | ❌               |
| Compras      | ✅    | ❌               |
| Reportes     | ✅    | ❌               |
| Usuarios     | ✅    | ❌               |
| Empresa      | ✅    | ❌               |
