# Platform Overview: Bug Smasher QA System

Bug Smasher is an internal platform designed to standardize quality assurance (QA) processes across web, event, and product launches. It consolidates how bugs are reported, how checklists are managed, and how teams collaborate during pre-launch reviews.  

By connecting users, teams, QA lists, and bug reports in a single system, it ensures that issues are caught early, responsibilities are clearly assigned, and no task slips through the cracks.

---

## 1. User Creation & Authentication

User access is tightly managed through a secure authentication flow:

- **Sign-Up Flow**: Users register through `/auth/signup`, providing basic information like name, email, and password. The signup form validates inputs and creates a new user record in the system.

- **Login Flow**: After registration, users are redirected to the login page where they enter their credentials. Upon successful login, a session token is issued.

- **Token Management**: Authentication is handled via the `AuthContext`, which stores JWT tokens in `localStorage`. This allows users to stay logged in across sessions securely.

- **Session Security**: The platform includes token refresh mechanisms and route protection. Users are redirected to onboarding if required information is missing, ensuring a complete user profile before proceeding.

This ensures only verified, context-aware users can access sensitive QA and bug data.

---

## 2. QA Lists & Checklist Items

QA lists are reusable checklists tailored to different types of launches (e.g., websites, campaigns):

- **Checklist Structure**:
  - Each checklist has a title, description, and status (draft/active/archived)
  - Items are ordered and can be required or optional
  - Items have unique identifiers (e.g., #U0001)
  - Items can be of different types (text, checkbox)
  - Each item tracks its status (passed/failed/pending)

- **Access Rules**: A user can view or contribute to a QA list if:
  - They created it
  - They are assigned to it through their team
  - They are directly assigned specific checklist items

- **Item Actions**:
  - Each checklist item can be marked as "passed" or "failed"
  - Users can write **failure reasons** and **contextual notes** for failed checks
  - When issues are found, bugs can be directly linked to failed items, providing traceability

This checklist-first structure ensures every launch meets internal standards before going live.

---

## 3. Bug Reporting

Bug Smasher simplifies and structures bug reports, making them consistent and actionable:

- **Entry Points**:
  - Bugs can be reported while reviewing a checklist
  - Or via the main bug reporting form (standalone reporting)

- **Bug Details Captured**:
  - Title and description of the issue
  - Steps to reproduce the bug
  - Expected behavior vs. actual behavior
  - Environment (browser, OS, device)
  - Priority level (low, medium, high)
  - Bug status (open, in progress, resolved)
  - Assignment to a user or team

- **Team Collaboration**:
  - Assigned users are notified
  - Bugs are tracked with status updates and timestamps
  - Team members can comment on bug threads, document solutions, and mark bugs as resolved

This keeps everyone on the same page and ensures no bug is lost or unclear.

---

## 4. Teams & Roles

Teams are the backbone of assignment and accountability:

- **Structure**:
  - Each team has a name and description
  - Multiple users can join a team
  - Users can hold different **roles** within each team (e.g., Developer, QA Lead)

- **User Capabilities**:
  - Users can be part of multiple teams
  - Roles determine what actions they can take (e.g., assign bugs, create QA lists)
  - Membership in a team is the basis for assigning tasks and bugs

Teams provide structure to what would otherwise be ad-hoc assignments, ensuring ownership and accountability.

---

## 5. Team Page

The `/team` page provides visibility into how each team is composed and what they're responsible for:

- **Page Overview**:
  - Shows all teams the user belongs to
  - Lists members within each team, along with their roles and statuses
  - Displays team-related activity, such as bugs assigned/resolved by members

- **Admin Controls**:
  - Team leaders or admins can manage memberships
  - They can assign roles or remove users from teams
  - They can review performance metrics (e.g., how many bugs resolved per member)

This page gives PMs, QA leads, and devs a centralized view of who's doing what.

---

## 6. Assignment Flow

Bug Smasher enables granular and traceable task assignment:

- **During QA List Creation**:
  - The creator can assign the list to a team
  - Specific team members can then be assigned checklist items
  - Assignments include due dates and notes

- **During Bug Submission**:
  - Bugs can be assigned to an entire team or specific members
  - The assignee is notified immediately
  - Each assignment is timestamped for tracking

- **Ongoing Tracking**:
  - Teams can monitor who is responsible for which bugs or QA items
  - Assignment status can be filtered and reported on
  - Assignment statuses include: accepted, rejected

This flow ensures accountability and prevents duplication of effort.

---

## 7. Technical Implementation

### Project Structure

The app is structured for modularity and maintainability:

- `app/Models` – Contains Eloquent models for QA checklists, items, assignments, and responses
- `app/Http/Controllers` – Handles request processing and business logic
- `app/Filament/Resources` – Admin panel resources for managing QA checklists and templates
- `database/migrations` – Database schema definitions
- `app/Services` – Business logic services (e.g., Asana integration)

### Key Models

- `QaChecklist`: Core model for managing checklists
  - Tracks title, description, status, version, and metadata
  - Manages relationships with items, assignments, and responses

- `QaChecklistItem`: Individual checklist items
  - Stores item text, type, required status, and order
  - Generates unique identifiers (e.g., #U0001)
  - Tracks completion status and responses

- `QaChecklistAssignment`: Manages user assignments
  - Links users to checklists
  - Tracks assignment status, due dates, and notes
  - Maintains assignment history

- `QaChecklistTemplate`: Reusable checklist templates
  - Stores template name, description, and items
  - Can be used to create new checklists quickly

### Database Schema

The system uses a relational database with the following key tables:

- `qa_checklists`: Stores checklist metadata and status
- `qa_checklist_items`: Individual checklist items
- `qa_checklist_assignments`: User assignments and due dates
- `qa_checklist_responses`: User responses to checklist items
- `qa_checklist_templates`: Reusable checklist templates

### API Integration

- REST API endpoints for every major function (bugs, users, teams, QA)
- Environment-based base URL configurations for dev/staging/prod
- CSRF protection, headers, and consistent error handling across endpoints

This architecture makes it easy to maintain, scale, and integrate with external systems like Asana or Teams.

---

## Summary

Bug Smasher transforms how QA is handled by replacing scattered communication with a focused system of checklists, assignments, and structured bug reports.

It ensures:
- Everyone knows their responsibilities
- Issues are reported in a standardized format
- Progress is transparent to all stakeholders

By building accountability and clarity into each step of the QA process, Bug Smasher reduces launch-day chaos and improves product quality across teams. 
