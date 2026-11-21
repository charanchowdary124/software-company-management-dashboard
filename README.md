# ArchiNexus — Studio Architecture & Design Management System (SAMS)

[![React](https://img.shields.io/badge/React-18.3-blue.svg)](https://reactjs.org/)
[![TypeScript](https://img.shields.io/badge/TypeScript-5.5-blue.svg)](https://www.typescriptlang.org/)
[![Vite](https://img.shields.io/badge/Vite-5.4-purple.svg)](https://vitejs.dev/)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-3.4-38B2AC.svg)](https://tailwindcss.com/)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-777BB4.svg)](https://www.php.net/)
[![Supabase](https://img.shields.io/badge/Supabase-PostgreSQL-3ECF8E.svg)](https://supabase.com/)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

An enterprise-grade Studio Architecture and Design Management System built with a dark glassmorphism UI, real-time KPI metrics, resource allocation analytics, interactive relationship graphing, and dual-stack flexibility (React + Vite SPA and PHP server-rendered application).

---

## 🌟 Key Features

- **Executive Dashboard**: Real-time KPI summary (Total Employees, Active Projects, Total Clients, Studio Capacity/Workload), workload bar charts, studio availability donut charts, and smart AI recommendations.
- **Interactive Relationship Graph**: SVG-powered dependency graph mapping connections between **Clients &rarr; Projects &rarr; Active Team Members** with hover isolation and role badges.
- **Employee Management**: Directory of personnel with department filters, workload capacity bars, star ratings, and an AJAX/slide-in profile detail drawer.
- **Project Tracking & Budgets**: Track project milestones, budget vs. actual spend, deadlines, team assignments, and toggle between grid and list views.
- **Client Directory**: Manage client accounts, industries, communication notes, and active project portfolios.
- **Teams & Resources**: Assign team leads, monitor resource allocations (workstations, CAD/BIM software licenses, facilities).
- **Reports & Analytics**: Visual charts for budget utilization, project timeline progress, department performance, and workforce availability.
- **Admin Profile & Settings**: Custom profile settings, activity logs, notification preferences, and session persistence.
- **Zero-Setup Mock Data Fallback**: Automatically falls back to full realistic mock datasets if database credentials are not supplied, allowing immediate evaluation without setup friction.

---

## 🏛️ Architecture & Tech Stack

This repository provides two complete, synchronized implementations of ArchiNexus:

### 1. React + TypeScript + Vite (Single Page Application)
- **Framework**: React 18, TypeScript 5.5, Vite 5.4
- **Styling**: Tailwind CSS with custom glassmorphic utilities and 60fps CSS animations
- **Data Visualization**: Recharts & Lucide React icons
- **State & Data**: TanStack React Query + Supabase JS Client

### 2. PHP Server-Rendered Application
- **Backend**: Native PHP 8.0+ with PDO PostgreSQL support
- **Styling**: Tailwind CSS & Glassmorphism design system
- **Charts**: ApexCharts for responsive dark-theme charts
- **Dynamic Elements**: Vanilla JavaScript AJAX drawers, live search, and session-backed profile storage

---

## 📂 Project Structure

```
├── src/                          # React + TypeScript source code
│   ├── components/               # Reusable UI & Layout components
│   │   ├── ui/                   # Glass cards, stat widgets, progress rings
│   │   ├── Layout.tsx            # Main application shell & sidebar
│   │   ├── EmployeeDetail.tsx    # Slide-in employee drawer
│   │   └── RelationshipGraph.tsx # Interactive SVG dependency visualizer
│   ├── pages/                    # React page views
│   │   ├── Dashboard.tsx         # Executive dashboard
│   │   ├── Employees.tsx         # Employee directory & filter system
│   │   ├── Projects.tsx          # Project management & view modes
│   │   ├── Clients.tsx           # Client CRM portfolio
│   │   ├── Teams.tsx             # Team leads and member assignment
│   │   ├── Resources.tsx         # Software/hardware resource tracking
│   │   ├── Reports.tsx           # Financial & progress analytics
│   │   └── AdminProfile.tsx      # Admin settings & activity feeds
│   ├── lib/                      # Supabase client, mock data, & theme utilities
│   └── types/                    # TypeScript interfaces & types
├── includes/                     # PHP layout partials & helper functions
│   ├── header.php                # Responsive PHP topbar & glass sidebar
│   ├── footer.php                # Footer & scripts
│   └── functions.php             # Data query engine with database & mock data fallback
├── supabase/
│   └── migrations/               # PostgreSQL schema & seed migration scripts
├── index.html                    # Vite HTML entry point
├── index.php                     # PHP Dashboard entry point
├── employees.php                 # PHP Employees page
├── projects.php                  # PHP Projects page
├── clients.php                   # PHP Clients page
├── teams.php                     # PHP Teams page
├── resources.php                 # PHP Resources page
├── reports.php                   # PHP Reports page
├── admin-profile.php             # PHP Admin Profile page
├── config.php                    # PHP Environment loader & PDO database connection
├── package.json                  # Node dependencies & npm scripts
├── tailwind.config.js            # Tailwind theme configurations
├── .env.example                  # Environment configuration template
└── LICENSE                       # MIT License
```

---

## 🚀 Getting Started

### Prerequisites

- **Node.js** (v18.0.0 or higher) and **npm**
- **PHP** (v8.0 or higher) — *optional, only required if running the PHP version*

---

### Running the React Application

1. **Clone the repository:**
   ```bash
   git clone https://github.com/your-username/archinexus.git
   cd archinexus
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Configure environment variables (optional):**
   Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
   Add your Supabase URL and anon key if connecting to a live Supabase backend. If omitted, the application seamlessly runs on built-in mock data.

4. **Start the development server:**
   ```bash
   npm run dev
   ```
   Open your browser at `http://localhost:5173/`.

5. **Build for production:**
   ```bash
   npm run build
   ```

---

### Running the PHP Application

1. Open your terminal in the project root directory.
2. Start the PHP built-in web server:
   ```bash
   php -S localhost:8000
   ```
3. Open your browser and navigate to:
   ```
   http://localhost:8000/
   ```

---

## 🗄️ Database Setup (Optional)

If you wish to use a live PostgreSQL or Supabase database instead of the built-in mock data:

1. Create a PostgreSQL database or a new project on [Supabase](https://supabase.com/).
2. Run the SQL migration scripts located in `supabase/migrations/` in order:
   - `20260606084752_seed_departments.sql`
   - `20260606084835_seed_complete_data.sql`
   - `20260606084909_seed_clients_projects_clean.sql`
   - `20260606084948_seed_assignments_clean.sql`
   - `20260606085023_seed_resources.sql`
3. Add your database credentials to `.env`:
   ```ini
   DB_HOST=db.your-project.supabase.co
   DB_PORT=5432
   DB_NAME=postgres
   DB_USER=postgres
   DB_PASSWORD=your-db-password
   ```

---

## 🧪 Available Scripts

| Command | Description |
|---|---|
| `npm run dev` | Starts the Vite development server at `localhost:5173` |
| `npm run build` | Builds optimized production bundles into `dist/` |
| `npm run preview` | Previews the production build locally |
| `npm run typecheck` | Validates TypeScript types across the project |
| `npm run lint` | Runs ESLint to check for code style and errors |

---

## 📄 License

This project is licensed under the MIT License — see the [LICENSE](LICENSE) file for details.
