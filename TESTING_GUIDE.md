# Guía de Pruebas - API de Gestión de Usuarios

Esta guía proporciona ejemplos detallados para probar todos los endpoints de la API usando curl, Postman o Thunder Client.

## 📋 Tabla de Contenidos

1. [Configuración Inicial](#configuración-inicial)
2. [Pruebas de Autenticación](#pruebas-de-autenticación)
3. [Pruebas de Gestión de Usuarios](#pruebas-de-gestión-de-usuarios)
4. [Pruebas de Estadísticas](#pruebas-de-estadísticas)
5. [Pruebas de Validación](#pruebas-de-validación)
6. [Pruebas de Manejo de Errores](#pruebas-de-manejo-de-errores)
7. [Checklist de Validación](#checklist-de-validación)

## 🔧 Configuración Inicial

### Variables de Entorno

Asegúrate de tener configuradas las siguientes variables en tu archivo `.env`:

```env
APP_URL=http://localhost:8000
DB_DATABASE=user_management
JWT_TTL=5
JWT_REFRESH_TTL=10080
```

### Iniciar el Servidor

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000`

### Generar Datos de Prueba

```bash
php artisan db:seed
```

Esto creará 50 usuarios de prueba con fechas distribuidas.

## 🔐 Pruebas de Autenticación

### 1. Registrar Nuevo Usuario

**Endpoint:** `POST /api/auth/register`

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test@example.com\",\"password\":\"Password123\",\"first_name\":\"Juan\",\"last_name\":\"Pérez\"}"
```

**Respuesta Esperada (201):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502001a2b3c4d5e6f...",
    "user": {
      "id": 51,
      "email": "test@example.com",
      "first_name": "Juan",
      "last_name": "Pérez",
      "created_at": "2024-11-12T10:30:00.000000Z",
      "updated_at": "2024-11-12T10:30:00.000000Z"
    }
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 201
- ✅ Retorna access_token y refresh_token
- ✅ Retorna datos del usuario sin password
- ✅ Usuario se crea en la base de datos

### 2. Iniciar Sesión

**Endpoint:** `POST /api/auth/login`

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test@example.com\",\"password\":\"Password123\"}"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "refresh_token": "def502001a2b3c4d5e6f...",
    "user": {
      "id": 51,
      "email": "test@example.com",
      "first_name": "Juan",
      "last_name": "Pérez",
      "created_at": "2024-11-12T10:30:00.000000Z",
      "updated_at": "2024-11-12T10:30:00.000000Z"
    }
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Retorna tokens válidos
- ✅ Retorna datos del usuario

**Guardar el access_token para las siguientes pruebas:**
```bash
set ACCESS_TOKEN=eyJ0eXAiOiJKV1QiLCJhbGc...
set REFRESH_TOKEN=def502001a2b3c4d5e6f...
```

### 3. Renovar Token de Acceso

**Endpoint:** `POST /api/auth/refresh`

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/refresh ^
  -H "Content-Type: application/json" ^
  -d "{\"refresh_token\":\"%REFRESH_TOKEN%\"}"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": {
    "access_token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Retorna nuevo access_token
- ✅ El nuevo token es válido

### 4. Cerrar Sesión

**Endpoint:** `POST /api/auth/logout`

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/logout ^
  -H "Content-Type: application/json" ^
  -H "Authorization: Bearer %ACCESS_TOKEN%" ^
  -d "{\"refresh_token\":\"%REFRESH_TOKEN%\"}"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": {
    "message": "Sesión cerrada exitosamente"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Refresh token queda revocado
- ✅ No se puede usar el refresh token después del logout

## 👥 Pruebas de Gestión de Usuarios

**Nota:** Todos estos endpoints requieren autenticación. Usa el access_token obtenido en el login.

### 5. Obtener Todos los Usuarios

**Endpoint:** `GET /api/users`

**curl:**
```bash
curl -X GET http://localhost:8000/api/users ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "email": "usuario1@example.com",
      "first_name": "Usuario",
      "last_name": "Uno",
      "created_at": "2024-11-01T10:00:00.000000Z",
      "updated_at": "2024-11-01T10:00:00.000000Z"
    },
    {
      "id": 2,
      "email": "usuario2@example.com",
      "first_name": "Usuario",
      "last_name": "Dos",
      "created_at": "2024-11-02T11:00:00.000000Z",
      "updated_at": "2024-11-02T11:00:00.000000Z"
    }
  ]
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Retorna array de usuarios
- ✅ Ningún usuario incluye el campo password
- ✅ Todos los usuarios tienen los campos requeridos

### 6. Obtener Usuario por ID

**Endpoint:** `GET /api/users/{id}`

**curl:**
```bash
curl -X GET http://localhost:8000/api/users/1 ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "usuario1@example.com",
    "first_name": "Usuario",
    "last_name": "Uno",
    "created_at": "2024-11-01T10:00:00.000000Z",
    "updated_at": "2024-11-01T10:00:00.000000Z"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Retorna datos del usuario específico
- ✅ No incluye password

### 7. Actualizar Usuario

**Endpoint:** `PUT /api/users/{id}`

**curl:**
```bash
curl -X PUT http://localhost:8000/api/users/1 ^
  -H "Content-Type: application/json" ^
  -H "Authorization: Bearer %ACCESS_TOKEN%" ^
  -d "{\"first_name\":\"Juan Carlos\",\"last_name\":\"Pérez García\"}"
```

**Respuesta Esperada (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "email": "usuario1@example.com",
    "first_name": "Juan Carlos",
    "last_name": "Pérez García",
    "created_at": "2024-11-01T10:00:00.000000Z",
    "updated_at": "2024-11-12T15:45:00.000000Z"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Datos actualizados correctamente
- ✅ Campo updated_at se actualiza

### 8. Eliminar Usuario

**Endpoint:** `DELETE /api/users/{id}`

**curl:**
```bash
curl -X DELETE http://localhost:8000/api/users/1 ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (204):**
Sin contenido en el cuerpo de la respuesta.

**Validaciones a Verificar:**
- ✅ Código de estado: 204
- ✅ Usuario eliminado de la base de datos
- ✅ Tokens asociados al usuario también eliminados

## 📊 Pruebas de Estadísticas

### 9. Estadísticas Diarias

**Endpoint:** `GET /api/stats/daily`

**curl:**
```bash
curl -X GET http://localhost:8000/api/stats/daily ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (200):**
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
    },
    {
      "date": "2024-11-10",
      "count": 12
    }
  ]
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Datos ordenados por fecha descendente
- ✅ Cada elemento tiene date y count
- ✅ Los conteos son correctos

### 10. Estadísticas Semanales

**Endpoint:** `GET /api/stats/weekly`

**curl:**
```bash
curl -X GET http://localhost:8000/api/stats/weekly ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (200):**
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

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Datos ordenados por año y semana descendente
- ✅ Cada elemento tiene year, week y count

### 11. Estadísticas Mensuales

**Endpoint:** `GET /api/stats/monthly`

**curl:**
```bash
curl -X GET http://localhost:8000/api/stats/monthly ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (200):**
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

**Validaciones a Verificar:**
- ✅ Código de estado: 200
- ✅ Datos ordenados por año y mes descendente
- ✅ Cada elemento tiene year, month y count

## ✅ Pruebas de Validación

### 12. Email Inválido

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"email-invalido\",\"password\":\"Password123\",\"first_name\":\"Juan\",\"last_name\":\"Pérez\"}"
```

**Respuesta Esperada (400):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Error de validación de datos",
    "details": {
      "email": [
        "El campo email debe ser una dirección de correo válida."
      ]
    }
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 400
- ✅ Mensaje de error descriptivo en español
- ✅ Detalles específicos del campo que falló

### 13. Contraseña Débil

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test2@example.com\",\"password\":\"pass\",\"first_name\":\"Juan\",\"last_name\":\"Pérez\"}"
```

**Respuesta Esperada (400):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Error de validación de datos",
    "details": {
      "password": [
        "El campo password debe tener al menos 8 caracteres.",
        "El campo password debe contener al menos una letra mayúscula, una minúscula y un número."
      ]
    }
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 400
- ✅ Validación de longitud mínima
- ✅ Validación de complejidad (mayúscula, minúscula, número)

### 14. Campos Requeridos Faltantes

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test3@example.com\"}"
```

**Respuesta Esperada (400):**
```json
{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "Error de validación de datos",
    "details": {
      "password": ["El campo password es obligatorio."],
      "first_name": ["El campo first name es obligatorio."],
      "last_name": ["El campo last name es obligatorio."]
    }
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 400
- ✅ Todos los campos faltantes reportados

### 15. Email Duplicado

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/register ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test@example.com\",\"password\":\"Password123\",\"first_name\":\"Juan\",\"last_name\":\"Pérez\"}"
```

**Respuesta Esperada (409):**
```json
{
  "success": false,
  "error": {
    "code": "USER_ALREADY_EXISTS",
    "message": "El email ya está registrado"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 409
- ✅ Mensaje de error claro

## 🚨 Pruebas de Manejo de Errores

### 16. Sin Token de Autenticación

**curl:**
```bash
curl -X GET http://localhost:8000/api/users
```

**Respuesta Esperada (401):**
```json
{
  "success": false,
  "error": {
    "code": "UNAUTHORIZED",
    "message": "Token de autenticación no proporcionado"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 401
- ✅ Mensaje descriptivo

### 17. Token Inválido

**curl:**
```bash
curl -X GET http://localhost:8000/api/users ^
  -H "Authorization: Bearer token_invalido"
```

**Respuesta Esperada (403):**
```json
{
  "success": false,
  "error": {
    "code": "TOKEN_INVALID",
    "message": "El token de acceso es inválido"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 403
- ✅ Mensaje descriptivo

### 18. Token Expirado

**Nota:** Para probar esto, espera 5 minutos después de obtener un access_token o modifica JWT_TTL=1 en .env para que expire en 1 minuto.

**curl:**
```bash
curl -X GET http://localhost:8000/api/users ^
  -H "Authorization: Bearer %EXPIRED_TOKEN%"
```

**Respuesta Esperada (401):**
```json
{
  "success": false,
  "error": {
    "code": "TOKEN_EXPIRED",
    "message": "El token de acceso ha expirado"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 401
- ✅ Mensaje indica que el token expiró

### 19. Usuario No Encontrado

**curl:**
```bash
curl -X GET http://localhost:8000/api/users/99999 ^
  -H "Authorization: Bearer %ACCESS_TOKEN%"
```

**Respuesta Esperada (404):**
```json
{
  "success": false,
  "error": {
    "code": "USER_NOT_FOUND",
    "message": "Usuario no encontrado"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 404
- ✅ Mensaje descriptivo

### 20. Credenciales Inválidas

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/login ^
  -H "Content-Type: application/json" ^
  -d "{\"email\":\"test@example.com\",\"password\":\"WrongPassword123\"}"
```

**Respuesta Esperada (401):**
```json
{
  "success": false,
  "error": {
    "code": "INVALID_CREDENTIALS",
    "message": "Las credenciales proporcionadas son incorrectas"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 401
- ✅ Mensaje descriptivo

### 21. Refresh Token Inválido

**curl:**
```bash
curl -X POST http://localhost:8000/api/auth/refresh ^
  -H "Content-Type: application/json" ^
  -d "{\"refresh_token\":\"token_invalido\"}"
```

**Respuesta Esperada (403):**
```json
{
  "success": false,
  "error": {
    "code": "TOKEN_INVALID",
    "message": "El refresh token es inválido"
  }
}
```

**Validaciones a Verificar:**
- ✅ Código de estado: 403
- ✅ Mensaje descriptivo

## 📋 Checklist de Validación Final

### Códigos de Estado HTTP

- [ ] **200 OK** - Login, obtener usuarios, actualizar usuario, logout, refresh token, estadísticas
- [ ] **201 Created** - Registro de usuario
- [ ] **204 No Content** - Eliminación de usuario
- [ ] **400 Bad Request** - Errores de validación
- [ ] **401 Unauthorized** - Sin autenticación, token expirado, credenciales inválidas
- [ ] **403 Forbidden** - Token inválido
- [ ] **404 Not Found** - Usuario no encontrado
- [ ] **409 Conflict** - Email duplicado

### Mensajes de Error

- [ ] Todos los mensajes están en español
- [ ] Los mensajes son descriptivos y claros
- [ ] Los errores de validación incluyen detalles específicos
- [ ] Cada error tiene un código único (ERROR_CODE)

### Validaciones

- [ ] Email: formato válido y único
- [ ] Password: mínimo 8 caracteres, mayúscula, minúscula, número
- [ ] Nombres: requeridos, máximo 100 caracteres
- [ ] Campos requeridos no pueden estar vacíos

### Seguridad

- [ ] Las contraseñas nunca se retornan en las respuestas
- [ ] Las contraseñas se almacenan encriptadas (bcrypt)
- [ ] Los tokens JWT tienen expiración correcta (5 min access, 7 días refresh)
- [ ] Los refresh tokens se pueden revocar
- [ ] Los endpoints protegidos requieren autenticación

### Funcionalidad

- [ ] CRUD completo de usuarios funciona correctamente
- [ ] Autenticación con JWT funciona
- [ ] Renovación de tokens funciona
- [ ] Logout revoca el refresh token
- [ ] Estadísticas retornan datos correctos
- [ ] Estadísticas están ordenadas correctamente (descendente)

### Documentación

- [ ] README.md está completo y actualizado
- [ ] Variables de entorno documentadas
- [ ] Ejemplos de curl funcionan
- [ ] Instrucciones de instalación son claras

## 🔍 Herramientas Recomendadas

### Postman

1. Importar colección desde archivo JSON (si está disponible)
2. Configurar variable de entorno `base_url` = `http://localhost:8000`
3. Configurar variable `access_token` que se actualiza automáticamente

### Thunder Client (VS Code)

1. Crear nueva colección "User Management API"
2. Agregar requests para cada endpoint
3. Usar variables de entorno para tokens

### curl (Línea de Comandos)

Ventajas:
- Rápido para pruebas simples
- Fácil de automatizar
- No requiere instalación adicional

## 📝 Notas Adicionales

### Probar Token Expirado

Para probar la expiración de tokens más rápidamente:

1. Modificar `.env`: `JWT_TTL=1` (1 minuto)
2. Reiniciar servidor: `php artisan serve`
3. Hacer login y obtener token
4. Esperar 1 minuto
5. Intentar usar el token

### Probar Refresh Token Expirado

1. Modificar `.env`: `JWT_REFRESH_TTL=1` (1 minuto)
2. Reiniciar servidor
3. Hacer login
4. Esperar 1 minuto
5. Intentar renovar token

### Verificar Base de Datos

```bash
# Conectar a MySQL
mysql -u root -p

# Seleccionar base de datos
USE user_management;

# Ver usuarios
SELECT id, email, first_name, last_name, created_at FROM users;

# Ver refresh tokens
SELECT id, user_id, expires_at, is_revoked FROM refresh_tokens;
```

## ✅ Conclusión

Esta guía cubre todas las pruebas necesarias para validar que la API funciona correctamente según los requisitos. Asegúrate de ejecutar todas las pruebas y verificar que los códigos de estado, mensajes de error y respuestas sean los esperados.

Si encuentras algún problema, revisa:
1. Configuración de `.env`
2. Migraciones ejecutadas correctamente
3. Servidor Laravel corriendo
4. Base de datos MySQL accesible
