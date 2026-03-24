# ControClinic - Multi-Tenant SaaS for Medical Clinic Management

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red.svg" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3-purple.svg" alt="Livewire 3">
  <img src="https://img.shields.io/badge/PHP-8.2+-blue.svg" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Architecture-Multi--Tenant%20SaaS-orange.svg" alt="Multi-Tenant SaaS">
</p>

A multi-tenant SaaS platform for medical clinic management built with **Laravel 12** and **Livewire 3 (Volt)**. Designed for clinics to manage patients, appointments, medical records, and billing through a modern reactive interface with subscription-based access via Paddle.

## Key Features

- **Multi-Tenant Architecture** — Each clinic operates in an isolated data context with tenant-scoped queries and role-based access control (Spatie Permission)
- **Patient Management** — Complete patient registry with medical history, contact information, and document tracking
- **Appointment Scheduling** — Real-time appointment booking, calendar management, and automated status workflows
- **Medical Records (EMR)** — Electronic medical records with structured clinical data per patient visit
- **Activity Logging** — Full audit trail of all system operations using Spatie Activity Log
- **Subscription Billing** — Integrated payment processing with Laravel Cashier (Paddle) for SaaS monetization
- **Internationalization** — Multi-language support via `mcamara/laravel-localization`
- **Reactive UI** — Livewire 3 + Volt components for real-time interactions without page reloads

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 12 |
| **Frontend** | Livewire 3 + Volt, Tailwind CSS, Vite |
| **Authentication** | Laravel Breeze |
| **Authorization** | Spatie Laravel Permission (roles & permissions) |
| **Audit Trail** | Spatie Activity Log |
| **Payments** | Laravel Cashier (Paddle) |
| **i18n** | mcamara/laravel-localization |
| **Testing** | PHPUnit 11 |
| **Code Style** | Laravel Pint |

## Architecture Overview

```
app/
├── Http/              # Controllers & middleware
├── Livewire/
│   ├── Actions/       # Reusable Livewire actions
│   ├── App/
│   │   ├── Appointments/  # Appointment management components
│   │   ├── Patients/      # Patient CRUD components
│   │   └── Settings/      # Clinic settings components
│   └── Forms/         # Livewire form objects
├── Models/
│   ├── Clinic.php         # Tenant model (multi-tenant root)
│   ├── User.php           # Authenticated users with roles
│   ├── Patient.php        # Patient registry
│   ├── Appointment.php    # Scheduling & calendar
│   └── MedicalRecord.php  # Clinical records (EMR)
├── Traits/            # Shared behaviors (tenant scoping, etc.)
└── View/              # View composers & components
```

## Getting Started

### Requirements
- PHP 8.2+
- Composer
- Node.js 18+ & NPM
- SQLite (default) or MySQL 8.0+

### Installation

```bash
git clone https://github.com/szystems/controclinic.git
cd controclinic

# Run the setup script (installs deps, generates key, runs migrations)
composer setup

# Start the development server (app + queue + logs + vite)
composer dev
```

The application will be available at `http://localhost:8000`.

## Testing

```bash
composer test
```

## Author

**Otto Szarata** — Senior Full-Stack Developer  
[GitHub](https://github.com/szystems) · Victoria, BC, Canada

## License

This project is proprietary software. All rights reserved.
