# Receptionist Module Refactoring

This document outlines the refactoring approach for the Receptionist module components to make them more maintainable, reusable, and concise.

## Refactoring Approach

### 1. Component Extraction

We've extracted reusable components to reduce duplication and improve maintainability:

- `NavigationButtons.vue`: Reusable navigation buttons component
- `ConfirmDialog.vue`: Reusable confirmation dialog component
- `ClientTable.vue`: Reusable client data table component
- `ReservationTable.vue`: Reusable reservation data table component

### 2. Composables for Business Logic

We've created composables to encapsulate business logic and state management:

- `useClientManagement.js`: Handles client-related operations (approve, reject, ban, etc.)
- `useReservationManagement.js`: Handles reservation-related operations (approve, cancel, complete, etc.)

### 3. Simplified Templates

- Reduced template complexity by leveraging the extracted components
- Improved readability by using consistent naming and structure
- Removed duplicate code blocks

### 4. Improved Props and Emits

- Clearly defined props with proper types and defaults
- Used emits for component communication
- Leveraged slots for flexible content rendering

## How to Use the Refactored Components

### Example: Using the ClientTable Component

```vue
<ClientTable 
  :clients="pendingClients" 
  :empty-message="'No pending client registrations found.'"
  @page-change="goToPage(`/receptionist/clients?page=${$event}`)"
>
  <template #actions="{ client }">
    <button @click="approveClient(client.id)">Approve</button>
    <button @click="rejectClient(client.id)">Reject</button>
  </template>
</ClientTable>
```

### Example: Using the NavigationButtons Component

```vue
<NavigationButtons 
  :buttons="[
    {
      path: '/receptionist/clients',
      label: 'Manage Clients',
      primary: true
    },
    {
      path: '/receptionist/reservations',
      label: 'Reservations',
      primary: false
    }
  ]" 
  :is-admin="isAdmin" 
/>
```

### Example: Using the Client Management Composable

```javascript
import { useClientManagement } from '@/composables/useClientManagement';

const {
  openApproveDialog,
  confirmApprove,
  banClient,
  deleteClient
} = useClientManagement();
```

## Implementation Steps

1. Review the refactored components in the `.refactored.vue` files
2. Test the refactored components to ensure they work as expected
3. Replace the original components with the refactored versions
4. Update any imports or references to the components

## Benefits of Refactoring

- **Reduced Code Duplication**: Common functionality is now in reusable components
- **Improved Maintainability**: Smaller, focused components are easier to understand and modify
- **Better Organization**: Clear separation of concerns between UI and business logic
- **Enhanced Readability**: Consistent naming and structure makes the code easier to follow
- **Easier Testing**: Isolated components and composables are easier to test