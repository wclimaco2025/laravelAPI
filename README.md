# API de Gestión de Usuarios

API RESTful construida con Laravel para gestión completa de usuarios, autenticación JWT con renovación automática de tokens, y generación de estadísticas de registro.

## 📋 Características

- ✅ **Operaciones CRUD completas** para usuarios
- 🔐 **Autenticación JWT** con tokens de acceso (5 minutos) y refresh tokens (7 días)
- 📊 **Estadísticas de registro** por día, semana y mes
- 📝 **Validación robusta** de datos de entrada
- 🔒 **Encriptación de contraseñas** con bcrypt
- 📚 **Documentación Swagger/OpenAPI** interactiva
- 🌐 **Mensajes de error descriptivos** en español
- 🎯 **Arquitectura en capas** (Controllers, Services, Repositories)

## 🛠️ Requisitos del Sistema

- PHP >= 8.1
- Composer >= 2.0
- MySQL >= 8.0
- Extensiones PHP: OpenSSL, PDO, Mbstring, Tokenizer, XML, Ctype, JSON

## 📦 Instalación

### 1. Clonar el repositorio

```bash
git clone <repository-url>
cd user-management-api
```

### 2. Instalar dependencias

```bash
composer install
```

### 3. Configurar variables de entorno

Copiar el archivo de ejemplo y configurar las variables:

```bash
copy .env.example .env
```

Editar el archivo `.env` con tus credenciales:

```env
APP_NAME="API de Gestión de Usuarios"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=user_management
DB_USERNAME=root
DB_PASSWORD=tu_password

JWT_SECRET=
JWT_TTL=5
JWT_REFRESH_TTL=10080
```

### 4. Generar claves de aplicación

```bash
php artisan key:generate
php artisan jwt:secret
```

### 5. Crear base de datos

Crear la base de datos en MySQL:

```sql
CREATE DATABASE user_management CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 6. Ejecutar migraciones

```bash
php artisan migrate
```

### 7. (Opcional) Generar datos de prueba

```bash
php artisan db:seed
```

Esto creará 50 usuarios de prueba con fechas distribuidas para probar las estadísticas.

### 8. Generar documentación Swagger

```bash
php artisan l5-swagger:generate
```

### 9. Iniciar servidor de desarrollo

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

## 📖 Documentación de la API

### Acceder a Swagger UI

Una vez iniciado el servidor, accede a la documentación interactiva:

```
http://localhost:8000/api/documentation
```

## 🔌 Endpoints Disponibles

### Autenticación (Públicos)

#### Registrar Usuario
```bash
POST /api/auth/register
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "Password123",
  "first_name": "Juan",
  "last_name": "Pérez"
}
```

**Respuesta exitosa (201):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502001a2b3c4d5e6f...",
    "user": {
      "id": 1,
      "email": "usuario@example.com",
      "first_name": "Juan",
      "last_name": "Pérez",
      "created_at": "2024-11-12T10:30:00.000000Z",
      "updated_at": "2024-11-12T10:30:00.000000Z"
    }
  }
}
```

#### Iniciar Sesión
```bash
POST /api/auth/login
Content-Type: application/json

{
  "email": "usuario@example.com",
  "password": "Password123"
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502001a2b3c4d5e6f...",
    "user": {
      "id": 1,
      "email": "usuario@example.com",
      "first_name": "Juan",
      "last_name": "Pérez",
      "created_at": "2024-11-12T10:30:00.000000Z",
      "updated_at": "2024-11-12T10:30:00.000000Z"
    }
  }
}
```

#### Renovar Token de Acceso
```bash
POST /api/auth/refresh
Content-Type: application/json

{
  "refresh_token": "def502001a2b3c4d5e6f..."
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

#### Cerrar Sesión
```bash
POST /api/auth/logout
Content-Type: application/json
Authorization: Bearer {access_token}

{
  "refresh_token": "def502001a2b3c4d5e6f..."
}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "message": "Sesión cerrada exitosamente"
  }
}
```

### Gestión de Usuarios (Requieren Autenticación)

Todos estos endpoints requieren el header:
```
Authorization: Bearer {access_token}
```

#### Obtener Todos los Usuarios
```bash
GET /api/users
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "usuario@example.com",
      "first_name": "Juan",
      "last_name": "Pérez",
      "created_at": "2024-11-12T10:30:00.000000Z",
      "updated_at": "2024-11-12T10:30:00.000000Z"
    }
  ]
}
```

#### Obtener Usuario por ID
```bash
GET /api/users/{id}
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "usuario@example.com",
    "first_name": "Juan",
    "last_name": "Pérez",
    "created_at": "2024-11-12T10:30:00.000000Z",
    "updated_at": "2024-11-12T10:30:00.000000Z"
  }
}
```

#### Actualizar Usuario
```bash
PUT /api/users/{id}
Content-Type: application/json
Authorization: Bearer {access_token}

{
  "email": "nuevo@example.com",
  "first_name": "Juan Carlos",
  "last_name": "Pérez García",
  "password": "NewPassword123"
}
```

**Nota:** Todos los campos son opcionales. Solo se actualizarán los campos enviados.

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "nuevo@example.com",
    "first_name": "Juan Carlos",
    "last_name": "Pérez García",
    "created_at": "2024-11-12T10:30:00.000000Z",
    "updated_at": "2024-11-12T15:45:00.000000Z"
  }
}
```

#### Eliminar Usuario
```bash
DELETE /api/users/{id}
Authorization: Bearer {access_token}
```

**Respuesta exitosa (204):**
Sin contenido en el cuerpo de la respuesta.

### Estadísticas (Requieren Autenticación)

#### Estadísticas Diarias
```bash
GET /api/stats/daily
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "date": "2024-11-12",
      "count": 15
    },
    {
      "date": "2024-11-11",
      "count": 23
    }
  ]
}
```

#### Estadísticas Semanales
```bash
GET /api/stats/weekly
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "year": 2024,
      "week": 46,
      "count": 42
    },
    {
      "year": 2024,
      "week": 45,
      "count": 38
    }
  ]
}
```

#### Estadísticas Mensuales
```bash
GET /api/stats/monthly
Authorization: Bearer {access_token}
```

**Respuesta exitosa (200):**
```json
{
  "success": true,
  "data": [
    {
      "year": 2024,
      "month": 11,
      "count": 127
    },
    {
      "year": 2024,
      "month": 10,
      "count": 98
    }
  ]
}
```

## ⚠️ Códigos de Estado HTTP

La API utiliza los siguientes códigos de estado:

- **200 OK** - Solicitud exitosa
- **201 Created** - Recurso creado exitosamente
- **204 No Content** - Recurso eliminado exitosamente
- **400 Bad Request** - Error de validación de datos
- **401 Unauthorized** - No autenticado o token expirado
- **403 Forbidden** - Token inválido
- **404 Not Found** - Recurso no encontrado
- **409 Conflict** - Conflicto (email duplicado)
- **500 Internal Server Error** - Error del servidor

## 🚨 Manejo de Errores

Todas las respuestas de error siguen el siguiente formato:

```json
{
  "success": false,
  "error": {
    "code": "ERROR_CODE",
    "message": "Mensaje descriptivo del error",
    "details": {}
  }
}
```

### Códigos de Error

- **VALIDATION_ERROR** (400) - Error de validación de datos
  ```json
  {
    "success": false,
    "error": {
      "code": "VALIDATION_ERROR",
      "message": "Error de validación de datos",
      "details": {
        "email": ["El campo email debe ser una dirección de correo válida"],
        "password": ["El campo password debe tener al menos 8 caracteres"]
      }
    }
  }
  ```

- **UNAUTHORIZED** (401) - No autenticado
  ```json
  {
    "success": false,
    "error": {
      "code": "UNAUTHORIZED",
      "message": "Token de autenticación no proporcionado"
    }
  }
  ```

- **INVALID_CREDENTIALS** (401) - Credenciales incorrectas
  ```json
  {
    "success": false,
    "error": {
      "code": "INVALID_CREDENTIALS",
      "message": "Las credenciales proporcionadas son incorrectas"
    }
  }
  ```

- **TOKEN_EXPIRED** (401) - Token expirado
  ```json
  {
    "success": false,
    "error": {
      "code": "TOKEN_EXPIRED",
      "message": "El token de acceso ha expirado"
    }
  }
  ```

- **TOKEN_INVALID** (403) - Token inválido
  ```json
  {
    "success": false,
    "error": {
      "code": "TOKEN_INVALID",
      "message": "El token de acceso es inválido"
    }
  }
  ```

- **USER_NOT_FOUND** (404) - Usuario no encontrado
  ```json
  {
    "success": false,
    "error": {
      "code": "USER_NOT_FOUND",
      "message": "Usuario no encontrado"
    }
  }
  ```

- **USER_ALREADY_EXISTS** (409) - Email duplicado
  ```json
  {
    "success": false,
    "error": {
      "code": "USER_ALREADY_EXISTS",
      "message": "El email ya está registrado"
    }
  }
  ```

## ✅ Reglas de Validación

### Registro y Actualización de Usuario

- **email**: Requerido, formato de email válido, único en la base de datos
- **password**: Requerido (registro), mínimo 8 caracteres, debe contener al menos:
  - Una letra mayúscula
  - Una letra minúscula
  - Un número
- **first_name**: Requerido, string, máximo 100 caracteres
- **last_name**: Requerido, string, máximo 100 caracteres

### Ejemplos de Contraseñas Válidas

✅ `Password123`
✅ `MySecure1Pass`
✅ `Admin2024!`

### Ejemplos de Contraseñas Inválidas

❌ `password` (sin mayúscula ni número)
❌ `PASSWORD123` (sin minúscula)
❌ `Password` (sin número)
❌ `Pass1` (menos de 8 caracteres)

## 🔐 Seguridad

### Tokens JWT

- **Access Token**: Expira en 5 minutos. Se usa para autenticar cada solicitud.
- **Refresh Token**: Expira en 7 días. Se usa para obtener nuevos access tokens.

### Flujo de Autenticación Recomendado

1. **Login/Register**: Obtener ambos tokens
2. **Solicitudes**: Usar access token en header `Authorization: Bearer {token}`
3. **Token Expirado**: Si recibes error 401 con código `TOKEN_EXPIRED`:
   - Usar refresh token para obtener nuevo access token
   - Reintentar la solicitud original con el nuevo token
4. **Refresh Token Expirado**: Si el refresh token expira, el usuario debe hacer login nuevamente

### Mejores Prácticas

- ✅ Almacenar tokens de forma segura (nunca en localStorage para producción)
- ✅ Usar HTTPS en producción
- ✅ Implementar rate limiting
- ✅ Rotar refresh tokens periódicamente
- ✅ Revocar tokens al cerrar sesión

## 🏗️ Arquitectura

### Estructura de Directorios

```
app/
├── Http/
│   ├── Controllers/Api/     # Controladores de API
│   ├── Middleware/          # Middleware de autenticación
│   ├── Requests/            # Form Requests para validación
│   └── Resources/           # API Resources para transformación
├── Models/                  # Modelos Eloquent
├── Services/                # Lógica de negocio
├── Repositories/            # Acceso a datos
└── Exceptions/              # Excepciones personalizadas
```

### Capas de la Aplicación

1. **Controllers**: Manejan requests HTTP y retornan responses
2. **Services**: Contienen la lógica de negocio
3. **Repositories**: Abstraen el acceso a la base de datos
4. **Models**: Representan las entidades de la base de datos

## 🧪 Testing

### Ejecutar Tests

```bash
# Todos los tests
php artisan test

# Tests específicos
php artisan test --filter=AuthTest
php artisan test --filter=UserTest
php artisan test --filter=StatsTest

# Con cobertura
php artisan test --coverage
```

### Configuración de Testing

Los tests usan SQLite en memoria para mayor velocidad. La configuración está en `phpunit.xml`.

## 📊 Variables de Entorno

### Variables Requeridas

| Variable | Descripción | Ejemplo |
|----------|-------------|---------|
| `APP_NAME` | Nombre de la aplicación | API de Gestión de Usuarios |
| `APP_ENV` | Entorno de ejecución | local, production |
| `APP_KEY` | Clave de encriptación | base64:... |
| `APP_DEBUG` | Modo debug | true, false |
| `APP_URL` | URL base de la aplicación | http://localhost:8000 |
| `DB_CONNECTION` | Tipo de base de datos | mysql |
| `DB_HOST` | Host de la base de datos | 127.0.0.1 |
| `DB_PORT` | Puerto de la base de datos | 3306 |
| `DB_DATABASE` | Nombre de la base de datos | user_management |
| `DB_USERNAME` | Usuario de la base de datos | root |
| `DB_PASSWORD` | Contraseña de la base de datos | |
| `JWT_SECRET` | Secreto para firmar tokens JWT | (generado automáticamente) |
| `JWT_TTL` | Tiempo de vida del access token (minutos) | 5 |
| `JWT_REFRESH_TTL` | Tiempo de vida del refresh token (minutos) | 10080 (7 días) |

## 🚀 Despliegue en Producción

### Checklist de Producción

- [ ] Configurar `APP_ENV=production`
- [ ] Configurar `APP_DEBUG=false`
- [ ] Usar HTTPS
- [ ] Configurar CORS apropiadamente
- [ ] Implementar rate limiting
- [ ] Configurar logs
- [ ] Optimizar autoloader: `composer install --optimize-autoloader --no-dev`
- [ ] Cachear configuración: `php artisan config:cache`
- [ ] Cachear rutas: `php artisan route:cache`
- [ ] Configurar backups de base de datos
- [ ] Implementar monitoreo y alertas

## 🤝 Contribución

1. Fork el proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

## 📝 Licencia

Este proyecto está bajo la Licencia MIT.

## 📧 Contacto

Para preguntas o soporte, contacta a: admin@example.com

## 🙏 Agradecimientos

- Laravel Framework
- tymon/jwt-auth
- L5-Swagger
- Comunidad de Laravel
