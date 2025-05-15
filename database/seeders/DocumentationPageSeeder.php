<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\DocumentationPage;
use App\Models\User;

class DocumentationPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user to use as creator
        $user = User::first();

        if (!$user) {
            $this->command->error('No users found. Please run the UserSeeder first.');
            return;
        }

        $pages = [
            [
                'title' => 'QA Checklist Creation and Assignment Flow',
                'content' => <<<EOT
# QA Checklist Creation and Assignment Flow

A QA checklist is created with a title, description, and other metadata. The checklist can be assigned to one or more users through `QaChecklistAssignment`.

Each assignment includes:
- The assigned user
- Assignment status (accepted/rejected)
- Assignment date
- Due date
- Notes
- Who assigned it
EOT,
                'category' => 'qa-checklist',
                'order' => 1,
            ],
            [
                'title' => 'QA Checklist Items',
                'content' => <<<EOT
# QA Checklist Items

Each checklist contains multiple items (`QaChecklistItem`).

Items have:
- Text content
- Type (checkbox, text, etc.)
- Required status
- Order number
- Status (passed/failed)
- Unique identifier (e.g., #U0001)
EOT,
                'category' => 'qa-checklist',
                'order' => 2,
            ],
            [
                'title' => 'Asana Ticket Creation Flow',
                'content' => <<<EOT
# Asana Ticket Creation Flow

Asana tickets can be created from two sources:
1. From a Bug
2. From a QA Checklist Item
EOT,
                'category' => 'asana-integration',
                'order' => 3,
            ],
            [
                'title' => 'Asana Ticket from Bug',
                'content' => <<<EOT
# Asana Ticket from Bug

When created from a bug:
- Title format: "[Bug] {bug_title} - {ticket_number}"
- Includes bug details in notes:
  - Description
  - Steps to reproduce
  - Expected behavior
  - Actual behavior
  - Additional notes
EOT,
                'category' => 'asana-integration',
                'order' => 4,
            ],
            [
                'title' => 'Asana Ticket from QA Checklist',
                'content' => <<<EOT
# Asana Ticket from QA Checklist

When created from a QA checklist:
- Title format: "[QA] {checklist_title} - {ticket_number}"
- Creates a main task in Asana
- Automatically creates subtasks for each checklist item
- Each subtask corresponds to a checklist item
- Subtask status syncs with checklist item status
EOT,
                'category' => 'asana-integration',
                'order' => 5,
            ],
            [
                'title' => 'Status Synchronization',
                'content' => <<<EOT
# Status Synchronization

When a checklist item is marked as completed in the frontend:
- The system finds the associated Asana ticket
- Locates the corresponding subtask
- Updates the subtask's completion status in Asana

This works in both directions:
- Frontend completion → Asana subtask completion
- Asana subtask completion → Frontend item status
EOT,
                'category' => 'asana-integration',
                'order' => 6,
            ],
            [
                'title' => 'System Relationships',
                'content' => <<<EOT
# System Relationships

```
User
├── QaChecklistAssignment (assigned checklists)
├── QaChecklist (created checklists)
└── Bug (reported/assigned bugs)

QaChecklist
├── QaChecklistItem (checklist items)
├── QaChecklistAssignment (user assignments)
└── QaChecklistResponse (item responses)

QaChecklistItem
├── Bug (related bugs)
└── AsanaTicket (created tickets)

Bug
└── AsanaTicket (created tickets)

AsanaTicket
├── Bug (if created from bug)
└── QaChecklistItem (if created from checklist)
```
EOT,
                'category' => 'user-management',
                'order' => 7,
            ],
            [
                'title' => 'Status Tracking',
                'content' => <<<EOT
# Status Tracking

- QA Checklist statuses: draft, active, archived
- Assignment statuses: accepted, rejected
- Asana ticket statuses: open, in_progress, resolved, closed
- Checklist item statuses: passed, failed, pending

This system provides a comprehensive workflow for:
- Creating and assigning QA checklists
- Tracking checklist item completion
- Creating Asana tickets from either bugs or QA checklist items
- Maintaining synchronization between the frontend and Asana
- Managing the entire QA process from checklist creation to task completion
EOT,
                'category' => 'bug-tracking',
                'order' => 8,
            ],
        ];

        foreach ($pages as $page) {
            DocumentationPage::create([
                'title' => $page['title'],
                'content' => $page['content'],
                'category' => $page['category'],
                'order' => $page['order'],
                'is_active' => true,
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
        }
    }
}
