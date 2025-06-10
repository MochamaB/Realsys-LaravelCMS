# Political Party User Management Module - Comprehensive Structure

## Overview

This document outlines the structure of the UserManagement module for the RealsysCMS political party system. The module provides registration, authentication, and role-specific features for party members, leadership, aspirants, voters, and administrators.

## User Roles & Capabilities

### 1. Public User/Guest
- Access to public content only
- View policy & manifesto section
- Browse events & rally schedule
- Access volunteer & membership signup forms
- Read news & updates

### 2. Party Member/Delegates
- Access to exclusive member resources
- Event coordination & volunteer dashboard
- Participation in voting & decision-making
- Direct communication with leadership

### 3. Executive / Leadership
- Access to strategic planning & decision-making tools
- Executive communication portal
- Member & volunteer management dashboard
- Policy development & internal voting platform

### 4. Admin User
- Access to admin CMS section
- Blog administration
- Document management (EDMS)
- Event management

### 5. Aspirant/Candidate
- Access to delegate and voter information by geography
- Email and SMS campaign tools
- Candidate profile and minisite
- Poster creation tools

### 6. Voter
- Voter registration
- View candidacy and party officials by geography
- Access to ecommerce shop

## Module Organization

### Core Components

#### User Authentication & Authorization
- Built on existing authentication system
- Extended with role management (Spatie Permissions)
- Profile extensions for different user types

#### Common Features
- User profile management
- Notification system
- Geographical data structure (counties, constituencies, wards)

### Role-Specific Modules

#### UserManagement (core module)
- Base user profiles
- Authentication
- Common features

#### MemberPortal
- Exclusive resource access
- Volunteer management
- Internal voting

#### LeadershipPortal
- Strategic dashboards
- Member management
- Policy development tools

#### CandidacyModule
- Campaign management
- Delegate communication
- Profile/minisite management

#### VoterEngagement
- Candidate information
- Geographic filtering
- Merchandise shop

## Database Structure

### Core User Tables

#### users
- id (PK)
- name
- email
- password
- phone
- remember_token
- email_verified_at
- created_at
- updated_at

#### profiles
- id (PK)
- user_id (FK)
- id_passport_number
- membership_number
- date_of_birth
- postal_address
- mobile_number
- gender
- ethnicity
- is_pwd (boolean)
- ncpwd_number
- religion
- county_of_registration
- constituency_of_registration
- ward_of_registration
- enlisting_date
- recruiting_person
- profile_type (party_member, executive, aspirant, voter)
- additional_data (JSON)
- created_at
- updated_at

### Geographic Structure

#### counties
- id (PK)
- name
- code
- created_at
- updated_at

#### constituencies
- id (PK)
- county_id (FK)
- name
- code
- created_at
- updated_at

#### wards
- id (PK)
- constituency_id (FK)
- name
- code
- created_at
- updated_at

### Feature-Specific Tables

#### resources
- id (PK)
- title
- description
- file_path
- resource_type
- visibility (public, member, executive, etc.)
- created_at
- updated_at

#### events
- id (PK)
- title
- description
- start_datetime
- end_datetime
- location
- county_id (FK, optional)
- constituency_id (FK, optional)
- ward_id (FK, optional)
- event_type
- created_at
- updated_at

#### votes
- id (PK)
- title
- description
- start_datetime
- end_datetime
- status (pending, active, closed)
- vote_type
- created_at
- updated_at

#### campaigns
- id (PK)
- aspirant_id (FK to users)
- title
- description
- geographic_scope (county, constituency, ward)
- geographic_id (FK)
- start_date
- end_date
- status
- created_at
- updated_at

#### products
- id (PK)
- name
- description
- price
- image_path
- stock
- category
- created_at
- updated_at

## Frontend Organization

### Public Areas
- Home page with public content
- News, events, policies
- Registration/login forms

### Role-Specific Dashboards
- Member dashboard
- Leadership dashboard
- Admin dashboard
- Aspirant dashboard
- Voter dashboard

### Shared Components
- User profile management
- Notification center
- Geographic selection tools

## Integration with Existing CMS

The UserManagement module will integrate with the existing CMS through:

1. **Content Management**
   - Public pages management
   - Blog/news management via existing CMS

2. **Document Management**
   - EDMS integration for party documents
   - Role-based access control

3. **Event Management**
   - Calendar integration
   - Location-based event filtering

4. **Membership Management**
   - Member profiles
   - Role assignments
   - Geographic data
