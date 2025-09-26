---
applyTo: '**'
---

# Authorization Strategy - Creative Matching Platform

## User Role Architecture

This platform operates with two primary user types that require distinct permissions and access patterns:

- **Creative Professionals** (`creative`) - Freelancers, artists, designers seeking opportunities
- **Opportunity Owners** (`opportunity_owner`) - Companies, individuals posting projects

## Authorization Implementation

### Laravel Gates Approach
Use Laravel Gates for role-based authorization - simple, performant, and suitable for the dual-role structure.

### User Model Enhancement
- Add `role` enum field to users table: `['creative', 'opportunity_owner']`
- Include helper methods: `isCreative()`, `isOpportunityOwner()`
- Establish relationships to role-specific profile models

### Core Authorization Gates

#### Role-Based Gates
- `is-creative` - Basic creative user validation
- `is-opportunity-owner` - Basic opportunity owner validation

#### Feature-Specific Gates
- `manage-creative-profile` - Creative profile management
- `manage-opportunity-profile` - Opportunity owner profile management  
- `create-project` - Project/opportunity posting (opportunity owners only)
- `apply-to-project` - Project applications (creatives only)
- `view-applications` - View project applications (project owners only)
- `message-user` - Cross-role messaging permissions

### Route Protection Strategy

#### Role-Specific Route Groups
- **Creative Routes**: Profile management, project browsing, applications
- **Opportunity Owner Routes**: Project management, application reviews, creative discovery
- **Shared Routes**: Search, messaging, dashboard navigation

#### Middleware Usage
Apply `can:` middleware to route groups for role-based access control.

### Frontend Authorization

#### Inertia Integration
- Pass user role via shared data props
- Implement conditional rendering based on user role
- Role-specific dashboard components and navigation

#### UI Permissions
- Show/hide features based on user capabilities
- Dynamic button states (Apply vs View Applications)
- Role-appropriate navigation menus

### Registration & Onboarding Flow

#### Role Selection
- User chooses role during registration
- Validation ensures role is one of accepted values
- Post-registration redirect based on selected role

#### Profile Completion
- Role-specific profile setup flows
- Creative: Portfolio, skills, experience
- Opportunity Owner: Company details, verification

## Key Benefits

1. **Simplicity**: Lightweight authorization without complex permission systems
2. **Performance**: No additional database queries for basic role checks  
3. **Flexibility**: Easy to extend with more granular permissions
4. **Integration**: Seamless with existing Laravel + Inertia + React stack
5. **Scalability**: Can migrate to Policy-based system when needed

## Authorization Patterns to Follow

1. **Controller Authorization**: Always use `Gate::authorize()` in controllers
2. **Route Middleware**: Apply `can:` middleware to route groups
3. **Frontend Checks**: Conditional rendering based on user role
4. **Database Relations**: Role-specific model relationships
5. **Validation Rules**: Role-based form validation and business rules
