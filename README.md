# Bug Smasher CRM

Bug Smasher is a powerful bug tracking and quality assurance (QA) management system designed to standardize QA processes across web, event, and product launches. It provides a comprehensive solution for tracking bugs, managing QA checklists, and facilitating team collaboration.

## 🌟 Key Features

### 1. Bug Tracking
- Detailed bug reporting with reproduction steps
- Priority and status management
- Environment information tracking (browser, OS, device)
- Screenshot attachments
- Assignment and due date tracking
- Integration with Asana for task management

### 2. QA Checklist Management
- Create and manage QA checklists
- Customizable checklist items
- Required and optional items
- Status tracking (passed/failed/pending)
- Unique item identifiers
- Team assignments and due dates

### 3. Team Collaboration
- Team-based organization
- Role-based access control
- Real-time notifications
- Team performance metrics
- Member assignment tracking

### 4. Integration Features
- Asana integration for task management
- Automatic ticket creation
- Status synchronization
- Two-way updates between systems

## 🛠 Technical Stack

- **Backend**: Laravel PHP Framework
- **Frontend**: Next.js
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum
- **Admin Panel**: Filament
- **API**: RESTful API

## 📊 Dashboard Features

- Bug statistics overview
- QA checklist completion rates
- Team performance metrics
- Bug trends visualization
- Asana ticket analytics

## 🔒 Security Features

- Secure authentication flow
- JWT token management
- Role-based access control
- Protected API endpoints
- CSRF protection

## 🚀 Getting Started

1. Clone the repository
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Set up environment variables:
   ```bash
   cp .env.example .env
   ```
4. Run migrations:
   ```bash
   php artisan migrate
   ```
5. Start the development server:
   ```bash
   php artisan serve
   npm run dev
   ```

## 📝 API Documentation

The API provides endpoints for:
- User authentication
- Bug management
- QA checklist operations
- Team management
- Profile management

All API endpoints are protected and require authentication using Laravel Sanctum.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.
