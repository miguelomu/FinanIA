# 💰 FinanIA — Gestor Inteligente Financiero

Sistema web de gestión de finanzas personales diseñado para estudiantes universitarios colombianos. Combina **inteligencia artificial**, **educación financiera** y **contexto local** para ayudar a los usuarios a tomar decisiones económicas informadas.

---

## 👥 Autores

| Nombre | Rol |
|--------|-----|
| **Miguel Angel Muñoz Bohorquez** | Desarrollo |
| **Daniel Estiven Canchon Roa** | Desarrollo |
| **Luis Alejandro Neira Rivera** | Desarrollo |

**Docente:** Martín Emilio Rodríguez Forero  
**Institución:** Universidad Uniminuto  
**Materia:** Ingeniería de Software  
**Año:** 2026

---

## 🎯 Problema que resuelve

Más del 60% de los jóvenes colombianos entre 18 y 25 años no lleva un registro sistemático de sus ingresos y gastos. La mayoría desconoce conceptos financieros básicos como ahorro programado, interés compuesto o tasa de usura.

Las apps existentes están diseñadas para mercados extranjeros y no contemplan el contexto colombiano (Nequi, Daviplata, tasa de usura, CDT, inflación local).

**FinanIA** ataca estos tres problemas con:
- Registro simplificado (manual + importación CSV)
- Análisis automático (chatbot IA + gráficas)
- Contexto colombiano (COP, usura, simuladores)

---

## ✨ Módulos implementados (21)

### Finanzas básicas
- 🔐 Autenticación (login, registro, 2FA)
- 🎯 Onboarding de 6 pasos con perfil de riesgo
- 🏦 Cuentas (efectivo, ahorros, banco, tarjeta)
- 💸 Transacciones (manual + importación CSV)
- 🏷️ Categorías personalizables
- 🎯 Metas de ahorro con aportes e historial
- 💳 Deudas y pagos con alerta de usura
- 📅 Presupuestos mensuales por categoría
- 💼 Fuentes de ingreso (variable, freelance)
- 🔄 Recurrentes y suscripciones

### Análisis
- 📈 Reportes con gráficas y exportación PDF
- 📅 Calendario financiero con proyecciones
- 🔍 Búsqueda global con Ctrl+K
- 🏆 Salud financiera (0-100)
- 🏅 Logros y gamificación

### Premium (IA y análisis avanzado)
- 🤖 Chatbot IA con análisis predictivo
- 🏔️ Simulador de deudas (avalancha vs bola de nieve)
- 💰 Simulador de inversiones (CDT, FIC, comparador)
- 💡 Ideas de inversión (intangibles + tangibles)
- ⚙️ Configuración financiera (tasas del mercado)
- ⭐ Pago simulado Premium

### Extras
- 📱 PWA instalable en PC, Android e iPhone
- 🌙 Modo oscuro/claro global

---

## 🛠️ Stack tecnológico

| Capa | Tecnología |
|------|-----------|
| **Backend** | PHP 8+ con PDO |
| **Base de datos** | MySQL 8 (17 tablas) |
| **Frontend** | HTML5, CSS3, JavaScript |
| **Gráficas** | Chart.js |
| **Entorno local** | XAMPP |
| **Deploy** | InfinityFree |
| **Control de versiones** | Git + GitHub |

---

## 🚀 Instalación local

### Requisitos
- XAMPP instalado ([descargar](https://www.apachefriends.org/))
- Git instalado ([descargar](https://git-scm.com/))

### Pasos

1. **Clonar el repositorio**
   bash
   cd C:\xampp\htdocs
   git clone https://github.com/miguelomu/FinanIA.git
   

2. **Configurar la conexión a la base de datos**
   
   Copia `config/database.example.php` a `config/database.php` y ajusta los valores:
   php
   define('DB_HOST', 'localhost');
   define('DB_PORT', '3306');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'gestor_financiero');
   

3. **Crear la base de datos**
   - Abre `http://localhost/phpmyadmin`
   - Crea una BD llamada `gestor_financiero` (utf8mb4_unicode_ci)
   - Importa `database/gestor_financiero.sql`

4. **Encender Apache y MySQL en XAMPP**

5. **Abrir en el navegador**
   
   http://localhost/FinanIA/auth/login.php
   

### Usuarios de prueba

| Email | Contraseña | Rol |
|-------|-----------|-----|
| admin@demo.com | admin123 | Premium |
| user@demo.com | admin123 | Registrado |
| demo@finania.com | demo123 | Registrado (con datos) |

---

## 🌐 Acceso en producción

**URL:** https://finania.rf.gd

**Usuario demo:** demo@finania.com  
**Contraseña:** demo123

---

## 📱 Instalación como app (PWA)

### Android (Chrome)
1. Abre `https://finania.rf.gd` en Chrome
2. Toca el menú (⋮) → **"Instalar aplicación"**
3. Confirma y el ícono aparece en tu pantalla de inicio

### iPhone (Safari)
1. Abre `https://finania.rf.gd` en Safari
2. Toca **Compartir** → **"Añadir a pantalla de inicio"**
3. Activa **"Abrir como app web"** y toca **Añadir**

---

## 📁 Estructura del proyecto

FinanIA/
├── app/                    → MVC (Models, Controllers, Views, Helpers)
├── assets/                 → CSS, JS, imágenes, uploads
├── auth/                   → Login, registro, onboarding
├── config/                 → Conexión a BD
├── dashboards/             → Paneles por rol (invitado, registrado, premium)
├── database/               → Scripts SQL
├── docs/                   → Documentos HTML (planteamiento, causas, RF, SQL)
├── includes/               → Header, footer, sidebar, auth
├── modulos/                → 21 módulos funcionales
├── presentacion/           → Presentación HTML de sustentación
└── premium/                → Pago simulado Premium

---

## 📚 Documentación

| Documento | Descripción |
|-----------|-------------|
| [`docs/01-planteamiento-problema.html`](docs/01-planteamiento-problema.html) | Planteamiento del problema |
| [`docs/02-causas-consecuencias-soluciones.html`](docs/02-causas-consecuencias-soluciones.html) | Matriz de causas, consecuencias y soluciones |
| [`docs/03-requerimientos-funcionales.html`](docs/03-requerimientos-funcionales.html) | 50 RF + 25 RNF con buscador |
| [`docs/04-consultas-multitabla.html`](docs/04-consultas-multitabla.html) | 6 consultas SQL multitabla |
| [`presentacion/index.html`](presentacion/index.html) | Presentación de sustentación |

---

## 🗺️ Roadmap (Futuro)

- [ ] Integración con IA real (Claude Haiku / GPT)
- [ ] Notificaciones por email (PHPMailer)
- [ ] Sincronización bancaria (Belvo)
- [ ] App móvil nativa (React Native)
- [ ] Multi-moneda (USD, EUR)

---

## 📄 Licencia

MIT License — Ver [LICENSE](LICENSE) para más detalles.

---

## 🙏 Agradecimientos

A la Universidad Uniminuto y al docente Martín Emilio Rodríguez Forero por el acompañamiento durante el desarrollo del proyecto.

---

⭐ **Si te gustó el proyecto, dale una estrella en GitHub** ⭐


---
