---
applyTo: 'resources/**'
---

# Resources Folder Architecture - Creative Matching Platform

## Overview
The resources folder contains all frontend assets, React components, and views for the Laravel + Inertia.js + React application. This follows a modern hybrid SPA architecture with server-side rendering support.

## Directory Structure

### `/css/`
- **app.css** - Main stylesheet with TailwindCSS v4 configuration and custom CSS variables for theming

### `/js/`
Core JavaScript/TypeScript application files:
- **app.tsx** - Main client-side entry point with Inertia app setup
- **ssr.tsx** - Server-side rendering entry point for production builds

#### `/js/actions/` (Auto-generated)
Laravel Wayfinder-generated form actions from PHP controllers:
- **App/Http/** - Form actions for app controllers (auth, profiles, projects)
- **Illuminate/Routing/** - Core Laravel routing actions
- **Laravel/Fortify/** - Authentication form actions
- ⚠️ **Git ignored** - Files are auto-generated during build

#### `/js/components/`
Reusable React components organized by functionality:
- **UI Components**: Core app shell (header, sidebar, navigation)
- **Auth Components**: User authentication flows and 2FA setup
- **Business Components**: User management, profile display
- **ui/** - Radix UI-based component library (shadcn/ui style)

#### `/js/hooks/`
Custom React hooks for shared functionality:
- **use-appearance.tsx** - Dark/light mode theme management
- **use-mobile.tsx** - Responsive design utilities
- **use-two-factor-auth.ts** - 2FA state management
- **use-clipboard.ts** - Clipboard operations

#### `/js/layouts/`
Page layout components for different contexts:
- **app/** - Authenticated app layouts (header, sidebar variants)
- **auth/** - Authentication layouts (simple, card, split variants)
- **settings/** - Settings page specific layouts

#### `/js/lib/`
Utility functions and shared logic:
- **utils.ts** - Common utilities (className merging, formatting, etc.)

#### `/js/pages/`
Inertia.js page components organized by feature:
- **Root pages**: dashboard.tsx, welcome.tsx
- **auth/** - Authentication flows (login, register, 2FA, password reset)
- **settings/** - User settings (profile, password, appearance, 2FA)

#### `/js/routes/` (Auto-generated)
Laravel Wayfinder-generated route helpers:
- Type-safe route functions for navigation
- Organized by feature areas (auth, profile, etc.)
- ⚠️ **Git ignored** - Files are auto-generated from Laravel routes

#### `/js/types/`
TypeScript type definitions:
- **index.d.ts** - Shared types for Inertia pages and components
- **vite-env.d.ts** - Vite environment type definitions

#### `/js/wayfinder/` (Auto-generated)
Laravel Wayfinder type definitions and utilities:
- ⚠️ **Git ignored** - Files are auto-generated during build

### `/views/`
Blade templates for the Laravel application:
- **app.blade.php** - Main app template with Inertia.js and Vite integration

## Key Architectural Patterns

### Component Organization
- **ui/** - Pure UI components (buttons, inputs, cards)
- **Root level** - Business logic components with app-specific functionality
- **Layouts** - Page structure components with role-based variants

### Page Structure
- **Inertia Pages** - Server-rendered React components receiving props from Laravel
- **Nested Routing** - Organized by feature areas (auth, settings, etc.)
- **Shared Data** - Global props available across all pages via `usePage<SharedData>()`

### Form Handling
- **Laravel Wayfinder Integration** - Type-safe form actions auto-generated from controllers
- **Form Components** - Consistent error handling and loading states
- **Validation** - Server-side validation with client-side error display

### Styling Approach
- **TailwindCSS v4** - Utility-first CSS with custom theme variables
- **Dark Mode Support** - System-based theme switching
- **Radix UI** - Headless components for accessibility and consistent behavior
- **Component Variants** - CVA (Class Variance Authority) for component styling

### Authentication Integration
- **Laravel Fortify** - Server-side authentication with Inertia views
- **2FA Support** - Complete two-factor authentication flow
- **Role-Based UI** - Conditional rendering based on user roles (creative/opportunity_owner)

## Development Patterns for Creative Matching Platform

### User Role Considerations
- Components should handle both `creative` and `opportunity_owner` user types
- Use conditional rendering based on `auth.user.role`
- Role-specific navigation and dashboard components

### Form Patterns
```tsx
// Use Wayfinder-generated actions
<Form {...ControllerName.method.form()}>
  {({ processing, errors }) => (
    // Form implementation
  )}
</Form>
```

### Route Usage
```tsx
// Import type-safe route helpers
import { dashboard, login } from '@/routes';

// Use in components
<Link href={dashboard()}>Dashboard</Link>
```

### Component Imports
```tsx
// UI components from component library
import { Button, Card } from '@/components/ui';

// Business components
import { UserMenu } from '@/components';

// Layout components
import AppLayout from '@/layouts/app-layout';
```

## Future Extensions for Creative Matching

### Expected New Components
- **Project Components**: Project cards, creation forms, application flows
- **Creative Profile**: Portfolio display, skills management
- **Search Interface**: Semantic search UI, filters, results display
- **Messaging**: Real-time communication components
- **Matching**: Recommendation displays, match scoring UI

### Expected New Pages
- **projects/** - Project browsing, creation, management
- **profiles/** - Profile viewing and editing
- **search/** - Search and discovery interfaces
- **messages/** - Communication interfaces
- **matches/** - Recommendation and matching interfaces

## Build Integration

### Auto-generation Process
- Routes and actions are generated during `npm run dev` or `npm run build`
- Wayfinder watches Laravel routes and controllers for changes
- TypeScript types are automatically updated

### Development Workflow
- Use `npm run dev` for hot-reload development
- Use `npm run build:ssr` for production builds with SSR
- Run `npm run types` for TypeScript checking
- Use `npm run lint` and `npm run format` for code quality
